<div>
    <x-slot:title>
        Team Admin | Coolify
    </x-slot>

    <x-team.navbar />

    <div class="application-settings-form">
        <x-application.settings-section title="Instance users"
            description="Search and manage every user registered on this Coolify instance." flush>
            <form wire:submit="submitSearch"
                class="flex items-end gap-2 border-b border-neutral-200 p-3 dark:border-white/[0.08]">
                <div class="relative max-w-md flex-1">
                    <x-reicon name="search"
                        class="pointer-events-none absolute top-1/2 left-2.5 z-10 size-3.5 -translate-y-1/2 text-neutral-400" />
                    <input wire:model="search" placeholder="Search users"
                        class="w-full pl-8! text-[12px]">
                </div>
                <button type="submit" class="button">Search</button>
            </form>

            @forelse ($users as $user)
                <div wire:key="instance-user-{{ $user->id }}"
                    class="flex min-h-14 items-center gap-3 border-b border-neutral-200 px-4 py-2.5 last:border-b-0 dark:border-white/[0.07]">
                    <div
                        class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-neutral-100 text-[11px] font-semibold text-neutral-600 dark:bg-white/[0.06] dark:text-fg-dim">
                        {{ Str::upper(Str::substr($user->name ?: $user->email, 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-[13px] font-medium text-black dark:text-fg">{{ $user->name }}</p>
                        <p class="truncate text-[11px] text-neutral-500 dark:text-fg-faint">{{ $user->email }}</p>
                    </div>
                    <x-modal-confirmation title="Confirm User Deletion?" buttonTitle="Delete" isErrorButton
                        submitAction="delete({{ $user->id }})" :actions="[
                            'The selected user and their default team resources will be permanently deleted.',
                        ]"
                        confirmationText="{{ $user->name }}"
                        confirmationLabel="Enter the user name to confirm deletion"
                        shortConfirmationLabel="User name" />
                </div>
            @empty
                <x-empty title="No users found" description="Try a different name or email address." size="sm">
                    <x-slot:icon>
                        <x-reicon name="teams" class="size-6" />
                    </x-slot:icon>
                </x-empty>
            @endforelse

            @if ($lots_of_users)
                <div
                    class="border-t border-neutral-200 px-4 py-3 text-[11px] text-neutral-500 dark:border-white/[0.08] dark:text-fg-faint">
                    More users are available. Refine your search to find a specific account.
                </div>
            @endif
        </x-application.settings-section>
    </div>
</div>
