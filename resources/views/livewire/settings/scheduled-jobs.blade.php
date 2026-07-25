<div>
    <x-slot:title>
        Scheduled Jobs | Coolify
    </x-slot>

    <x-settings.navbar>
        <x-slot:actions>
            <button type="button" class="button" wire:click="refresh">
                <x-reicon name="refresh" class="size-3.5" />
                Refresh
            </button>
        </x-slot:actions>
    </x-settings.navbar>

    <div class="application-settings-form" x-data="{
        activeTab: ['executions', 'scheduler-runs', 'skipped-jobs'].includes(location.hash.slice(1))
            ? location.hash.slice(1)
            : 'executions',
        select(tab) {
            this.activeTab = tab;
            history.replaceState(null, '', `#${tab}`);
        }
    }">
        <x-application.settings-section title="Scheduler activity"
            description="Inspect failed executions, manager runs, and jobs skipped by their conditions." flush>
            <div
                class="flex flex-col gap-3 border-b border-neutral-200 p-3 sm:flex-row sm:items-center sm:justify-between dark:border-white/[0.08]">
                <div
                    class="flex w-fit items-center gap-0.5 rounded-[10px] border border-neutral-200 bg-neutral-100 p-1 dark:border-white/[0.07] dark:bg-white/[0.035]">
                    <button type="button" class="app-tab"
                        :class="activeTab === 'executions' &&
                            'bg-coollabs/10 text-coollabs ring-1 ring-coollabs/25 dark:bg-warning/15 dark:text-warning dark:ring-warning/25'"
                        @click="select('executions')">
                        Failures <span class="opacity-60">{{ $executions->count() }}</span>
                    </button>
                    <button type="button" class="app-tab"
                        :class="activeTab === 'scheduler-runs' &&
                            'bg-coollabs/10 text-coollabs ring-1 ring-coollabs/25 dark:bg-warning/15 dark:text-warning dark:ring-warning/25'"
                        @click="select('scheduler-runs')">
                        Scheduler runs <span class="opacity-60">{{ $managerRuns->count() }}</span>
                    </button>
                    <button type="button" class="app-tab"
                        :class="activeTab === 'skipped-jobs' &&
                            'bg-coollabs/10 text-coollabs ring-1 ring-coollabs/25 dark:bg-warning/15 dark:text-warning dark:ring-warning/25'"
                        @click="select('skipped-jobs')">
                        Skipped <span class="opacity-60">{{ $skipTotalCount }}</span>
                    </button>
                </div>

                <div x-show="activeTab === 'executions'" class="grid gap-2 sm:grid-cols-2">
                    <x-forms.listbox id="filterType" live :options="[
                        ['value' => 'all', 'label' => 'All types'],
                        ['value' => 'backup', 'label' => 'Backups'],
                        ['value' => 'task', 'label' => 'Tasks'],
                        ['value' => 'cleanup', 'label' => 'Docker cleanup'],
                    ]" />
                    <x-forms.listbox id="filterDate" live :options="[
                        ['value' => 'last_24h', 'label' => 'Last 24 hours'],
                        ['value' => 'last_7d', 'label' => 'Last 7 days'],
                        ['value' => 'last_30d', 'label' => 'Last 30 days'],
                        ['value' => 'all', 'label' => 'All time'],
                    ]" />
                </div>
            </div>

            <div x-cloak x-show="activeTab === 'executions'">
                @if ($executions->isEmpty())
                    <x-empty title="No failures"
                        description="No failed scheduled executions match the current filters." size="sm">
                        <x-slot:icon>
                            <x-reicon name="check-circle" class="size-6" />
                        </x-slot:icon>
                    </x-empty>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[860px]">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2.5 text-left">Type</th>
                                    <th class="px-4 py-2.5 text-left">Resource</th>
                                    <th class="px-4 py-2.5 text-left">Server</th>
                                    <th class="px-4 py-2.5 text-left">Started</th>
                                    <th class="px-4 py-2.5 text-left">Duration</th>
                                    <th class="px-4 py-2.5 text-left">Message</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($executions as $execution)
                                    @php
                                        $typeLabel = match ($execution['type']) {
                                            'backup' => 'Backup',
                                            'task' => 'Task',
                                            'cleanup' => 'Cleanup',
                                            default => ucfirst($execution['type']),
                                        };
                                    @endphp
                                    <tr wire:key="exec-{{ $execution['type'] }}-{{ $execution['id'] }}"
                                        class="border-t border-neutral-200 hover:bg-neutral-50 dark:border-white/[0.07] dark:hover:bg-white/[0.025]">
                                        <td class="px-4 py-3">
                                            <span
                                                class="rounded-full bg-neutral-100 px-2 py-0.5 text-[10px] font-medium text-neutral-600 dark:bg-white/[0.06] dark:text-fg-dim">
                                                {{ $typeLabel }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-[12px] font-medium text-black dark:text-fg">
                                            {{ $execution['resource_name'] }}
                                            @if ($execution['resource_type'])
                                                <span class="text-[10px] font-normal text-neutral-400">
                                                    {{ $execution['resource_type'] }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-[11px] text-neutral-500 dark:text-fg-dim">
                                            {{ $execution['server_name'] }}
                                        </td>
                                        <td class="px-4 py-3 text-[11px] text-neutral-500 dark:text-fg-dim">
                                            {{ $execution['created_at']->format('Y-m-d H:i') }}
                                        </td>
                                        <td class="px-4 py-3 text-[11px] text-neutral-500 dark:text-fg-dim">
                                            @if ($execution['finished_at'] && $execution['created_at'])
                                                {{ \Carbon\Carbon::parse($execution['created_at'])->diffInSeconds(\Carbon\Carbon::parse($execution['finished_at'])) }}s
                                            @elseif ($execution['status'] === 'running')
                                                <x-loading class="size-3.5" />
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="max-w-xs truncate px-4 py-3 text-[11px] text-neutral-500 dark:text-fg-dim"
                                            title="{{ $execution['message'] }}">
                                            {{ Str::limit($execution['message'], 80) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div x-cloak x-show="activeTab === 'scheduler-runs'">
                @if ($managerRuns->isEmpty())
                    <x-empty title="No manager runs"
                        description="Scheduler manager activity appears here after its next run." size="sm">
                        <x-slot:icon>
                            <x-reicon name="refresh" class="size-6" />
                        </x-slot:icon>
                    </x-empty>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[680px]">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2.5 text-left">Time</th>
                                    <th class="px-4 py-2.5 text-left">Event</th>
                                    <th class="px-4 py-2.5 text-left">Duration</th>
                                    <th class="px-4 py-2.5 text-left">Dispatched</th>
                                    <th class="px-4 py-2.5 text-left">Skipped</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($managerRuns as $run)
                                    <tr wire:key="run-{{ $loop->index }}"
                                        class="border-t border-neutral-200 dark:border-white/[0.07]">
                                        <td class="px-4 py-3 font-mono text-[11px] text-neutral-500 dark:text-fg-dim">
                                            {{ $run['timestamp'] }}
                                        </td>
                                        <td class="px-4 py-3 text-[12px] text-black dark:text-fg">{{ $run['message'] }}</td>
                                        <td class="px-4 py-3 text-[11px] text-neutral-500 dark:text-fg-dim">
                                            {{ $run['duration_ms'] !== null ? $run['duration_ms'] . 'ms' : '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-[11px] text-neutral-500 dark:text-fg-dim">
                                            {{ $run['dispatched'] ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3 text-[11px] text-neutral-500 dark:text-fg-dim">
                                            {{ $run['skipped'] ?? '—' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div x-cloak x-show="activeTab === 'skipped-jobs'">
                @if ($skipLogs->isEmpty())
                    <x-empty title="No skipped jobs"
                        description="All scheduled jobs met their dispatch conditions." size="sm">
                        <x-slot:icon>
                            <x-reicon name="check-circle" class="size-6" />
                        </x-slot:icon>
                    </x-empty>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[760px]">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2.5 text-left">Time</th>
                                    <th class="px-4 py-2.5 text-left">Type</th>
                                    <th class="px-4 py-2.5 text-left">Resource</th>
                                    <th class="px-4 py-2.5 text-left">Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($skipLogs as $skip)
                                    @php
                                        $reasonLabel = match ($skip['reason']) {
                                            'server_not_functional' => 'Server not functional',
                                            'subscription_unpaid' => 'Subscription unpaid',
                                            'database_deleted' => 'Database deleted',
                                            'server_deleted' => 'Server deleted',
                                            'resource_deleted' => 'Resource deleted',
                                            'application_not_running' => 'Application not running',
                                            'service_not_running' => 'Service not running',
                                            default => ucfirst(str_replace('_', ' ', $skip['reason'])),
                                        };
                                    @endphp
                                    <tr wire:key="skip-{{ $loop->index }}"
                                        class="border-t border-neutral-200 dark:border-white/[0.07]">
                                        <td class="px-4 py-3 font-mono text-[11px] text-neutral-500 dark:text-fg-dim">
                                            {{ $skip['timestamp'] }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <span
                                                class="rounded-full bg-neutral-100 px-2 py-0.5 text-[10px] font-medium capitalize text-neutral-600 dark:bg-white/[0.06] dark:text-fg-dim">
                                                {{ str_replace('_', ' ', $skip['type']) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-[12px] text-black dark:text-fg">
                                            @if ($skip['link'] ?? null)
                                                <a href="{{ $skip['link'] }}"
                                                    class="font-medium text-coollabs hover:underline dark:text-warning">
                                                    {{ $skip['resource_name'] }}
                                                </a>
                                            @else
                                                {{ $skip['resource_name'] ?? $skip['context']['task_name'] ?? $skip['context']['server_name'] ?? 'Deleted resource' }}
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-[11px] text-neutral-500 dark:text-fg-dim">
                                            {{ $reasonLabel }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div
                        class="flex min-h-12 items-center justify-between border-t border-neutral-200 px-4 dark:border-white/[0.08]">
                        <span class="text-[11px] text-neutral-500 dark:text-fg-faint">
                            {{ $skipTotalCount }} {{ Str::plural('skipped job', $skipTotalCount) }}
                        </span>
                        @if ($skipTotalCount > $skipDefaultTake)
                            <div class="flex items-center gap-2">
                                <span class="text-[11px] text-neutral-500 dark:text-fg-dim">
                                    Page {{ $skipCurrentPage }} of {{ ceil($skipTotalCount / $skipDefaultTake) }}
                                </span>
                                <button type="button" class="button size-8! px-0!" wire:click="skipPreviousPage"
                                    @disabled(!$showSkipPrev) aria-label="Previous page">
                                    <x-reicon name="arrow-right" class="size-3.5 rotate-180" />
                                </button>
                                <button type="button" class="button size-8! px-0!" wire:click="skipNextPage"
                                    @disabled(!$showSkipNext) aria-label="Next page">
                                    <x-reicon name="arrow-right" class="size-3.5" />
                                </button>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </x-application.settings-section>
    </div>
</div>
