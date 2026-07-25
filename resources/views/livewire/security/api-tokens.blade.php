<div>
    <x-slot:title>
        API Tokens | Coolify
    </x-slot>

    <x-security.navbar />

    @if (!$isApiEnabled)
        <div class="application-settings-form">
            <x-application.settings-section title="API disabled"
                description="Enable the Coolify API before creating access tokens.">
                <x-empty title="API access is turned off"
                    description="Enable API access in instance settings to issue tokens." size="sm">
                    <x-slot:icon>
                        <x-reicon name="keys" class="size-6" />
                    </x-slot:icon>
                    <x-slot:actions>
                        <a href="{{ route('settings.advanced') }}" class="button" {{ wireNavigate() }}>
                            Open settings
                        </a>
                    </x-slot:actions>
                </x-empty>
            </x-application.settings-section>
        </div>
    @else
        @php
            $expirationList = collect($expirationOptions)
                ->map(fn ($label, $days) => ['value' => (string) $days, 'label' => $label])
                ->values()
                ->push(['value' => '', 'label' => 'Never'])
                ->all();
        @endphp

        <div class="application-settings-form flex flex-col gap-6">
            @can('create', App\Models\PersonalAccessToken::class)
                <form wire:submit="addNewToken">
                    <x-application.settings-section title="New API token"
                        description="Tokens are scoped to the current team and only shown once.">
                        <x-slot:actions>
                            <button type="submit"
                                class="button bg-coollabs/10! text-coollabs! ring-1 ring-coollabs/25 hover:bg-coollabs/15! dark:bg-warning/15! dark:text-warning! dark:ring-warning/25 dark:hover:bg-warning/20!">
                                <x-reicon name="plus" class="size-3.5" />
                                Create token
                            </button>
                        </x-slot:actions>

                        <div class="grid gap-4 lg:grid-cols-2">
                            <x-forms.input required id="description" label="Description"
                                placeholder="CI deployment token" />
                            <x-forms.listbox id="expiresInDays" label="Expires in"
                                :options="$expirationList" />
                        </div>

                        <div class="mt-5 border-t border-neutral-200 pt-4 dark:border-white/[0.08]">
                            <div class="mb-3 flex items-center gap-2">
                                <h4 class="text-[12px] font-semibold text-black dark:text-fg">Permissions</h4>
                                <x-helper helper="Only grant the abilities this token needs." />
                            </div>
                            <div class="grid gap-3 lg:grid-cols-2">
                                @if ($canUseRootPermissions)
                                    <x-forms.checkbox label="Root" wire:model.live="permissions" domValue="root"
                                        helper="Full access to every API operation." :checked="in_array('root', $permissions)" />
                                @else
                                    <x-forms.checkbox label="Root" disabled domValue="root"
                                        helper="Requires an admin or owner role." :checked="false" />
                                @endif

                                @if (!in_array('root', $permissions))
                                    @if ($canUseWritePermissions)
                                        <x-forms.checkbox label="Write" wire:model.live="permissions"
                                            domValue="write" helper="Create and update resources."
                                            :checked="in_array('write', $permissions)" />
                                    @else
                                        <x-forms.checkbox label="Write" disabled domValue="write"
                                            helper="Requires an admin or owner role." :checked="false" />
                                    @endif

                                    @if ($canUseDeployPermissions)
                                        <x-forms.checkbox label="Deploy" wire:model.live="permissions"
                                            domValue="deploy" helper="Trigger deployments through webhooks."
                                            :checked="in_array('deploy', $permissions)" />
                                    @else
                                        <x-forms.checkbox label="Deploy" disabled domValue="deploy"
                                            helper="Requires an admin or owner role." :checked="false" />
                                    @endif

                                    <x-forms.checkbox label="Read" wire:model.live="permissions" domValue="read"
                                        helper="Read non-sensitive resource data."
                                        :checked="in_array('read', $permissions)" />

                                    @if ($canUseSensitivePermissions)
                                        <x-forms.checkbox label="Read sensitive data" wire:model.live="permissions"
                                            domValue="read:sensitive"
                                            helper="Include secrets, logs, passwords, and Compose content."
                                            :checked="in_array('read:sensitive', $permissions)" />
                                    @else
                                        <x-forms.checkbox label="Read sensitive data" disabled
                                            domValue="read:sensitive" helper="Requires an admin or owner role."
                                            :checked="false" />
                                    @endif
                                @endif
                            </div>
                        </div>
                    </x-application.settings-section>
                </form>
            @endcan

            @if (session()->has('token'))
                <x-application.settings-section title="Copy your token"
                    description="This value will not be shown again after you leave this page.">
                    <div class="relative" x-data="{ copied: false }">
                        <input type="text" value="{{ session('token') }}" readonly
                            class="w-full pr-12! font-mono text-[12px]">
                        <button type="button"
                            x-on:click="copied = true; navigator.clipboard.writeText(@js(session('token'))); setTimeout(() => copied = false, 1200)"
                            class="absolute top-1/2 right-2 flex size-7 -translate-y-1/2 items-center justify-center rounded-md text-neutral-400 transition-colors hover:bg-neutral-100 hover:text-black dark:hover:bg-white/[0.06] dark:hover:text-fg"
                            title="Copy token">
                            <x-reicon name="file-content" class="size-3.5" />
                        </button>
                        <span x-cloak x-show="copied"
                            class="absolute top-full right-0 mt-1 text-[10px] text-success">Copied</span>
                    </div>
                </x-application.settings-section>
            @endif

            <x-application.settings-section title="Issued tokens"
                description="Active API credentials created for this team." flush>
                <div x-data="{ search: '' }">
                    @if ($tokens->count() > 1)
                        <div class="border-b border-neutral-200 p-3 dark:border-white/[0.08]">
                            <div class="relative max-w-sm">
                                <x-reicon name="search"
                                    class="pointer-events-none absolute top-1/2 left-2.5 z-10 size-3.5 -translate-y-1/2 text-neutral-400" />
                                <input x-model="search" placeholder="Search tokens"
                                    class="w-full pl-8! text-[12px]">
                            </div>
                        </div>
                    @endif

                    @if ($tokens->isEmpty())
                        <x-empty title="No API tokens" description="Create a token when an external client needs access."
                            size="sm">
                            <x-slot:icon>
                                <x-reicon name="keys" class="size-6" />
                            </x-slot:icon>
                        </x-empty>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[860px]">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-2.5 text-left">Description</th>
                                        <th class="px-4 py-2.5 text-left">Permissions</th>
                                        <th class="px-4 py-2.5 text-left">Last used</th>
                                        <th class="px-4 py-2.5 text-left">Created</th>
                                        <th class="px-4 py-2.5 text-left">Expires</th>
                                        <th class="px-4 py-2.5 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($tokens as $token)
                                        <tr wire:key="api-token-{{ $token->id }}"
                                            x-show="!search || @js(strtolower($token->name) . ' ' . strtolower(implode(' ', $token->abilities ?? []))).includes(search.toLowerCase())"
                                            class="border-t border-neutral-200 dark:border-white/[0.07]">
                                            <td class="px-4 py-3 text-[12px] font-medium text-black dark:text-fg">
                                                {{ $token->name }}
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach ($token->abilities ?? [] as $ability)
                                                        <span @class([
                                                            'rounded-full px-2 py-0.5 text-[10px] font-medium',
                                                            'bg-error/10 text-error' => $ability === 'root',
                                                            'bg-warning/10 text-warning' => in_array($ability, ['write', 'write:sensitive']),
                                                            'bg-coollabs/10 text-coollabs dark:bg-warning/10 dark:text-warning' => $ability === 'deploy',
                                                            'bg-neutral-100 text-neutral-600 dark:bg-white/[0.06] dark:text-fg-dim' => in_array($ability, ['read', 'read:sensitive']),
                                                        ])>{{ $ability }}</span>
                                                    @endforeach
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-[11px] text-neutral-500 dark:text-fg-dim">
                                                {{ $token->last_used_at?->diffForHumans() ?? 'Never' }}
                                            </td>
                                            <td class="px-4 py-3 text-[11px] text-neutral-500 dark:text-fg-dim">
                                                {{ $token->created_at->format('Y-m-d') }}
                                            </td>
                                            <td class="px-4 py-3 text-[11px] text-neutral-500 dark:text-fg-dim">
                                                @if (!$token->expires_at)
                                                    Never
                                                @elseif ($token->expires_at->isPast())
                                                    <x-status-badge label="Expired" type="error" />
                                                @else
                                                    {{ $token->expires_at->format('Y-m-d') }}
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                @if (auth()->id() === $token->tokenable_id)
                                                    <x-modal-confirmation title="Confirm API Token Revocation?"
                                                        isErrorButton buttonTitle="Revoke"
                                                        submitAction="revoke({{ $token->id }})" :actions="[
                                                            'This API token will be permanently revoked.',
                                                        ]"
                                                        confirmationText="{{ $token->name }}"
                                                        confirmationLabel="Enter the token description to confirm"
                                                        shortConfirmationLabel="Token description"
                                                        :confirmWithPassword="false" step2ButtonText="Revoke token" />
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </x-application.settings-section>
        </div>
    @endif
</div>
