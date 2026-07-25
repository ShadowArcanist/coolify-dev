<div class="flex items-center justify-between gap-3">
    <p class="font-mono text-sm font-medium text-neutral-950 dark:text-fg">
        {{ str_replace('|', '.', $fileName) }}
    </p>
    @can('update', $server)
        <div class="flex items-center gap-2">
            <x-modal-input buttonTitle="Edit" title="Edit Configuration">
                <livewire:server.proxy.new-dynamic-configuration :server_id="$server_id" :fileName="$fileName" :value="$value"
                    :newFile="$newFile" wire:key="{{ $fileName }}" />
            </x-modal-input>
            <x-forms.button isError wire:click="delete('{{ $fileName }}')">Delete</x-forms.button>
        </div>
    @endcan
</div>
