<div>
    <x-slot:title>Admin | Coolify</x-slot>
    <x-dashboard.navbar section="admin" />

    <div class="mt-8 w-full max-w-[1180px] space-y-8 lg:mt-3">
        <section class="grid gap-3 sm:grid-cols-3">
            <div class="rounded-[10px] border border-neutral-200 bg-white p-4 dark:border-white/[0.07] dark:bg-surface">
                <div class="text-xs font-medium text-neutral-500 dark:text-fg-dim">Current user</div>
                <div class="mt-2 truncate text-sm font-semibold text-black dark:text-fg">
                    {{ auth()->user()->name }}
                </div>
                <div class="mt-0.5 truncate text-xs text-neutral-500 dark:text-fg-faint">
                    {{ auth()->user()->email }}
                </div>
            </div>
            <div class="rounded-[10px] border border-neutral-200 bg-white p-4 dark:border-white/[0.07] dark:bg-surface">
                <div class="text-xs font-medium text-neutral-500 dark:text-fg-dim">Active subscribers</div>
                <div class="mt-2 text-2xl font-semibold tracking-tight text-black dark:text-fg">
                    {{ $activeSubscribers }}
                </div>
            </div>
            <div class="rounded-[10px] border border-neutral-200 bg-white p-4 dark:border-white/[0.07] dark:bg-surface">
                <div class="text-xs font-medium text-neutral-500 dark:text-fg-dim">Inactive subscribers</div>
                <div class="mt-2 text-2xl font-semibold tracking-tight text-black dark:text-fg">
                    {{ $inactiveSubscribers }}
                </div>
            </div>
        </section>

        @if (session('impersonating'))
            <x-callout type="warning" title="Impersonation is active">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <span>You are viewing Coolify as {{ auth()->user()->name }}.</span>
                    <x-forms.button wire:click="back">Return to root user</x-forms.button>
                </div>
            </x-callout>
        @endif

        <section class="application-settings-section">
            <div class="application-settings-section-header">
                <div>
                    <h2>User lookup</h2>
                    <p>Find an account and switch into it for support or administration.</p>
                </div>
            </div>
            <div class="application-settings-section-body p-0!">
                <form wire:submit="submitSearch"
                    class="flex items-end gap-2 border-b border-neutral-200 p-4 dark:border-white/[0.06]">
                    <div class="max-w-md flex-1">
                        <x-forms.input wire:model="search" label="Name or email"
                            placeholder="Search for a user…" />
                    </div>
                    <x-forms.button type="submit">
                        <x-reicon name="search" class="size-4" />
                        Search
                    </x-forms.button>
                </form>

                @if ($search)
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[640px] table-fixed text-left">
                            <thead>
                                <tr
                                    class="border-b border-neutral-200 bg-neutral-50 text-[11px] font-semibold tracking-wide text-neutral-500 uppercase dark:border-white/[0.06] dark:bg-white/[0.025] dark:text-fg-faint">
                                    <th class="w-[34%] px-4 py-2.5">Name</th>
                                    <th class="w-[38%] px-4 py-2.5">Email</th>
                                    <th class="w-[14%] px-4 py-2.5">Subscription</th>
                                    <th class="w-[14%] px-4 py-2.5 text-right"><span class="sr-only">Action</span></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-200 dark:divide-white/[0.06]">
                                @forelse ($foundUsers as $user)
                                    @php
                                        $hasActiveSubscription = $user->teams()
                                            ->whereRelation('subscription', 'stripe_invoice_paid', true)
                                            ->exists();
                                    @endphp
                                    <tr class="transition-colors hover:bg-neutral-50 dark:hover:bg-white/[0.025]">
                                        <td class="truncate px-4 py-3 text-sm font-semibold text-black dark:text-fg">
                                            {{ $user->name }}
                                        </td>
                                        <td class="truncate px-4 py-3 text-sm text-neutral-600 dark:text-fg-dim">
                                            {{ $user->email }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <x-status-badge :status="$hasActiveSubscription ? 'Active' : 'Inactive'"
                                                :type="$hasActiveSubscription ? 'success' : 'neutral'" />
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <button type="button" class="button"
                                                wire:click="switchUser({{ $user->id }})">
                                                Switch user
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4">
                                            <x-empty size="sm" title="No users found"
                                                description="No account matches {{ $search }}." />
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @else
                    <x-empty size="sm" title="Search for an account"
                        description="Enter a name or email address to begin.">
                        <x-slot:icon>
                            <x-reicon name="profile" class="size-5" />
                        </x-slot:icon>
                    </x-empty>
                @endif
            </div>
        </section>
    </div>
</div>
