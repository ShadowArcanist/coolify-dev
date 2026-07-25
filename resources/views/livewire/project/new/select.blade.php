<div class="application-settings-form" x-data x-init="$wire.loadServers">
    <div x-data="searchResources()">
        @if ($current_step === 'type')
            @php
                $environmentOptions = $environments
                    ->map(fn ($environment) => [
                        'value' => $environment->name,
                        'label' => $environment->name,
                    ])
                    ->values()
                    ->all();
            @endphp

            <x-application.settings-section title="Choose a resource"
                description="Deploy an application, database, or service into this environment." flush>
                <div
                    class="flex flex-col gap-3 border-b border-neutral-200 p-4 dark:border-white/[0.08] lg:flex-row lg:items-end">
                    <div class="relative min-w-0 flex-1">
                        <x-reicon name="search"
                            class="pointer-events-none absolute top-1/2 left-2.5 z-10 size-3.5 -translate-y-1/2 text-neutral-400 dark:text-fg-faint" />
                        <input autocomplete="off" x-ref="searchInput" x-model="search" type="search"
                            placeholder="Search resources"
                            class="h-8! w-full rounded-lg! border-neutral-200! bg-white! py-0! pr-8! pl-8! text-[12px]! shadow-none! placeholder:text-neutral-400 focus:border-neutral-300! focus:ring-0! dark:border-white/[0.08]! dark:bg-white/[0.035]! dark:text-fg! dark:placeholder:text-fg-faint"
                            @keydown.window.slash.prevent="$refs.searchInput.focus()">
                    </div>

                    <div class="w-full lg:w-52">
                        <x-forms.listbox id="selectedEnvironment" :options="$environmentOptions"
                            placeholder="Environment" live />
                    </div>

                    <div class="relative w-full lg:w-52"
                        x-data="{ openCategoryDropdown: false, categorySearch: '' }"
                        @click.outside="openCategoryDropdown = false" @keydown.escape="openCategoryDropdown = false">
                        <button type="button" class="listbox-trigger"
                            :disabled="loading || categories.length === 0"
                            @click="openCategoryDropdown = !openCategoryDropdown; $nextTick(() => openCategoryDropdown && $refs.categorySearchInput.focus())">
                            <span class="truncate capitalize"
                                x-text="selectedCategory === '' ? 'All categories' : selectedCategory"></span>
                            <svg class="size-3.5 shrink-0 opacity-60" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="m8 9 4-4 4 4m0 6-4 4-4-4" />
                            </svg>
                        </button>
                        <div x-cloak x-show="openCategoryDropdown" x-transition class="listbox-panel">
                            <div class="border-b border-neutral-200 p-2 dark:border-white/[0.08]">
                                <input type="search" x-ref="categorySearchInput" x-model="categorySearch"
                                    placeholder="Search categories"
                                    class="h-8! w-full rounded-md! border-neutral-200! bg-neutral-50! px-2.5! py-0! text-[12px]! shadow-none! focus:ring-0! dark:border-white/[0.08]! dark:bg-white/[0.04]! dark:text-fg!"
                                    @click.stop>
                            </div>
                            <div class="max-h-60 overflow-auto p-1">
                                <button type="button" class="listbox-option"
                                    @click="selectedCategory = ''; categorySearch = ''; openCategoryDropdown = false"
                                    :aria-selected="selectedCategory === ''">
                                    <span>All categories</span>
                                    <x-reicon name="check-circle" class="size-3.5"
                                        x-show="selectedCategory === ''" />
                                </button>
                                <template
                                    x-for="category in categories.filter(cat => categorySearch === '' || cat.toLowerCase().includes(categorySearch.toLowerCase()))"
                                    :key="category">
                                    <button type="button" class="listbox-option capitalize"
                                        @click="selectedCategory = category; categorySearch = ''; openCategoryDropdown = false"
                                        :aria-selected="selectedCategory === category">
                                        <span class="truncate" x-text="category"></span>
                                        <x-reicon name="check-circle" class="size-3.5"
                                            x-show="selectedCategory === category" />
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </x-application.settings-section>

            <div x-show="loading" class="flex items-center justify-center py-8">
                <x-loading text="Loading resources..." />
            </div>
            <div x-show="!loading" class="mt-6 flex flex-col gap-6">
                <div x-show="filteredGitBasedApplications.length > 0 || filteredDockerBasedApplications.length > 0">
                    <div class="mb-3 flex items-center gap-2">
                        <x-reicon name="globe" class="size-4 text-neutral-400 dark:text-fg-faint" />
                        <h2 class="text-[14px]! font-semibold!">Applications</h2>
                    </div>
                <div x-show="filteredGitBasedApplications.length > 0 || filteredDockerBasedApplications.length > 0"
                        class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                    <div x-show="filteredGitBasedApplications.length > 0" class="space-y-2">
                            <p class="text-[11px] font-medium text-neutral-500 dark:text-fg-faint">Git based</p>
                        <div class="grid justify-start grid-cols-1 gap-2 text-left">
                            <template x-for="application in filteredGitBasedApplications" :key="application.name">
                                <div x-on:click='setType(application.id)'
                                    :class="{ 'cursor-pointer': !selecting, 'cursor-not-allowed opacity-50': selecting }">
                                    <x-resource-view>
                                        <x-slot:title><span x-text="application.name"></span></x-slot>
                                        <x-slot:description>
                                            <span x-html="window.sanitizeHTML(application.description)"></span>
                                        </x-slot>
                                        <x-slot:logo>
                                            <img class="w-full h-full p-2 transition-all duration-200 dark:bg-white/10 bg-black/10 object-contain"
                                                :src="application.logo">
                                        </x-slot:logo>
                                    </x-resource-view>
                                </div>
                            </template>
                        </div>
                    </div>
                    <div x-show="filteredDockerBasedApplications.length > 0" class="space-y-2">
                            <p class="text-[11px] font-medium text-neutral-500 dark:text-fg-faint">Docker based</p>
                        <div class="grid justify-start grid-cols-1 gap-2 text-left">
                            <template x-for="application in filteredDockerBasedApplications" :key="application.name">
                                <div x-on:click="setType(application.id)"
                                    :class="{ 'cursor-pointer': !selecting, 'cursor-not-allowed opacity-50': selecting }">
                                    <x-resource-view>
                                        <x-slot:title><span x-text="application.name"></span></x-slot>
                                        <x-slot:description><span x-text="application.description"></span></x-slot>
                                        <x-slot:logo> <img
                                                class="w-full h-full p-2 transition-all duration-200 dark:bg-white/10 bg-black/10 object-contain"
                                                :src="application.logo"></x-slot>
                                    </x-resource-view>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
                </div>
                <div x-show="filteredDatabases.length > 0">
                    <div class="mb-3 flex items-center gap-2">
                        <x-reicon name="database" class="size-4 text-neutral-400 dark:text-fg-faint" />
                        <h2 class="text-[14px]! font-semibold!">Databases</h2>
                    </div>
                    <div class="grid justify-start grid-cols-1 gap-2 text-left md:grid-cols-2 xl:grid-cols-3">
                        <template x-for="database in filteredDatabases" :key="database.id">
                            <div x-on:click="setType(database.id)"
                                :class="{ 'cursor-pointer': !selecting, 'cursor-not-allowed opacity-50': selecting }">
                                <x-resource-view>
                                    <x-slot:title><span x-text="database.name"></span></x-slot>
                                    <x-slot:description><span x-text="database.description"></span></x-slot>
                                    <x-slot:logo>
                                        <span x-show="database.logo">
                                            <span x-html="database.logo"></span>
                                        </span>
                                    </x-slot>
                                </x-resource-view>
                            </div>
                        </template>
                    </div>
                </div>
                <div x-show="filteredServices.length > 0">
                    <div class="mb-3 flex flex-wrap items-center gap-3" x-init="loadResources">
                        <x-reicon name="layers" class="size-4 text-neutral-400 dark:text-fg-faint" />
                        <h2 class="text-[14px]! font-semibold!">Services</h2>
                        <button type="button" class="button" x-on:click="loadResources">
                            <x-reicon name="refresh" class="size-3.5" />
                            Reload
                        </button>
                        <div x-show="serviceTemplatesLastUpdated"
                                class="text-[11px] text-neutral-500 dark:text-fg-faint">
                            Updated
                            <span x-text="serviceTemplatesLastUpdated"></span>
                        </div>
                    </div>
                    <x-callout type="info" title="Trademarks policy" class="mb-4">
                        The respective trademarks mentioned here are owned by the respective companies, and use of them
                        does not imply any affiliation or endorsement.
                    </x-callout>

                    <div class="grid justify-start grid-cols-1 gap-2 text-left md:grid-cols-2 xl:grid-cols-3">
                        <template x-for="service in filteredServices" :key="service.name">
                            <div class="relative" x-on:click="setType('one-click-service-' + service.id)"
                                :class="{ 'cursor-pointer': !selecting, 'cursor-not-allowed opacity-50': selecting }">
                                <x-resource-view>
                                    <x-slot:title>
                                        <template x-if="service.name">
                                            <div>
                                                <span x-text="service.name"></span>
                                                <template x-if="service.templateLastUpdated">
                                                    <div class="mt-1 text-[0.7rem] font-normal text-neutral-500 dark:text-neutral-500">
                                                        Updated: <span x-text="service.templateLastUpdated"></span>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>
                                    </x-slot>
                                    <x-slot:description>
                                        <template x-if="service.slogan">
                                            <span x-text="service.slogan"></span>
                                        </template>
                                    </x-slot>
                                    <x-slot:logo>
                                        <template x-if="service.logo">
                                            <img class="w-full h-full p-2 transition-all duration-200 dark:bg-white/10 bg-black/10 object-contain"
                                                :src='service.logo'
                                                x-on:error.window="$event.target.src = service.logo_github_url"
                                                onerror="this.onerror=null; this.src=this.getAttribute('data-fallback');"
                                                x-on:error="$event.target.src = '/coolify-logo.svg'"
                                                :data-fallback='service.logo_github_url' />
                                        </template>
                                    </x-slot:logo>
                                </x-resource-view>
                                <template x-if="service.amd_only">
                                    <div class="absolute top-2 right-10 group">
                                        <span
                                            class="px-2 py-0.5 text-xs rounded bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-200 cursor-pointer">
                                            AMD only
                                        </span>
                                        <div class="info-helper-popup right-0 w-sm">
                                            <div class="p-4">
                                                This service only supports AMD64/x86_64 architecture. It will not work
                                                on ARM-based servers (e.g., Apple Silicon, Raspberry Pi, AWS Graviton).
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="service.arm_only">
                                    <div class="absolute top-2 right-10 group">
                                        <span
                                            class="px-2 py-0.5 text-xs rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-200 cursor-pointer">
                                            ARM only
                                        </span>
                                        <div class="info-helper-popup right-0 w-sm">
                                            <div class="p-4">
                                                This service only supports ARM64/aarch64 architecture. It will not work
                                                on AMD64/x86_64-based servers.
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="shouldShowDocIcon(service)">
                                    <a :href="getDocLink(service) || coolifyDocsUrl(service)" target="_blank"
                                        @click.stop @mouseenter="resolveDocLink(service)"
                                        class="absolute top-2 right-2 p-1.5 rounded hover:bg-neutral-200 dark:hover:bg-coolgray-300 transition-colors"
                                        :class="{ 'opacity-50': docCheckInProgress[service.name] }"
                                        title="View documentation">
                                        <svg class="w-4 h-4 text-neutral-600 dark:text-neutral-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </a>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
                <div
                    x-show="filteredGitBasedApplications.length === 0 && filteredDockerBasedApplications.length === 0 && filteredDatabases.length === 0 && filteredServices.length === 0 && loading === false">
                    <div>No resources found.</div>
                </div>
            </div>
            <script>
                function sortFn(a, b) {
                    return a.name.localeCompare(b.name)
                }

                function searchResources() {
                    return {
                        search: '',
                        selectedCategory: '',
                        categories: [],
                        loading: false,
                        isSticky: false,
                        selecting: false,
                        services: [],
                        serviceTemplatesLastUpdated: null,
                        gitBasedApplications: [],
                        dockerBasedApplications: [],
                        databases: [],
                        docLinkCache: {}, // Cache resolved doc URLs: { serviceName: url | null }
                        docCheckInProgress: {}, // Track ongoing checks: { serviceName: boolean }
                        setType(type) {
                            if (this.selecting) return;
                            this.selecting = true;
                            this.$wire.setType(type);
                        },
                        async loadResources() {
                            this.loading = true;
                            const {
                                services,
                                serviceTemplatesLastUpdated,
                                categories,
                                gitBasedApplications,
                                dockerBasedApplications,
                                databases
                            } = await this.$wire.loadServices();
                            this.services = services;
                            this.serviceTemplatesLastUpdated = serviceTemplatesLastUpdated;
                            this.categories = categories || [];
                            this.gitBasedApplications = gitBasedApplications;
                            this.dockerBasedApplications = dockerBasedApplications;
                            this.databases = databases;
                            this.loading = false;
                            this.$nextTick(() => {
                                this.$refs.searchInput.focus();
                            });
                        },
                        extractBaseServiceName(serviceName) {
                            // Convert to lowercase and replace spaces with dashes to match original format
                            const normalized = serviceName.toLowerCase().replace(/\s+/g, '-');
                            // Remove flavor suffixes: -with-*, -without-*
                            return normalized.replace(/-(with|without)-.+$/, '');
                        },
                        coolifyDocsUrl(service) {
                            const baseName = service.docsSlug || this.extractBaseServiceName(service.name);
                            return 'https://coolify.io/docs/services/' + baseName;
                        },
                        officialDocsUrl(service) {
                            return service.documentation || null;
                        },
                        async checkUrlExists(url) {
                            if (!url) return false;
                            try {
                                const response = await fetch(url, {
                                    method: 'HEAD'
                                });
                                return response.ok;
                            } catch (e) {
                                // CORS error or network error - assume URL exists
                                return true;
                            }
                        },
                        async resolveDocLink(service) {
                            const serviceName = service.name;

                            // Already cached?
                            if (this.docLinkCache.hasOwnProperty(serviceName)) {
                                return this.docLinkCache[serviceName];
                            }

                            // Already checking?
                            if (this.docCheckInProgress[serviceName]) {
                                return null;
                            }

                            this.docCheckInProgress[serviceName] = true;

                            // 1. Try Coolify docs first
                            const coolifyUrl = this.coolifyDocsUrl(service);
                            const coolifyExists = await this.checkUrlExists(coolifyUrl);

                            if (coolifyExists) {
                                this.docLinkCache[serviceName] = coolifyUrl;
                                this.docCheckInProgress[serviceName] = false;
                                return coolifyUrl;
                            }

                            // 2. Fall back to official docs
                            const officialUrl = this.officialDocsUrl(service);
                            if (officialUrl) {
                                const officialExists = await this.checkUrlExists(officialUrl);

                                if (officialExists) {
                                    this.docLinkCache[serviceName] = officialUrl;
                                    this.docCheckInProgress[serviceName] = false;
                                    return officialUrl;
                                }
                            }

                            // 3. Both failed - cache null to hide icon
                            this.docLinkCache[serviceName] = null;
                            this.docCheckInProgress[serviceName] = false;
                            return null;
                        },
                        getDocLink(service) {
                            return this.docLinkCache[service.name];
                        },
                        shouldShowDocIcon(service) {
                            const cached = this.docLinkCache[service.name];
                            // Show icon if: not checked yet OR has a valid URL
                            return cached === undefined || cached !== null;
                        },
                        filterAndSort(items, isSort = true) {
                            const searchLower = this.search.trim().toLowerCase();
                            let filtered = Object.values(items);

                            // Filter by category if selected
                            if (this.selectedCategory !== '') {
                                const selectedCategoryLower = this.selectedCategory.toLowerCase();
                                filtered = filtered.filter(item => {
                                    if (!item.category) return false;
                                    // Handle comma-separated categories
                                    const categories = item.category.includes(',') ?
                                        item.category.split(',').map(c => c.trim().toLowerCase()) : [item.category
                                            .toLowerCase()
                                        ];
                                    return categories.includes(selectedCategoryLower);
                                });
                            }

                            // Filter by search term
                            if (searchLower !== '') {
                                filtered = filtered.filter(item => {
                                    return (item.name?.toLowerCase().includes(searchLower) ||
                                        item.description?.toLowerCase().includes(searchLower) ||
                                        item.slogan?.toLowerCase().includes(searchLower))
                                });
                            }

                            return isSort ? filtered.sort(sortFn) : filtered;
                        },
                        get filteredGitBasedApplications() {
                            if (this.gitBasedApplications.length === 0) {
                                return [];
                            }
                            return [
                                this.gitBasedApplications,
                            ].flatMap((items) => this.filterAndSort(items, false));
                        },
                        get filteredDockerBasedApplications() {
                            if (this.dockerBasedApplications.length === 0) {
                                return [];
                            }
                            return [
                                this.dockerBasedApplications,
                            ].flatMap((items) => this.filterAndSort(items, false));
                        },
                        get filteredDatabases() {
                            if (this.databases.length === 0) {
                                return [];
                            }
                            return [
                                this.databases,
                            ].flatMap((items) => this.filterAndSort(items, false));
                        },
                        get filteredServices() {
                            if (this.services.length === 0) {
                                return [];
                            }
                            return [
                                this.services,
                            ].flatMap((items) => this.filterAndSort(items, true));
                        }
                    }
                }
            </script>
        @endif
    </div>
    @if ($current_step === 'servers')
        <x-application.settings-section title="Select a server"
            description="Choose the machine that will host this resource." flush>
            @if ($onlyBuildServerAvailable)
                <x-callout type="warning" title="No deployment server" class="m-4">
                    Only build servers are available. Add or reconfigure a server before continuing.
                    <a class="font-medium underline" href="{{ route('server.index') }}" {{ wireNavigate() }}>Open
                        servers</a>
                </x-callout>
            @endif
            <div class="divide-y divide-neutral-200 dark:divide-white/[0.07]">
                @forelse($servers as $server)
                    <button type="button" wire:click="setServer({{ $server }})"
                        class="group flex min-h-14 w-full items-center gap-3 px-4 py-3 text-left transition-colors hover:bg-neutral-50 dark:hover:bg-white/[0.025]">
                        <span
                            class="flex size-8 shrink-0 items-center justify-center rounded-lg border border-neutral-200 bg-neutral-50 text-neutral-500 dark:border-white/[0.08] dark:bg-white/[0.035] dark:text-fg-dim">
                            <x-reicon name="servers" class="size-4" />
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-[13px] font-semibold text-black dark:text-fg">
                                {{ $server->name }}
                            </span>
                            <span class="block truncate text-[11px] text-neutral-500 dark:text-fg-faint">
                                {{ $server->description ?: $server->ip }}
                            </span>
                        </span>
                        <x-status-badge status="running" text="Ready" />
                        <x-reicon name="arrow-right"
                            class="size-3.5 text-neutral-300 transition-transform group-hover:translate-x-0.5 dark:text-fg-faint" />
                    </button>
                @empty
                    @if ($buildServers?->isEmpty() && ! $onlyBuildServerAvailable)
                        <x-empty title="No available servers"
                            description="Validate a reachable server before creating this resource." size="sm">
                            <x-slot:icon>
                                <x-reicon name="servers" class="size-6" />
                            </x-slot:icon>
                            <x-slot:actions>
                                <a class="button" href="{{ route('server.index') }}" {{ wireNavigate() }}>Open
                                    servers</a>
                            </x-slot:actions>
                        </x-empty>
                    @endif
                @endforelse

                @foreach($buildServers ?? [] as $buildServer)
                    <div class="flex min-h-14 items-center gap-3 px-4 py-3 opacity-55">
                        <span
                            class="flex size-8 shrink-0 items-center justify-center rounded-lg border border-neutral-200 bg-neutral-50 text-neutral-500 dark:border-white/[0.08] dark:bg-white/[0.035] dark:text-fg-dim">
                            <x-reicon name="servers" class="size-4" />
                        </span>
                        <span class="min-w-0 flex-1">
                            <span
                                class="block truncate text-[13px] font-semibold text-black dark:text-fg">{{ $buildServer->name }}</span>
                            <span class="block text-[11px] text-neutral-500 dark:text-fg-faint">Build-only servers
                                cannot host resources.</span>
                        </span>
                        <x-status-badge status="exited" text="Build only" />
                        <a href="{{ route('server.show', ['server_uuid' => $buildServer->uuid]) }}"
                            {{ wireNavigate() }} class="button">Settings</a>
                    </div>
                @endforeach
            </div>
        </x-application.settings-section>
    @endif
    @if ($current_step === 'destinations')
        <x-application.settings-section title="Select a destination"
            description="Destinations separate resources by Docker network. Use the default destination when unsure."
            flush>
            <div class="divide-y divide-neutral-200 dark:divide-white/[0.07]">
                @if ($server->isSwarm())
                    @foreach ($swarmDockers as $swarmDocker)
                        <button type="button" wire:click="setDestination('{{ $swarmDocker->uuid }}')"
                            class="group flex min-h-14 w-full items-center gap-3 px-4 py-3 text-left transition-colors hover:bg-neutral-50 dark:hover:bg-white/[0.025]">
                            <span
                                class="flex size-8 shrink-0 items-center justify-center rounded-lg border border-neutral-200 bg-neutral-50 text-neutral-500 dark:border-white/[0.08] dark:bg-white/[0.035] dark:text-fg-dim">
                                <x-reicon name="destinations" class="size-4" />
                            </span>
                            <span class="min-w-0 flex-1">
                                <span
                                    class="block truncate text-[13px] font-semibold text-black dark:text-fg">{{ $swarmDocker->name }}</span>
                                <span class="block text-[11px] text-neutral-500 dark:text-fg-faint">Docker Swarm
                                    destination</span>
                            </span>
                            <x-deprecated-badge />
                            <x-reicon name="arrow-right"
                                class="size-3.5 text-neutral-300 transition-transform group-hover:translate-x-0.5 dark:text-fg-faint" />
                        </button>
                    @endforeach
                @else
                    @foreach ($standaloneDockers as $standaloneDocker)
                        <button type="button" wire:click="setDestination('{{ $standaloneDocker->uuid }}')"
                            class="group flex min-h-14 w-full items-center gap-3 px-4 py-3 text-left transition-colors hover:bg-neutral-50 dark:hover:bg-white/[0.025]">
                            <span
                                class="flex size-8 shrink-0 items-center justify-center rounded-lg border border-neutral-200 bg-neutral-50 text-neutral-500 dark:border-white/[0.08] dark:bg-white/[0.035] dark:text-fg-dim">
                                <x-reicon name="destinations" class="size-4" />
                            </span>
                            <span class="min-w-0 flex-1">
                                <span
                                    class="block truncate text-[13px] font-semibold text-black dark:text-fg">{{ $standaloneDocker->name }}</span>
                                <span class="block truncate text-[11px] text-neutral-500 dark:text-fg-faint">Network:
                                    {{ $standaloneDocker->network }}</span>
                            </span>
                            <x-status-badge status="running" text="Standalone Docker" />
                            <x-reicon name="arrow-right"
                                class="size-3.5 text-neutral-300 transition-transform group-hover:translate-x-0.5 dark:text-fg-faint" />
                        </button>
                    @endforeach
                @endif
            </div>
        </x-application.settings-section>
    @endif
    @if ($current_step === 'select-postgresql-type')
        <div x-data="{ selecting: false }">
            <div class="mb-4">
                <h2 class="text-[15px]! font-semibold!">Select a PostgreSQL image</h2>
                <p class="mt-1 text-[12px] text-neutral-500 dark:text-fg-dim">Use PostgreSQL 18 unless the workload
                    needs bundled extensions.</p>
            </div>
            <div class="grid grid-cols-1 gap-3 lg:grid-cols-2">
                <div class="group relative flex min-h-24 items-center gap-3 rounded-[10px] border border-neutral-200 bg-white p-4 transition-colors hover:border-neutral-300 hover:bg-neutral-50 dark:border-white/[0.07] dark:bg-surface dark:hover:border-white/[0.12] dark:hover:bg-white/[0.035]"
                    :class="{ 'cursor-pointer': !selecting, 'cursor-not-allowed opacity-50': selecting }"
                    x-on:click="!selecting && (selecting = true, $wire.setPostgresqlType('postgres:18-alpine'))"
                    :disabled="selecting">
                    <div class="flex flex-col">
                        <div class="text-[13px] font-semibold text-black dark:text-fg">PostgreSQL 18 <span
                                class="ml-1 rounded-full bg-coollabs/10 px-2 py-0.5 text-[10px] font-medium text-coollabs dark:bg-warning/15 dark:text-warning">Default</span>
                        </div>
                        <div class="mt-1 pr-8 text-[11px] leading-4 text-neutral-500 dark:text-fg-faint">
                            PostgreSQL is a powerful, open-source object-relational database system (no extensions).
                        </div>
                    </div>
                    <a href="https://hub.docker.com/_/postgres/" target="_blank"
                        @click.stop
                        class="absolute top-2 right-2 flex size-7 items-center justify-center rounded-md text-neutral-400 transition-colors hover:bg-neutral-100 hover:text-black dark:text-fg-faint dark:hover:bg-white/[0.06] dark:hover:text-fg"
                        title="View documentation">
                        <svg class="w-4 h-4 text-neutral-600 dark:text-neutral-400" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </a>
                </div>
                <div class="group relative flex min-h-24 items-center gap-3 rounded-[10px] border border-neutral-200 bg-white p-4 transition-colors hover:border-neutral-300 hover:bg-neutral-50 dark:border-white/[0.07] dark:bg-surface dark:hover:border-white/[0.12] dark:hover:bg-white/[0.035]"
                    :class="{ 'cursor-pointer': !selecting, 'cursor-not-allowed opacity-50': selecting }"
                    x-on:click="!selecting && (selecting = true, $wire.setPostgresqlType('postgres:17-alpine'))"
                    :disabled="selecting">
                    <div class="flex flex-col">
                        <div class="text-[13px] font-semibold text-black dark:text-fg">PostgreSQL 17</div>
                        <div class="mt-1 pr-8 text-[11px] leading-4 text-neutral-500 dark:text-fg-faint">
                            PostgreSQL is a powerful, open-source object-relational database system (no extensions).
                        </div>
                    </div>
                    <a href="https://hub.docker.com/_/postgres/" target="_blank"
                        @click.stop
                        class="absolute top-2 right-2 flex size-7 items-center justify-center rounded-md text-neutral-400 transition-colors hover:bg-neutral-100 hover:text-black dark:text-fg-faint dark:hover:bg-white/[0.06] dark:hover:text-fg"
                        title="View documentation">
                        <svg class="w-4 h-4 text-neutral-600 dark:text-neutral-400" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </a>
                </div>
                <div class="group relative flex min-h-24 items-center gap-3 rounded-[10px] border border-neutral-200 bg-white p-4 transition-colors hover:border-neutral-300 hover:bg-neutral-50 dark:border-white/[0.07] dark:bg-surface dark:hover:border-white/[0.12] dark:hover:bg-white/[0.035]"
                    :class="{ 'cursor-pointer': !selecting, 'cursor-not-allowed opacity-50': selecting }"
                    x-on:click="!selecting && (selecting = true, $wire.setPostgresqlType('postgres:16-alpine'))"
                    :disabled="selecting">
                    <div class="flex flex-col">
                        <div class="text-[13px] font-semibold text-black dark:text-fg">PostgreSQL 16</div>
                        <div class="mt-1 pr-8 text-[11px] leading-4 text-neutral-500 dark:text-fg-faint">
                            PostgreSQL is a powerful, open-source object-relational database system (no extensions).
                        </div>
                    </div>
                    <a href="https://hub.docker.com/_/postgres/" target="_blank"
                        @click.stop
                        class="absolute top-2 right-2 flex size-7 items-center justify-center rounded-md text-neutral-400 transition-colors hover:bg-neutral-100 hover:text-black dark:text-fg-faint dark:hover:bg-white/[0.06] dark:hover:text-fg"
                        title="View documentation">
                        <svg class="w-4 h-4 text-neutral-600 dark:text-neutral-400" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </a>
                </div>
                <div class="group relative flex min-h-24 items-center gap-3 rounded-[10px] border border-neutral-200 bg-white p-4 transition-colors hover:border-neutral-300 hover:bg-neutral-50 dark:border-white/[0.07] dark:bg-surface dark:hover:border-white/[0.12] dark:hover:bg-white/[0.035]"
                    :class="{ 'cursor-pointer': !selecting, 'cursor-not-allowed opacity-50': selecting }"
                    x-on:click="!selecting && (selecting = true, $wire.setPostgresqlType('supabase/postgres:17.4.1.032'))"
                    :disabled="selecting">
                    <div class="flex flex-col">
                        <div class="text-[13px] font-semibold text-black dark:text-fg">Supabase PostgreSQL</div>
                        <div class="mt-1 pr-8 text-[11px] leading-4 text-neutral-500 dark:text-fg-faint">
                            Supabase is a modern, open-source alternative to PostgreSQL with lots of extensions.
                        </div>
                    </div>
                    <a href="https://github.com/supabase/postgres" target="_blank"
                        @click.stop
                        class="absolute top-2 right-2 flex size-7 items-center justify-center rounded-md text-neutral-400 transition-colors hover:bg-neutral-100 hover:text-black dark:text-fg-faint dark:hover:bg-white/[0.06] dark:hover:text-fg"
                        title="View documentation">
                        <svg class="w-4 h-4 text-neutral-600 dark:text-neutral-400" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </a>
                </div>
                <div class="group relative flex min-h-24 items-center gap-3 rounded-[10px] border border-neutral-200 bg-white p-4 transition-colors hover:border-neutral-300 hover:bg-neutral-50 dark:border-white/[0.07] dark:bg-surface dark:hover:border-white/[0.12] dark:hover:bg-white/[0.035]"
                    :class="{ 'cursor-pointer': !selecting, 'cursor-not-allowed opacity-50': selecting }"
                    x-on:click="!selecting && (selecting = true, $wire.setPostgresqlType('postgis/postgis:17-3.5-alpine'))"
                    :disabled="selecting">
                    <div class="flex flex-col">
                        <div class="text-[13px] font-semibold text-black dark:text-fg">PostGIS <span
                                class="ml-1 text-[10px] font-medium text-amber-600 dark:text-amber-300">AMD only</span>
                        </div>
                        <div class="mt-1 pr-8 text-[11px] leading-4 text-neutral-500 dark:text-fg-faint">
                            PostGIS is a PostgreSQL extension for geographic objects.
                        </div>
                    </div>
                    <a href="https://github.com/postgis/docker-postgis" target="_blank"
                        @click.stop
                        class="absolute top-2 right-2 flex size-7 items-center justify-center rounded-md text-neutral-400 transition-colors hover:bg-neutral-100 hover:text-black dark:text-fg-faint dark:hover:bg-white/[0.06] dark:hover:text-fg"
                        title="View documentation">
                        <svg class="w-4 h-4 text-neutral-600 dark:text-neutral-400" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </a>
                </div>
                <div class="group relative flex min-h-24 items-center gap-3 rounded-[10px] border border-neutral-200 bg-white p-4 transition-colors hover:border-neutral-300 hover:bg-neutral-50 dark:border-white/[0.07] dark:bg-surface dark:hover:border-white/[0.12] dark:hover:bg-white/[0.035]"
                    :class="{ 'cursor-pointer': !selecting, 'cursor-not-allowed opacity-50': selecting }"
                    x-on:click="!selecting && (selecting = true, $wire.setPostgresqlType('pgvector/pgvector:pg18'))"
                    :disabled="selecting">
                    <div class="flex flex-col">
                        <div class="text-[13px] font-semibold text-black dark:text-fg">PGVector 18</div>
                        <div class="mt-1 pr-8 text-[11px] leading-4 text-neutral-500 dark:text-fg-faint">
                            PGVector is a PostgreSQL extension for vector data types.
                        </div>
                    </div>
                    <a href="https://github.com/pgvector/pgvector" target="_blank"
                        @click.stop
                        class="absolute top-2 right-2 flex size-7 items-center justify-center rounded-md text-neutral-400 transition-colors hover:bg-neutral-100 hover:text-black dark:text-fg-faint dark:hover:bg-white/[0.06] dark:hover:text-fg"
                        title="View documentation">
                        <svg class="w-4 h-4 text-neutral-600 dark:text-neutral-400" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </a>
                </div>
                <div class="group relative flex min-h-24 items-center gap-3 rounded-[10px] border border-neutral-200 bg-white p-4 transition-colors hover:border-neutral-300 hover:bg-neutral-50 dark:border-white/[0.07] dark:bg-surface dark:hover:border-white/[0.12] dark:hover:bg-white/[0.035]"
                    :class="{ 'cursor-pointer': !selecting, 'cursor-not-allowed opacity-50': selecting }"
                    x-on:click="!selecting && (selecting = true, $wire.setPostgresqlType('pgvector/pgvector:pg17'))"
                    :disabled="selecting">
                    <div class="flex flex-col">
                        <div class="text-[13px] font-semibold text-black dark:text-fg">PGVector 17</div>
                        <div class="mt-1 pr-8 text-[11px] leading-4 text-neutral-500 dark:text-fg-faint">
                            PGVector is a PostgreSQL extension for vector data types.
                        </div>
                    </div>
                    <a href="https://github.com/pgvector/pgvector" target="_blank"
                        @click.stop
                        class="absolute top-2 right-2 flex size-7 items-center justify-center rounded-md text-neutral-400 transition-colors hover:bg-neutral-100 hover:text-black dark:text-fg-faint dark:hover:bg-white/[0.06] dark:hover:text-fg"
                        title="View documentation">
                        <svg class="w-4 h-4 text-neutral-600 dark:text-neutral-400" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    @endif
    @if ($current_step === 'existing-postgresql')
        <x-application.settings-section title="Connect an existing PostgreSQL database"
            description="Provide the connection URL for the database Coolify should use.">
            <form wire:submit="addExistingPostgresql" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                <div class="min-w-0 flex-1">
                    <x-forms.input placeholder="postgres://username:password@database:5432" label="Database URL"
                        id="existingPostgresqlUrl" />
                </div>
                <x-forms.button type="submit">Add database</x-forms.button>
            </form>
        </x-application.settings-section>
    @endif
</div>
