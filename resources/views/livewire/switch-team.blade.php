@php
    $currentTeam = auth()->user()->currentTeam();
    $teamInitial = strtoupper(mb_substr($currentTeam->name, 0, 1));
@endphp
<div class="min-w-0">
    {{-- Expanded: inline switcher (team name + up/down chevron) --}}
    <div class="relative" :class="collapsed && 'lg:hidden'"
        x-data="{ open: false }" @keydown.escape.window="open = false">
        <button type="button" @click="open = !open" @click.outside="open = false"
            title="Switch team"
            class="group/team flex items-center gap-1.5 h-8 px-2 -ml-1 rounded-lg text-left opacity-70 transition-[background-color,opacity] hover:opacity-100 hover:bg-neutral-100 dark:hover:bg-white/[0.05]">
            <span class="whitespace-nowrap text-[13px] font-semibold text-black dark:text-fg">{{ $currentTeam->name }}</span>
            <svg class="size-4 shrink-0 text-neutral-400 dark:text-fg-faint" viewBox="0 0 24 24" fill="none">
                <path d="M8 9l4-4 4 4M8 15l4 4 4-4" stroke="currentColor" stroke-width="1.6"
                    stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </button>
        <div x-show="open" x-cloak x-transition.opacity.duration.120ms
            class="absolute left-0 z-[90] mt-1 min-w-56 max-w-72 max-h-72 overflow-y-auto rounded-lg border border-neutral-200 dark:border-white/10 bg-white dark:bg-raised py-1.5 shadow-modal">
            <div class="px-3 pb-1.5 pt-1 text-[10.5px] font-semibold uppercase tracking-wide text-neutral-400 dark:text-fg-faint">Teams</div>
            @foreach (auth()->user()->teams as $team)
                <button type="button" wire:click="switch_to({{ $team->id }})" @click="open = false"
                    class="flex w-full items-center gap-2 px-3 h-8 text-left text-[13px] transition-colors hover:bg-neutral-100 dark:hover:bg-white/[0.06] {{ $team->id === $currentTeam->id ? 'text-black dark:text-fg font-medium' : 'text-neutral-600 dark:text-fg-dim' }}">
                    <span class="flex items-center justify-center size-5 shrink-0 rounded-md bg-neutral-100 dark:bg-white/[0.06] text-[11px] font-semibold text-neutral-500 dark:text-fg-dim">{{ strtoupper(mb_substr($team->name, 0, 1)) }}</span>
                    <span class="min-w-0 flex-1 truncate">{{ $team->name }}</span>
                    @if ($team->id === $currentTeam->id)
                        <svg class="size-3.5 shrink-0 text-accent" viewBox="0 0 24 24" fill="none">
                            <path d="M5 12l5 5 9-11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    @endif
                </button>
            @endforeach
        </div>
    </div>

    {{-- Collapsed: square initial with flyout menu --}}
    <div class="hidden" :class="collapsed && 'lg:block'"
        x-data="{
            teamOpen: false,
            teamX: 0,
            teamY: 0,
            openTeamMenu(ev) {
                const rect = ev.currentTarget.getBoundingClientRect();
                this.teamX = rect.right + 8;
                this.teamY = rect.top;
                this.teamOpen = !this.teamOpen;
            }
        }">
        <button @click="openTeamMenu($event)" type="button" title="Team: {{ $currentTeam->name }}"
            class="flex items-center justify-center w-8 h-8 p-0 text-[13px] font-semibold text-neutral-600 dark:text-fg bg-neutral-100 dark:bg-white/[0.06] hover:bg-neutral-200 dark:hover:bg-white/[0.1] rounded-lg cursor-pointer transition-colors">
            {{ $teamInitial }}
        </button>
        <div x-show="teamOpen" @click.outside="teamOpen = false" x-transition.opacity.duration.100ms x-cloak
            :style="`left: ${teamX}px; top: ${teamY}px;`"
            class="fixed z-[100] min-w-48 max-h-72 overflow-y-auto bg-white dark:bg-raised border border-neutral-200 dark:border-white/10 rounded-xl shadow-modal py-1.5">
            <div class="px-3 py-1 text-[10.5px] font-semibold uppercase tracking-wide text-neutral-400 dark:text-fg-faint">Teams</div>
            @foreach (auth()->user()->teams as $team)
                <button type="button" wire:click="switch_to({{ $team->id }})" @click="teamOpen = false"
                    class="w-full px-3 h-8 text-left text-[13px] hover:bg-neutral-100 dark:hover:bg-white/[0.06] dark:text-fg {{ $team->id === $currentTeam->id ? 'font-medium' : 'text-neutral-600 dark:text-fg-dim' }}">
                    {{ $team->name }}
                </button>
            @endforeach
        </div>
    </div>
</div>
