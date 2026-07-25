<div>
    <x-slot:title>
        Team Variables | Coolify
    </x-slot>

    <x-shared-variables.editor :resource="$team" :variables="$team->environment_variables->sortBy('key')"
        type="team" title="Team variables"
        description="Available across this team with the syntax {{ '@{{ team.VARIABLENAME }}' }}."
        :view="$view" variablesLabel="Team shared variables" />
</div>
