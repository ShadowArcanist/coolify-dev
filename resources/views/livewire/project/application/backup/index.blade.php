<div x-data="{
    search: @js($search),
    backups: @js($backups->map(fn ($backup) => [
        'name' => strtolower($backup->targetName()),
        'type' => strtolower($backup->targetType()),
        'frequency' => strtolower($backup->frequency),
    ])->values()),
    hasMatches() {
        const query = this.search.toLowerCase();

        return this.backups.some((backup) => backup.name.includes(query) || backup.type.includes(query) || backup.frequency.includes(query));
    },
}">
    <x-slot:title>
        {{ data_get_str($application, 'name')->limit(10) }} > Backups | Coolify
    </x-slot>
    <h1>Backups</h1>
    <livewire:project.shared.configuration-checker :resource="$application" />
    <livewire:project.application.heading :application="$application" />

    <div class="application-settings-form flex flex-col gap-6">
        <x-application.settings-section title="Storage backups"
            helper="Schedule backups for persistent volumes and directory mounts attached to this application.">
            @can('update', $application)
                <x-slot:actions>
                    <x-modal-input title="New scheduled backup" :wireIgnore="false">
                        <x-slot:content>
                            <button type="button"
                                class="button bg-coollabs/10! text-coollabs! ring-1 ring-coollabs/25 hover:bg-coollabs/15! dark:bg-warning/15! dark:text-warning! dark:ring-warning/25 dark:hover:bg-warning/20!">
                                <x-reicon name="plus" class="size-3.5" />
                                Add
                            </button>
                        </x-slot:content>
                        <livewire:project.application.backup.create :application="$application"
                            wire:key="create-volume-backup-{{ $application->id }}" />
                    </x-modal-input>
                </x-slot:actions>
            @endcan

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <p class="text-xs font-medium text-neutral-500 dark:text-fg-dim">Schedules</p>
                    <p class="mt-1 text-xl font-semibold tabular-nums text-neutral-950 dark:text-fg">
                        {{ $backups->count() }}
                    </p>
                </div>
                <div>
                    <p class="text-xs font-medium text-neutral-500 dark:text-fg-dim">Enabled</p>
                    <p class="mt-1 text-xl font-semibold tabular-nums text-neutral-950 dark:text-fg">
                        {{ $backups->where('enabled', true)->count() }}
                    </p>
                </div>
                <div>
                    <p class="text-xs font-medium text-neutral-500 dark:text-fg-dim">Total executions</p>
                    <p class="mt-1 text-xl font-semibold tabular-nums text-neutral-950 dark:text-fg">
                        {{ $backups->sum('executions_count') }}
                    </p>
                </div>
            </div>
        </x-application.settings-section>

        <div class="flex flex-wrap items-center gap-2">
            <div class="relative min-w-0 max-w-md flex-1">
                <input type="search" x-model="search" placeholder="Search backups" aria-label="Search backups"
                    class="input w-full pl-8!" />
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5">
                    <x-reicon name="search" class="size-3.5 text-neutral-400 dark:text-fg-faint" />
                </div>
            </div>
        </div>

        <div class="application-settings-section-body is-flush w-full">
            <div x-cloak x-show="search !== '' && backups.length > 0 && !hasMatches()">
                <x-empty size="sm" title="No backups found"
                    description="No scheduled backups match your search." />
            </div>

            @if ($backups->isNotEmpty())
                <div class="data-table w-full" x-show="search === '' || hasMatches()">
                    <div class="data-table-header backup-table-grid">
                        <span>Target</span>
                        <span>Type</span>
                        <span>Schedule</span>
                        <span>Status</span>
                        <span>Last run</span>
                        <span class="text-right">Executions</span>
                        <span></span>
                    </div>

                    @foreach ($backups as $backup)
                        @php
                            $latestExecution = $backup->latestExecution;
                            $status = $latestExecution?->status;
                            $statusLabel = match ($status) {
                                'running' => 'In progress',
                                'success' => 'Success',
                                'failed' => 'Failed',
                                default => $backup->enabled ? 'Waiting' : 'Disabled',
                            };
                            $statusType = match ($status) {
                                'running' => 'warning',
                                'success' => 'success',
                                'failed' => 'error',
                                default => 'neutral',
                            };
                        @endphp
                        <a wire:key="volume-backup-{{ $backup->uuid }}"
                            x-show="search === '' || @js(strtolower($backup->targetName())).includes(search.toLowerCase()) || @js(strtolower($backup->targetType())).includes(search.toLowerCase()) || @js(strtolower($backup->frequency)).includes(search.toLowerCase())"
                            href="{{ route('project.application.backup.show', [...$parameters, 'backup_uuid' => $backup->uuid]) }}"
                            {{ wireNavigate() }}
                            class="data-table-row backup-table-grid text-[13px] text-neutral-700 dark:text-fg-dim">
                            <span class="min-w-0 truncate font-medium text-neutral-950 dark:text-fg"
                                title="{{ $backup->targetName() }}">
                                {{ $backup->targetName() }}
                            </span>
                            <span>{{ $backup->targetType() }}</span>
                            <span class="font-mono text-xs">{{ $backup->frequency }}</span>
                            <span><x-status-badge :status="$statusLabel" :type="$statusType" /></span>
                            <span>
                                {{ $latestExecution?->finished_at?->diffForHumans() ?? ($status === 'running' ? 'Running now' : 'Never') }}
                            </span>
                            <span class="text-right tabular-nums text-neutral-950 dark:text-fg">
                                {{ $backup->executions_count }}
                            </span>
                            <span class="flex justify-end text-neutral-400 dark:text-fg-faint">
                                <x-reicon name="arrow-right" class="size-3.5" />
                            </span>
                        </a>
                    @endforeach
                </div>
            @else
                <x-empty size="sm" title="No scheduled backups"
                    description="Add a persistent volume or directory backup schedule to protect application data.">
                    <x-slot:icon>
                        <x-reicon name="storages" class="size-8" />
                    </x-slot:icon>
                </x-empty>
            @endif
        </div>
    </div>
</div>
