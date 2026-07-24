<div>
    <x-slot:title>
        {{ data_get_str($application, 'name')->limit(10) }} > Configuration | Coolify
    </x-slot>
    <livewire:project.shared.configuration-checker :resource="$application" />
    <livewire:project.application.heading :application="$application" />

    @php
        $applicationRouteParameters = [
            'project_uuid' => $project->uuid,
            'environment_uuid' => $environment->uuid,
            'application_uuid' => $application->uuid,
        ];

        $configurationMenuItems = [
            [
                'label' => 'General',
                'route' => 'project.application.configuration',
                'active' => $currentRoute === 'project.application.configuration',
            ],
            [
                'label' => 'Advanced',
                'route' => 'project.application.advanced',
                'active' => $currentRoute === 'project.application.advanced',
            ],
            [
                'label' => 'Swarm',
                'route' => 'project.application.swarm',
                'active' => $currentRoute === 'project.application.swarm',
                'visible' => $application->destination->server->isSwarm(),
            ],
            [
                'label' => 'Environment Variables',
                'route' => 'project.application.environment-variables',
                'active' => $currentRoute === 'project.application.environment-variables',
            ],
            [
                'label' => 'Persistent Storage',
                'route' => 'project.application.persistent-storage',
                'active' => $currentRoute === 'project.application.persistent-storage',
            ],
            [
                'label' => 'Git Source',
                'route' => 'project.application.source',
                'active' => $currentRoute === 'project.application.source',
                'visible' => $application->git_based(),
            ],
            [
                'label' => 'Servers',
                'route' => 'project.application.servers',
                'active' => $currentRoute === 'project.application.servers',
                'badge' => true,
            ],
            [
                'label' => 'Scheduled Tasks',
                'route' => 'project.application.scheduled-tasks.show',
                'active' => str($currentRoute)->startsWith('project.application.scheduled-tasks'),
            ],
            [
                'label' => 'Webhooks',
                'route' => 'project.application.webhooks',
                'active' => $currentRoute === 'project.application.webhooks',
            ],
            [
                'label' => 'Preview Deployments',
                'route' => 'project.application.preview-deployments',
                'active' => $currentRoute === 'project.application.preview-deployments',
                'visible' => $application->git_based() || $application->build_pack === 'dockerimage',
            ],
            [
                'label' => 'Healthcheck',
                'route' => 'project.application.healthcheck',
                'active' => $currentRoute === 'project.application.healthcheck',
                'visible' => $application->build_pack !== 'dockercompose',
            ],
            [
                'label' => 'Rollback',
                'route' => 'project.application.rollback',
                'active' => $currentRoute === 'project.application.rollback',
            ],
            [
                'label' => 'Resource Limits',
                'route' => 'project.application.resource-limits',
                'active' => $currentRoute === 'project.application.resource-limits',
            ],
            [
                'label' => 'Resource Operations',
                'route' => 'project.application.resource-operations',
                'active' => $currentRoute === 'project.application.resource-operations',
            ],
            [
                'label' => 'Metrics',
                'route' => 'project.application.metrics',
                'active' => $currentRoute === 'project.application.metrics',
            ],
            [
                'label' => 'Tags',
                'route' => 'project.application.tags',
                'active' => $currentRoute === 'project.application.tags',
            ],
            [
                'label' => 'Danger Zone',
                'route' => 'project.application.danger',
                'active' => $currentRoute === 'project.application.danger',
            ],
        ];

        $configurationMenuItems = array_values(array_filter(
            $configurationMenuItems,
            fn (array $item): bool => $item['visible'] ?? true,
        ));

    @endphp

    <section class="application-settings-workspace mt-8 w-full max-w-[1180px]">
        <div class="grid min-w-0 gap-8 xl:grid-cols-[210px_minmax(0,1fr)] xl:gap-10">
            <aside class="application-settings-navigation min-w-0 xl:sticky xl:top-20 xl:self-start">
                <nav aria-label="Configuration sections"
                    class="grid grid-cols-2 gap-1 border-y border-neutral-200 py-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-1 xl:border-y-0 xl:py-0">
                    @foreach ($configurationMenuItems as $menuItem)
                        <a wire:key="application-settings-link-{{ str($menuItem['label'])->slug() }}"
                            @class([
                                'relative flex min-h-9 items-center gap-2 rounded-[5px] px-2.5 text-sm font-medium transition-colors',
                                'bg-neutral-100 text-black before:absolute before:inset-y-2 before:left-0 before:w-0.5 before:bg-accent dark:bg-white/[0.07] dark:text-fg' => $menuItem['active'],
                                'text-neutral-500 hover:bg-neutral-100 hover:text-black dark:text-fg-dim dark:hover:bg-white/[0.04] dark:hover:text-fg' => ! $menuItem['active'],
                            ])
                            {{ wireNavigate() }}
                            href="{{ route($menuItem['route'], $applicationRouteParameters) }}">
                            <span class="min-w-0 flex-1">{{ $menuItem['label'] }}</span>
                            @if ($menuItem['badge'] ?? false)
                                <span class="shrink-0">
                                    <livewire:project.application.server-status-badge :application="$application" />
                                </span>
                            @endif
                        </a>
                    @endforeach
                </nav>
            </aside>

            <div class="min-w-0">
            @if ($currentRoute === 'project.application.configuration')
                <livewire:project.application.general :application="$application" />
            @elseif ($currentRoute === 'project.application.swarm' && $application->destination->server->isSwarm())
                <livewire:project.application.swarm :application="$application" />
            @elseif ($currentRoute === 'project.application.advanced')
                <livewire:project.application.advanced :application="$application" />
            @elseif ($currentRoute === 'project.application.environment-variables')
                <livewire:project.shared.environment-variable.all :resource="$application" />
            @elseif ($currentRoute === 'project.application.persistent-storage')
                <livewire:project.service.storage :resource="$application" />
            @elseif ($currentRoute === 'project.application.source' && $application->git_based())
                <livewire:project.application.source :application="$application" />
            @elseif ($currentRoute === 'project.application.servers')
                <livewire:project.shared.destination :resource="$application" />
            @elseif ($currentRoute === 'project.application.scheduled-tasks.show')
                <livewire:project.shared.scheduled-task.all :resource="$application" />
            @elseif ($currentRoute === 'project.application.scheduled-tasks')
                <livewire:project.shared.scheduled-task.show />
            @elseif ($currentRoute === 'project.application.webhooks')
                <livewire:project.shared.webhooks :resource="$application" />
            @elseif ($currentRoute === 'project.application.preview-deployments')
                <livewire:project.application.previews :application="$application" />
            @elseif ($currentRoute === 'project.application.healthcheck' && $application->build_pack !== 'dockercompose')
                <livewire:project.shared.health-checks :resource="$application" />
            @elseif ($currentRoute === 'project.application.rollback')
                <livewire:project.application.rollback :application="$application" />
            @elseif ($currentRoute === 'project.application.resource-limits')
                <livewire:project.shared.resource-limits :resource="$application" />
            @elseif ($currentRoute === 'project.application.resource-operations')
                <livewire:project.shared.resource-operations :resource="$application" />
            @elseif ($currentRoute === 'project.application.metrics')
                <livewire:project.shared.metrics :resource="$application" />
            @elseif ($currentRoute === 'project.application.tags')
                <livewire:project.shared.tags :resource="$application" />
            @elseif ($currentRoute === 'project.application.danger')
                <livewire:project.shared.danger :resource="$application" />
            @endif
            </div>
        </div>
    </section>
</div>
