<div>
    <x-slot:title>
        Environment Variables | Coolify
    </x-slot>

    @php($variableDescription = 'Available in this environment with the syntax {' . '{ environment.VARIABLENAME }' . '}.')

    <x-shared-variables.editor :resource="$environment"
        :variables="$environment->environment_variables->sortBy('key')" type="environment"
        title="{{ $project->name }} / {{ $environment->name }}" :description="$variableDescription"
        :view="$view" variablesLabel="Environment shared variables" />
</div>
