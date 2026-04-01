<div x-data="{
    notifications: [],
    nextId: 1,

    providers: ['Discord', 'Email', 'Telegram', 'Slack', 'Pushover', 'Webhook'],
    priorities: ['Info', 'Success', 'Failure'],
    pingOptions: ['None', '@here', '@everyone', 'Custom'],
    events: [
        { key: 'deployment_success', label: 'Deployment Success' },
        { key: 'deployment_failure', label: 'Deployment Failure' },
        { key: 'status_change', label: 'Container Status Changes' },
        { key: 'backup_success', label: 'Backup Success' },
        { key: 'backup_failure', label: 'Backup Failure' },
        { key: 'scheduled_task_success', label: 'Scheduled Task Success' },
        { key: 'scheduled_task_failure', label: 'Scheduled Task Failure' },
        { key: 'docker_cleanup_success', label: 'Docker Cleanup Success' },
        { key: 'docker_cleanup_failure', label: 'Docker Cleanup Failure' },
        { key: 'server_disk_usage', label: 'Server Disk Usage' },
        { key: 'server_reachable', label: 'Server Reachable' },
        { key: 'server_unreachable', label: 'Server Unreachable' },
        { key: 'server_patch', label: 'Server Patching' },
        { key: 'traefik_outdated', label: 'Traefik Proxy Outdated' },
    ],

    addNotification() {
        this.notifications.push({
            id: this.nextId++,
            expanded: true,
            enabled: true,
            name: '',
            provider: '',
            webhookUrl: '',
            pingMention: 'None',
            customPingText: '',
            priority: 'Info',
            selectedEvents: [],
            eventSearch: '',
            eventDropdownOpen: false,
        });
    },

    removeNotification(id) {
        this.notifications = this.notifications.filter(n => n.id !== id);
    },

    getFilteredEvents(n) {
        if (!n.eventSearch) return this.events;
        const q = n.eventSearch.toLowerCase();
        return this.events.filter(e => e.label.toLowerCase().includes(q));
    },

    toggleEvent(n, key) {
        const idx = n.selectedEvents.indexOf(key);
        if (idx > -1) {
            n.selectedEvents.splice(idx, 1);
        } else {
            n.selectedEvents.push(key);
        }
    },

    isEventSelected(n, key) {
        return n.selectedEvents.includes(key);
    },

    getEventLabel(key) {
        const e = this.events.find(ev => ev.key === key);
        return e ? e.label : key;
    },

    getHeaderLabel(n) {
        if (n.name) return n.name;
        return n.provider ? ('Notification — ' + n.provider) : 'New Notification';
    },
}">
    <x-slot:title>
        Notifications | Coolify
    </x-slot>

    <h1>Notifications</h1>
    <div class="subtitle">Get notified about your infrastructure.</div>

    <div class="flex items-center gap-2 mt-6">
        <button @click="addNotification()"
            class="flex items-center gap-1.5 px-3 py-1.5 text-sm rounded-sm bg-white dark:bg-coolgray-100 border border-neutral-300 dark:border-coolgray-300 hover:bg-neutral-50 dark:hover:bg-coolgray-200 transition-colors cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add Notification
        </button>
    </div>

    {{-- Empty state --}}
    <template x-if="notifications.length === 0">
        <div class="mt-8 py-12 text-center border border-dashed dark:border-coolgray-300 border-neutral-300 rounded-lg">
            <svg class="w-10 h-10 mx-auto mb-3 text-neutral-400 dark:text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
            </svg>
            <p class="text-neutral-500 dark:text-neutral-400">No notifications configured yet.</p>
            <button @click="addNotification()"
                class="mt-3 text-sm underline dark:text-white hover:opacity-80 cursor-pointer">
                Add your first notification
            </button>
        </div>
    </template>

    {{-- Notification accordions --}}
    <div class="mt-4">
        <template x-for="notification in notifications" :key="notification.id">
            <div class="my-4 border dark:border-coolgray-200 border-neutral-200 rounded-sm">
                {{-- Accordion header --}}
                <div class="flex gap-2 items-center p-4 cursor-pointer select-none hover:bg-gray-50 dark:hover:bg-coolgray-200"
                    @click="notification.expanded = !notification.expanded">
                    <svg class="w-4 h-4 transition-transform shrink-0" :class="notification.expanded ? 'rotate-90' : ''"
                        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path fill="currentColor" d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z" />
                    </svg>
                    <h4 x-text="getHeaderLabel(notification)" class="text-sm font-medium"></h4>
                    {{-- Status chip --}}
                    <span class="text-xs px-2 py-0.5 rounded"
                        :class="notification.enabled
                            ? 'bg-green-500/10 text-green-500'
                            : 'bg-neutral-500/10 text-neutral-500'"
                        x-text="notification.enabled ? 'Enabled' : 'Disabled'"></span>
                    <template x-if="notification.provider">
                        <span class="text-xs px-2 py-0.5 rounded bg-blue-500/10 text-blue-500"
                            x-text="notification.selectedEvents.length + ' events'"></span>
                    </template>
                    <div class="ml-auto flex items-center gap-2">
                        {{-- Enable/Disable toggle --}}
                        <label @click.stop class="flex items-center cursor-pointer">
                            <input type="checkbox" x-model="notification.enabled"
                                class="dark:border-neutral-700 text-coolgray-400 dark:bg-coolgray-100 rounded-sm cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-coollabs dark:focus-visible:ring-warning focus-visible:ring-offset-2 dark:focus-visible:ring-offset-base">
                        </label>
                        {{-- Delete button --}}
                        <button @click.stop="removeNotification(notification.id)"
                            class="text-xs px-2 py-1 rounded-sm text-red-500 hover:bg-red-500/10 transition-colors cursor-pointer"
                            title="Delete notification">
                            Delete
                        </button>
                    </div>
                </div>

                {{-- Accordion content --}}
                <div x-show="notification.expanded" x-collapse>
                    <div class="px-4 pb-4 flex flex-col gap-4 border-t dark:border-coolgray-200 border-neutral-200 pt-4">

                        {{-- Name --}}
                        <div class="max-w-md">
                            <label class="flex gap-1 items-center mb-1 text-sm font-medium">Name</label>
                            <input type="text" x-model="notification.name" placeholder="e.g. Production Alerts"
                                class="input w-full">
                        </div>

                        {{-- Provider --}}
                        <div class="max-w-md">
                            <label class="flex gap-1 items-center mb-1 text-sm font-medium">Provider</label>
                            <select x-model="notification.provider" class="select w-full">
                                <option value="">Select a provider...</option>
                                <template x-for="p in providers" :key="p">
                                    <option :value="p" x-text="p" :disabled="p !== 'Discord'"></option>
                                </template>
                            </select>
                        </div>

                        {{-- Credentials (Discord only for POC) --}}
                        <template x-if="notification.provider === 'Discord'">
                            <div class="max-w-md">
                                <label class="flex gap-1 items-center mb-1 text-sm font-medium">Webhook URL</label>
                                <input type="password" x-model="notification.webhookUrl"
                                    placeholder="https://discord.com/api/webhooks/..."
                                    class="input w-full">
                            </div>
                        </template>

                        <template x-if="notification.provider && notification.provider !== 'Discord'">
                            <div class="max-w-md text-sm text-neutral-500 dark:text-neutral-400 italic">
                                Configuration for <span x-text="notification.provider"></span> coming soon.
                            </div>
                        </template>

                        {{-- Show remaining fields only when a provider is selected --}}
                        <template x-if="notification.provider">
                            <div class="flex flex-col gap-4">
                                {{-- Ping / Mention --}}
                                <div class="max-w-md">
                                    <label class="flex gap-1 items-center mb-1 text-sm font-medium">Ping / Mention</label>
                                    <div class="flex items-center gap-2">
                                        <select x-model="notification.pingMention" class="select w-full"
                                            :class="notification.pingMention === 'Custom' ? 'max-w-[160px]' : ''">
                                            <template x-for="opt in pingOptions" :key="opt">
                                                <option :value="opt" x-text="opt"></option>
                                            </template>
                                        </select>
                                        <template x-if="notification.pingMention === 'Custom'">
                                            <input type="text" x-model="notification.customPingText"
                                                placeholder="Enter role or user..."
                                                class="input flex-1">
                                        </template>
                                    </div>
                                </div>

                                {{-- Priority --}}
                                <div class="max-w-md">
                                    <label class="flex gap-1 items-center mb-1 text-sm font-medium">Ping Priority Level</label>
                                    <select x-model="notification.priority" class="select w-full">
                                        <template x-for="p in priorities" :key="p">
                                            <option :value="p" x-text="p"></option>
                                        </template>
                                    </select>
                                </div>

                                {{-- Events multi-select --}}
                                <div class="max-w-md">
                                    <label class="flex gap-1 items-center mb-1 text-sm font-medium">Events</label>
                                    <div @click.outside="notification.eventDropdownOpen = false" class="relative">
                                        {{-- Chips container + search input --}}
                                        <div @click="$el.querySelector('input[type=text]')?.focus()"
                                            class="input !bg-white dark:!bg-coolgray-100 flex flex-wrap gap-1.5 min-h-[38px] max-h-40 overflow-y-auto scrollbar py-1.5 px-2 w-full cursor-text">
                                            {{-- Selected event chips --}}
                                            <template x-for="eventKey in notification.selectedEvents" :key="eventKey">
                                                <button type="button"
                                                    @click.stop="toggleEvent(notification, eventKey)"
                                                    class="inline-flex items-center gap-1.5 px-2 py-0.5 text-xs bg-neutral-100 dark:bg-coolgray-300 rounded whitespace-nowrap cursor-pointer hover:bg-red-100 dark:hover:bg-red-900/20 hover:text-red-600 dark:hover:text-red-400">
                                                    <span x-text="getEventLabel(eventKey)" class="max-w-[200px] truncate"></span>
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </template>
                                            {{-- Search input --}}
                                            <input type="text" x-model="notification.eventSearch"
                                                @focus="notification.eventDropdownOpen = true"
                                                @keydown.escape="notification.eventDropdownOpen = false"
                                                :placeholder="notification.selectedEvents.length ? '' : 'Select events...'"
                                                class="flex-1 min-w-[120px] text-sm border-0 outline-none bg-transparent p-0 focus:ring-0 placeholder:text-neutral-400 dark:placeholder:text-neutral-600 text-black dark:text-white">
                                        </div>

                                        {{-- Dropdown --}}
                                        <div x-show="notification.eventDropdownOpen" x-transition
                                            class="absolute z-50 w-full mt-1 bg-white dark:bg-coolgray-100 border border-neutral-300 dark:border-coolgray-400 rounded shadow-lg max-h-60 overflow-auto scrollbar">
                                            {{-- Select All --}}
                                            <div @click="notification.selectedEvents.length === events.length
                                                    ? notification.selectedEvents = []
                                                    : notification.selectedEvents = events.map(e => e.key)"
                                                class="px-3 py-2 cursor-pointer hover:bg-neutral-100 dark:hover:bg-coolgray-200 flex items-center gap-3 border-b border-neutral-200 dark:border-coolgray-300">
                                                <input type="checkbox" :checked="notification.selectedEvents.length === events.length"
                                                    class="dark:border-neutral-700 text-coolgray-400 dark:bg-coolgray-100 rounded-sm cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-coollabs dark:focus-visible:ring-warning focus-visible:ring-offset-2 dark:focus-visible:ring-offset-base pointer-events-none"
                                                    tabindex="-1">
                                                <span class="text-sm flex-1 font-medium">Select All Events</span>
                                            </div>
                                            <template x-for="event in getFilteredEvents(notification)" :key="event.key">
                                                <div @click="toggleEvent(notification, event.key)"
                                                    class="px-3 py-2 cursor-pointer hover:bg-neutral-100 dark:hover:bg-coolgray-200 flex items-center gap-3"
                                                    :class="{ 'bg-neutral-50 dark:bg-coolgray-300': isEventSelected(notification, event.key) }">
                                                    <input type="checkbox" :checked="isEventSelected(notification, event.key)"
                                                        class="dark:border-neutral-700 text-coolgray-400 dark:bg-coolgray-100 rounded-sm cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-coollabs dark:focus-visible:ring-warning focus-visible:ring-offset-2 dark:focus-visible:ring-offset-base pointer-events-none"
                                                        tabindex="-1">
                                                    <span class="text-sm flex-1" x-text="event.label"></span>
                                                </div>
                                            </template>
                                            <template x-if="getFilteredEvents(notification).length === 0">
                                                <div class="px-3 py-2 text-sm text-neutral-500 dark:text-neutral-400">No events found</div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>
