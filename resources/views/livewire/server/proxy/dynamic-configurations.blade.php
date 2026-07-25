<div>
    <x-slot:title>
        Proxy Dynamic Configuration | Coolify
    </x-slot>

    <livewire:server.navbar :server="$server" />

    <div class="flex h-full flex-col gap-4 md:flex-row md:gap-8">
        <x-server.sidebar-proxy :server="$server" :parameters="$parameters" />

        <div class="application-settings-form w-full">
            @if ($server->isFunctional())
                <x-application.settings-section id="proxy-dynamic-configurations-section"
                    title="Dynamic configurations"
                    helper="Manage additional proxy routes, middleware, and services loaded at runtime." flush>
                    <x-slot:actions>
                        <div class="flex items-center gap-2">
                            <x-forms.button wire:click="loadDynamicConfigurations">
                                <x-reicon name="refresh" class="size-3.5" />
                                Reload
                            </x-forms.button>
                            @can('update', $server)
                                <x-modal-input buttonTitle="+ Add" title="New Dynamic Configuration">
                                    <livewire:server.proxy.new-dynamic-configuration :server_id="$server->id" />
                                </x-modal-input>
                            @endcan
                        </div>
                    </x-slot:actions>

                    <div wire:loading wire:target="initLoadDynamicConfigurations" class="p-6">
                        <x-loading text="Loading dynamic configurations…" />
                    </div>

                    <div x-init="$wire.initLoadDynamicConfigurations">
                        @if ($contents?->isNotEmpty())
                            @foreach ($contents as $fileName => $value)
                                <div
                                    class="border-b border-neutral-200 px-4 py-4 last:border-b-0 dark:border-white/[0.08]">
                                    @php($displayName = str_replace('|', '.', $fileName))
                                    @if (in_array($displayName, [
                                        'coolify.yaml',
                                        'Caddyfile',
                                        'coolify.caddy',
                                        'default_redirect_503.yaml',
                                        'default_redirect_503.caddy',
                                    ]))
                                        <div class="mb-3 flex items-center gap-2">
                                            <p class="font-mono text-sm font-medium text-neutral-950 dark:text-fg">
                                                {{ $displayName }}
                                            </p>
                                            <x-status-badge status="Managed" type="neutral" />
                                        </div>
                                    @else
                                        <livewire:server.proxy.dynamic-configuration-navbar
                                            :server_id="$server->id" :server="$server" :fileName="$fileName"
                                            :value="$value ?? ''" :newFile="false"
                                            wire:key="{{ $fileName }}-{{ $loop->index }}" />
                                    @endif
                                    <div class="mt-3">
                                        <x-forms.textarea disabled wire:model="contents.{{ $fileName }}"
                                            rows="8" />
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div wire:loading.remove>
                                <x-empty size="sm" title="No dynamic configurations"
                                    description="Add a configuration file to extend the proxy at runtime.">
                                    <x-slot:icon>
                                        <x-reicon name="file-content" class="size-8" />
                                    </x-slot:icon>
                                </x-empty>
                            </div>
                        @endif
                    </div>
                </x-application.settings-section>
            @else
                <x-application.settings-section title="Dynamic configurations"
                    helper="Manage additional runtime proxy configuration.">
                    <x-empty size="sm" title="Server validation required"
                        description="Validate this server before loading proxy configuration.">
                        <x-slot:icon>
                            <x-reicon name="file-content" class="size-8" />
                        </x-slot:icon>
                    </x-empty>
                </x-application.settings-section>
            @endif
        </div>
    </div>
</div>
