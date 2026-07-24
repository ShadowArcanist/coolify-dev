<div>
    <x-slot:title>{{ data_get_str($application, 'name')->limit(10) }} > Deployments | Coolify</x-slot>
    <h1>Deployments</h1>
    <livewire:project.shared.configuration-checker :resource="$application" />
    <livewire:project.application.heading :application="$application" />

    @php
        $lastPage = max(1, (int) ceil($deployments_count / $defaultTake));
        $firstVisibleRow = $deployments_count === 0 ? 0 : $skip + 1;
        $lastVisibleRow = min($skip + $deployments->count(), $deployments_count);
    @endphp

    <div class="application-settings-form flex flex-col gap-6"
        @if (!$skip) wire:poll.5000ms="reloadDeployments" @endif>
        <x-application.settings-section title="Deployment history"
            helper="Review application deployments, their source, status, timing, and build logs.">
            <x-slot:actions>
                <x-status-badge :status="$skip === 0 ? 'Live updates' : 'Historical page'"
                    :type="$skip === 0 ? 'success' : 'neutral'" />
            </x-slot:actions>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <p class="text-xs font-medium text-neutral-500 dark:text-fg-dim">Deployments</p>
                    <p class="mt-1 text-xl font-semibold tabular-nums text-neutral-950 dark:text-fg">
                        {{ $deployments_count }}
                    </p>
                </div>
                <div>
                    <p class="text-xs font-medium text-neutral-500 dark:text-fg-dim">Current page</p>
                    <p class="mt-1 text-xl font-semibold tabular-nums text-neutral-950 dark:text-fg">
                        {{ $currentPage }} <span class="text-sm font-medium text-neutral-400 dark:text-fg-faint">of
                            {{ $lastPage }}</span>
                    </p>
                </div>
                <div>
                    <p class="text-xs font-medium text-neutral-500 dark:text-fg-dim">Refresh</p>
                    <p class="mt-1 text-sm font-medium text-neutral-950 dark:text-fg">
                        {{ $skip === 0 ? 'Every 5 seconds' : 'Paused on older pages' }}
                    </p>
                </div>
            </div>
        </x-application.settings-section>

        <div class="flex flex-wrap items-end gap-2">
            <div class="w-full max-w-xs">
                <label for="deployment-pull-request-filter">Pull request</label>
                <div class="relative">
                    <input id="deployment-pull-request-filter" type="number" min="1"
                        wire:model.live.debounce.300ms="pull_request_id"
                        placeholder="Filter by pull request ID" class="input w-full pr-8!" />
                    @if ($pull_request_id)
                        <button type="button" wire:click="clearFilter" aria-label="Clear pull request filter"
                            class="absolute inset-y-0 right-0 flex w-8 items-center justify-center text-neutral-400 transition-colors hover:text-neutral-700 dark:text-fg-faint dark:hover:text-fg">
                            <x-reicon name="x" class="size-3" />
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <div class="application-settings-section-body is-flush w-full">
            @if ($deployments->isNotEmpty())
                <div class="data-table w-full">
                    <div class="data-table-header deployment-table-grid">
                        <span>Status</span>
                        <span>Source</span>
                        <span>Commit</span>
                        <span>Started</span>
                        <span>Duration</span>
                        <span>Server</span>
                        <span></span>
                    </div>

                    @foreach ($deployments as $deployment)
                        @php
                            $deploymentStatus = data_get($deployment, 'status');
                            $statusLabel = match ($deploymentStatus) {
                                'finished' => 'Success',
                                'in_progress' => 'In progress',
                                'cancelled-by-user' => 'Cancelled',
                                'queued' => 'Queued',
                                default => str($deploymentStatus)->headline()->toString(),
                            };
                            $statusType = match ($deploymentStatus) {
                                'finished' => 'success',
                                'in_progress', 'queued' => 'warning',
                                'failed' => 'error',
                                default => 'neutral',
                            };
                            $sourceLabel = match (true) {
                                (bool) data_get($deployment, 'is_webhook') && filled(data_get($deployment, 'pull_request_id')) => 'Webhook · PR #' . data_get($deployment, 'pull_request_id'),
                                (bool) data_get($deployment, 'is_webhook') => 'Webhook',
                                filled(data_get($deployment, 'pull_request_id')) => 'Pull request #' . data_get($deployment, 'pull_request_id'),
                                (bool) data_get($deployment, 'rollback') => 'Rollback',
                                (bool) data_get($deployment, 'is_api') => 'API',
                                default => 'Manual',
                            };
                            $duration = match ($deploymentStatus) {
                                'queued' => 'Waiting',
                                'in_progress' => calculateDuration(data_get($deployment, 'created_at'), now()),
                                default => data_get($deployment, 'finished_at')
                                    ? calculateDuration(data_get($deployment, 'created_at'), data_get($deployment, 'finished_at'))
                                    : '—',
                            };
                            $commitMessage = $deployment->commitMessage()
                                ? Str::before($deployment->commitMessage(), "\n")
                                : null;
                        @endphp
                        <a wire:key="deployment-{{ data_get($deployment, 'deployment_uuid') }}"
                            href="{{ $current_url . '/' . data_get($deployment, 'deployment_uuid') }}"
                            {{ wireNavigate() }}
                            class="data-table-row deployment-table-grid text-[13px] text-neutral-600 dark:text-fg-dim">
                            <span><x-status-badge :status="$statusLabel" :type="$statusType" /></span>
                            <span>{{ $sourceLabel }}</span>
                            <span class="min-w-0">
                                @if (data_get($deployment, 'commit'))
                                    <span class="flex min-w-0 items-center gap-2">
                                        <span class="shrink-0 font-mono text-xs text-neutral-950 dark:text-fg">
                                            {{ substr(data_get($deployment, 'commit'), 0, 7) }}
                                        </span>
                                        @if ($commitMessage)
                                            <span class="truncate text-neutral-500 dark:text-fg-faint"
                                                title="{{ $commitMessage }}">{{ $commitMessage }}</span>
                                        @endif
                                    </span>
                                @else
                                    <span class="text-neutral-400 dark:text-fg-faint">—</span>
                                @endif
                            </span>
                            <span title="{{ formatDateInServerTimezone(data_get($deployment, 'created_at'), data_get($application, 'destination.server')) }}">
                                {{ \Carbon\Carbon::parse(data_get($deployment, 'created_at'))->diffForHumans() }}
                            </span>
                            <span class="tabular-nums">{{ $duration }}</span>
                            <span class="truncate">
                                {{ data_get($deployment, 'server_name') ?: data_get($application, 'destination.server.name', '—') }}
                            </span>
                            <span class="flex justify-end text-neutral-400 dark:text-fg-faint">
                                <x-reicon name="arrow-right" class="size-3.5" />
                            </span>
                        </a>
                    @endforeach

                    <div class="flex items-center justify-between gap-3 px-4 py-3">
                        <p class="text-[13px] text-neutral-500 dark:text-fg-dim">
                            Showing <span
                                class="tabular-nums text-black dark:text-fg">{{ $firstVisibleRow }}–{{ $lastVisibleRow }}</span>
                            of <span class="tabular-nums text-black dark:text-fg">{{ $deployments_count }}</span>
                        </p>
                        <div
                            class="inline-flex h-8 overflow-hidden rounded-lg border border-neutral-200 dark:border-white/[0.10]">
                            <button type="button"
                                class="flex w-10 items-center justify-center border-r border-neutral-200 text-neutral-500 transition-colors hover:bg-neutral-100 disabled:cursor-not-allowed disabled:opacity-35 dark:border-white/[0.10] dark:text-fg-dim dark:hover:bg-white/[0.06]"
                                aria-label="First page" title="First page" wire:click="goToPage(1)"
                                @disabled($currentPage === 1)>
                                <svg class="size-3.5" viewBox="0 0 24 24" fill="none">
                                    <path d="M18 6L12 12L18 18M11 6L5 12L11 18" stroke="currentColor"
                                        stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>
                            <button type="button"
                                class="flex w-10 items-center justify-center border-r border-neutral-200 text-neutral-500 transition-colors hover:bg-neutral-100 disabled:cursor-not-allowed disabled:opacity-35 dark:border-white/[0.10] dark:text-fg-dim dark:hover:bg-white/[0.06]"
                                aria-label="Previous page" title="Previous page" wire:click="previousPage"
                                @disabled($currentPage === 1)>
                                <svg class="size-3.5" viewBox="0 0 24 24" fill="none">
                                    <path d="M15 6L9 12L15 18" stroke="currentColor" stroke-width="1.8"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>
                            <span
                                class="flex min-w-12 items-center justify-center border-r border-neutral-200 px-3 text-[13px] tabular-nums text-black dark:border-white/[0.10] dark:text-fg">
                                {{ $currentPage }}
                            </span>
                            <button type="button"
                                class="flex w-10 items-center justify-center border-r border-neutral-200 text-neutral-500 transition-colors hover:bg-neutral-100 disabled:cursor-not-allowed disabled:opacity-35 dark:border-white/[0.10] dark:text-fg-dim dark:hover:bg-white/[0.06]"
                                aria-label="Next page" title="Next page" wire:click="nextPage"
                                @disabled($currentPage >= $lastPage)>
                                <svg class="size-3.5" viewBox="0 0 24 24" fill="none">
                                    <path d="M9 6L15 12L9 18" stroke="currentColor" stroke-width="1.8"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>
                            <button type="button"
                                class="flex w-10 items-center justify-center text-neutral-500 transition-colors hover:bg-neutral-100 disabled:cursor-not-allowed disabled:opacity-35 dark:text-fg-dim dark:hover:bg-white/[0.06]"
                                aria-label="Last page" title="Last page" wire:click="goToPage({{ $lastPage }})"
                                @disabled($currentPage >= $lastPage)>
                                <svg class="size-3.5" viewBox="0 0 24 24" fill="none">
                                    <path d="M6 6L12 12L6 18M13 6L19 12L13 18" stroke="currentColor"
                                        stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            @else
                <x-empty size="sm" title="No deployments found"
                    :description="$pull_request_id ? 'No deployments match this pull request.' : 'Deploy the application to create its first deployment record.'">
                    <x-slot:icon>
                        <x-reicon name="layers" class="size-8" />
                    </x-slot:icon>
                </x-empty>
            @endif
        </div>
    </div>
</div>
