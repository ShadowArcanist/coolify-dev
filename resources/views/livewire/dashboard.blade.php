<div class="application-settings-form w-full max-w-[1180px]">
    <x-slot:title>
        Dashboard | Coolify
    </x-slot>

    @if (session('error'))
        <span x-data x-init="$wire.dispatch('error', @js(session('error')))" />
    @endif

    @php
        $dashboardItemLimit = 6;
        $dashboardProjects = $projects->sortBy('name', SORT_NATURAL)->take($dashboardItemLimit);
        $dashboardServers = $servers->sortBy('name', SORT_NATURAL)->take($dashboardItemLimit);
    @endphp

    <header class="mb-6">
        <h1 class="text-[24px]! leading-7! font-semibold! text-black dark:text-white">
            Dashboard
        </h1>
        <p class="mt-1 text-[13px] text-neutral-500 dark:text-fg-dim">
            {{ $projects->count() }} {{ str('project')->plural($projects->count()) }}
            <span class="px-1 text-neutral-300 dark:text-white/15">·</span>
            {{ $servers->count() }} {{ str('server')->plural($servers->count()) }}
            in your team
        </p>
    </header>

    <div class="grid min-w-0 grid-cols-1 items-start gap-6 xl:grid-cols-2">
        <x-application.settings-section title="Projects"
            helper="Projects organize environments and their deployed resources." flush>
            @can('create', App\Models\Project::class)
                <x-slot:actions>
                    <x-modal-input title="New Project">
                        <x-slot:content>
                            <button type="button"
                                class="button bg-coollabs/10! text-coollabs! ring-1 ring-coollabs/25 hover:bg-coollabs/15! dark:bg-warning/15! dark:text-warning! dark:ring-warning/25 dark:hover:bg-warning/20!">
                                <x-reicon name="plus" class="size-3.5" />
                                New project
                            </button>
                        </x-slot:content>
                        <livewire:project.add-empty />
                    </x-modal-input>
                </x-slot:actions>
            @endcan

            @forelse ($dashboardProjects as $project)
                @php
                    $firstEnvironment = $project->environments->first();
                    $resourceCount = collect([
                        $project->applications_count,
                        $project->services_count,
                        $project->postgresqls_count,
                        $project->redis_count,
                        $project->keydbs_count,
                        $project->dragonflies_count,
                        $project->clickhouses_count,
                        $project->mongodbs_count,
                        $project->mysqls_count,
                        $project->mariadbs_count,
                    ])->sum();
                @endphp

                <article
                    class="group relative flex min-h-16 items-center gap-3 border-b border-neutral-200 px-4 py-3 transition-colors last:border-b-0 hover:bg-neutral-50 dark:border-white/[0.08] dark:hover:bg-white/[0.025]">
                    <a href="{{ $project->navigateTo() }}" {{ wireNavigate() }}
                        class="absolute inset-0"
                        aria-label="Open {{ $project->name }}"></a>

                    <div
                        class="flex size-9 shrink-0 items-center justify-center rounded-lg border border-neutral-200 bg-neutral-50 text-neutral-500 dark:border-white/[0.08] dark:bg-white/[0.04] dark:text-fg-dim">
                        <x-reicon name="projects" class="size-4" />
                    </div>

                    <div class="min-w-0 flex-1">
                        <h2 class="truncate text-[13px]! leading-4! font-semibold! text-black dark:text-fg">
                            {{ $project->name }}
                        </h2>
                        <p class="mt-0.5 truncate text-[11px] text-neutral-500 dark:text-fg-faint">
                            {{ $project->description ?: 'No description' }}
                        </p>
                    </div>

                    <div class="hidden shrink-0 text-right sm:block">
                        <p class="text-[11px] text-neutral-600 dark:text-fg-dim">
                            {{ $project->environments->count() }}
                            {{ str('environment')->plural($project->environments->count()) }}
                        </p>
                        <p class="mt-0.5 text-[11px] text-neutral-400 dark:text-fg-faint">
                            {{ $resourceCount }} {{ str('resource')->plural($resourceCount) }}
                        </p>
                    </div>

                    <div class="relative z-10 flex shrink-0 items-center gap-0.5">
                        @if ($firstEnvironment)
                            @can('createAnyResource')
                                <a href="{{ route('project.resource.create', [
                                    'project_uuid' => $project->uuid,
                                    'environment_uuid' => $firstEnvironment->uuid,
                                ]) }}"
                                    {{ wireNavigate() }}
                                    class="flex size-7 items-center justify-center rounded-md text-neutral-400 transition-colors hover:bg-neutral-100 hover:text-black dark:text-fg-faint dark:hover:bg-white/[0.06] dark:hover:text-fg"
                                    title="Add resource" aria-label="Add resource to {{ $project->name }}">
                                    <x-reicon name="plus" class="size-3.5" />
                                </a>
                            @endcan
                        @endif
                        @can('update', $project)
                            <a href="{{ route('project.edit', ['project_uuid' => $project->uuid]) }}"
                                {{ wireNavigate() }}
                                class="flex size-7 items-center justify-center rounded-md text-neutral-400 transition-colors hover:bg-neutral-100 hover:text-black dark:text-fg-faint dark:hover:bg-white/[0.06] dark:hover:text-fg"
                                title="Project settings" aria-label="Open settings for {{ $project->name }}">
                                <x-reicon name="settings" class="size-3.5" />
                            </a>
                        @endcan
                    </div>
                </article>
            @empty
                <div class="p-6">
                    <x-empty size="sm" title="No projects yet"
                        description="Create a project to organize environments and resources.">
                        <x-slot:icon>
                            <x-reicon name="projects" class="size-8" />
                        </x-slot:icon>
                    </x-empty>
                </div>
            @endforelse

            @if ($projects->count() > $dashboardItemLimit)
                <a href="{{ route('project.index') }}" {{ wireNavigate() }}
                    class="flex min-h-10 items-center justify-between border-t border-neutral-200 px-4 text-[12px] font-medium text-neutral-500 transition-colors hover:bg-neutral-50 hover:text-black dark:border-white/[0.08] dark:text-fg-dim dark:hover:bg-white/[0.025] dark:hover:text-fg">
                    View all projects
                    <x-reicon name="arrow-right" class="size-3.5" />
                </a>
            @endif
        </x-application.settings-section>

        <x-application.settings-section title="Servers"
            helper="Servers provide the infrastructure where Coolify deploys resources." flush>
            @can('create', App\Models\Server::class)
                <x-slot:actions>
                    @if ($privateKeys->isNotEmpty())
                        <a href="{{ route('server.create') }}" {{ wireNavigate() }}
                            class="button bg-coollabs/10! text-coollabs! ring-1 ring-coollabs/25 hover:bg-coollabs/15! dark:bg-warning/15! dark:text-warning! dark:ring-warning/25 dark:hover:bg-warning/20!">
                            <x-reicon name="plus" class="size-3.5" />
                            New server
                        </a>
                    @else
                        <x-modal-input title="New Private Key">
                            <x-slot:content>
                                <button type="button"
                                    class="button bg-coollabs/10! text-coollabs! ring-1 ring-coollabs/25 hover:bg-coollabs/15! dark:bg-warning/15! dark:text-warning! dark:ring-warning/25 dark:hover:bg-warning/20!">
                                    <x-reicon name="plus" class="size-3.5" />
                                    Add private key
                                </button>
                            </x-slot:content>
                            <livewire:security.private-key.create from="server" />
                        </x-modal-input>
                    @endif
                </x-slot:actions>
            @endcan

            @forelse ($dashboardServers as $server)
                @php
                    [$serverStatus, $serverStatusType] = match (true) {
                        $server->settings->force_disabled => ['Disabled', 'error'],
                        ! $server->settings->is_reachable && ! $server->settings->is_usable => ['Unavailable', 'error'],
                        ! $server->settings->is_reachable => ['Unreachable', 'error'],
                        ! $server->settings->is_usable => ['Not ready', 'warning'],
                        default => ['Ready', 'success'],
                    };
                @endphp

                <a href="{{ route('server.show', ['server_uuid' => $server->uuid]) }}"
                    {{ wireNavigate() }}
                    class="group flex min-h-16 items-center gap-3 border-b border-neutral-200 px-4 py-3 transition-colors last:border-b-0 hover:bg-neutral-50 dark:border-white/[0.08] dark:hover:bg-white/[0.025]">
                    <div
                        class="flex size-9 shrink-0 items-center justify-center rounded-lg border border-neutral-200 bg-neutral-50 text-neutral-500 dark:border-white/[0.08] dark:bg-white/[0.04] dark:text-fg-dim">
                        <x-reicon name="servers" class="size-4" />
                    </div>

                    <div class="min-w-0 flex-1">
                        <h2 class="truncate text-[13px]! leading-4! font-semibold! text-black dark:text-fg">
                            {{ $server->name }}
                        </h2>
                        <p class="mt-0.5 truncate text-[11px] text-neutral-500 dark:text-fg-faint">
                            {{ $server->description ?: 'No description' }}
                        </p>
                    </div>

                    <x-status-badge :status="$serverStatus" :type="$serverStatusType" />
                    <x-reicon name="arrow-right"
                        class="size-3.5 shrink-0 text-neutral-300 transition-colors group-hover:text-neutral-500 dark:text-white/15 dark:group-hover:text-fg-dim" />
                </a>
            @empty
                <div class="p-6">
                    <x-empty size="sm"
                        :title="$privateKeys->isEmpty() ? 'A private key is required' : 'No servers yet'"
                        :description="$privateKeys->isEmpty()
                            ? 'Add a private key before connecting your first server.'
                            : 'Connect a server to start deploying resources.'">
                        <x-slot:icon>
                            <x-reicon name="servers" class="size-8" />
                        </x-slot:icon>
                    </x-empty>
                </div>
            @endforelse

            @if ($servers->count() > $dashboardItemLimit)
                <a href="{{ route('server.index') }}" {{ wireNavigate() }}
                    class="flex min-h-10 items-center justify-between border-t border-neutral-200 px-4 text-[12px] font-medium text-neutral-500 transition-colors hover:bg-neutral-50 hover:text-black dark:border-white/[0.08] dark:text-fg-dim dark:hover:bg-white/[0.025] dark:hover:text-fg">
                    View all servers
                    <x-reicon name="arrow-right" class="size-3.5" />
                </a>
            @endif
        </x-application.settings-section>
    </div>
</div>
