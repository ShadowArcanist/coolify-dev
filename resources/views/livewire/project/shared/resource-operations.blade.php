<div x-data="{
    selectedCloneServer: null,
    selectedCloneDestination: null,
    selectedMoveProject: null,
    selectedMoveEnvironment: null,
    currentProjectId: {{ $resource->environment->project->id }},
    currentEnvironmentId: {{ $resource->environment->id }},
    servers: @js(
        $servers->map(
            fn ($server) => [
                'id' => $server->id,
                'name' => $server->name,
                'ip' => $server->ip,
                'destinations' => $server->destinations()->map(
                    fn ($destination) => [
                        'id' => $destination->id,
                        'uuid' => $destination->uuid,
                        'name' => $destination->name,
                        'server_id' => $server->id,
                    ],
                ),
            ],
        )->values(),
    ),
    projects: @js(
        $projects->map(
            fn ($project) => [
                'id' => $project->id,
                'name' => $project->name,
                'environments' => $project->environments->map(
                    fn ($environment) => [
                        'id' => $environment->id,
                        'name' => $environment->name,
                        'project_id' => $project->id,
                    ],
                )->values(),
            ],
        )->values(),
    ),
    get availableDestinations() {
        if (!this.selectedCloneServer) return [];
        const server = this.servers.find(server => server.id == this.selectedCloneServer);
        return server ? server.destinations : [];
    },
    get availableEnvironments() {
        if (!this.selectedMoveProject) return [];
        const project = this.projects.find(project => project.id == this.selectedMoveProject);
        if (!project) return [];
        return project.environments.filter(environment => {
            if (project.id == this.currentProjectId) {
                return environment.id != this.currentEnvironmentId;
            }
            return true;
        });
    },
    get isCurrentProjectSelected() {
        return this.selectedMoveProject == this.currentProjectId;
    }
}" class="flex flex-col gap-6">
    @can('update', $resource)
        <x-application.settings-section id="clone-resource-section" title="Clone resource"
            helper="Duplicate this resource configuration onto another server and network destination.">
            <x-callout type="info" title="Configuration only">
                Cloning copies settings, environment variables, and resource configuration. Stored files,
                database records, and other persistent data are not copied.
            </x-callout>

            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <div>
                    <label for="clone-resource-server">Server</label>
                    <select id="clone-resource-server" x-model="selectedCloneServer"
                        @change="selectedCloneDestination = null" class="select w-full">
                        <option value="">Choose a server…</option>
                        <template x-for="server in servers" :key="server.id">
                            <option :value="server.id" x-text="`${server.name} (${server.ip})`"></option>
                        </template>
                        @foreach ($buildServers as $buildServer)
                            <option disabled>{{ $buildServer->name }} — build server</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="clone-resource-destination">Network destination</label>
                    <select id="clone-resource-destination" x-model="selectedCloneDestination"
                        :disabled="!selectedCloneServer" class="select w-full">
                        <option value="">Choose a destination…</option>
                        <template x-for="destination in availableDestinations" :key="destination.uuid">
                            <option :value="destination.uuid" x-text="destination.name"></option>
                        </template>
                    </select>
                </div>
            </div>

            <div x-show="selectedCloneDestination" x-cloak
                class="mt-4 flex flex-col gap-3 border-t border-neutral-200 pt-4 sm:flex-row sm:items-center sm:justify-between dark:border-white/[0.07]">
                <p class="text-[13px] text-neutral-500 dark:text-fg-dim">
                    The running resource will not be changed.
                </p>
                <x-forms.button @click="$wire.cloneTo(selectedCloneDestination)">
                    Clone resource
                </x-forms.button>
            </div>
        </x-application.settings-section>

        <x-application.settings-section id="move-resource-section" title="Move resource"
            helper="Transfer this resource to another project environment without changing the running deployment.">
            @if ($projects->count() > 0)
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="move-resource-project">Project</label>
                        <select id="move-resource-project" x-model="selectedMoveProject"
                            @change="selectedMoveEnvironment = null" class="select w-full">
                            <option value="">Choose a project…</option>
                            <template x-for="project in projects" :key="project.id">
                                <option :value="project.id"
                                    x-text="project.name + (project.id == currentProjectId ? ' (current)' : '')">
                                </option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label for="move-resource-environment" class="flex items-center gap-1.5">
                            Environment
                            <x-helper helper="The current environment is excluded." />
                        </label>
                        <select id="move-resource-environment" x-model="selectedMoveEnvironment"
                            :disabled="!selectedMoveProject || availableEnvironments.length === 0"
                            class="select w-full">
                            <option value=""
                                x-text="availableEnvironments.length === 0 && isCurrentProjectSelected
                                    ? 'No other environments available'
                                    : 'Choose an environment…'">
                            </option>
                            <template x-for="environment in availableEnvironments" :key="environment.id">
                                <option :value="environment.id" x-text="environment.name"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <div x-show="selectedMoveEnvironment" x-cloak
                    class="mt-4 flex flex-col gap-3 border-t border-neutral-200 pt-4 sm:flex-row sm:items-center sm:justify-between dark:border-white/[0.07]">
                    <p class="text-[13px] text-neutral-500 dark:text-fg-dim">
                        All configuration will move with the resource.
                    </p>
                    <x-forms.button @click="$wire.moveTo(selectedMoveEnvironment)">
                        Move resource
                    </x-forms.button>
                </div>
            @else
                <x-empty size="sm" title="No destination environments"
                    description="Create another project or environment before moving this resource.">
                    <x-slot:icon>
                        <x-reicon name="projects" class="size-8" />
                    </x-slot:icon>
                </x-empty>
            @endif
        </x-application.settings-section>
    @else
        <x-callout type="danger" title="Insufficient permissions">
            You do not have permission to clone or move this resource. Contact a team administrator for access.
        </x-callout>
    @endcan
</div>
