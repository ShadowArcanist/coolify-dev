<div>
    <x-slot:title>
        Sentinel Configuration | Coolify
    </x-slot>
    <livewire:server.navbar :server="$server" />
    <div class="flex h-full flex-col gap-4 md:flex-row md:gap-8">
        <x-server.sidebar-sentinel :server="$server" :parameters="$parameters" />
        @if ($server->isFunctional())
            <div class="w-full">
                <livewire:server.sentinel :server="$server" />
            </div>
        @else
            <div class="application-settings-form w-full">
                <x-application.settings-section title="Sentinel"
                    helper="Monitor server and container health while collecting metrics.">
                    <x-empty size="sm" title="Server validation required"
                        description="Validate this server before enabling Sentinel.">
                        <x-slot:icon>
                            <x-reicon name="dashboard" class="size-8" />
                        </x-slot:icon>
                    </x-empty>
                </x-application.settings-section>
            </div>
        @endif
    </div>
</div>
