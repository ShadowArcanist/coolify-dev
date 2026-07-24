@php
    $user = auth()->user();
    $userName = $user?->name ?? 'Account';
    $userEmail = $user?->email ?? '';
    $userInitial = strtoupper(mb_substr($userName, 0, 1));
@endphp
<div class="relative" x-data="{
    open: false,
    theme: localStorage.getItem('theme') || 'dark',
    setTheme(t) {
        this.theme = t;
        localStorage.setItem('theme', t);
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        const isDark = t === 'dark' || (t === 'system' && prefersDark);
        document.documentElement.classList.toggle('dark', isDark);
        document.querySelector('meta[name=theme-color]')?.setAttribute('content', isDark ? '#0d0d0d' : '#ffffff');
    }
}" @keydown.escape.window="open = false">
    <button type="button" @click="open = !open" @click.outside="open = false"
        class="flex items-center gap-1.5 h-9 px-2 rounded-md transition-colors hover:bg-neutral-100 dark:hover:bg-white/[0.05]">
        <span class="hidden sm:block max-w-[9rem] truncate text-[12px] font-medium text-black dark:text-fg">{{ $userName }}</span>
        <svg class="size-3.5 shrink-0 text-neutral-400 dark:text-fg-faint transition-transform" :class="open && 'rotate-180'"
            viewBox="0 0 24 24" fill="none">
            <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
    </button>

    <div x-show="open" x-cloak x-transition.opacity.duration.120ms
        class="absolute right-0 z-[90] mt-1.5 w-64 rounded-lg border border-neutral-200 dark:border-white/[0.08] bg-white dark:bg-surface py-1.5 shadow-modal">
        {{-- Identity --}}
        <div class="flex items-center gap-2.5 px-3 py-2">
            <span class="flex items-center justify-center size-8 shrink-0 rounded-full bg-gradient-to-br from-accent to-[#4d55cc] text-[13px] font-semibold text-white">{{ $userInitial }}</span>
            <div class="min-w-0">
                <div class="truncate text-[13px] font-semibold text-black dark:text-fg">{{ $userName }}</div>
                <div class="truncate text-[11px] text-neutral-500 dark:text-fg-faint">{{ $userEmail }}</div>
            </div>
        </div>
        <div class="my-1 h-px bg-neutral-200 dark:bg-white/[0.06]"></div>

        {{-- Account links --}}
        <a href="{{ route('profile') }}" {{ wireNavigate() }} class="user-menu-item">
            <x-reicon name="profile" class="size-4 opacity-80" /> Profile
        </a>
        <a href="{{ route('team.index') }}" {{ wireNavigate() }} class="user-menu-item">
            <x-reicon name="teams" class="size-4 opacity-80" /> Teams
        </a>
        <a href="{{ route('notifications.email') }}" {{ wireNavigate() }} class="user-menu-item">
            <x-reicon name="notifications" class="size-4 opacity-80" /> Notifications
        </a>
        @if (isInstanceAdmin())
            <a href="/settings" {{ wireNavigate() }} class="user-menu-item">
                <x-reicon name="settings" class="size-4 opacity-80" /> Settings
            </a>
        @endif

        <div class="my-1 h-px bg-neutral-200 dark:bg-white/[0.06]"></div>

        {{-- Theme --}}
        <div class="px-3 pt-1 pb-1.5">
            <div class="mb-1.5 text-[10.5px] font-semibold uppercase tracking-wide text-neutral-400 dark:text-fg-faint">Theme</div>
            <div class="flex items-center gap-1 rounded-md bg-neutral-100 dark:bg-white/[0.05] p-0.5">
                @foreach (['light' => 'Light', 'dark' => 'Dark', 'system' => 'Auto'] as $val => $lbl)
                    <button type="button" @click="setTheme('{{ $val }}')"
                        class="flex-1 h-7 rounded text-[12px] font-medium transition-colors"
                        :class="theme === '{{ $val }}' ? 'bg-white dark:bg-white/[0.1] text-black dark:text-fg shadow-sm' : 'text-neutral-500 dark:text-fg-faint hover:text-black dark:hover:text-fg'">{{ $lbl }}</button>
                @endforeach
            </div>
        </div>

        <div class="my-1 h-px bg-neutral-200 dark:bg-white/[0.06]"></div>

        <a href="https://coolify.io/docs" target="_blank" class="user-menu-item">
            <x-reicon name="feedback" class="size-4 opacity-80" /> Documentation
        </a>
        <form action="/logout" method="POST">
            @csrf
            <button type="submit" class="user-menu-item w-full text-left text-error dark:text-error">
                <x-reicon name="logout" class="size-4 opacity-90" /> Log out
            </button>
        </form>
    </div>
</div>
