<div>
    <x-slot:title>
        Team Variables | Coolify
    </x-slot>

    @php($variableDescription = 'Available across this team with the syntax {' . '{ team.VARIABLENAME }' . '}.')

    <x-shared-variables.editor :resource="$team" :variables="$team->environment_variables->sortBy('key')"
        type="team" title="Team variables" :description="$variableDescription"
        :view="$view" variablesLabel="Team shared variables" />
</div>
