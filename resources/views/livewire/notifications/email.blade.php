<div>
    <x-slot:title>
        Notifications | Coolify
    </x-slot>

    <x-notification.navbar />

    <div class="flex flex-col gap-6">
        <form wire:submit="submit" class="application-settings-form">
            <x-application.settings-section title="Email delivery"
                description="Configure the sender identity and email service used for this team.">
                <x-slot:actions>
                    @if (auth()->user()->isAdminFromSession())
                        @can('sendTest', $settings)
                            @if ($team->isNotificationEnabled('email'))
                                <x-modal-input title="Send Test Email">
                                    <x-slot:content>
                                        <button type="button" class="button">
                                            <x-reicon name="notifications" class="size-3.5" />
                                            Send test
                                        </button>
                                    </x-slot:content>
                                    <form wire:submit.prevent="sendTestEmail" class="flex w-full flex-col gap-4">
                                        <x-forms.input wire:model="testEmailAddress" placeholder="test@example.com"
                                            id="testEmailAddress" label="Recipient" required />
                                        <div class="flex justify-end border-t border-neutral-200 pt-4 dark:border-white/[0.08]">
                                            <button type="submit" @click="modalOpen=false"
                                                class="button bg-coollabs/10! text-coollabs! ring-1 ring-coollabs/25 hover:bg-coollabs/15! dark:bg-warning/15! dark:text-warning! dark:ring-warning/25 dark:hover:bg-warning/20!">
                                                Send email
                                            </button>
                                        </div>
                                    </form>
                                </x-modal-input>
                            @else
                                <button type="button" class="button" disabled>Send test</button>
                            @endif
                        @endcan
                    @endif

                    @can('update', $settings)
                        <button type="submit"
                            class="button bg-coollabs/10! text-coollabs! ring-1 ring-coollabs/25 hover:bg-coollabs/15! dark:bg-warning/15! dark:text-warning! dark:ring-warning/25 dark:hover:bg-warning/20!">
                            Save changes
                        </button>
                    @endcan
                </x-slot:actions>

                <div class="grid gap-4 lg:grid-cols-2">
                    <div class="lg:col-span-2">
                        @if (isCloud())
                            <x-forms.checkbox canGate="update" :canResource="$settings" instantSave="instantSave()"
                                id="useInstanceEmailSettings" label="Use Hosted Email Service" />
                        @else
                            <x-forms.checkbox canGate="update" :canResource="$settings" instantSave="instantSave()"
                                id="useInstanceEmailSettings"
                                label="Use system-wide transactional email settings" />
                        @endif
                    </div>

                    @if (!$useInstanceEmailSettings)
                        <x-forms.input canGate="update" :canResource="$settings" required id="smtpFromName"
                            helper="Name used in emails." label="From name" />
                        <x-forms.input canGate="update" :canResource="$settings" required id="smtpFromAddress"
                            helper="Email address used in emails." label="From address" />

                        @if (isInstanceAdmin())
                            <div class="lg:col-span-2">
                                <button type="button" class="button" wire:click="copyFromInstanceSettings">
                                    Copy from instance settings
                                </button>
                            </div>
                        @endif
                    @endif
                </div>
            </x-application.settings-section>
        </form>

        @if (!$useInstanceEmailSettings)
            <div class="application-settings-form">
                <x-application.settings-section title="SMTP server"
                    description="Deliver messages through your own SMTP server.">
                    <div class="grid gap-4 lg:grid-cols-3">
                        <div class="lg:col-span-3">
                            <x-forms.checkbox canGate="update" :canResource="$settings" wire:model="smtpEnabled"
                                instantSave="instantSave('SMTP')" id="smtpEnabled" label="Enabled" />
                        </div>
                        <x-forms.input canGate="update" :canResource="$settings" required id="smtpHost"
                            placeholder="smtp.mailgun.org" label="Host" />
                        <x-forms.input canGate="update" :canResource="$settings" required id="smtpPort"
                            type="number" placeholder="587" label="Port" />
                        <x-forms.listbox id="smtpEncryption" label="Encryption" required
                            :disabled="!auth()->user()->can('update', $settings)" :options="[
                            ['value' => 'starttls', 'label' => 'StartTLS'],
                            ['value' => 'tls', 'label' => 'TLS / SSL'],
                            ['value' => 'none', 'label' => 'None'],
                        ]" />
                        <x-forms.input canGate="update" :canResource="$settings" id="smtpUsername"
                            label="SMTP username" />
                        @can('update', $settings)
                            <x-forms.input canGate="update" :canResource="$settings" id="smtpPassword" type="password"
                                label="SMTP password" />
                        @else
                            <x-forms.input disabled label="SMTP password" value="Hidden (only admins can view)" />
                        @endcan
                        <x-forms.input canGate="update" :canResource="$settings" id="smtpTimeout" type="number"
                            helper="Timeout value for sending emails." label="Timeout" />
                    </div>
                </x-application.settings-section>
            </div>

            <div class="application-settings-form">
                <x-application.settings-section title="Resend"
                    description="Use Resend as an alternative email delivery provider.">
                    <div class="grid gap-4 lg:grid-cols-2">
                        <div class="lg:col-span-2">
                            <x-forms.checkbox canGate="update" :canResource="$settings" wire:model="resendEnabled"
                                instantSave="instantSave('Resend')" id="resendEnabled" label="Enabled" />
                        </div>
                        @can('update', $settings)
                            <x-forms.input canGate="update" :canResource="$settings" required type="password"
                                id="resendApiKey" placeholder="API key" label="API key" />
                        @else
                            <x-forms.input disabled label="API key" value="Hidden (only admins can view)" />
                        @endcan
                    </div>
                </x-application.settings-section>
            </div>
        @endif

        <div class="application-settings-form">
            <x-application.settings-section title="Notification events"
                description="Choose which events should send an email to this team.">
                <div class="grid gap-3 lg:grid-cols-2">
                    <div
                        class="rounded-lg border border-neutral-200 bg-neutral-50/70 p-3 dark:border-white/[0.08] dark:bg-white/[0.025]">
                        <h4 class="mb-3 text-[12px] font-semibold text-black dark:text-fg">Deployments</h4>
                        <div class="flex flex-col gap-2.5">
                            <x-forms.checkbox canGate="update" :canResource="$settings" instantSave="saveModel"
                                id="deploymentSuccessEmailNotifications" label="Deployment success" />
                            <x-forms.checkbox canGate="update" :canResource="$settings" instantSave="saveModel"
                                id="deploymentFailureEmailNotifications" label="Deployment failure" />
                            <x-forms.checkbox canGate="update" :canResource="$settings" instantSave="saveModel"
                                helper="Send an email when a container stops or restarts."
                                id="statusChangeEmailNotifications" label="Container status changes" />
                        </div>
                    </div>

                    <div
                        class="rounded-lg border border-neutral-200 bg-neutral-50/70 p-3 dark:border-white/[0.08] dark:bg-white/[0.025]">
                        <h4 class="mb-3 text-[12px] font-semibold text-black dark:text-fg">Backups</h4>
                        <div class="flex flex-col gap-2.5">
                            <x-forms.checkbox canGate="update" :canResource="$settings" instantSave="saveModel"
                                id="backupSuccessEmailNotifications" label="Backup success" />
                            <x-forms.checkbox canGate="update" :canResource="$settings" instantSave="saveModel"
                                id="backupFailureEmailNotifications" label="Backup failure" />
                        </div>
                    </div>

                    <div
                        class="rounded-lg border border-neutral-200 bg-neutral-50/70 p-3 dark:border-white/[0.08] dark:bg-white/[0.025]">
                        <h4 class="mb-3 text-[12px] font-semibold text-black dark:text-fg">Scheduled tasks</h4>
                        <div class="flex flex-col gap-2.5">
                            <x-forms.checkbox canGate="update" :canResource="$settings" instantSave="saveModel"
                                id="scheduledTaskSuccessEmailNotifications" label="Scheduled task success" />
                            <x-forms.checkbox canGate="update" :canResource="$settings" instantSave="saveModel"
                                id="scheduledTaskFailureEmailNotifications" label="Scheduled task failure" />
                        </div>
                    </div>

                    <div
                        class="rounded-lg border border-neutral-200 bg-neutral-50/70 p-3 dark:border-white/[0.08] dark:bg-white/[0.025]">
                        <h4 class="mb-3 text-[12px] font-semibold text-black dark:text-fg">Server</h4>
                        <div class="grid gap-2.5 sm:grid-cols-2">
                            <x-forms.checkbox canGate="update" :canResource="$settings" instantSave="saveModel"
                                id="dockerCleanupSuccessEmailNotifications" label="Docker cleanup success" />
                            <x-forms.checkbox canGate="update" :canResource="$settings" instantSave="saveModel"
                                id="dockerCleanupFailureEmailNotifications" label="Docker cleanup failure" />
                            <x-forms.checkbox canGate="update" :canResource="$settings" instantSave="saveModel"
                                id="serverDiskUsageEmailNotifications" label="Server disk usage" />
                            <x-forms.checkbox canGate="update" :canResource="$settings" instantSave="saveModel"
                                id="serverReachableEmailNotifications" label="Server reachable" />
                            <x-forms.checkbox canGate="update" :canResource="$settings" instantSave="saveModel"
                                id="serverUnreachableEmailNotifications" label="Server unreachable" />
                            <x-forms.checkbox canGate="update" :canResource="$settings" instantSave="saveModel"
                                id="serverPatchEmailNotifications" label="Server patching" />
                            <x-forms.checkbox canGate="update" :canResource="$settings" instantSave="saveModel"
                                id="traefikOutdatedEmailNotifications" label="Traefik proxy outdated" />
                        </div>
                    </div>
                </div>
            </x-application.settings-section>
        </div>
    </div>
</div>
