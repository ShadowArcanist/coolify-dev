<?php

namespace App\Livewire\Project\Application;

use App\Actions\Application\LoadBalancerConfig;
use App\Actions\Application\StopApplicationOneServer;
use App\Models\Application;
use App\Models\Server;
use App\Services\ProxyDashboardCacheService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Livewire\Component;

class LoadBalancing extends Component
{
    use AuthorizesRequests;

    public Application $application;

    public bool $enabled = false;

    public ?int $load_balancer_server_id = null;

    public string $algorithm = 'wrr';

    public bool $sticky_enabled = false;

    public string $sticky_cookie_name = 'lb_session';

    public array $weights = [];

    public string $tls_cert_resolver = 'letsencrypt';

    public bool $healthcheck_enabled = false;

    public string $healthcheck_path = '/';

    public int $healthcheck_interval = 10;

    public int $healthcheck_timeout = 3;

    public int $healthcheck_status = 200;

    public string $healthcheck_scheme = 'http';

    public string $healthcheck_hostname = '';

    public string $healthcheck_headers = '';

    public array $backend_statuses = [];

    public bool $backend_status_available = false;

    public array $containers_present = [];

    public function mount()
    {
        try {
            $this->application->loadMissing(['destination', 'additional_servers']);
            $this->authorize('view', $this->application);
            $this->loadSettings();
        } catch (\Throwable $e) {
            return handleError($e, $this);
        }
    }

    public function refreshBackendStatuses(): void
    {
        $this->backend_statuses = [];
        $this->backend_status_available = false;

        $this->application->refresh()->load(['destination', 'additional_servers']);

        $this->containers_present = $this->probeContainersPresent();

        if (! $this->enabled || ! $this->healthcheck_enabled || $this->load_balancer_server_id === null) {
            return;
        }

        $lbServer = Server::ownedByCurrentTeam()->whereId($this->load_balancer_server_id)->first();
        if (! $lbServer) {
            return;
        }

        $statuses = LoadBalancerConfig::fetchBackendStatuses($this->application, $lbServer);
        if (! empty($statuses)) {
            $this->backend_statuses = $statuses;
            $this->backend_status_available = true;
        }
    }

    protected function probeContainersPresent(): array
    {
        $result = [];
        foreach ($this->deploymentServers() as $srv) {
            $id = (int) $srv->id;
            $result[$id] = false;
            if (! $srv->isFunctional()) {
                continue;
            }
            try {
                $cmd = "docker ps --filter label=coolify.applicationId={$this->application->id} --filter label=coolify.pullRequestId=0 --format '{{.ID}}' | head -n 1";
                $output = instant_remote_process([$cmd], $srv, throwError: false);
                $result[$id] = is_string($output) && trim($output) !== '';
            } catch (\Throwable) {
                // keep false
            }
        }

        return $result;
    }

    public function isContainerRunningOn(Server $srv): bool
    {
        $id = (int) $srv->id;
        if (array_key_exists($id, $this->containers_present)) {
            return $this->containers_present[$id];
        }

        // Fallback to tracked DB status before the first refresh completes.
        $destinationServerId = $this->application->destination?->server?->id;
        if ($destinationServerId && (int) $destinationServerId === $id) {
            $status = (string) ($this->application->getRawOriginal('status') ?? '');
        } else {
            $pivotServer = $this->application->additional_servers->firstWhere('id', $srv->id);
            $status = (string) ($pivotServer?->pivot?->status ?? '');
        }

        return $status !== '' && ! str_starts_with($status, 'exited');
    }

    public function backendStatusFor(string $url): ?string
    {
        if (empty($this->backend_statuses)) {
            return null;
        }

        $normalizedTarget = $this->normalizeBackendUrl($url);
        foreach ($this->backend_statuses as $key => $state) {
            if ($this->normalizeBackendUrl((string) $key) === $normalizedTarget) {
                return $state;
            }
        }

        return null;
    }

    protected function normalizeBackendUrl(string $url): string
    {
        $url = trim($url);
        $url = rtrim($url, '/');
        $url = strtolower($url);
        // Drop implicit default ports so "http://h" and "http://h:80" match.
        $url = preg_replace('#^http://([^/:]+):80$#', 'http://$1', $url);
        $url = preg_replace('#^https://([^/:]+):443$#', 'https://$1', $url);

        return (string) $url;
    }

    public function shouldShowStatusWarning(): bool
    {
        if (! $this->enabled || ! $this->healthcheck_enabled || $this->load_balancer_server_id === null) {
            return false;
        }

        if ($this->backend_status_available) {
            return false;
        }

        if ($this->application->isExited()) {
            return false;
        }

        $lbServer = Server::ownedByCurrentTeam()->whereId($this->load_balancer_server_id)->first();
        if (! $lbServer) {
            return false;
        }

        return ProxyDashboardCacheService::isTraefikDashboardAvailableFromCache($lbServer);
    }

    protected function loadSettings(): void
    {
        $s = $this->application->load_balancer_settings ?? [];
        $this->enabled = (bool) ($s['enabled'] ?? false);
        $this->load_balancer_server_id = $s['load_balancer_server_id'] ?? null;
        $this->algorithm = $s['algorithm'] ?? 'wrr';
        $this->sticky_enabled = (bool) ($s['sticky_enabled'] ?? false);
        $this->sticky_cookie_name = $s['sticky_cookie_name'] ?? 'lb_session';
        $this->weights = collect($s['weights'] ?? [])->mapWithKeys(fn ($v, $k) => [(int) $k => (int) $v])->all();
        $this->tls_cert_resolver = $s['tls_cert_resolver'] ?? 'letsencrypt';
        $this->healthcheck_enabled = (bool) ($s['healthcheck_enabled'] ?? false);
        $this->healthcheck_path = $s['healthcheck_path'] ?? '/';
        $this->healthcheck_interval = (int) ($s['healthcheck_interval'] ?? 10);
        $this->healthcheck_timeout = (int) ($s['healthcheck_timeout'] ?? 3);
        $this->healthcheck_status = (int) ($s['healthcheck_status'] ?? 200);
        $this->healthcheck_scheme = $s['healthcheck_scheme'] ?? 'http';
        $this->healthcheck_hostname = $s['healthcheck_hostname'] ?? '';
        if (! $this->portMapped()) {
            $fqdnHost = $this->primaryFqdnHost();
            if ($fqdnHost !== '') {
                $this->healthcheck_hostname = $fqdnHost;
            }
        }
        $this->healthcheck_headers = $this->headersArrayToText($s['healthcheck_headers'] ?? []);

        foreach ($this->deploymentServers() as $server) {
            if (! array_key_exists($server->id, $this->weights)) {
                $this->weights[$server->id] = 1;
            }
        }
    }

    public function deploymentServers(): Collection
    {
        $servers = collect();
        $primary = $this->application->destination?->server;
        if ($primary) {
            $servers->push($primary);
        }
        foreach ($this->application->additional_servers as $srv) {
            $servers->push($srv);
        }

        return $servers->unique('id')->values();
    }

    public function isMultiServer(): bool
    {
        return $this->deploymentServers()->count() >= 2;
    }

    public function isDeployedOnLbServer(): bool
    {
        if ($this->load_balancer_server_id === null) {
            return false;
        }

        return $this->deploymentServers()->contains(fn ($srv) => (int) $srv->id === (int) $this->load_balancer_server_id);
    }

    public function isLbServerPrimary(): bool
    {
        $primary = $this->application->destination?->server;

        return $primary && (int) $primary->id === (int) $this->load_balancer_server_id;
    }

    public function availableServers(): Collection
    {
        return Server::ownedByCurrentTeam()->get();
    }

    public function portMapped(): bool
    {
        $mapping = collect($this->application->ports_mappings_array)->first();
        if (! $mapping || ! str_contains((string) $mapping, ':')) {
            return false;
        }
        [$hostPort] = explode(':', (string) $mapping, 2);

        return ctype_digit($hostPort);
    }

    protected function primaryFqdnHost(): string
    {
        $fqdn = trim((string) collect(explode(',', (string) $this->application->fqdn))->first());
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

    protected function resolveHealthcheckHostname(): string
    {
        if (! $this->portMapped()) {
            $fqdnHost = $this->primaryFqdnHost();
            if ($fqdnHost !== '') {
                return $fqdnHost;
            }
        }

        return (string) $this->healthcheck_hostname;
    }

    public function toggleEnabled()
    {
        try {
            $this->authorize('update', $this->application);

            if (! $this->enabled && ! $this->isMultiServer()) {
                $this->dispatch('error', 'Application must be deployed on at least two servers to enable load balancing.');

                return;
            }

            $previous = $this->application->load_balancer_settings ?? [];
            $previousLbServerId = $previous['load_balancer_server_id'] ?? null;

            if ($this->enabled) {
                $this->disableAndCleanup($previousLbServerId);
                $this->dispatch('success', 'Load balancer disabled.');

                return;
            }

            $this->enabled = true;
            $settings = $previous;
            $settings['enabled'] = true;
            $this->application->load_balancer_settings = $settings;
            $this->application->save();
            $this->dispatch('success', 'Load balancing enabled. Configure the options below and click Save.');
        } catch (\Throwable $e) {
            return handleError($e, $this);
        }
    }

    public function toggleSticky()
    {
        try {
            $this->authorize('update', $this->application);
            $this->sticky_enabled = ! $this->sticky_enabled;
            $this->persistQuickToggle();
            $this->dispatch('success', 'Sticky sessions '.($this->sticky_enabled ? 'enabled' : 'disabled').'.');
        } catch (\Throwable $e) {
            return handleError($e, $this);
        }
    }

    public function toggleHealthcheckSection()
    {
        try {
            $this->authorize('update', $this->application);
            $this->healthcheck_enabled = ! $this->healthcheck_enabled;
            $this->persistQuickToggle();
            $this->dispatch('success', 'Health checks '.($this->healthcheck_enabled ? 'enabled' : 'disabled').'.');
        } catch (\Throwable $e) {
            return handleError($e, $this);
        }
    }

    protected function persistQuickToggle(): void
    {
        $settings = $this->application->load_balancer_settings ?? [];
        $settings['enabled'] = $this->enabled;
        $settings['sticky_enabled'] = $this->sticky_enabled;
        $settings['sticky_cookie_name'] = $this->sticky_cookie_name;
        $settings['healthcheck_enabled'] = $this->healthcheck_enabled;
        $settings['healthcheck_path'] = $this->healthcheck_path;
        $settings['healthcheck_interval'] = $this->healthcheck_interval;
        $settings['healthcheck_timeout'] = $this->healthcheck_timeout;
        $settings['healthcheck_status'] = $this->healthcheck_status;
        $settings['healthcheck_scheme'] = $this->healthcheck_scheme;
        $settings['healthcheck_hostname'] = $this->resolveHealthcheckHostname();
        $settings['healthcheck_headers'] = $this->parseHeadersText($this->healthcheck_headers);

        $this->application->load_balancer_settings = $settings;
        $this->application->save();

        if ($this->load_balancer_server_id === null || empty($this->application->fqdn)) {
            return;
        }

        $lbServer = Server::ownedByCurrentTeam()->whereId($this->load_balancer_server_id)->first();
        if (! $lbServer || ! $lbServer->isFunctional() || ! $lbServer->proxySet()) {
            return;
        }

        try {
            LoadBalancerConfig::run($this->application, $lbServer, $settings);
        } catch (\Throwable) {
            // YAML regeneration is best-effort on quick toggle — user can hit Save to surface errors.
        }
    }

    public function deployOnLbServer()
    {
        try {
            $this->authorize('update', $this->application);

            if ($this->load_balancer_server_id === null) {
                $this->dispatch('error', 'Select a load balancer server first.');

                return;
            }

            $lbServer = Server::ownedByCurrentTeam()->whereId($this->load_balancer_server_id)->first();
            if (! $lbServer) {
                $this->dispatch('error', 'Selected server is not available in your team.');

                return;
            }

            if ($this->isDeployedOnLbServer()) {
                $this->dispatch('error', 'Application is already deployed on this server.');

                return;
            }

            $network = $lbServer->standaloneDockers()->first();
            if (! $network) {
                $this->dispatch('error', 'Load balancer server has no Docker network configured.');

                return;
            }

            $this->application->additional_networks()->attach($network->id, ['server_id' => $lbServer->id]);
            $this->application->refresh()->load(['destination', 'additional_servers']);
            LoadBalancerConfig::regenerateIfEnabled($this->application);
            $this->dispatch('success', 'Load balancer server added as a deployment target. Deploy the application to start a container there.');
        } catch (\Throwable $e) {
            return handleError($e, $this);
        }
    }

    public function removeFromLbServer()
    {
        try {
            $this->authorize('update', $this->application);

            if ($this->load_balancer_server_id === null) {
                return;
            }

            if ($this->isLbServerPrimary()) {
                $this->dispatch('error', 'Load balancer is the primary deployment server and cannot be removed from here.');

                return;
            }

            $lbServer = Server::ownedByCurrentTeam()->whereId($this->load_balancer_server_id)->first();
            if (! $lbServer) {
                return;
            }

            $network = $this->application->additional_networks()
                ->wherePivot('server_id', $lbServer->id)
                ->first();
            if (! $network) {
                return;
            }

            StopApplicationOneServer::run($this->application, $lbServer);
            $this->application->additional_networks()->detach($network->id, ['server_id' => $lbServer->id]);
            $this->application->refresh()->load(['destination', 'additional_servers']);
            LoadBalancerConfig::regenerateIfEnabled($this->application);
            $this->dispatch('success', 'Application deployment removed from the load balancer server.');
        } catch (\Throwable $e) {
            return handleError($e, $this);
        }
    }

    public function save()
    {
        try {
            $this->authorize('update', $this->application);

            if (! $this->enabled) {
                $this->dispatch('error', 'Enable load balancing first.');

                return;
            }

            if (! $this->isMultiServer()) {
                $this->addError('enabled', 'Application must be deployed on at least two servers to enable load balancing.');

                return;
            }

            $this->validate([
                'load_balancer_server_id' => ['nullable', 'integer'],
                'algorithm' => ['in:wrr,p2c,hrw,leasttime'],
                'sticky_enabled' => ['boolean'],
                'sticky_cookie_name' => ['required_if:sticky_enabled,true', 'nullable', 'string', 'max:255', 'regex:/^[A-Za-z0-9_\-]+$/'],
                'tls_cert_resolver' => ['required', 'string', 'max:64'],
                'weights' => ['array'],
                'weights.*' => ['integer', 'min:1', 'max:1000'],
                'healthcheck_enabled' => ['boolean'],
                'healthcheck_path' => ['required_if:healthcheck_enabled,true', 'string', 'max:255'],
                'healthcheck_interval' => ['integer', 'min:1', 'max:3600'],
                'healthcheck_timeout' => ['integer', 'min:1', 'max:600'],
                'healthcheck_status' => ['integer', 'min:100', 'max:599'],
                'healthcheck_scheme' => ['in:http,https'],
                'healthcheck_hostname' => ['nullable', 'string', 'max:255'],
                'healthcheck_headers' => ['nullable', 'string', 'max:4000'],
            ]);

            $previous = $this->application->load_balancer_settings ?? [];
            $previousLbServerId = $previous['load_balancer_server_id'] ?? null;

            if ($this->load_balancer_server_id === null) {
                $this->addError('load_balancer_server_id', 'Select a load balancer server.');

                return;
            }

            $lbServer = Server::ownedByCurrentTeam()->whereId($this->load_balancer_server_id)->first();
            if (! $lbServer) {
                $this->addError('load_balancer_server_id', 'Selected server is not available in your team.');

                return;
            }
            if (! $lbServer->isFunctional()) {
                $this->addError('load_balancer_server_id', 'Load balancer server is not reachable.');

                return;
            }
            if (! $lbServer->proxySet()) {
                $this->addError('load_balancer_server_id', 'Traefik proxy is not configured on the selected server.');

                return;
            }

            if (empty($this->application->fqdn)) {
                $this->addError('enabled', 'Application has no domain configured.');

                return;
            }

            $settings = [
                'enabled' => true,
                'load_balancer_server_id' => $lbServer->id,
                'algorithm' => $this->algorithm,
                'sticky_enabled' => $this->sticky_enabled,
                'sticky_cookie_name' => $this->sticky_cookie_name,
                'weights' => $this->weights,
                'tls_cert_resolver' => $this->tls_cert_resolver,
                'healthcheck_enabled' => $this->healthcheck_enabled,
                'healthcheck_path' => $this->healthcheck_path,
                'healthcheck_interval' => $this->healthcheck_interval,
                'healthcheck_timeout' => $this->healthcheck_timeout,
                'healthcheck_status' => $this->healthcheck_status,
                'healthcheck_scheme' => $this->healthcheck_scheme,
                'healthcheck_hostname' => $this->resolveHealthcheckHostname(),
                'healthcheck_headers' => $this->parseHeadersText($this->healthcheck_headers),
            ];

            $this->application->load_balancer_settings = $settings;
            $this->application->save();
            $this->application->refresh()->load(['destination', 'additional_servers']);

            if ($previousLbServerId !== null && (int) $previousLbServerId !== (int) $lbServer->id) {
                $prev = Server::find($previousLbServerId);
                if ($prev) {
                    LoadBalancerConfig::removeFile($prev, $this->application);
                }
            }

            LoadBalancerConfig::run($this->application, $lbServer, $settings);

            $this->loadSettings();
            $this->refreshBackendStatuses();
            $this->dispatch('success', 'Load balancer configured.');
        } catch (\Throwable $e) {
            return handleError($e, $this);
        }
    }

    protected function disableAndCleanup(?int $previousLbServerId): void
    {
        if ($previousLbServerId !== null) {
            $prev = Server::find($previousLbServerId);
            if ($prev) {
                LoadBalancerConfig::removeFile($prev, $this->application);
            }
        }
        $this->application->load_balancer_settings = null;
        $this->application->save();
        $this->loadSettings();
    }

    protected function parseHeadersText(?string $text): array
    {
        if (! $text) {
            return [];
        }

        $headers = [];
        foreach (preg_split('/\r\n|\r|\n/', $text) as $line) {
            $line = trim($line);
            if ($line === '' || ! str_contains($line, ':')) {
                continue;
            }
            [$name, $value] = explode(':', $line, 2);
            $name = trim($name);
            $value = trim($value);
            if ($name !== '') {
                $headers[$name] = $value;
            }
        }

        return $headers;
    }

    protected function headersArrayToText(array $headers): string
    {
        $lines = [];
        foreach ($headers as $name => $value) {
            $lines[] = $name.': '.$value;
        }

        return implode("\n", $lines);
    }

    public function backendUrlFor(Server $server): string
    {
        $lbServer = $this->load_balancer_server_id !== null
            ? Server::ownedByCurrentTeam()->whereId($this->load_balancer_server_id)->first()
            : null;

        return LoadBalancerConfig::backendUrl($this->application, $server, $lbServer);
    }

    public function render()
    {
        return view('livewire.project.application.load-balancing', [
            'availableServers' => $this->availableServers(),
            'deploymentServers' => $this->deploymentServers(),
            'isMultiServer' => $this->isMultiServer(),
            'isDeployedOnLbServer' => $this->isDeployedOnLbServer(),
            'isLbServerPrimary' => $this->isLbServerPrimary(),
            'portMapped' => $this->portMapped(),
        ]);
    }
}
