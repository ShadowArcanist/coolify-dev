<div x-data="{
    search: '',
    backups: @js($database->scheduledBackups->map(fn ($backup) => [
        'name' => strtolower($database->name),
        'frequency' => strtolower($backup->frequency),
        's3_storage' => strtolower($backup->s3?->name ?? ''),
    ])->values()),
    hasMatches() {
        const query = this.search.toLowerCase();

        return this.backups.some((backup) =>
            backup.name.includes(query)
            || backup.frequency.includes(query)
            || backup.s3_storage.includes(query)
        );
    },
}">
    @if ($database->is_migrated && blank($database->custom_type))
        <form wire:submit="setCustomType" class="grid gap-4 p-5 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-end">
            <div>
                <x-forms.listbox id="custom_type" label="Database type" :options="[
                    ['value' => 'mysql', 'label' => 'MySQL'],
                    ['value' => 'mariadb', 'label' => 'MariaDB'],
                    ['value' => 'postgresql', 'label' => 'PostgreSQL'],
                    ['value' => 'mongodb', 'label' => 'MongoDB'],
                ]" />
                <p class="mt-2 text-xs text-neutral-500 dark:text-fg-dim">
                    Select the database engine before enabling automated backups.
                </p>
            </div>
            <x-forms.button type="submit">Set database type</x-forms.button>
        </form>
    @else
        @if ($database->scheduledBackups->isNotEmpty())
            <div class="border-b border-neutral-200 p-3 dark:border-white/[0.06]">
                <div class="relative max-w-sm">
                    <x-reicon name="search"
                        class="pointer-events-none absolute top-1/2 left-2.5 z-10 size-3.5 -translate-y-1/2 text-neutral-400 dark:text-fg-faint" />
                    <input type="search" x-model="search"
                        class="input h-8! pl-8! text-[13px]!" placeholder="Search backup schedules…" />
                </div>
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="w-full min-w-[720px] table-fixed text-left">
                <thead>
                    <tr
                        class="border-b border-neutral-200 bg-neutral-50 text-[11px] font-semibold tracking-wide text-neutral-500 uppercase dark:border-white/[0.06] dark:bg-white/[0.025] dark:text-fg-faint">
                        <th class="w-[28%] px-4 py-2.5">Schedule</th>
                        <th class="w-[18%] px-4 py-2.5">Latest run</th>
                        <th class="w-[20%] px-4 py-2.5">S3 storage</th>
                        <th class="w-[18%] px-4 py-2.5">Executions</th>
                        <th class="w-[16%] px-4 py-2.5 text-right"><span class="sr-only">Open</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-white/[0.06]">
                    @forelse ($database->scheduledBackups as $backup)
                        @php
                            $latestStatus = data_get($backup->latest_log, 'status');
                            [$statusLabel, $statusType] = match ($latestStatus) {
                                'success' => ['Success', 'success'],
                                'running' => ['In progress', 'warning'],
                                'failed' => ['Failed', 'error'],
                                default => ['Never run', 'neutral'],
                            };
                            $backupRoute = $type === 'database'
                                ? route('project.database.backup.execution', [...$parameters, 'backup_uuid' => $backup->uuid])
                                : route('project.service.database.backup.show', [...$parameters, 'backup_uuid' => $backup->uuid]);
                        @endphp
                        <tr x-show="search === ''
                            || @js(strtolower($database->name)).includes(search.toLowerCase())
                            || @js(strtolower($backup->frequency)).includes(search.toLowerCase())
                            || @js(strtolower($backup->s3?->name ?? '')).includes(search.toLowerCase())"
                            class="group transition-colors hover:bg-neutral-50 dark:hover:bg-white/[0.025]">
                            <td class="px-4 py-3">
                                <a class="block truncate text-sm font-semibold text-black dark:text-fg"
                                    {{ wireNavigate() }} href="{{ $backupRoute }}">
                                    {{ $backup->frequency }}
                                </a>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <x-status-badge :status="$statusLabel" :type="$statusType" />
                                    @if ($latestStatus === 'running')
                                        <x-loading />
                                    @endif
                                </div>
                            </td>
                            <td class="truncate px-4 py-3 text-sm text-neutral-600 dark:text-fg-dim">
                                {{ $backup->save_s3 ? ($backup->s3?->name ?? 'Unavailable') : 'Local only' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-neutral-600 dark:text-fg-dim">
                                {{ $backup->executions()->count() }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a class="button" {{ wireNavigate() }} href="{{ $backupRoute }}">Manage</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-empty size="sm" title="No scheduled backups"
                                    description="Create a schedule to start protecting this database.">
                                    <x-slot:icon>
                                        <x-reicon name="storages" class="size-5" />
                                    </x-slot:icon>
                                </x-empty>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div x-cloak x-show="search !== '' && backups.length > 0 && !hasMatches()"
            class="border-t border-neutral-200 dark:border-white/[0.06]">
            <x-empty size="sm" title="No matching backup schedules"
                description="Try another database name, frequency, or storage name." />
        </div>
    @endif
</div>
