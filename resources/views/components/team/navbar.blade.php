<div class="mb-6">
    <header class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <h1 class="text-[24px]! leading-7! font-semibold!">Team</h1>
            <p class="mt-1 truncate text-[13px] text-neutral-500 dark:text-fg-dim">
                {{ currentTeam()->name }} · Team-wide configuration and access
            </p>
        </div>

        <x-modal-input title="New Team">
            <x-slot:content>
                <button type="button"
                    class="button bg-coollabs/10! text-coollabs! ring-1 ring-coollabs/25 hover:bg-coollabs/15! dark:bg-warning/15! dark:text-warning! dark:ring-warning/25 dark:hover:bg-warning/20!">
                    <x-reicon name="plus" class="size-3.5" />
                    New team
                </button>
            </x-slot:content>
            <livewire:team.create />
        </x-modal-input>
    </header>

    <nav class="flex items-center gap-1 overflow-x-auto border-b border-neutral-200 pb-2 dark:border-white/[0.08]"
        aria-label="Team sections">
        <a class="inline-flex h-8 shrink-0 items-center rounded-lg px-3 text-[12px] font-medium transition-colors {{ request()->routeIs('team.index') ? 'bg-coollabs/10 text-coollabs dark:bg-warning/15 dark:text-warning' : 'text-neutral-500 hover:bg-neutral-100 hover:text-black dark:text-fg-dim dark:hover:bg-white/[0.06] dark:hover:text-fg' }}"
            {{ wireNavigate() }} href="{{ route('team.index') }}">
            General
        </a>
        <a class="inline-flex h-8 shrink-0 items-center rounded-lg px-3 text-[12px] font-medium transition-colors {{ request()->routeIs('team.member.index') ? 'bg-coollabs/10 text-coollabs dark:bg-warning/15 dark:text-warning' : 'text-neutral-500 hover:bg-neutral-100 hover:text-black dark:text-fg-dim dark:hover:bg-white/[0.06] dark:hover:text-fg' }}"
            {{ wireNavigate() }} href="{{ route('team.member.index') }}">
            Members
        </a>
        @if (isInstanceAdmin())
            <a class="inline-flex h-8 shrink-0 items-center rounded-lg px-3 text-[12px] font-medium transition-colors {{ request()->routeIs('team.admin-view') ? 'bg-coollabs/10 text-coollabs dark:bg-warning/15 dark:text-warning' : 'text-neutral-500 hover:bg-neutral-100 hover:text-black dark:text-fg-dim dark:hover:bg-white/[0.06] dark:hover:text-fg' }}"
                {{ wireNavigate() }} href="{{ route('team.admin-view') }}">
                Admin view
            </a>
        @endif
    </nav>
</div>
