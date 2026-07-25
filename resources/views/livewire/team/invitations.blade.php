<div>
    @can('manageInvitations', currentTeam())
        @if ($invitations->count() > 0)
            <x-application.settings-section title="Pending invitations"
                description="Invitation links that have not been accepted yet." flush>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px]">
                        <thead>
                            <tr>
                                <th class="px-4 py-2.5 text-left">Email</th>
                                <th class="px-4 py-2.5 text-left">Method</th>
                                <th class="px-4 py-2.5 text-left">Role</th>
                                <th class="px-4 py-2.5 text-left">Invitation link</th>
                                <th class="px-4 py-2.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($invitations as $invite)
                                <tr wire:key="team-invitation-{{ $invite->id }}"
                                    class="border-t border-neutral-200 dark:border-white/[0.07]">
                                    <td class="px-4 py-3 text-[12px] font-medium text-black dark:text-fg">
                                        {{ $invite->email }}
                                    </td>
                                    <td class="px-4 py-3 text-[12px] capitalize text-neutral-500 dark:text-fg-dim">
                                        {{ $invite->via }}
                                    </td>
                                    <td class="px-4 py-3 text-[12px] capitalize text-neutral-500 dark:text-fg-dim">
                                        {{ $invite->role }}
                                    </td>
                                    <td class="max-w-72 px-4 py-3">
                                        <button type="button"
                                            class="flex max-w-full items-center gap-2 text-[12px] text-neutral-500 transition-colors hover:text-black dark:text-fg-dim dark:hover:text-fg"
                                            x-on:click="copyToClipboard(@js($invite->link))">
                                            <span class="truncate font-mono">{{ $invite->link }}</span>
                                            <x-reicon name="file-content" class="size-3.5 shrink-0" />
                                        </button>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <button type="button" class="button text-error! hover:text-error!"
                                            wire:click.prevent="deleteInvitation({{ $invite->id }})">
                                            Revoke
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-application.settings-section>
        @endif
    @endcan
</div>
