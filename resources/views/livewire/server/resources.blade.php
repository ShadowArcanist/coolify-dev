<div>
    <x-slot:title>
        {{ data_get_str($server, 'name')->limit(10) }} > Server Resources | Coolify
    </x-slot>

    <livewire:server.navbar :server="$server" />

    <div class="application-settings-form flex w-full flex-col gap-6">
        <x-application.settings-section id="server-resources-overview-section" title="Resources"
            helper="Review Coolify-managed resources and other Docker containers running on this server.">
            <x-slot:actions>
                <x-forms.button wire:click="refreshStatus">
                    <x-reicon name="refresh" class="size-3.5" />
                    Refresh
                </x-forms.button>
            </x-slot:actions>

            <div class="inline-flex w-fit rounded-lg bg-neutral-100 p-1 dark:bg-white/[0.05]">
                <button type="button" wire:click="loadManagedContainers"
                    class="rounded-md px-4 py-1.5 text-xs font-medium transition-colors {{ $activeTab === 'managed' ? 'bg-white text-neutral-950 shadow-sm dark:bg-warning/15 dark:text-warning' : 'text-neutral-500 hover:text-neutral-900 dark:text-fg-dim dark:hover:text-fg' }}">
                    Managed
                </button>
                <button type="button" wire:click="loadUnmanagedContainers"
                    class="rounded-md px-4 py-1.5 text-xs font-medium transition-colors {{ $activeTab === 'unmanaged' ? 'bg-white text-neutral-950 shadow-sm dark:bg-warning/15 dark:text-warning' : 'text-neutral-500 hover:text-neutral-900 dark:text-fg-dim dark:hover:text-fg' }}">
                    Unmanaged
                </button>
            </div>
        </x-application.settings-section>

        @if ($activeTab === 'managed')
            @php($managedResources = $server->definedResources()->sortBy('name', SORT_NATURAL))
            <x-application.settings-section id="server-managed-resources-section" title="Managed resources"
                helper="Applications, services, and databases controlled by Coolify." flush>
                @if ($managedResources->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[780px] text-left">
                            <thead>
                                <tr class="border-b border-neutral-200 bg-neutral-50/80 dark:border-white/[0.08] dark:bg-white/[0.025]">
                                    @foreach (['Name', 'Project', 'Environment', 'Type', 'Status'] as $heading)
                                        <th class="px-4 py-2.5 text-xs font-medium text-neutral-500 dark:text-fg-dim">
                                            {{ $heading }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($managedResources as $resource)
                                    @php($resourceStatus = (string) data_get($resource, 'status', 'unknown'))
                                    <tr
                                        class="border-b border-neutral-200 last:border-b-0 hover:bg-neutral-50 dark:border-white/[0.08] dark:hover:bg-white/[0.025]">
                                        <td class="px-4 py-3">
                                            <a class="inline-flex items-center gap-1 text-sm font-medium text-neutral-950 hover:underline dark:text-fg"
                                                {{ wireNavigate() }} href="{{ $resource->link() }}">
                                                {{ $resource->name }}
                                                <x-internal-link />
                                            </a>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-neutral-600 dark:text-fg-dim">
                                            {{ data_get($resource->project(), 'name') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-neutral-600 dark:text-fg-dim">
                                            {{ data_get($resource, 'environment.name') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-neutral-600 dark:text-fg-dim">
                                            {{ str($resource->type())->headline() }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <x-status-badge :status="str($resourceStatus)->headline()"
                                                :type="str($resourceStatus)->contains('running')
                                                    ? 'success'
                                                    : (str($resourceStatus)->contains(['failed', 'exited']) ? 'error' : 'neutral')" />
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <x-empty size="sm" title="No managed resources"
                        description="Resources assigned to this server will appear here.">
                        <x-slot:icon>
                            <x-reicon name="projects" class="size-8" />
                        </x-slot:icon>
                    </x-empty>
                @endif
            </x-application.settings-section>
        @else
            <x-application.settings-section id="server-unmanaged-resources-section"
                title="Unmanaged containers"
                helper="Docker containers running outside Coolify resource management." flush>
                @if (count($unmanagedContainers) > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[680px] text-left">
                            <thead>
                                <tr class="border-b border-neutral-200 bg-neutral-50/80 dark:border-white/[0.08] dark:bg-white/[0.025]">
                                    @foreach (['Name', 'Image', 'Status', 'Actions'] as $heading)
                                        <th class="px-4 py-2.5 text-xs font-medium text-neutral-500 dark:text-fg-dim">
                                            {{ $heading }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach (collect($unmanagedContainers)->sortBy('name', SORT_NATURAL) as $resource)
                                    @php($containerState = (string) data_get($resource, 'State', 'unknown'))
                                    <tr
                                        class="border-b border-neutral-200 last:border-b-0 hover:bg-neutral-50 dark:border-white/[0.08] dark:hover:bg-white/[0.025]">
                                        <td class="px-4 py-3 font-mono text-sm text-neutral-950 dark:text-fg">
                                            {{ data_get($resource, 'Names') }}
                                        </td>
                                        <td class="max-w-sm truncate px-4 py-3 font-mono text-xs text-neutral-600 dark:text-fg-dim">
                                            {{ data_get($resource, 'Image') }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <x-status-badge :status="str($containerState)->headline()"
                                                :type="$containerState === 'running'
                                                    ? 'success'
                                                    : ($containerState === 'exited' ? 'error' : 'warning')" />
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-2">
                                                @if ($containerState === 'running')
                                                    <x-forms.button canGate="update" :canResource="$server"
                                                        wire:click="restartUnmanaged('{{ data_get($resource, 'ID') }}')"
                                                        wire:key="restart-{{ data_get($resource, 'ID') }}">
                                                        Restart
                                                    </x-forms.button>
                                                    <x-forms.button canGate="update" :canResource="$server" isError
                                                        wire:click="stopUnmanaged('{{ data_get($resource, 'ID') }}')"
                                                        wire:key="stop-{{ data_get($resource, 'ID') }}">
                                                        Stop
                                                    </x-forms.button>
                                                @elseif ($containerState === 'exited')
                                                    <x-forms.button canGate="update" :canResource="$server"
                                                        wire:click="startUnmanaged('{{ data_get($resource, 'ID') }}')"
                                                        wire:key="start-{{ data_get($resource, 'ID') }}">
                                                        Start
                                                    </x-forms.button>
                                                @elseif ($containerState === 'restarting')
                                                    <x-forms.button canGate="update" :canResource="$server"
                                                        wire:click="stopUnmanaged('{{ data_get($resource, 'ID') }}')"
                                                        wire:key="stop-restarting-{{ data_get($resource, 'ID') }}">
                                                        Stop
                                                    </x-forms.button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <x-empty size="sm" title="No unmanaged containers"
                        description="All detected Docker containers are managed by Coolify.">
                        <x-slot:icon>
                            <x-reicon name="servers" class="size-8" />
                        </x-slot:icon>
                    </x-empty>
                @endif
            </x-application.settings-section>
        @endif
    </div>
</div>
