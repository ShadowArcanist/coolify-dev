<div class="mb-6">
    <header class="mb-5">
        <h1 class="text-[24px]! leading-7! font-semibold!">Keys &amp; Tokens</h1>
        <p class="mt-1 text-[13px] text-neutral-500 dark:text-fg-dim">
            Manage credentials and bootstrap configuration for your infrastructure.
        </p>
    </header>

    <nav class="flex items-center gap-1 overflow-x-auto border-b border-neutral-200 pb-2 dark:border-white/[0.08]"
        aria-label="Keys and tokens sections">
        <a class="inline-flex h-8 shrink-0 items-center rounded-lg px-3 text-[12px] font-medium transition-colors {{ request()->routeIs('security.private-key.*') ? 'bg-coollabs/10 text-coollabs dark:bg-warning/15 dark:text-warning' : 'text-neutral-500 hover:bg-neutral-100 hover:text-black dark:text-fg-dim dark:hover:bg-white/[0.06] dark:hover:text-fg' }}"
            href="{{ route('security.private-key.index') }}" {{ wireNavigate() }}>
            Private keys
        </a>
        @can('viewAny', App\Models\CloudProviderToken::class)
            <a class="inline-flex h-8 shrink-0 items-center rounded-lg px-3 text-[12px] font-medium transition-colors {{ request()->routeIs('security.cloud-tokens*') ? 'bg-coollabs/10 text-coollabs dark:bg-warning/15 dark:text-warning' : 'text-neutral-500 hover:bg-neutral-100 hover:text-black dark:text-fg-dim dark:hover:bg-white/[0.06] dark:hover:text-fg' }}"
                href="{{ route('security.cloud-tokens') }}" {{ wireNavigate() }}>
                Cloud tokens
            </a>
        @endcan
        @can('viewAny', App\Models\CloudInitScript::class)
            <a class="inline-flex h-8 shrink-0 items-center rounded-lg px-3 text-[12px] font-medium transition-colors {{ request()->routeIs('security.cloud-init-scripts*') ? 'bg-coollabs/10 text-coollabs dark:bg-warning/15 dark:text-warning' : 'text-neutral-500 hover:bg-neutral-100 hover:text-black dark:text-fg-dim dark:hover:bg-white/[0.06] dark:hover:text-fg' }}"
                href="{{ route('security.cloud-init-scripts') }}" {{ wireNavigate() }}>
                Cloud-init scripts
            </a>
        @endcan
        <a class="inline-flex h-8 shrink-0 items-center rounded-lg px-3 text-[12px] font-medium transition-colors {{ request()->routeIs('security.api-tokens') ? 'bg-coollabs/10 text-coollabs dark:bg-warning/15 dark:text-warning' : 'text-neutral-500 hover:bg-neutral-100 hover:text-black dark:text-fg-dim dark:hover:bg-white/[0.06] dark:hover:text-fg' }}"
            href="{{ route('security.api-tokens') }}" {{ wireNavigate() }}>
            API tokens
        </a>
    </nav>
</div>
