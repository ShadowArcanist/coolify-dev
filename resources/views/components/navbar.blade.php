<nav class="flex flex-col flex-1 bg-white border-r border-neutral-200 dark:border-white/[0.06] dark:bg-panel pt-4"
    :class="collapsed ? 'px-2 lg:px-3 sidebar-collapsed' : 'px-2 lg:px-3'"
    @mouseover="
        if (!collapsed) return;
        const el = $event.target.closest('.menu-item, .menu-subitem');
        if (!el) { tooltip.show = false; return; }
        const text = el.getAttribute('title') || el.getAttribute('aria-label') || '';
        if (!text) return;
        const rect = el.getBoundingClientRect();
        tooltip.text = text;
        tooltip.x = rect.right + 8;
        tooltip.y = rect.top + rect.height / 2;
        tooltip.show = true;
    "
    @mouseleave="tooltip.show = false"
    x-data="{
        tooltip: { text: '', x: 0, y: 0, show: false },
        init() {
                window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
                    const userSettings = localStorage.getItem('theme');
                    if (userSettings !== 'system') { return; }
                    document.documentElement.classList.toggle('dark', e.matches);
                });
                this.queryTheme();
            },
            queryTheme() {
                const darkModePreference = window.matchMedia('(prefers-color-scheme: dark)').matches;
                const userSettings = localStorage.getItem('theme') || 'dark';
                localStorage.setItem('theme', userSettings);
                let isDark = false;
                if (userSettings === 'dark') {
                    document.documentElement.classList.add('dark');
                    isDark = true;
                } else if (userSettings === 'light') {
                    document.documentElement.classList.remove('dark');
                } else if (darkModePreference) {
                    document.documentElement.classList.add('dark');
                    isDark = true;
                } else {
                    document.documentElement.classList.remove('dark');
                }
                document.querySelector('meta[name=theme-color]')?.setAttribute('content', isDark ? '#0d0d0d' : '#ffffff');
            }
    }">
    {{-- Search --}}
    <div class="px-1 pb-4" :class="collapsed && 'lg:px-0 lg:flex lg:justify-center'">
        <button @click="$dispatch('open-global-search')" type="button" title="Search (Press / or ⌘K)"
            class="menu-item justify-between !bg-neutral-100 dark:!bg-white/[0.04] hover:!bg-neutral-200 dark:hover:!bg-white/[0.07] !text-fg-faint"
            :class="collapsed && 'lg:w-8 lg:justify-center lg:px-0'">
            <span class="flex items-center gap-2.5 min-w-0">
                <x-reicon name="search" class="menu-item-icon" />
                <span class="menu-item-label" :class="collapsed && 'lg:hidden'">Search</span>
            </span>
            <kbd class="px-1.5 py-0.5 text-[11px] font-medium text-fg-faint bg-neutral-200 dark:bg-white/[0.06] rounded-md border border-transparent dark:border-white/5"
                :class="collapsed && 'lg:hidden'">⌘K</kbd>
        </button>
    </div>

    <ul role="list" class="flex flex-col flex-1 gap-y-0.5 pb-2">
        @if (isSubscribed() || !isCloud())
            {{-- Platform --}}
            <li class="nav-section" :class="collapsed && 'lg:hidden'">Platform</li>
            <li>
                <a title="Dashboard" href="/" {{ wireNavigate() }}
                    class="{{ request()->is('/') ? 'menu-item-active menu-item' : 'menu-item' }}"
                    :class="collapsed && 'lg:justify-center lg:px-0'">
                    <x-reicon name="dashboard" class="menu-item-icon" />
                    <span class="menu-item-label" :class="collapsed && 'lg:hidden'">Dashboard</span>
                </a>
            </li>
            <li>
                <a title="Projects" {{ wireNavigate() }}
                    class="{{ request()->is('project/*') || request()->is('projects') ? 'menu-item menu-item-active' : 'menu-item' }}"
                    :class="collapsed && 'lg:justify-center lg:px-0'" href="/projects">
                    <x-reicon name="projects" class="menu-item-icon" />
                    <span class="menu-item-label" :class="collapsed && 'lg:hidden'">Projects</span>
                </a>
            </li>
            <li>
                <a title="Servers" {{ wireNavigate() }}
                    class="{{ request()->is('server/*') || request()->is('servers') ? 'menu-item menu-item-active' : 'menu-item' }}"
                    :class="collapsed && 'lg:justify-center lg:px-0'" href="/servers">
                    <x-reicon name="servers" class="menu-item-icon" />
                    <span class="menu-item-label" :class="collapsed && 'lg:hidden'">Servers</span>
                </a>
            </li>
            <li>
                <a title="Deployments" href="/" {{ wireNavigate() }}
                    class="menu-item" :class="collapsed && 'lg:justify-center lg:px-0'">
                    <x-reicon name="destinations" class="menu-item-icon" />
                    <span class="menu-item-label" :class="collapsed && 'lg:hidden'">Deployments</span>
                    <span class="shrink-0 min-w-[18px] h-[18px] px-1 inline-flex items-center justify-center rounded-full text-[10px] font-semibold bg-neutral-200 text-neutral-600 dark:bg-white/[0.08] dark:text-fg-dim"
                        :class="collapsed && 'lg:hidden'">3</span>
                </a>
            </li>

            {{-- Infrastructure --}}
            <li class="nav-section mt-4" :class="collapsed && 'lg:hidden'">Infrastructure</li>
            <li>
                <a title="Sources" {{ wireNavigate() }}
                    class="{{ request()->is('source*') ? 'menu-item-active menu-item' : 'menu-item' }}"
                    :class="collapsed && 'lg:justify-center lg:px-0'" href="{{ route('source.all') }}">
                    <x-reicon name="sources" class="menu-item-icon" />
                    <span class="menu-item-label" :class="collapsed && 'lg:hidden'">Sources</span>
                </a>
            </li>
            <li>
                <a title="Destinations" {{ wireNavigate() }}
                    class="{{ request()->is('destination*') ? 'menu-item-active menu-item' : 'menu-item' }}"
                    :class="collapsed && 'lg:justify-center lg:px-0'" href="{{ route('destination.index') }}">
                    <x-reicon name="destinations" class="menu-item-icon" />
                    <span class="menu-item-label" :class="collapsed && 'lg:hidden'">Destinations</span>
                </a>
            </li>
            <li>
                <a title="S3 Storages" {{ wireNavigate() }}
                    class="{{ request()->is('storages*') ? 'menu-item-active menu-item' : 'menu-item' }}"
                    :class="collapsed && 'lg:justify-center lg:px-0'" href="{{ route('storage.index') }}">
                    <x-reicon name="storages" class="menu-item-icon" />
                    <span class="menu-item-label" :class="collapsed && 'lg:hidden'">S3 Storages</span>
                </a>
            </li>
            <li>
                <a title="Shared variables" {{ wireNavigate() }}
                    class="{{ request()->is('shared-variables*') ? 'menu-item-active menu-item' : 'menu-item' }}"
                    :class="collapsed && 'lg:justify-center lg:px-0'" href="{{ route('shared-variables.index') }}">
                    <x-reicon name="variables" class="menu-item-icon" />
                    <span class="menu-item-label" :class="collapsed && 'lg:hidden'">Shared Variables</span>
                </a>
            </li>

            {{-- Observability --}}
            <li class="nav-section mt-4" :class="collapsed && 'lg:hidden'">Observability</li>
            <li>
                <a title="Metrics" href="/" {{ wireNavigate() }}
                    class="menu-item" :class="collapsed && 'lg:justify-center lg:px-0'">
                    <x-reicon name="dashboard" class="menu-item-icon" />
                    <span class="menu-item-label" :class="collapsed && 'lg:hidden'">Metrics</span>
                </a>
            </li>
            <li>
                <a title="Logs" href="/" {{ wireNavigate() }}
                    class="menu-item" :class="collapsed && 'lg:justify-center lg:px-0'">
                    <x-reicon name="terminal" class="menu-item-icon" />
                    <span class="menu-item-label" :class="collapsed && 'lg:hidden'">Logs</span>
                    <span class="shrink-0 px-1.5 h-[18px] inline-flex items-center justify-center rounded text-[10px] font-semibold bg-accent/15 text-accent"
                        :class="collapsed && 'lg:hidden'">New</span>
                </a>
            </li>
            <li>
                <a title="Notifications" {{ wireNavigate() }}
                    class="{{ request()->is('notifications*') ? 'menu-item-active menu-item' : 'menu-item' }}"
                    :class="collapsed && 'lg:justify-center lg:px-0'" href="{{ route('notifications.email') }}">
                    <x-reicon name="notifications" class="menu-item-icon" />
                    <span class="menu-item-label" :class="collapsed && 'lg:hidden'">Notifications</span>
                </a>
            </li>

            {{-- Access --}}
            <li class="nav-section mt-4" :class="collapsed && 'lg:hidden'">Access</li>
            <li>
                <a title="Keys & Tokens" {{ wireNavigate() }}
                    class="{{ request()->is('security*') ? 'menu-item-active menu-item' : 'menu-item' }}"
                    :class="collapsed && 'lg:justify-center lg:px-0'" href="{{ route('security.private-key.index') }}">
                    <x-reicon name="keys" class="menu-item-icon" />
                    <span class="menu-item-label" :class="collapsed && 'lg:hidden'">Keys & Tokens</span>
                </a>
            </li>
            <li>
                <a title="Tags" {{ wireNavigate() }}
                    class="{{ request()->is('tags*') ? 'menu-item-active menu-item' : 'menu-item' }}"
                    :class="collapsed && 'lg:justify-center lg:px-0'" href="{{ route('tags.show') }}">
                    <x-reicon name="tags" class="menu-item-icon" />
                    <span class="menu-item-label" :class="collapsed && 'lg:hidden'">Tags</span>
                </a>
            </li>
            @can('canAccessTerminal')
                <li>
                    <a title="Terminal"
                        class="{{ request()->is('terminal*') ? 'menu-item-active menu-item' : 'menu-item' }}"
                        :class="collapsed && 'lg:justify-center lg:px-0'" href="{{ route('terminal') }}">
                        <x-reicon name="terminal" class="menu-item-icon" />
                        <span class="menu-item-label" :class="collapsed && 'lg:hidden'">Terminal</span>
                    </a>
                </li>
            @endcan

            <div class="flex-1"></div>

            <li class="mt-2">
                <livewire:settings-dropdown trigger="changelog-sidebar" />
            </li>
            @if (isInstanceAdmin() && !isCloud())
                @persist('upgrade')
                    <li>
                        <livewire:upgrade />
                    </li>
                @endpersist
            @endif
            <li>
                <a title="Sponsor us" class="menu-item" href="https://coolify.io/sponsorships" target="_blank"
                    :class="collapsed && 'lg:justify-center lg:px-0'">
                    <x-reicon name="sponsor" class="menu-item-icon !text-pink-500" />
                    <span class="menu-item-label" :class="collapsed && 'lg:hidden'">Sponsor us</span>
                </a>
            </li>
        @endif
        @if (!isSubscribed() && isCloud() && auth()->user()->teams()->get()->count() > 1)
            <livewire:navbar-delete-team />
        @endif
        <li>
            <x-modal-input title="How can we help?">
                <x-slot:content>
                    <div title="Send us feedback or get help!" class="cursor-pointer menu-item mb-4" wire:click="help"
                        :class="collapsed && 'lg:justify-center lg:px-0'">
                        <x-reicon name="feedback" class="menu-item-icon" />
                        <span class="menu-item-label" :class="collapsed && 'lg:hidden'">Feedback</span>
                    </div>
                </x-slot:content>
                <livewire:help />
            </x-modal-input>
        </li>
    </ul>
    <div x-show="collapsed && tooltip.show" x-cloak x-transition.opacity.duration.100ms
        :style="`left: ${tooltip.x}px; top: ${tooltip.y}px;`"
        class="fixed z-[100] -translate-y-1/2 px-2 py-1 text-xs font-medium rounded-lg bg-neutral-900 dark:bg-raised text-white whitespace-nowrap pointer-events-none shadow-lg border border-neutral-700 dark:border-white/10"
        x-text="tooltip.text"></div>
</nav>
