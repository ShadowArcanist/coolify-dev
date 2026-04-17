@php
    $readonlyClass = 'read-only:!bg-neutral-50 read-only:!text-black read-only:!border read-only:!border-neutral-300 dark:read-only:!bg-coolgray-200 dark:read-only:!text-white dark:read-only:!border dark:read-only:!border-coolgray-300';
@endphp

<form wire:submit="save" wire:init="refreshBackendStatuses" wire:poll.5s="refreshBackendStatuses" class="flex flex-col">
    <div class="flex items-center gap-2">
        <h2>Load Balancing</h2>
        @if ($isMultiServer && $enabled)
            <x-forms.button canGate="update" :canResource="$application" type="submit">Save</x-forms.button>
        @endif
        @if ($isMultiServer)
            @if (! $enabled)
                <x-modal-confirmation title="Confirm Load Balancer Enable?" buttonTitle="Enable Load Balancer"
                    submitAction="toggleEnabled"
                    :actions="['Enable load balancing for this application. Traffic for the configured domain will be routed through the load balancer server.']"
                    warningMessage="The application's domain DNS must point to the load balancer server once configured. Make sure the selected load balancer server is reachable and has Traefik configured."
                    step2ButtonText="Enable Load Balancer" :confirmWithText="false" :confirmWithPassword="false"
                    isHighlightedButton>
                </x-modal-confirmation>
            @else
                <x-forms.button type="button" canGate="update" :canResource="$application"
                    wire:click="toggleEnabled">Disable Load Balancer</x-forms.button>
            @endif
        @endif
    </div>
    <div class="mt-1 pb-4">
        Route public traffic for this application through a Traefik load balancer that distributes requests across the
        servers it is deployed on.
    </div>

    @if (! $isMultiServer)
        <x-callout type="info" title="Load balancing requires multiple servers">
            This application is deployed on a single server, so there is nothing to load balance. Add another server from
            the <strong>Servers</strong> page first.
        </x-callout>
    @else
        @if (empty($application->fqdn))
            <x-callout type="warning" title="Domain required">
                Set an FQDN on the <strong>General</strong> page before enabling the load balancer. The application's
                domain will be the load balancer's public domain.
            </x-callout>
        @endif

        @if ($enabled)
            <div class="flex flex-col gap-6 mt-2">
                <div>
                    <h3 class="pb-2">Entry point</h3>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <x-forms.select canGate="update" :canResource="$application" id="load_balancer_server_id"
                            label="Load balancer server" required wire:model.live="load_balancer_server_id"
                            helper="Server that will accept public traffic for this application. Point the application's domain DNS to this server.">
                            <option value="">Select server...</option>
                            @foreach ($availableServers as $srv)
                                <option value="{{ $srv->id }}">{{ $srv->name }} ({{ $srv->ip }})</option>
                            @endforeach
                        </x-forms.select>
                        <x-forms.input canGate="update" :canResource="$application" id="tls_cert_resolver"
                            label="TLS certificate resolver" required
                            helper="Traefik cert resolver name (e.g. letsencrypt). The load balancer terminates TLS." />
                    </div>
                    @if ($load_balancer_server_id && ! $isLbServerPrimary)
                        <div class="flex items-center gap-3 mt-4">
                            @if ($isDeployedOnLbServer)
                                <x-modal-confirmation title="Stop deploying on LB server?" isErrorButton
                                    buttonTitle="Stop deploying on LB server" submitAction="removeFromLbServer"
                                    :actions="[
                                        'The application container on the load balancer server will be stopped.',
                                        'The load balancer server will be removed from this application\'s deployment targets.',
                                        'If this leaves the application on a single server, load balancing will be disabled automatically on the next save.',
                                    ]"
                                    confirmationText="{{ $application->name }}" confirmationLabel="Please confirm this action by typing the application name below."
                                    step2ButtonText="Stop deploying on LB server" :confirmWithPassword="false">
                                </x-modal-confirmation>
                                <div class="text-xs dark:text-neutral-400">
                                    The application is deployed on the load balancer server and is included as a backend.
                                </div>
                            @else
                                <x-forms.button type="button" canGate="update" :canResource="$application"
                                    wire:click="deployOnLbServer">Also deploy on LB server</x-forms.button>
                                <div class="text-xs dark:text-neutral-400">
                                    Add the load balancer server as an additional deployment target so the app runs there too.
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <div>
                    <h3 class="pb-2">Algorithm</h3>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <x-forms.select canGate="update" :canResource="$application" id="algorithm" label="Algorithm"
                            required wire:model.live="algorithm"
                            helper="Traefik v3 balancing strategy for the service.">
                            <option value="wrr">Weighted Round Robin</option>
                            <option value="p2c">Power of Two Choices</option>
                            <option value="hrw">Highest Random Weight</option>
                            <option value="leasttime">Least Response Time</option>
                        </x-forms.select>
                    </div>
                </div>

                <div>
                    <div class="flex items-center gap-2 pb-2">
                        <h3>Backends</h3>
                        @if ($healthcheck_enabled)
                            <x-forms.button type="button" wire:click="refreshBackendStatuses">Refresh
                                status</x-forms.button>
                        @endif
                    </div>
                    <div class="pb-2 text-sm dark:text-neutral-400">
                        One backend per server the application is deployed on.
                        @if ($algorithm === 'wrr')
                            Weight controls how much traffic each backend receives relative to the others.
                        @endif
                    </div>
                    @if ($this->shouldShowStatusWarning())
                        <x-callout type="warning" title="Live backend status unavailable">
                            The Traefik API on the load balancer server reports <code>--api.insecure=true</code>, but we
                            could not reach <code>127.0.0.1:8080/api/http/services/loadbalancer-{{ $application->uuid }}@file</code>
                            from inside the <code>coolify-proxy</code> container. Save the load balancer at least once so
                            Traefik picks up the dynamic config, then click <strong>Refresh status</strong>.
                        </x-callout>
                    @endif
                    <div class="flex flex-col gap-2">
                        @foreach ($deploymentServers as $srv)
                            @php
                                $weight = $weights[$srv->id] ?? 1;
                                $targetUrl = $this->backendUrlFor($srv);
                                $isLbServer = $load_balancer_server_id !== null && (int) $load_balancer_server_id === (int) $srv->id;
                                $containerRunning = $this->isContainerRunningOn($srv);
                            @endphp
                            <div x-data="{ expanded: false }"
                                class="rounded border border-neutral-200 dark:border-coolgray-200 dark:bg-coolgray-100">
                                <div class="flex gap-2 justify-between items-center p-4 cursor-pointer select-none hover:bg-gray-50 dark:hover:bg-coolgray-200"
                                    @click="expanded = !expanded">
                                    <div class="flex gap-3 items-center min-w-0">
                                        <svg class="w-4 h-4 transition-transform shrink-0"
                                            :class="expanded ? 'rotate-90' : ''" viewBox="0 0 24 24"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path fill="currentColor"
                                                d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z" />
                                        </svg>
                                        @if ($healthcheck_enabled)
                                            @php
                                                $status = $containerRunning ? $this->backendStatusFor($targetUrl) : null;
                                                $statusLabel = match (true) {
                                                    ! $containerRunning => 'Backend not deployed on this server',
                                                    $status === 'UP' => 'Backend is UP (Traefik health check passing)',
                                                    $status === 'DOWN' => 'Backend is DOWN (Traefik health check failing)',
                                                    default => 'Backend status unknown (Traefik API not reachable)',
                                                };
                                                $dotColor = match (true) {
                                                    ! $containerRunning => 'bg-neutral-400',
                                                    $status === 'UP' => 'bg-green-500',
                                                    $status === 'DOWN' => 'bg-red-500',
                                                    default => 'bg-neutral-400',
                                                };
                                                $pingColor = match (true) {
                                                    $containerRunning && $status === 'UP' => 'bg-green-400',
                                                    $containerRunning && $status === 'DOWN' => 'bg-red-400',
                                                    default => null,
                                                };
                                            @endphp
                                            <span class="relative flex w-2.5 h-2.5 shrink-0" title="{{ $statusLabel }}">
                                                @if ($pingColor)
                                                    <span
                                                        class="absolute inline-flex w-full h-full {{ $pingColor }} rounded-full opacity-75 animate-ping"></span>
                                                @endif
                                                <span
                                                    class="relative inline-flex w-2.5 h-2.5 {{ $dotColor }} rounded-full"></span>
                                            </span>
                                        @endif
                                        <h4 class="dark:text-white shrink-0">{{ $srv->name }}</h4>
                                        <span class="text-xs dark:text-gray-400">{{ $srv->ip }}</span>
                                        @if ($isLbServer)
                                            <span
                                                class="px-2 py-0.5 text-xs rounded bg-coolgray-200 dark:bg-coolgray-300 dark:text-white">LB
                                                server</span>
                                        @endif
                                    </div>
                                    @if ($algorithm === 'wrr')
                                        <div class="shrink-0 text-xs dark:text-gray-400">weight {{ $weight }}</div>
                                    @endif
                                </div>
                                <div x-show="expanded" x-collapse x-cloak>
                                    <div class="px-4 pb-4">
                                        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                                            <x-forms.input label="IP" :value="$srv->ip" readonly :class="$readonlyClass" />
                                            <x-forms.input label="Target URL" :value="$targetUrl" readonly :class="$readonlyClass" />
                                        </div>
                                        @if ($algorithm === 'wrr')
                                            <div class="grid grid-cols-1 gap-3 mt-3 md:grid-cols-3">
                                                <x-forms.input canGate="update" :canResource="$application"
                                                    type="number" min="1" max="1000"
                                                    id="weights.{{ $srv->id }}" label="Weight"
                                                    wire:model="weights.{{ $srv->id }}"
                                                    helper="Higher weight means more traffic." />
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div>
                    <div class="flex items-center gap-2 pb-2">
                        <h3>Session affinity</h3>
                        @if (! $sticky_enabled)
                            <x-forms.button type="button" canGate="update" :canResource="$application"
                                wire:click="toggleSticky">Enable Sticky Sessions</x-forms.button>
                        @else
                            <x-forms.button type="button" canGate="update" :canResource="$application"
                                wire:click="toggleSticky">Disable Sticky Sessions</x-forms.button>
                        @endif
                    </div>
                    <div class="text-sm dark:text-neutral-400 pb-2">
                        Pin each client to the same backend using a cookie.
                    </div>
                    @if ($sticky_enabled)
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <x-forms.input canGate="update" :canResource="$application" id="sticky_cookie_name"
                                label="Cookie name" required
                                helper="Only letters, numbers, dashes, underscores." />
                        </div>
                    @endif
                </div>

                <div>
                    <div class="flex items-center gap-2 pb-2">
                        <h3>Health checks</h3>
                        @if (! $healthcheck_enabled)
                            <x-forms.button type="button" canGate="update" :canResource="$application"
                                wire:click="toggleHealthcheckSection">Enable Health Checks</x-forms.button>
                        @else
                            <x-forms.button type="button" canGate="update" :canResource="$application"
                                wire:click="toggleHealthcheckSection">Disable Health Checks</x-forms.button>
                        @endif
                    </div>
                    <div class="text-sm dark:text-neutral-400 pb-2">
                        Traefik will probe each backend and remove unhealthy ones from rotation.
                    </div>
                    @if ($healthcheck_enabled)
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <x-forms.select canGate="update" :canResource="$application" id="healthcheck_scheme"
                                label="Scheme" required>
                                <option value="http">http</option>
                                <option value="https">https</option>
                            </x-forms.select>
                            <x-forms.input canGate="update" :canResource="$application" id="healthcheck_path"
                                label="Path" placeholder="/health" required />
                            <x-forms.input canGate="update" :canResource="$application" type="number" min="100"
                                max="599" id="healthcheck_status" label="Expected status"
                                helper="HTTP status code that marks the backend healthy." required />
                        </div>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3 mt-4">
                            <x-forms.input canGate="update" :canResource="$application" type="number" min="1"
                                id="healthcheck_interval" label="Interval (s)"
                                helper="How often to probe a backend." required />
                            <x-forms.input canGate="update" :canResource="$application" type="number" min="1"
                                id="healthcheck_timeout" label="Timeout (s)"
                                helper="Maximum time to wait for a probe response." required />
                            <x-forms.input canGate="update" :canResource="$application" id="healthcheck_hostname"
                                label="Host header" :readonly="!$portMapped" :class="!$portMapped ? $readonlyClass : ''"
                                :helper="$portMapped
                                    ? 'Optional Host: header sent with each probe. Leave empty to skip.'
                                    : 'Auto-filled with the application domain because the backend is reached through Traefik (no host port mapping) and the backend Traefik routes by Host header. Add a port mapping on General to override.'" />
                        </div>
                        <div class="mt-4">
                            <x-forms.textarea canGate="update" :canResource="$application" id="healthcheck_headers"
                                label="Headers"
                                helper="One header per line, format: Name: value. Sent with every health check probe." />
                        </div>
                    @endif
                </div>
            </div>
        @endif
    @endif
</form>
