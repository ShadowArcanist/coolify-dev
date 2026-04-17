<?php

namespace App\Actions\Application;

use App\Models\Application;
use App\Models\Server;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\Yaml\Yaml;

class LoadBalancerConfig
{
    use AsAction;

    public function handle(Application $application, Server $lbServer, array $settings): array
    {
        $config = static::buildConfig($application, $lbServer, $settings);
        static::writeFile($lbServer, $application, $config);

        return $config;
    }

    public static function filePath(Server $server, Application $application): string
    {
        return rtrim($server->proxyPath(), '/').'/dynamic/loadbalancer-'.$application->uuid.'.yaml';
    }

    public static function dirPath(Server $server): string
    {
        return rtrim($server->proxyPath(), '/').'/dynamic';
    }

    public static function writeFile(Server $server, Application $application, array $config): void
    {
        $file = static::filePath($server, $application);
        $escapedFile = escapeshellarg($file);
        $dir = static::dirPath($server);
        $yaml = Yaml::dump($config, 10, 2);
        $base64 = base64_encode($yaml);
        instant_remote_process([
            "mkdir -p {$dir}",
            "echo '{$base64}' | base64 -d | tee {$escapedFile} > /dev/null",
        ], $server);
    }

    public static function removeFile(Server $server, Application $application): void
    {
        $file = escapeshellarg(static::filePath($server, $application));
        instant_remote_process(["rm -f {$file}"], $server, throwError: false);
    }

    public static function regenerateIfEnabled(Application $application): void
    {
        $application->refresh()->load(['destination', 'additional_servers']);

        $settings = $application->load_balancer_settings;
        if (! data_get($settings, 'enabled', false)) {
            return;
        }

        $lbServerId = data_get($settings, 'load_balancer_server_id');
        if (! $lbServerId) {
            return;
        }

        $lbServer = Server::find($lbServerId);
        if (! $lbServer || ! $lbServer->isFunctional() || ! $lbServer->proxySet()) {
            return;
        }

        if (empty($application->fqdn)) {
            return;
        }

        try {
            static::run($application, $lbServer, $settings);
        } catch (\Throwable) {
            // best-effort; the user will see the failure when saving manually.
        }
    }

    public static function fetchBackendStatuses(Application $application, Server $lbServer): array
    {
        $service = 'loadbalancer-'.$application->uuid.'@file';
        $escaped = escapeshellarg($service);
        $command = "docker exec coolify-proxy wget -qO- --timeout=3 http://127.0.0.1:8080/api/http/services/{$escaped}";

        try {
            $output = instant_remote_process([$command], $lbServer, throwError: false);
        } catch (\Throwable $e) {
            return [];
        }

        if (! is_string($output) || trim($output) === '') {
            return [];
        }

        $decoded = json_decode($output, true);
        if (! is_array($decoded)) {
            return [];
        }

        $serverStatus = data_get($decoded, 'serverStatus');
        if (! is_array($serverStatus)) {
            return [];
        }

        return $serverStatus;
    }

    public static function buildConfig(Application $application, Server $lbServer, array $settings): array
    {
        $uuid = $application->uuid;
        $baseName = "loadbalancer-{$uuid}";

        $fqdns = collect($application->fqdns ?? [])
            ->map(fn ($f) => static::extractHost((string) $f))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($fqdns)) {
            throw new \InvalidArgumentException('Application has no FQDN configured.');
        }

        $backends = static::resolveBackends($application);
        if (empty($backends)) {
            throw new \InvalidArgumentException('No backend servers available for load balancing.');
        }

        $requestedStrategy = $settings['algorithm'] ?? 'wrr';
        $strategy = in_array($requestedStrategy, ['wrr', 'p2c', 'hrw', 'leasttime'], true)
            ? $requestedStrategy
            : 'wrr';
        $weights = $settings['weights'] ?? [];
        $stickyEnabled = (bool) ($settings['sticky_enabled'] ?? false);
        $stickyCookie = $settings['sticky_cookie_name'] ?? 'lb_session';

        $healthEnabled = (bool) ($settings['healthcheck_enabled'] ?? false);
        $healthPath = $settings['healthcheck_path'] ?? '/';
        $healthInterval = ((int) ($settings['healthcheck_interval'] ?? 10)).'s';
        $healthTimeout = ((int) ($settings['healthcheck_timeout'] ?? 3)).'s';
        $healthStatus = (int) ($settings['healthcheck_status'] ?? 200);
        $healthScheme = $settings['healthcheck_scheme'] ?? 'http';
        $healthHostname = trim((string) ($settings['healthcheck_hostname'] ?? ''));
        $healthHeaders = $settings['healthcheck_headers'] ?? [];

        $certResolver = $settings['tls_cert_resolver'] ?? 'letsencrypt';

        $hostRule = 'Host('.implode(', ', array_map(fn ($d) => "`{$d}`", $fqdns)).')';

        $routers = [
            $baseName => [
                'rule' => $hostRule,
                'entryPoints' => ['https'],
                'service' => $baseName,
                'priority' => 1000,
                'tls' => [
                    'certResolver' => $certResolver,
                ],
            ],
            "{$baseName}-http" => [
                'rule' => $hostRule,
                'entryPoints' => ['http'],
                'service' => $baseName,
                'priority' => 1000,
                'middlewares' => ["{$baseName}-https-redirect"],
            ],
        ];

        $middlewares = [
            "{$baseName}-https-redirect" => [
                'redirectScheme' => ['scheme' => 'https', 'permanent' => true],
            ],
        ];

        $servers = [];
        foreach ($backends as $backend) {
            $entry = ['url' => static::backendUrl($application, $backend, $lbServer)];
            if ($strategy === 'wrr') {
                $entry['weight'] = max(1, (int) ($weights[$backend->id] ?? 1));
            }
            $servers[] = $entry;
        }

        $loadBalancer = [
            'strategy' => $strategy,
            'passHostHeader' => true,
            'servers' => $servers,
        ];
        if ($stickyEnabled) {
            $loadBalancer['sticky'] = [
                'cookie' => ['name' => $stickyCookie],
            ];
        }
        if ($healthEnabled) {
            $healthCheck = [
                'path' => $healthPath,
                'interval' => $healthInterval,
                'timeout' => $healthTimeout,
                'status' => $healthStatus,
                'scheme' => $healthScheme,
                'followRedirects' => false,
            ];
            if ($healthHostname !== '') {
                $healthCheck['hostname'] = $healthHostname;
            }
            if (is_array($healthHeaders) && ! empty($healthHeaders)) {
                $healthCheck['headers'] = $healthHeaders;
            }
            $loadBalancer['healthCheck'] = $healthCheck;
        }

        $services = [
            $baseName => ['loadBalancer' => $loadBalancer],
        ];

        return [
            'http' => [
                'routers' => $routers,
                'services' => $services,
                'middlewares' => $middlewares,
            ],
        ];
    }

    public static function resolveBackends(Application $application): array
    {
        $servers = collect();
        $primary = $application->destination?->server;
        if ($primary) {
            $servers->push($primary);
        }
        foreach ($application->additional_servers as $srv) {
            $servers->push($srv);
        }

        return $servers->unique('id')->values()->all();
    }

    public static function backendUrl(Application $application, Server $server, ?Server $lbServer = null): string
    {
        $host = $server->id === 0 ? 'host.docker.internal' : $server->ip;

        $mapping = collect($application->ports_mappings_array)->first();
        if ($mapping && str_contains((string) $mapping, ':')) {
            [$hostPort] = explode(':', (string) $mapping, 2);
            if (ctype_digit($hostPort)) {
                return 'http://'.$host.':'.$hostPort;
            }
        }

        // No host port mapping. Going to <backend-ip>:80 normally routes through the
        // backend's Traefik. But when the backend IS the LB server itself, that lands
        // back on the LB's own file-provider router and loops. Bypass Traefik entirely
        // by addressing the app container over the shared `coolify` Docker network.
        // Use the app's UUID as the network alias (stable across re-deploys, unlike
        // container_name when is_consistent_container_name_enabled is off).
        if ($lbServer !== null && (int) $lbServer->id === (int) $server->id) {
            $ports = $application->settings->is_static ? [80] : $application->ports_exposes_array;
            $port = (int) ($ports[0] ?? 80);

            return 'http://'.$application->uuid.':'.$port;
        }

        return 'http://'.$host.':80';
    }

    protected static function extractHost(string $fqdn): string
    {
        $fqdn = trim($fqdn);
        if ($fqdn === '') {
            return '';
        }
        if (str_contains($fqdn, '://')) {
            $host = parse_url($fqdn, PHP_URL_HOST);
            if ($host) {
                return strtolower($host);
            }
        }

        return strtolower($fqdn);
    }
}
