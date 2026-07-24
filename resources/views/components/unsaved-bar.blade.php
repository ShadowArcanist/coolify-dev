@props([
    'action' => 'submit',
    'label' => 'You have unsaved changes.',
])

{{-- Footer action bar. Reveals itself via Livewire's wire:dirty
     whenever the surrounding component has un-saved model changes. --}}
<div wire:dirty.class.remove="opacity-0 translate-y-6 pointer-events-none"
    class="opacity-0 translate-y-6 pointer-events-none transition-all duration-200 ease-out fixed bottom-0 inset-x-0 z-[80] border-t border-neutral-200 dark:border-white/[0.09] bg-white/95 dark:bg-panel/95 backdrop-blur">
    <div class="flex items-center justify-between gap-4 px-5 py-4 sm:px-8">
        <button type="button" onclick="window.location.reload()"
            class="inline-flex h-10 items-center gap-2 rounded-lg border border-neutral-200 bg-white px-4 text-sm font-medium text-black transition-colors hover:bg-neutral-100 dark:border-white/[0.10] dark:bg-white/[0.03] dark:text-fg dark:hover:bg-white/[0.08]">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                stroke="currentColor" class="size-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Cancel
        </button>
        <div class="flex items-center gap-4">
            <span class="hidden text-sm text-neutral-500 dark:text-fg-dim sm:block">{{ $label }}</span>
            <button type="button" wire:click="{{ $action }}" wire:loading.attr="disabled"
                class="inline-flex h-10 items-center rounded-lg bg-[#0e6ef4] px-5 text-sm font-medium text-white transition-colors hover:bg-[#2b80f6] active:scale-[0.99] disabled:opacity-60">
                Save changes
            </button>
        </div>
    </div>
</div>
