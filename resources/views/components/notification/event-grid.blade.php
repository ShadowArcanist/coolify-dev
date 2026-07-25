@props([
    'settings',
    'channel',
    'threaded' => false,
])

@php
    $eventGroups = [
        'Deployments' => [
            ['key' => 'deploymentSuccess', 'label' => 'Deployment success'],
            ['key' => 'deploymentFailure', 'label' => 'Deployment failure'],
            [
                'key' => 'statusChange',
                'label' => 'Container status changes',
                'helper' => 'Notify when a container stops or restarts.',
            ],
        ],
        'Backups' => [
            ['key' => 'backupSuccess', 'label' => 'Backup success'],
            ['key' => 'backupFailure', 'label' => 'Backup failure'],
        ],
        'Scheduled tasks' => [
            ['key' => 'scheduledTaskSuccess', 'label' => 'Scheduled task success'],
            ['key' => 'scheduledTaskFailure', 'label' => 'Scheduled task failure'],
        ],
        'Servers' => [
            ['key' => 'dockerCleanupSuccess', 'label' => 'Docker cleanup success'],
            ['key' => 'dockerCleanupFailure', 'label' => 'Docker cleanup failure'],
            ['key' => 'serverDiskUsage', 'label' => 'Disk usage warning'],
            ['key' => 'serverReachable', 'label' => 'Server reachable'],
            ['key' => 'serverUnreachable', 'label' => 'Server unreachable'],
            ['key' => 'serverPatch', 'label' => 'Server patching'],
            ['key' => 'traefikOutdated', 'label' => 'Traefik proxy outdated'],
        ],
    ];
@endphp

<x-application.settings-section title="Notification events"
    description="Choose the events that should be delivered through {{ $channel }}.">
    <div class="grid gap-3 lg:grid-cols-2">
        @foreach ($eventGroups as $group => $events)
            <div
                class="rounded-lg border border-neutral-200 bg-neutral-50/70 p-3 dark:border-white/[0.08] dark:bg-white/[0.025]">
                <h4 class="mb-3 text-[12px] font-semibold text-black dark:text-fg">{{ $group }}</h4>
                <div class="flex flex-col gap-3">
                    @foreach ($events as $event)
                        @php
                            $notificationModel = $event['key'] . Str::studly($channel) . 'Notifications';
                            $threadModel = Str::camel(
                                Str::studly($channel) . 'Notifications' . Str::studly($event['key']) . 'ThreadId',
                            );
                        @endphp
                        <div @class([
                            'grid items-end gap-3',
                            'sm:grid-cols-[minmax(0,1fr)_minmax(180px,0.75fr)]' => $threaded,
                        ])>
                            <x-forms.checkbox canGate="update" :canResource="$settings" instantSave="saveModel"
                                :id="$notificationModel" :label="$event['label']"
                                :helper="$event['helper'] ?? null" />
                            @if ($threaded)
                                <x-forms.input canGate="update" :canResource="$settings" type="password"
                                    :id="$threadModel" label="Thread ID" placeholder="Optional" />
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</x-application.settings-section>
