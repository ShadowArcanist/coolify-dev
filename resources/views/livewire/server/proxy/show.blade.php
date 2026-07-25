<div>
    <x-slot:title>
        Proxy Configuration | Coolify
    </x-slot>
    <livewire:server.navbar :server="$server" />
    <div class="flex h-full flex-col gap-4 md:flex-row md:gap-8">
        <x-server.sidebar-proxy :server="$server" :parameters="$parameters" />
        @if ($server->isFunctional())
            <div class="w-full">
                <livewire:server.proxy :server="$server" />
            </div>
        @else
            <div class="application-settings-form w-full">
                <x-application.settings-section title="Proxy"
                    helper="Configure the reverse proxy for this server.">
                    <x-empty size="sm" title="Server validation required"
                        description="Validate this server before configuring its proxy.">
                        <x-slot:icon>
                            <x-reicon name="servers" class="size-8" />
                        </x-slot:icon>
                    </x-empty>
                </x-application.settings-section>
            </div>
        @endif
    </div>
</div>
