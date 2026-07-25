<div>
    <x-slot:title>
        Team Members | Coolify
    </x-slot>

    <x-team.navbar />

    <div class="application-settings-form flex flex-col gap-6">
        <x-application.settings-section title="Members"
            description="People who can access this team and their current role." flush>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px]">
                    <thead>
                        <tr>
                            <th class="w-[26%] px-4 py-2.5 text-left">Name</th>
                            <th class="w-[34%] px-4 py-2.5 text-left">Email</th>
                            <th class="w-[14%] px-4 py-2.5 text-left">Role</th>
                            <th class="px-4 py-2.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach (currentTeam()->members as $member)
                            <livewire:team.member :member="$member" :wire:key="$member->id" />
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div
                class="flex min-h-11 items-center border-t border-neutral-200 px-4 text-[11px] text-neutral-500 dark:border-white/[0.08] dark:text-fg-faint">
                {{ currentTeam()->members->count() }}
                {{ Str::plural('member', currentTeam()->members->count()) }}
            </div>
        </x-application.settings-section>

        @can('manageInvitations', currentTeam())
            <livewire:team.invite-link />
            <livewire:team.invitations :invitations="$invitations" />
        @endcan
    </div>
</div>
