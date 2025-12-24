<div>
    <x-slot:title>
        {{ data_get_str($server, 'name')->limit(10) }} > Environment Variables | Coolify
    </x-slot>
    <livewire:server.navbar :server="$server" />
    <div class="flex flex-col h-full gap-8 sm:flex-row">
        <x-server.sidebar :server="$server" activeMenu="environment-variables" />
        <div class="w-full">
            <livewire:project.shared.environment-variable.all :resource="$server" :runtimeOnly="true" />
        </div>
    </div>
</div>