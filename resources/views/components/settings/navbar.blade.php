<div class="mb-6">
    <header class="mb-5">
        <h1 class="text-[24px]! leading-7! font-semibold!">Settings</h1>
        <p class="mt-1 text-[13px] text-neutral-500 dark:text-fg-dim">
            Instance-wide configuration for Coolify.
        </p>
    </header>

    @php
        $settingsTabs = [
            [
                'label' => 'Configuration',
                'route' => 'settings.index',
                'active' => request()->routeIs('settings.index', 'settings.advanced', 'settings.updates'),
            ],
            ['label' => 'Backup', 'route' => 'settings.backup', 'active' => request()->routeIs('settings.backup')],
            [
                'label' => 'Transactional email',
                'route' => 'settings.email',
                'active' => request()->routeIs('settings.email'),
            ],
            ['label' => 'OAuth', 'route' => 'settings.oauth', 'active' => request()->routeIs('settings.oauth')],
            [
                'label' => 'Scheduled jobs',
                'route' => 'settings.scheduled-jobs',
                'active' => request()->routeIs('settings.scheduled-jobs'),
            ],
        ];
    @endphp

    <nav class="flex items-center gap-1 overflow-x-auto border-b border-neutral-200 pb-2 dark:border-white/[0.08]"
        aria-label="Instance settings sections">
        @foreach ($settingsTabs as $tab)
            <a class="inline-flex h-8 shrink-0 items-center rounded-lg px-3 text-[12px] font-medium transition-colors {{ $tab['active'] ? 'bg-coollabs/10 text-coollabs dark:bg-warning/15 dark:text-warning' : 'text-neutral-500 hover:bg-neutral-100 hover:text-black dark:text-fg-dim dark:hover:bg-white/[0.06] dark:hover:text-fg' }}"
                {{ wireNavigate() }} href="{{ route($tab['route']) }}">
                {{ $tab['label'] }}
            </a>
        @endforeach
    </nav>
</div>
