<tr wire:key="team-member-row-{{ $member->id }}"
    class="border-t border-neutral-200 transition-colors hover:bg-neutral-50 dark:border-white/[0.07] dark:hover:bg-white/[0.025]">
    <td class="px-4 py-3">
        <div class="flex items-center gap-2">
            <div
                class="flex size-7 shrink-0 items-center justify-center rounded-lg bg-neutral-100 text-[11px] font-semibold text-neutral-600 dark:bg-white/[0.06] dark:text-fg-dim">
                {{ Str::upper(Str::substr($member->name ?: $member->email, 0, 1)) }}
            </div>
            <span class="truncate text-[13px] font-medium text-black dark:text-fg">{{ $member->name }}</span>
            @if ($member->id === Auth::id())
                <span
                    class="rounded-full bg-coollabs/10 px-1.5 py-0.5 text-[10px] font-medium text-coollabs dark:bg-warning/15 dark:text-warning">
                    You
                </span>
            @endif
        </div>
    </td>
    <td class="px-4 py-3 text-[12px] text-neutral-500 dark:text-fg-dim">{{ $member->email }}</td>
    <td class="px-4 py-3">
        <span
            class="inline-flex rounded-full bg-neutral-100 px-2 py-0.5 text-[10px] font-medium capitalize text-neutral-600 dark:bg-white/[0.06] dark:text-fg-dim">
            {{ data_get($member, 'pivot.role') }}
        </span>
    </td>
    <td class="px-4 py-3">
        <div class="flex justify-end gap-1.5">
            @can('manageMembers', currentTeam())
                @if ($member->id !== Auth::id())
                    @if (Auth::user()->isOwner())
                        @if (data_get($member, 'pivot.role') !== 'owner')
                            <button type="button" class="button" wire:click="makeOwner">Owner</button>
                        @endif
                        @if (data_get($member, 'pivot.role') !== 'admin')
                            <button type="button" class="button" wire:click="makeAdmin">Admin</button>
                        @endif
                        @if (data_get($member, 'pivot.role') !== 'member')
                            <button type="button" class="button" wire:click="makeReadonly">Member</button>
                        @endif
                    @elseif (Auth::user()->isAdmin())
                        @if (data_get($member, 'pivot.role') === 'admin')
                            <button type="button" class="button" wire:click="makeReadonly">Member</button>
                        @elseif (data_get($member, 'pivot.role') === 'member')
                            <button type="button" class="button" wire:click="makeAdmin">Admin</button>
                        @endif
                    @endif
                    <button type="button" class="button text-error! hover:text-error!" wire:click="remove">
                        Remove
                    </button>
                @endif
            @endcan
        </div>
    </td>
</tr>
