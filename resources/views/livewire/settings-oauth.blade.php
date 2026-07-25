<div>
    <x-slot:title>
        Authentication | Coolify
    </x-slot>

    <x-settings.navbar />

    <form wire:submit="submit" class="application-settings-form mx-auto w-full max-w-[930px]">
        <x-unsaved-bar action="submit" />

        <div class="application-settings-workspace flex flex-col gap-6">
            <h1 class="text-[24px]! leading-7! font-semibold! tracking-tight!">OAuth</h1>
            @foreach ($oauth_settings_map as $oauth_setting)
                @php
                    $provider = $oauth_setting['provider'];
                    $providerLabel = str($provider)->headline();
                @endphp

                <x-application.settings-section title="{{ $providerLabel }}">
                    <div class="grid gap-4 lg:grid-cols-2">
                        <x-forms.listbox id="oauth_settings_map.{{ $provider }}.enabled"
                            label="Provider status" :options="[
                                ['value' => true, 'label' => 'Enabled'],
                                ['value' => false, 'label' => 'Disabled'],
                            ]" />

                        <x-forms.input id="oauth_settings_map.{{ $provider }}.redirect_uri"
                            placeholder="{{ route('auth.callback', $provider) }}" label="Redirect URI" />

                        <x-forms.input id="oauth_settings_map.{{ $provider }}.client_id"
                            label="Client ID" />
                        <x-forms.input id="oauth_settings_map.{{ $provider }}.client_secret"
                            type="password" label="Client secret" autocomplete="new-password" />

                        @if ($provider === 'azure')
                            <x-forms.input id="oauth_settings_map.{{ $provider }}.tenant"
                                label="Tenant" />
                        @endif

                        @if ($provider === 'google')
                            <x-forms.input id="oauth_settings_map.{{ $provider }}.tenant"
                                helper="Optional hosted domain supplied to Google as a login hint."
                                label="Hosted domain" />
                        @endif

                        @if (in_array($provider, ['authentik', 'clerk', 'zitadel', 'gitlab'], true))
                            <x-forms.input id="oauth_settings_map.{{ $provider }}.base_url"
                                label="Base URL" />
                        @endif
                    </div>
                </x-application.settings-section>
            @endforeach
        </div>
    </form>
</div>
