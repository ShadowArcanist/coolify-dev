<div>
    <x-slot:title>
        Environment Variables | Coolify
    </x-slot>

    <x-shared-variables.editor :resource="$environment"
        :variables="$environment->environment_variables->sortBy('key')" type="environment"
        title="{{ $project->name }} / {{ $environment->name }}"
        description="Available in this environment with the syntax {{ '@{{ environment.VARIABLENAME }}' }}."
        :view="$view" variablesLabel="Environment shared variables" />
</div>
