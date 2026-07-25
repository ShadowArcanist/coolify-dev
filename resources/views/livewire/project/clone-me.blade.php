<div>
    <x-slot:title>{{ data_get_str($project, 'name')->limit(10) }} > Clone | Coolify</x-slot>
    <x-project.navbar :project="$project" :environment="$environment" />

    <div class="mt-8 w-full max-w-[1180px] space-y-8 lg:mt-3">
        <section class="application-settings-section">
            <div class="application-settings-section-header">
                <div>
                    <h2>Clone environment</h2>
                    <p>Copy every resource from {{ $environment->name }} to a new project or environment.</p>
                </div>
            </div>
            <div class="application-settings-section-body">
                <div class="max-w-md">
                    <x-forms.input required id="newName" label="New name" />
                </div>
            </div>
        </section>

        <section class="application-settings-section">
            <div class="application-settings-section-header">
                <div>
                    <h2>Destination</h2>
                    <p>Choose the server and Docker network that will receive the cloned resources.</p>
                </div>
            </div>
            <div class="application-settings-section-body p-0!">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[620px] table-fixed text-left">
                        <thead>
                            <tr
                                class="border-b border-neutral-200 bg-neutral-50 text-[11px] font-semibold tracking-wide text-neutral-500 uppercase dark:border-white/[0.06] dark:bg-white/[0.025] dark:text-fg-faint">
                                <th class="w-8 px-4 py-2.5"><span class="sr-only">Selected</span></th>
                                <th class="w-[46%] px-4 py-2.5">Server</th>
                                <th class="px-4 py-2.5">Network</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-white/[0.06]">
                            @foreach ($servers->sortBy('id') as $server)
                                @foreach ($server->destinations() as $destination)
                                    <tr wire:click="selectServer('{{ $server->id }}', '{{ $destination->uuid }}')"
                                        @class([
                                            'cursor-pointer transition-colors hover:bg-neutral-50 dark:hover:bg-white/[0.025]',
                                            'bg-coollabs/5 dark:bg-warning/[0.06]' => $selectedDestination === $destination->uuid,
                                        ])>
                                        <td class="px-4 py-3">
                                            <span @class([
                                                'flex size-4 items-center justify-center rounded-full border',
                                                'border-coollabs bg-coollabs text-white dark:border-warning dark:bg-warning dark:text-black' => $selectedDestination === $destination->uuid,
                                                'border-neutral-300 dark:border-white/[0.15]' => $selectedDestination !== $destination->uuid,
                                            ])>
                                                @if ($selectedDestination === $destination->uuid)
                                                    <span class="size-1.5 rounded-full bg-current"></span>
                                                @endif
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm font-semibold text-black dark:text-fg">
                                            {{ $server->name }}
                                        </td>
                                        <td class="px-4 py-3 font-mono text-xs text-neutral-600 dark:text-fg-dim">
                                            {{ $destination->name }}
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="application-settings-section">
            @php
                $resourceCount = $environment->applications->count()
                    + $environment->databases()->count()
                    + $environment->services->count();
            @endphp
            <div class="application-settings-section-header">
                <div>
                    <h2>Resources</h2>
                    <p>{{ $resourceCount }} {{ Str::plural('resource', $resourceCount) }} will be cloned.</p>
                </div>
            </div>
            <div class="application-settings-section-body p-0!">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[680px] table-fixed text-left">
                        <thead>
                            <tr
                                class="border-b border-neutral-200 bg-neutral-50 text-[11px] font-semibold tracking-wide text-neutral-500 uppercase dark:border-white/[0.06] dark:bg-white/[0.025] dark:text-fg-faint">
                                <th class="w-[34%] px-4 py-2.5">Name</th>
                                <th class="w-[18%] px-4 py-2.5">Type</th>
                                <th class="px-4 py-2.5">Description</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-200 dark:divide-white/[0.06]">
                            @foreach ($environment->applications->sortBy('name') as $application)
                                <tr>
                                    <td class="px-4 py-3 text-sm font-semibold text-black dark:text-fg">
                                        {{ $application->name }}
                                    </td>
                                    <td class="px-4 py-3"><x-status-badge status="Application" type="neutral" /></td>
                                    <td class="truncate px-4 py-3 text-sm text-neutral-600 dark:text-fg-dim">
                                        {{ $application->description ?: '—' }}
                                    </td>
                                </tr>
                            @endforeach
                            @foreach ($environment->databases()->sortBy('name') as $database)
                                <tr>
                                    <td class="px-4 py-3 text-sm font-semibold text-black dark:text-fg">
                                        {{ $database->name }}
                                    </td>
                                    <td class="px-4 py-3"><x-status-badge status="Database" type="neutral" /></td>
                                    <td class="truncate px-4 py-3 text-sm text-neutral-600 dark:text-fg-dim">
                                        {{ $database->description ?: '—' }}
                                    </td>
                                </tr>
                            @endforeach
                            @foreach ($environment->services->sortBy('name') as $service)
                                <tr>
                                    <td class="px-4 py-3 text-sm font-semibold text-black dark:text-fg">
                                        {{ $service->name }}
                                    </td>
                                    <td class="px-4 py-3"><x-status-badge status="Service" type="neutral" /></td>
                                    <td class="truncate px-4 py-3 text-sm text-neutral-600 dark:text-fg-dim">
                                        {{ $service->description ?: '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div
                    class="flex flex-col gap-2 border-t border-neutral-200 p-4 sm:flex-row sm:justify-end dark:border-white/[0.06]">
                    <x-forms.button isHighlighted wire:click="clone('environment')"
                        :disabled="! filled($selectedDestination)">
                        Clone to environment
                    </x-forms.button>
                    <x-forms.button isHighlighted wire:click="clone('project')"
                        :disabled="! filled($selectedDestination)">
                        Clone to project
                    </x-forms.button>
                </div>
            </div>
        </section>
    </div>
</div>
