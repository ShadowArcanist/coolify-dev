<div class="max-w-4xl mx-auto animate-in fade-in-0 duration-300">
    <div class="text-center space-y-8">
        <div class="space-y-4">
            <h1 class="text-3xl font-semibold tracking-tight lg:text-5xl text-black dark:text-white">{{ $title }}</h1>
            @isset($question)
                <p class="text-lg text-neutral-600 dark:text-neutral-300 leading-relaxed">
                    {{ $question }}
                </p>
            @endisset
        </div>
        @if ($actions)
            <div class="flex flex-col gap-4 items-center w-full max-w-xl mx-auto">
                {{ $actions }}
            </div>
        @endif
    </div>

    @isset($explanation)
        <div class="mt-8 flex justify-center">
            <div class="w-full max-w-xl inline-flex flex-col p-6 border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white rounded-lg">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 mb-4">Info</h3>
                <div class="space-y-4 text-sm text-neutral-600 dark:text-neutral-300 leading-relaxed">
                    {{ $explanation }}
                </div>
            </div>
        </div>
    @endisset
</div>
