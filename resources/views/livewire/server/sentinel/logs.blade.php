<div>
    <x-slot:title>
        Sentinel Logs | Coolify
    </x-slot>
    <livewire:server.navbar :server="$server" />
    <div
        class="server-settings-workspace application-settings-workspace mt-8 grid w-full max-w-[1180px] min-w-0 gap-8 xl:mt-0 xl:grid-cols-[210px_minmax(0,1fr)] xl:gap-10">
        <x-server.sidebar-sentinel :server="$server" :parameters="$parameters" />
        <div class="application-settings-form flex w-full flex-col gap-6">
            <x-application.settings-section title="Sentinel logs"
                helper="Live output from the Sentinel container on this server.">
                <x-slot:actions>
                    <x-status-badge :status="$server->isSentinelLive() ? 'In sync' : 'Out of sync'"
                        :type="$server->isSentinelLive() ? 'success' : 'warning'" />
                </x-slot:actions>
                <p class="text-sm text-neutral-600 dark:text-fg-dim">
                    Search, filter, follow, copy, or download recent Sentinel output.
                </p>
            </x-application.settings-section>
            <livewire:project.shared.get-logs :server="$server" container="coolify-sentinel"
                displayName="Sentinel" :collapsible="false" />
        </div>
    </div>
</div>
