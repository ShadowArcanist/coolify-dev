<div class="mb-6">
    <header class="mb-5">
        <h1 class="text-[24px]! leading-7! font-semibold!">Notifications</h1>
        <p class="mt-1 text-[13px] text-neutral-500 dark:text-fg-dim">
            Choose how your team receives infrastructure and deployment alerts.
        </p>
    </header>

    @php
        $notificationTabs = [
            ['label' => 'Email', 'route' => 'notifications.email'],
            ['label' => 'Discord', 'route' => 'notifications.discord'],
            ['label' => 'Telegram', 'route' => 'notifications.telegram'],
            ['label' => 'Slack', 'route' => 'notifications.slack'],
            ['label' => 'Pushover', 'route' => 'notifications.pushover'],
            ['label' => 'Webhook', 'route' => 'notifications.webhook'],
        ];
    @endphp

    <nav class="flex items-center gap-1 overflow-x-auto border-b border-neutral-200 pb-2 dark:border-white/[0.08]"
        aria-label="Notification channels">
        @foreach ($notificationTabs as $tab)
            <a class="inline-flex h-8 shrink-0 items-center rounded-lg px-3 text-[12px] font-medium transition-colors {{ request()->routeIs($tab['route']) ? 'bg-coollabs/10 text-coollabs dark:bg-warning/15 dark:text-warning' : 'text-neutral-500 hover:bg-neutral-100 hover:text-black dark:text-fg-dim dark:hover:bg-white/[0.06] dark:hover:text-fg' }}"
                {{ wireNavigate() }} href="{{ route($tab['route']) }}">
                {{ $tab['label'] }}
            </a>
        @endforeach
    </nav>
</div>
