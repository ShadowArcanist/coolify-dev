<form class="flex w-full flex-col gap-4" wire:submit="submit">
    <div class="grid gap-4 sm:grid-cols-2">
        <x-forms.input placeholder="Database cleanup" id="name" label="Name" />
        <x-forms.input placeholder="0 0 * * * or daily"
            helper="Use every_minute, hourly, daily, weekly, monthly, yearly, or a cron expression."
            id="frequency" label="Schedule" />
    </div>

    <x-forms.input placeholder="php artisan schedule:run" id="command" label="Command" />

    <div class="grid gap-4 sm:grid-cols-2">
        <x-forms.input type="number" placeholder="300" id="timeout"
            helper="Maximum execution time from 60 to 36,000 seconds." label="Timeout (seconds)" />
        @if ($type === 'application' && $containerNames->count() > 1)
            <x-forms.select id="container" label="Container">
                @foreach ($containerNames as $containerName)
                    <option value="{{ $containerName }}">{{ $containerName }}</option>
                @endforeach
            </x-forms.select>
        @elseif ($type === 'service')
            <x-forms.select id="container" label="Container">
                @foreach ($containerNames as $containerName)
                    <option value="{{ $containerName }}">{{ $containerName }}</option>
                @endforeach
            </x-forms.select>
        @else
            <x-forms.input placeholder="php" id="container"
                helper="Leave empty when the resource only has one container." label="Container" />
        @endif
    </div>

    <div class="flex justify-end border-t border-neutral-200 pt-4 dark:border-white/[0.07]">
        <x-forms.button @click="modalOpen=false" type="submit">
            Add task
        </x-forms.button>
    </div>
</form>
