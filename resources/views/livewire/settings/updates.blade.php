<div>
    <x-slot:title>
        Update Settings | Coolify
    </x-slot>

    <x-settings.navbar />

    <div
        class="application-settings-workspace grid w-full min-w-0 gap-8 xl:grid-cols-[210px_minmax(0,1fr)] xl:gap-10">
        <x-settings.sidebar activeMenu="updates" />

        <form wire:submit="submit" class="application-settings-form flex min-w-0 flex-col gap-6">
            <x-unsaved-bar action="submit" />

            <x-application.settings-section title="Update checks"
                description="Schedule version checks and refresh service templates.">
                <x-slot:actions>
                    <button type="button" class="button" wire:click="checkManually">
                        <x-reicon name="refresh" class="size-3.5" />
                        Check now
                    </button>
                </x-slot:actions>
                <x-forms.input required id="update_check_frequency" label="Check frequency"
                    placeholder="0 * * * *"
                    helper="A cron expression or preset such as hourly, daily, weekly, monthly, or yearly." />
            </x-application.settings-section>

            <x-application.settings-section title="Automatic updates"
                description="Choose whether Coolify should install new versions automatically.">
                <div class="grid gap-4 lg:grid-cols-2">
                    @if (!is_null(config('constants.coolify.autoupdate', null)))
                        <x-forms.checkbox instantSave disabled
                            checked="{{ config('constants.coolify.autoupdate') }}"
                            label="Automatic updates"
                            helper="Controlled by the AUTOUPDATE environment variable." />
                    @else
                        <x-forms.checkbox instantSave id="is_auto_update_enabled"
                            label="Automatic updates" />
                    @endif

                    @if (is_null(config('constants.coolify.autoupdate', null)) && $is_auto_update_enabled)
                        <x-forms.input required id="auto_update_frequency" label="Update frequency"
                            placeholder="0 0 * * *"
                            helper="Cron expression or preset for installing updates." />
                    @else
                        <x-forms.input label="Update frequency" disabled placeholder="Disabled" />
                    @endif
                </div>
            </x-application.settings-section>

            <x-application.settings-section title="Image registry"
                description="Select the registry used to pull Coolify release images.">
                <div class="max-w-md">
                    <x-forms.listbox id="docker_registry_url" label="Docker registry" :options="[
                        ['value' => 'docker.io', 'label' => 'Docker Hub'],
                        ['value' => 'ghcr.io', 'label' => 'GitHub Container Registry'],
                    ]"
                        helper="Switch registries if the current source is rate limited." />
                </div>
            </x-application.settings-section>
        </form>
    </div>
</div>
