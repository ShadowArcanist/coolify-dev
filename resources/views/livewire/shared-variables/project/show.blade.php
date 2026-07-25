<div>
    <x-slot:title>
        Project Variables | Coolify
    </x-slot>

    @php($variableDescription = 'Available across this project with the syntax {' . '{ project.VARIABLENAME }' . '}.')

    <x-shared-variables.editor :resource="$project" :variables="$project->environment_variables->sortBy('key')"
        type="project" title="{{ $project->name }}" :description="$variableDescription"
        :view="$view" variablesLabel="Project shared variables" />
</div>
