<div>
    <x-slot:title>
        Server Variables | Coolify
    </x-slot>

    <x-shared-variables.editor :resource="$server"
        :variables="$server->environment_variables->whereNotIn('key', ['COOLIFY_SERVER_UUID', 'COOLIFY_SERVER_NAME'])->sortBy('key')"
        type="server" title="{{ $server->name }}"
        description="Available on this server with the syntax {{ '@{{ server.VARIABLENAME }}' }}."
        :view="$view" variablesLabel="Server shared variables" />
</div>
