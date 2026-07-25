@props([
    'resource',
    'variables',
    'type',
    'title',
    'description',
    'view',
    'variablesLabel',
])

<div class="application-settings-form">
    <x-dashboard.navbar section="shared-variables">
        <x-slot:actions>
            <button type="button" class="button" wire:click="switch">
                <x-reicon :name="$view === 'normal' ? 'browser-code' : 'unordered-list'" class="size-3.5" />
                {{ $view === 'normal' ? 'Developer view' : 'Normal view' }}
            </button>

            @if ($view === 'normal')
                @can('update', $resource)
                    <x-modal-input title="New Shared Variable">
                        <x-slot:content>
                            <button type="button"
                                class="button bg-coollabs/10! text-coollabs! ring-1 ring-coollabs/25 hover:bg-coollabs/15! dark:bg-warning/15! dark:text-warning! dark:ring-warning/25 dark:hover:bg-warning/20!">
                                <x-reicon name="plus" class="size-3.5" />
                                Add variable
                            </button>
                        </x-slot:content>
                        <livewire:project.shared.environment-variable.add :shared="true" />
                    </x-modal-input>
                @endcan
            @endif
        </x-slot:actions>
    </x-dashboard.navbar>

    <x-application.settings-section :title="$title" :description="$description" flush>
        @if ($view === 'normal')
            @if ($variables->isEmpty())
                <x-empty title="No shared variables"
                    description="Add a variable to make it available to resources in this scope." size="sm">
                    <x-slot:icon>
                        <x-reicon name="variables" class="size-6" />
                    </x-slot:icon>
                </x-empty>
            @else
                <div class="data-table w-full">
                    <div class="data-table-header env-table-grid">
                        <span>Name</span>
                        <span>Scope</span>
                        <span>Comment</span>
                        <span class="text-center">Literal</span>
                        <span class="text-center">Multiline</span>
                        <span class="text-center">Buildtime</span>
                        <span class="text-center">Runtime</span>
                        <span></span>
                    </div>
                    @foreach ($variables as $env)
                        <livewire:project.shared.environment-variable.show
                            wire:key="shared-variable-{{ $type }}-{{ $env->id }}" :env="$env"
                            :type="$type" />
                    @endforeach
                    <div
                        class="flex min-h-11 items-center border-t border-neutral-200 px-4 text-[11px] text-neutral-500 dark:border-white/[0.08] dark:text-fg-faint">
                        {{ $variables->count() }} {{ Str::plural('variable', $variables->count()) }}
                    </div>
                </div>
            @endif
        @else
            <form wire:submit="submit" class="p-4">
                <x-unsaved-bar action="submit" />
                <x-forms.textarea canGate="update" :canResource="$resource" rows="20"
                    class="whitespace-pre-wrap" id="variables" wire:model="variables" monospace
                    :label="$variablesLabel" />
            </form>
        @endif
    </x-application.settings-section>
</div>
