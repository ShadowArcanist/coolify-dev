<form class="application-settings-form flex w-full flex-col gap-4" wire:submit="submit">
    <x-forms.input id="name" label="Name" required />
    <x-forms.input id="description" label="Description" />
    <div class="flex justify-end border-t border-neutral-200 pt-4 dark:border-border-subtle">
        <x-forms.button type="submit">Create team</x-forms.button>
    </div>
</form>
