<div>
    <x-slot:title>
        {{ data_get_str($resource, 'name')->limit(10) }} > Logs | Coolify
    </x-slot>
    <livewire:project.shared.configuration-checker :resource="$resource" />
    @if ($type === 'application')
        <h1>Logs</h1>
        <livewire:project.application.heading :application="$resource" />
        <div class="application-settings-form flex flex-col gap-6">
            <x-application.settings-section title="Runtime logs"
                helper="Inspect and stream output from the application's running containers.">
                <x-slot:actions>
                    <x-status-badge :status="str($status)->contains('running') ? 'Application running' : 'Application stopped'"
                        :type="str($status)->contains('running') ? 'success' : 'neutral'" />
                </x-slot:actions>
                <div class="flex items-start gap-3">
                    <div
                        class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-neutral-100 text-neutral-600 dark:bg-white/[0.06] dark:text-fg-dim">
                        <x-reicon name="file-content" class="size-4.5" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-neutral-950 dark:text-fg">Live container output</p>
                        <p class="mt-1 text-xs leading-5 text-neutral-500 dark:text-fg-dim">
                            Search, filter, follow, copy, or download the output shown for each container.
                        </p>
                    </div>
                </div>
            </x-application.settings-section>

            <div wire:loading wire:target="loadAllContainers"
                class="application-settings-section-body flex min-h-32 items-center justify-center">
                <x-loading text="Loading containers" />
            </div>

            <div class="flex flex-col gap-4" x-init="$wire.loadAllContainers()" wire:loading.remove
                wire:target="loadAllContainers">
                @forelse ($servers as $server)
                    @if ($server->isFunctional())
                        @if (isset($serverContainers[$server->id]) && count($serverContainers[$server->id]) > 0)
                            @php
                                $totalContainers = collect($serverContainers)->flatten(1)->count();
                            @endphp
                            @if ($servers->count() > 1)
                                <div class="flex items-center gap-2 px-1">
                                    <x-reicon name="servers" class="size-3.5 text-neutral-400 dark:text-fg-faint" />
                                    <p class="text-xs font-medium text-neutral-500 dark:text-fg-dim">{{ $server->name }}</p>
                                </div>
                            @endif
                            @foreach ($serverContainers[$server->id] as $container)
                                <livewire:project.shared.get-logs
                                    wire:key="{{ data_get($container, 'ID', uniqid()) }}" :server="$server"
                                    :resource="$resource" :container="data_get($container, 'Names')"
                                    :expandByDefault="$totalContainers === 1" />
                            @endforeach
                        @else
                            <x-empty size="sm" title="No running containers"
                                description="No containers are currently running on {{ $server->name }}.">
                                <x-slot:icon>
                                    <x-reicon name="file-content" class="size-8" />
                                </x-slot:icon>
                            </x-empty>
                        @endif
                    @else
                        <x-callout type="warning" title="Server unavailable">
                            {{ $server->name }} is not functional, so its container logs cannot be loaded.
                        </x-callout>
                    @endif
                @empty
                    <x-empty size="sm" title="No functional server"
                        description="Connect and validate a server before viewing application logs.">
                        <x-slot:icon>
                            <x-reicon name="servers" class="size-8" />
                        </x-slot:icon>
                    </x-empty>
                @endforelse
            </div>
        </div>
    @elseif ($type === 'database')
        <h1>Logs</h1>
        <livewire:project.database.heading :database="$resource" />
        <div>
            <h2>Logs</h2>
            @if (str($status)->contains('exited'))
                <div class="pt-4">The resource is not running.</div>
            @else
                <div class="pt-2" wire:loading wire:target="loadAllContainers">
                    Loading containers...
                </div>
                <div x-init="$wire.loadAllContainers()" wire:loading.remove wire:target="loadAllContainers">
                    @forelse ($containers as $container)
                        @if (data_get($servers, '0'))
                            <livewire:project.shared.get-logs wire:key='{{ $container }}' :server="data_get($servers, '0')"
                                :resource="$resource" :container="$container"
                                :expandByDefault="count($containers) === 1" />
                        @else
                            <div>No functional server found for the database.</div>
                        @endif
                    @empty
                        <div class="pt-2">No containers are running.</div>
                    @endforelse
                </div>
            @endif
        </div>
    @elseif ($type === 'service')
        <livewire:project.service.heading :service="$resource" :parameters="$parameters" :query="$query" title="Logs" />
        <div>
            <h2>Logs</h2>
            @if (str($status)->contains('exited'))
                <div class="pt-4">The resource is not running.</div>
            @else
                <div class="pt-2" wire:loading wire:target="loadAllContainers">
                    Loading containers...
                </div>
                <div x-init="$wire.loadAllContainers()" wire:loading.remove wire:target="loadAllContainers">
                    @forelse ($containers as $container)
                        @if (data_get($servers, '0'))
                            <livewire:project.shared.get-logs wire:key='{{ $container }}' :server="data_get($servers, '0')"
                                :resource="$resource" :container="$container"
                                :expandByDefault="count($containers) === 1" />
                        @else
                            <div>No functional server found for the service.</div>
                        @endif
                    @empty
                        <div class="pt-2">No containers are running.</div>
                    @endforelse
                </div>
            @endif
        </div>
    @endif
</div>
