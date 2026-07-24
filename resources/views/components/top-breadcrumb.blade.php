@php
    $team = auth()->user()?->currentTeam();
    $projectUuid = request()->route('project_uuid');
    $environmentUuid = request()->route('environment_uuid');
    $projects = $projectUuid && $team ? $team->projects()->get() : collect();
    $currentProject = $projectUuid ? $projects->firstWhere('uuid', $projectUuid) : null;
    $environments = $currentProject ? $currentProject->environments()->get() : collect();
    $currentEnvironment = $environmentUuid ? $environments->firstWhere('uuid', $environmentUuid) : null;
@endphp
<div class="flex items-center gap-0.5 min-w-0 text-[13px]">
    {{-- Team --}}
    <div class="shrink-0" x-data="{ collapsed: false }">
        <livewire:switch-team />
    </div>

    @if ($currentProject)
        <span class="shrink-0 text-neutral-300 dark:text-fg-faint px-0.5">/</span>
        {{-- Project switcher --}}
        <div class="relative min-w-0 shrink" x-data="{ open: false }" @keydown.escape.window="open = false">
            <button type="button" @click="open = !open" @click.outside="open = false" title="Switch project"
                class="flex items-center gap-1.5 min-w-0 h-8 px-2 rounded-md transition-colors hover:bg-neutral-100 dark:hover:bg-white/[0.05]">
                <span class="min-w-0 truncate font-semibold text-black dark:text-fg">{{ $currentProject->name }}</span>
                <svg class="size-4 shrink-0 text-neutral-400 dark:text-fg-faint" viewBox="0 0 24 24" fill="none">
                    <path d="M8 9l4-4 4 4M8 15l4 4 4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </button>
            <div x-show="open" x-cloak x-transition.opacity.duration.120ms
                class="absolute left-0 z-[90] mt-1 min-w-56 max-w-72 max-h-80 overflow-y-auto rounded-lg border border-neutral-200 dark:border-white/10 bg-white dark:bg-raised py-1.5 shadow-modal scrollbar">
                <div class="px-3 pb-1 pt-0.5 text-[10.5px] font-semibold uppercase tracking-wide text-neutral-400 dark:text-fg-faint">Projects</div>
                @foreach ($projects as $p)
                    <a href="{{ route('project.show', ['project_uuid' => $p->uuid]) }}" {{ wireNavigate() }} @click="open = false"
                        class="flex items-center gap-2 px-3 h-8 text-[13px] transition-colors hover:bg-neutral-100 dark:hover:bg-white/[0.06] {{ $p->uuid === $currentProject->uuid ? 'text-black dark:text-fg font-medium' : 'text-neutral-600 dark:text-fg-dim' }}">
                        <span class="min-w-0 flex-1 truncate">{{ $p->name }}</span>
                        @if ($p->uuid === $currentProject->uuid)
                            <svg class="size-3.5 shrink-0 text-accent" viewBox="0 0 24 24" fill="none"><path d="M5 12l5 5 9-11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    @if ($currentProject && $currentEnvironment)
        <span class="shrink-0 text-neutral-300 dark:text-fg-faint px-0.5">/</span>
        {{-- Environment switcher --}}
        <div class="relative min-w-0 shrink" x-data="{ open: false }" @keydown.escape.window="open = false">
            <button type="button" @click="open = !open" @click.outside="open = false" title="Switch environment"
                class="flex items-center gap-1.5 min-w-0 h-8 px-2 rounded-md transition-colors hover:bg-neutral-100 dark:hover:bg-white/[0.05]">
                <span class="min-w-0 truncate font-semibold text-black dark:text-fg">{{ $currentEnvironment->name }}</span>
                <svg class="size-4 shrink-0 text-neutral-400 dark:text-fg-faint" viewBox="0 0 24 24" fill="none">
                    <path d="M8 9l4-4 4 4M8 15l4 4 4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </button>
            <div x-show="open" x-cloak x-transition.opacity.duration.120ms
                class="absolute left-0 z-[90] mt-1 min-w-52 max-w-72 max-h-80 overflow-y-auto rounded-lg border border-neutral-200 dark:border-white/10 bg-white dark:bg-raised py-1.5 shadow-modal scrollbar">
                <div class="px-3 pb-1 pt-0.5 text-[10.5px] font-semibold uppercase tracking-wide text-neutral-400 dark:text-fg-faint">Environments</div>
                @foreach ($environments as $env)
                    <a href="{{ route('project.resource.index', ['project_uuid' => $currentProject->uuid, 'environment_uuid' => $env->uuid]) }}" {{ wireNavigate() }} @click="open = false"
                        class="flex items-center gap-2 px-3 h-8 text-[13px] transition-colors hover:bg-neutral-100 dark:hover:bg-white/[0.06] {{ $env->uuid === $currentEnvironment->uuid ? 'text-black dark:text-fg font-medium' : 'text-neutral-600 dark:text-fg-dim' }}">
                        <span class="min-w-0 flex-1 truncate">{{ $env->name }}</span>
                        @if ($env->uuid === $currentEnvironment->uuid)
                            <svg class="size-3.5 shrink-0 text-accent" viewBox="0 0 24 24" fill="none"><path d="M5 12l5 5 9-11" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>
