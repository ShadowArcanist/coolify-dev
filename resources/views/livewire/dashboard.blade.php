<div>
    <x-slot:title>
        Dashboard | Coolify
    </x-slot>
    @if (session('error'))
        <span x-data x-init="$wire.emit('error', '{{ session('error') }}')" />
    @endif

    <div class="mb-8">
        <h1 class="text-3xl font-semibold tracking-tight text-black dark:text-white">Dashboard</h1>
        <div class="mt-1 text-sm text-neutral-500 dark:text-fg-faint">Your self-hosted infrastructure.</div>
    </div>

    <section class="mb-10">
        <div class="flex items-center gap-2 pb-4">
            <h3 class="text-base font-semibold text-black dark:text-white">Projects</h3>
            <div class="flex-1"></div>
            @can('create', App\Models\Project::class)
                @if ($projects->count() > 0)
                    <x-modal-input buttonTitle="Add" title="New Project">
                        <x-slot:content>
                            <button
                                class="inline-flex items-center gap-1.5 h-8 px-3 text-[13px] font-medium rounded-lg bg-neutral-100 text-black dark:bg-white/[0.06] dark:text-fg hover:bg-neutral-200 dark:hover:bg-white/[0.1] transition-colors cursor-pointer">
                                <x-reicon name="plus" class="size-3.5" />
                                New Project
                            </button>
                        </x-slot:content>
                        <livewire:project.add-empty />
                    </x-modal-input>
                @endif
            @endcan
        </div>
        @if ($projects->count() > 0)
            <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
                @foreach ($projects as $project)
                    <div class="relative gap-2 cursor-pointer coolbox group">
                        <a href="{{ $project->navigateTo() }}" {{ wireNavigate() }} class="absolute inset-0"></a>
                        <div class="flex flex-1 items-center gap-4 min-w-0">
                            <div class="flex flex-col justify-center flex-1 min-w-0">
                                <div class="box-title truncate">{{ $project->name }}</div>
                                <div class="box-description truncate">
                                    {{ $project->description }}
                                </div>
                            </div>
                            <div class="relative z-10 flex items-center justify-center gap-3 text-xs font-medium shrink-0">
                                @if ($project->environments->first())
                                    @can('createAnyResource')
                                        <a class="text-neutral-500 dark:text-fg-faint hover:text-black dark:hover:text-fg transition-colors" {{ wireNavigate() }}
                                            href="{{ route('project.resource.create', [
                                                'project_uuid' => $project->uuid,
                                                'environment_uuid' => $project->environments->first()->uuid,
                                            ]) }}">
                                            + Add Resource
                                        </a>
                                    @endcan
                                @endif
                                @can('update', $project)
                                    <a class="text-neutral-500 dark:text-fg-faint hover:text-black dark:hover:text-fg transition-colors" {{ wireNavigate() }}
                                        href="{{ route('project.edit', ['project_uuid' => $project->uuid]) }}">
                                        Settings
                                    </a>
                                @endcan
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="flex flex-col gap-1 rounded-2xl border border-dashed border-neutral-200 dark:border-white/[0.08] p-6 text-sm">
                <div class='font-semibold text-black dark:text-warning'>No projects found.</div>
                @can('create', App\Models\Project::class)
                    <div class="flex items-center gap-1 text-neutral-500 dark:text-fg-dim">
                        <x-modal-input buttonTitle="Add" title="New Project">
                            <livewire:project.add-empty />
                        </x-modal-input> your first project or
                        go to the <a class="underline dark:text-white" href="{{ route('onboarding') }}"
                            {{ wireNavigate() }}>onboarding</a> page.
                    </div>
                @endcan
            </div>
        @endif
    </section>

    <section>
        <div class="flex items-center gap-2 pb-4">
            <h3 class="text-base font-semibold text-black dark:text-white">Servers</h3>
            <div class="flex-1"></div>
            @can('create', App\Models\Server::class)
                @if ($servers->count() > 0 && $privateKeys->count() > 0)
                    <a href="{{ route('server.create') }}" {{ wireNavigate() }}
                        class="inline-flex items-center gap-1.5 h-8 px-3 text-[13px] font-medium rounded-lg bg-neutral-100 text-black dark:bg-white/[0.06] dark:text-fg hover:bg-neutral-200 dark:hover:bg-white/[0.1] transition-colors cursor-pointer">
                        <x-reicon name="plus" class="size-3.5" />
                        New Server
                    </a>
                @endif
            @endcan
        </div>
        @if ($servers->count() > 0)
            <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
                @foreach ($servers as $server)
                    <a href="{{ route('server.show', ['server_uuid' => data_get($server, 'uuid')]) }}" {{ wireNavigate() }}
                        @class([
                            'gap-2 coolbox group',
                            '!border-error/60 dark:!border-error/50' =>
                                !$server->settings->is_reachable || $server->settings->force_disabled,
                        ])>
                        <div class="flex items-center gap-3 flex-1 min-w-0">
                            <div class="flex items-center justify-center size-9 shrink-0 rounded-xl bg-neutral-100 dark:bg-white/[0.05] text-neutral-500 dark:text-fg-dim">
                                <x-reicon name="servers" class="size-4" />
                            </div>
                            <div class="flex flex-col justify-center min-w-0">
                                <div class="box-title truncate">
                                    {{ $server->name }}
                                </div>
                                <div class="box-description truncate">
                                    {{ $server->description }}</div>
                                <div class="flex gap-1 text-xs text-error">
                                    @if (!$server->settings->is_reachable)
                                        Not reachable
                                    @endif
                                    @if (!$server->settings->is_reachable && !$server->settings->is_usable)
                                        &
                                    @endif
                                    @if (!$server->settings->is_usable)
                                        Not usable by Coolify
                                    @endif
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            @if ($privateKeys->count() === 0)
                <div class="flex flex-col gap-1 rounded-2xl border border-dashed border-neutral-200 dark:border-white/[0.08] p-6 text-sm">
                    <div class='font-semibold text-black dark:text-warning'>No private keys found.</div>
                    @can('create', App\Models\Server::class)
                        <div class="flex items-center gap-1 text-neutral-500 dark:text-fg-dim">Before you can add your server, first <x-modal-input
                                buttonTitle="add" title="New Private Key">
                                <livewire:security.private-key.create from="server" />
                            </x-modal-input> a private key
                            or
                            go to the <a class="underline dark:text-white"
                                href="{{ route('onboarding') }}"
                                {{ wireNavigate() }}>onboarding</a>
                            page.
                        </div>
                    @endcan
                </div>
            @else
                <div class="flex flex-col gap-1 rounded-2xl border border-dashed border-neutral-200 dark:border-white/[0.08] p-6 text-sm">
                    <div class='font-semibold text-black dark:text-warning'>No servers found.</div>
                    @can('create', App\Models\Server::class)
                        <div class="flex items-center gap-1 text-neutral-500 dark:text-fg-dim">
                            <a href="{{ route('server.create') }}" {{ wireNavigate() }}>
                                <x-forms.button>Add</x-forms.button>
                            </a> your first server
                            or
                            go to the <a class="underline dark:text-white"
                                href="{{ route('onboarding') }}"
                                {{ wireNavigate() }}>onboarding</a>
                            page.
                        </div>
                    @endcan
                </div>
            @endif
        @endif
    </section>
</div>
