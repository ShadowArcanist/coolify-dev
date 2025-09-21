@php use App\Enums\ProxyTypes; @endphp
<x-slot:title>
    Onboarding | Coolify
</x-slot>
<section class="flex flex-col lg:items-center lg:justify-center min-h-screen pb-20">
    <div
        class="flex flex-col items-center justify-center p-8 mx-4 mt-8 lg:p-16 max-w-6xl w-full animate-in fade-in-0 duration-500">
        @if ($currentState === 'welcome')
            <div class="text-center space-y-8 max-w-2xl mx-auto">
                <div class="space-y-4">
                    <h1 class="text-4xl font-semibold tracking-tight lg:text-6xl text-black dark:text-white">Welcome to Coolify</h1>
                    <p class="text-xl text-neutral-600 dark:text-neutral-300 leading-relaxed">Let's get you set up with your first server and project in just a few minutes.</p>
                </div>
                <div class="flex justify-center pt-4">
                    <x-forms.button class="box-boarding bg-coollabs-gradient text-base px-8 py-4 text-white"
                        wire:click="explanation">Get Started
                    </x-forms.button>
                </div>
            </div>

        @elseif ($currentState === 'select-server-type')
            <x-boarding-step title="Choose your server">
                <x-slot:question>
                    Where would you like to deploy your applications and services?
                </x-slot:question>
                 <x-slot:actions>
                     <div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full">
                         <x-forms.button class="box-boarding justify-start w-full" wire:target="setServerType('localhost')"
                             wire:click="setServerType('localhost')">
                             <div class="text-left">
                                 <div class="font-medium">Localhost</div>
                                 <div class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Use this server</div>
                             </div>
                         </x-forms.button>

                         <x-forms.button class="box-boarding justify-start w-full" wire:target="setServerType('remote')"
                             wire:click="setServerType('remote')">
                             <div class="text-left">
                                 <div class="font-medium">Remote Server</div>
                                 <div class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Connect to another server</div>
                             </div>
                         </x-forms.button>
                     </div>

                    @if (!$serverReachable)
                        <div class="mt-6 p-4 border border-error rounded-lg text-gray-800 dark:text-gray-200">
                            <h2 class="text-lg font-bold mb-2">Server is not reachable</h2>
                            <p class="mb-4">Please check the connection details below and correct them if they are
                                incorrect.</p>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <x-forms.input placeholder="Default is 22" label="Port" id="remoteServerPort"
                                    wire:model="remoteServerPort" :value="$remoteServerPort" />
                                <div>
                                    <x-forms.input placeholder="Default is root" label="User" id="remoteServerUser"
                                        wire:model="remoteServerUser" :value="$remoteServerUser" />
                                    <p class="text-xs mt-1">
                                        Non-root user is experimental:
                                        <a class="font-bold underline" target="_blank"
                                            href="https://coolify.io/docs/knowledge-base/server/non-root-user">docs</a>
                                    </p>
                                </div>
                            </div>

                            <div class="mb-4">
                                <p class="mb-2">If the connection details are correct, please ensure:</p>
                                <ul class="list-disc list-inside">
                                    <li>The correct public key is in your <code
                                            class="bg-red-200 dark:bg-red-900 px-1 rounded-sm">~/.ssh/authorized_keys</code>
                                        file for the specified user</li>
                                    <li>Or skip the boarding process and manually add a new private key to Coolify and
                                        the server</li>
                                </ul>
                            </div>

                            <p class="mb-4">
                                For more help, check this <a target="_blank" class="underline font-semibold"
                                    href="https://coolify.io/docs/knowledge-base/server/openssh">documentation</a>.
                            </p>

                            <x-forms.input readonly id="serverPublicKey" class="mb-4"
                                label="Current Public Key"></x-forms.input>

                            <x-forms.button class="w-full box-boarding" wire:click="saveAndValidateServer">
                                Check Again
                            </x-forms.button>
                        </div>
                    @endif
                </x-slot:actions>
                <x-slot:explanation>
                    <p>Servers are the main building blocks, as they will host your applications, databases,
                        services, called resources. Any CPU intensive process will use the server's CPU where you
                        are deploying your resources.</p>
                    <p>
                        <x-highlighted text="Localhost" /> is the server where Coolify is running on. It is not
                        recommended to use one server
                        for everything.
                    </p>
                    <p>
                        <x-highlighted text="A remote server" /> is a server reachable through SSH. It can be hosted
                        at home, or from any cloud
                        provider.
                    </p>
                </x-slot:explanation>
            </x-boarding-step>
        @elseif ($currentState === 'private-key')
            <x-boarding-step title="SSH Key Setup">
                <x-slot:question>
                    How would you like to authenticate with your server?
                </x-slot:question>
                 <x-slot:actions>
                     <div class="space-y-6 w-full">
                         <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                             <x-forms.button class="box-boarding justify-start" wire:target="setPrivateKey('own')"
                                 wire:click="setPrivateKey('own')">
                                 <div class="text-left">
                                     <div class="font-medium">Use your own SSH key</div>
                                     <div class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Enter your existing key</div>
                                 </div>
                             </x-forms.button>
                             <x-forms.button class="box-boarding justify-start w-full" wire:target="setPrivateKey('create')"
                                 wire:click="setPrivateKey('create')">
                                 <div class="text-left">
                                     <div class="font-medium">Generate new SSH key</div>
                                     <div class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Let Coolify create one</div>
                                 </div>
                             </x-forms.button>
                         </div>
                         @if (count($privateKeys) > 0)
                             <div class="border-t border-neutral-200 dark:border-neutral-700 pt-6">
                                 <div class="flex items-end gap-4">
                                     <div class="flex-[1.15]">
                                         <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2 text-left">Or use an existing key from Coolify</label>
                                         <x-forms.select id='selectedExistingPrivateKey' class="w-full h-12 px-4 py-3 text-sm border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-coollabs dark:focus:ring-warning">
                                             <option value="">Select an existing key...</option>
                                             @foreach ($privateKeys as $privateKey)
                                                 <option wire:key="{{ $loop->index }}" value="{{ $privateKey->id }}">
                                                     {{ $privateKey->name }}</option>
                                             @endforeach
                                         </x-forms.select>
                                     </div>
                                     <form wire:submit='selectExistingPrivateKey' class="flex-shrink-0">
                                         <x-forms.button type="submit" class="box-boarding h-12 px-6">Use this key</x-forms.button>
                                     </form>
                                 </div>
                             </div>
                         @endif
                     </div>
                 </x-slot:actions>
                <x-slot:explanation>
                    <p>SSH Keys are used to connect to a remote server through a secure shell, called SSH.</p>
                    <p>You can use your own ssh private key, or you can let Coolify to create one for you.</p>
                    <p>In both ways, you need to add the public version of your ssh private key to the remote
                        server's
                        <code class="text-coollabs dark:text-warning">~/.ssh/authorized_keys</code> file.
                    </p>
                </x-slot:explanation>
            </x-boarding-step>
        @elseif ($currentState === 'select-existing-server')
            <x-boarding-step title="Select a server">
                <x-slot:question>
                    There are already servers available for your Team. Do you want to use one of them?
                </x-slot:question>
                <x-slot:actions>
                     <div class="space-y-6 w-full">
                         <x-forms.button class="box-boarding justify-start w-full" wire:click="createNewServer">
                             <div class="text-left">
                                 <div class="font-medium">No, add a new server</div>
                                 <div class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Set up a different server</div>
                             </div>
                         </x-forms.button>

                         <div class="border-t border-neutral-200 dark:border-neutral-700 pt-6">
                             <div class="flex items-end gap-4">
                                 <div class="flex-[1.15]">
                                     <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2 text-left">Or use an existing server</label>
                                     <x-forms.select id='selectedExistingServer' class="w-full h-12 px-4 py-3 text-sm border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-coollabs dark:focus:ring-warning">
                                         @foreach ($servers as $server)
                                             <option wire:key="{{ $loop->index }}" value="{{ $server->id }}">
                                                 {{ $server->name }}</option>
                                         @endforeach
                                     </x-forms.select>
                                 </div>
                                 <form wire:submit='selectExistingServer' class="flex-shrink-0">
                                     <x-forms.button type="submit" class="box-boarding h-12 px-6">Use this server</x-forms.button>
                                 </form>
                             </div>
                         </div>
                     </div>
                    @if (!$serverReachable)
                        <div class="mt-6 p-4 bg-red-100 dark:bg-red-950 rounded-lg text-gray-800 dark:text-gray-200">
                            <h2 class="text-lg font-bold mb-2">Server is not reachable</h2>
                            <p class="mb-4">Please check the connection details below and correct them if they are
                                incorrect.</p>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <x-forms.input placeholder="Default is 22" label="Port" id="remoteServerPort"
                                    wire:model="remoteServerPort" :value="$remoteServerPort" />
                                <div>
                                    <x-forms.input placeholder="Default is root" label="User" id="remoteServerUser"
                                        wire:model="remoteServerUser" :value="$remoteServerUser" />
                                    <p class="text-xs mt-1">
                                        Non-root user is experimental:
                                        <a class="font-bold underline" target="_blank"
                                            href="https://coolify.io/docs/knowledge-base/server/non-root-user">docs</a>
                                    </p>
                                </div>
                            </div>

                            <div class="mb-4">
                                <p class="mb-2">If the connection details are correct, please ensure:</p>
                                <ul class="list-disc list-inside">
                                    <li>The correct public key is in your <code
                                            class="bg-red-200 dark:bg-red-900 px-1 rounded-sm">~/.ssh/authorized_keys</code>
                                        file for the specified user</li>
                                    <li>Or skip the boarding process and manually add a new private key to Coolify and
                                        the server</li>
                                </ul>
                            </div>

                            <p class="mb-4">
                                For more help, check this <a target="_blank" class="underline font-semibold"
                                    href="https://coolify.io/docs/knowledge-base/server/openssh">documentation</a>.
                            </p>

                            <x-forms.input readonly id="serverPublicKey" class="mb-4"
                                label="Current Public Key"></x-forms.input>

                            <x-forms.button class="w-full md:w-auto box-boarding" wire:click="saveAndValidateServer">
                                Check again
                            </x-forms.button>
                        </div>
                    @endif
                </x-slot:actions>
                <x-slot:explanation>
                    <p>Private Keys are used to connect to a remote server through a secure shell, called SSH.</p>
                    <p>You can use your own private key, or you can let Coolify to create one for you.</p>
                    <p>In both ways, you need to add the public version of your private key to the remote server's
                        <code>~/.ssh/authorized_keys</code> file.
                    </p>
                </x-slot:explanation>
            </x-boarding-step>
         @elseif ($currentState === 'create-private-key')
             @if ($privateKeyType === 'create')
                 <x-boarding-step title="Generated SSH Key">
                     <x-slot:question>
                         Coolify has generated a new SSH key pair for you.
                     </x-slot:question>
                     <x-slot:actions>
                         <div class="space-y-6 w-full">
                             <div class="grid grid-cols-1 gap-4">
                                 <div>
                                     <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2 text-left">Key Name <span class="text-coollabs dark:text-warning">*</span></label>
                                     <input type="text" required wire:model="privateKeyName" id="generatedKeyName"
                                         class="w-full h-12 px-4 py-3 text-sm border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-coollabs dark:focus:ring-warning" />
                                 </div>
                                 <div>
                                     <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2 text-left">Description</label>
                                     <input type="text" wire:model="privateKeyDescription" id="generatedKeyDescription"
                                         class="w-full h-12 px-4 py-3 text-sm border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-coollabs dark:focus:ring-warning" />
                                 </div>
                                 <div>
                                     <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2 text-left">Private Key</label>
                                     <textarea readonly rows="6" id="generatedPrivateKey"
                                         class="w-full px-4 py-3 text-sm border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white rounded-lg font-mono text-xs">{{ $privateKey }}</textarea>
                                 </div>
                                 <div>
                                     <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2 text-left">Public Key</label>
                                     <textarea readonly rows="3" id="generatedPublicKey"
                                         class="w-full px-4 py-3 text-sm border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white rounded-lg font-mono text-xs">{{ $publicKey }}</textarea>
                                 </div>
                             </div>
                             <div class="flex gap-4">
                                 <x-forms.button wire:click="generateNewKey" class="box-boarding flex-1">Generate another</x-forms.button>
                                 <x-forms.button wire:click="savePrivateKey" class="box-boarding flex-1">Use this key</x-forms.button>
                             </div>
                         </div>
                     </x-slot:actions>
                     <x-slot:explanation>
                         <p><strong>Important:</strong> Save this private key securely. You'll need to add the public key to your server's <code>~/.ssh/authorized_keys</code> file.</p>
                         <p>The private key is encrypted and stored securely in Coolify. You can download or copy it now.</p>
                     </x-slot:explanation>
                 </x-boarding-step>
             @else
                 <x-boarding-step title="Create Private Key">
                     <x-slot:question>
                         Please let me know your key details.
                     </x-slot:question>
                     <x-slot:actions>
                         <div class="space-y-6 w-full">
                             <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                 <x-forms.button class="box-boarding justify-start" wire:target="setPrivateKey('own')"
                                     wire:click="setPrivateKey('own')">
                                     <div class="text-left">
                                         <div class="font-medium">Use existing key</div>
                                         <div class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">I have my own SSH key</div>
                                     </div>
                                 </x-forms.button>
                                 <x-forms.button class="box-boarding justify-start w-full" wire:target="setPrivateKey('create')"
                                     wire:click="setPrivateKey('create')">
                                     <div class="text-left">
                                         <div class="font-medium">Generate new key</div>
                                         <div class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Let Coolify create one</div>
                                     </div>
                                 </x-forms.button>
                             </div>
                             @if (count($privateKeys) > 0)
                                 <div class="border-t border-neutral-200 dark:border-neutral-700 pt-6">
                                     <div>
                                         <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">Or use an existing key</label>
                                         <x-forms.select id='selectedExistingPrivateKey' class="w-full h-12 px-4 py-3 text-sm border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-coollabs dark:focus:ring-warning">
                                             @foreach ($privateKeys as $privateKey)
                                                 <option wire:key="{{ $loop->index }}" value="{{ $privateKey->id }}">
                                                     {{ $privateKey->name }}</option>
                                             @endforeach
                                         </x-forms.select>
                                     </div>
                                 </div>
                             @endif
                         </div>
                     </x-slot:actions>
                     <x-slot:explanation>
                         <p>Private Keys are used to connect to a remote server through a secure shell, called SSH.</p>
                         <p>You can use your own private key, or you can let Coolify to create one for you.</p>
                         <p>In both ways, you need to add the public version of your private key to the remote server's
                             <code>~/.ssh/authorized_keys</code> file.
                         </p>
                     </x-slot:explanation>
                 </x-boarding-step>
             @endif
         @elseif ($currentState === 'enter-private-key')
             <x-boarding-step title="Enter your SSH key">
                 <x-slot:question>
                     Please provide your SSH key details.
                 </x-slot:question>
                  <x-slot:actions>
                      <div class="space-y-6 w-full">
                         <div class="grid grid-cols-1 gap-4">
                             <div>
                                 <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2 text-left">Key Name <span class="text-coollabs dark:text-warning">*</span></label>
                                 <input type="text" required placeholder="my-ssh-key" id="privateKeyName" wire:model="privateKeyName"
                                     class="w-full h-12 px-4 py-3 text-sm border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-coollabs dark:focus:ring-warning" />
                             </div>
                             <div>
                                 <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2 text-left">Description (optional)</label>
                                 <input type="text" placeholder="Personal SSH key" id="privateKeyDescription" wire:model="privateKeyDescription"
                                     class="w-full h-12 px-4 py-3 text-sm border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-coollabs dark:focus:ring-warning" />
                             </div>
                             <div>
                                 <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2 text-left">Private Key <span class="text-coollabs dark:text-warning">*</span></label>
                                 <textarea required placeholder="Paste your private SSH key here..." id="privateKey" wire:model="privateKey" rows="8"
                                     class="w-full px-4 py-3 text-sm border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-coollabs dark:focus:ring-warning resize-y"></textarea>
                                 @error('privateKey') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                             </div>
                         </div>
                         <x-forms.button wire:click="savePrivateKey" class="box-boarding w-full">Save SSH Key</x-forms.button>
                     </div>
                 </x-slot:actions>
                 <x-slot:explanation>
                     <p>Enter the details for your existing SSH private key. Make sure to include the full key including the header and footer lines.</p>
                     <p>The private key will be securely stored and used to connect to your servers.</p>
                 </x-slot:explanation>
             </x-boarding-step>
         @elseif ($currentState === 'create-server')
            <x-boarding-step title="Server Details">
                <x-slot:question>
                    Tell us about your server so we can connect to it.
                </x-slot:question>
                 <x-slot:actions>
                     <form wire:submit='saveServer' class="space-y-6 w-full">
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2 text-left">Server Name <span class="text-coollabs dark:text-warning">*</span></label>
                                <input type="text" required placeholder="my-server" id="remoteServerName" wire:model="remoteServerName"
                                    class="w-full h-12 px-4 py-3 text-sm border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-coollabs dark:focus:ring-warning" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2 text-left">Description (optional)</label>
                                <input type="text" placeholder="Production server" id="remoteServerDescription" wire:model="remoteServerDescription"
                                    class="w-full h-12 px-4 py-3 text-sm border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-coollabs dark:focus:ring-warning" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2 text-left">IP Address or Domain <span class="text-coollabs dark:text-warning">*</span></label>
                                <input type="text" required placeholder="192.168.1.100" id="remoteServerHost" wire:model="remoteServerHost"
                                    class="w-full h-12 px-4 py-3 text-sm border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-coollabs dark:focus:ring-warning" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2 text-left">SSH Port <span class="text-coollabs dark:text-warning">*</span></label>
                                <input type="number" required placeholder="22" id="remoteServerPort" wire:model="remoteServerPort"
                                    class="w-full h-12 px-4 py-3 text-sm border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-coollabs dark:focus:ring-warning" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2 text-left">SSH User <span class="text-coollabs dark:text-warning">*</span></label>
                                <input type="text" required placeholder="root" id="remoteServerUser" wire:model="remoteServerUser"
                                    class="w-full h-12 px-4 py-3 text-sm border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-coollabs dark:focus:ring-warning" />
                                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                                    Non-root user support is experimental.
                                    <a class="text-neutral-700 dark:text-neutral-300 underline hover:text-neutral-900 dark:hover:text-white" target="_blank"
                                        href="https://coolify.io/docs/knowledge-base/server/non-root-user">Learn more</a>.
                                </p>
                            </div>
                        </div>

                        <x-forms.button type="submit" class="box-boarding w-full">Connect Server</x-forms.button>
                    </form>
                </x-slot:actions>
                <x-slot:explanation>
                    <div class="space-y-4">
                        <div class="flex items-start gap-3">
                            <div class="w-2 h-2 rounded-full bg-neutral-400 dark:bg-neutral-500 mt-2 flex-shrink-0"></div>
                            <p><strong class="text-neutral-900 dark:text-white">SSH Access:</strong> Make sure your server is accessible via SSH and you have the correct credentials.</p>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="w-2 h-2 rounded-full bg-neutral-400 dark:bg-neutral-500 mt-2 flex-shrink-0"></div>
                            <p><strong class="text-neutral-900 dark:text-white">Security:</strong> We'll install Docker and configure your server securely for deployments.</p>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="w-2 h-2 rounded-full bg-neutral-400 dark:bg-neutral-500 mt-2 flex-shrink-0"></div>
                            <p><strong class="text-neutral-900 dark:text-white">Requirements:</strong> Your server needs internet access and at least 2GB RAM for basic operations.</p>
                        </div>
                    </div>
                </x-slot:explanation>
            </x-boarding-step>
        @elseif ($currentState === 'validate-server')
            <x-boarding-step title="Validate & Configure Server">
                <x-slot:question>
                    I need to validate your server (connection, Docker Engine, etc) and configure if something is
                    missing for me. Are you okay with this?
                </x-slot:question>
                <x-slot:actions>
                    <x-slide-over closeWithX fullScreen>
                        <x-slot:title>Validate & configure</x-slot:title>
                        <x-slot:content>
                            <livewire:server.validate-and-install :server="$this->createdServer" />
                        </x-slot:content>
                        <x-forms.button @click="slideOverOpen=true" class="w-full font-bold box-boarding lg:w-96"
                            wire:click.prevent='installServer' isHighlighted>
                            Let's do it!
                        </x-forms.button>
                    </x-slide-over>
                </x-slot:actions>
                <x-slot:explanation>
                    <p>This will install the latest Docker Engine on your server, configure a few things to be able
                        to run optimal.<br><br>Minimum Docker Engine version is: {{ $minDockerVersion }}<br><br>To
                        manually install
                        Docker
                        Engine, check <a target="_blank" class="underline text-coollabs dark:text-warning"
                            href="https://docs.docker.com/engine/install/#server">this
                            documentation</a>.</p>
                </x-slot:explanation>
            </x-boarding-step>
        @elseif ($currentState === 'create-project')
            <x-boarding-step title="Your Project">
                <x-slot:question>
                    @if (count($projects) > 0)
                        You have existing projects. Would you like to use one or create a new one?
                    @else
                        Let's create your first project to organize your applications and services.
                    @endif
                </x-slot:question>
                 <x-slot:actions>
                     <div class="space-y-6 w-full">
                         <x-forms.button class="box-boarding justify-start w-full" wire:click="createNewProject">
                             <div class="text-left">
                                 <div class="font-medium">Create new project</div>
                                 <div class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Start fresh with "My first project"</div>
                             </div>
                         </x-forms.button>

                        @if (count($projects) > 0)
                            <div class="border-t border-neutral-200 dark:border-neutral-700 pt-6">
                                <div class="flex items-end gap-4">
                                    <div class="flex-[1.15]">
                                         <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2 text-left">Or use an existing project</label>
                                        <x-forms.select id='selectedProject' class="w-full h-12 px-4 py-3 text-sm border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-coollabs dark:focus:ring-warning">
                                            @foreach ($projects as $project)
                                                <option wire:key="{{ $loop->index }}" value="{{ $project->id }}">
                                                    {{ $project->name }}</option>
                                            @endforeach
                                        </x-forms.select>
                                    </div>
                                    <form wire:submit='selectExistingProject' class="flex-shrink-0">
                                        <x-forms.button type="submit" class="box-boarding h-12 px-6">Use this project</x-forms.button>
                                    </form>
                                </div>
                            </div>
                        @endif
                    </div>
                </x-slot:actions>
                <x-slot:explanation>
                    <p>Projects contain several resources combined into one virtual group. There are no
                        limitations on the number of projects you can add.</p>
                    <p>Each project should have at least one environment, this allows you to create a production &
                        staging version of the same application, but grouped separately.</p>
                </x-slot:explanation>
            </x-boarding-step>
        @elseif ($currentState === 'enter-project-name')
            <x-boarding-step title="Name your project">
                <x-slot:question>
                    What would you like to call your first project?
                </x-slot:question>
                 <x-slot:actions>
                     <form wire:submit='saveNewProject' class="space-y-6 w-full">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2 text-left">Project Name <span class="text-coollabs dark:text-warning">*</span></label>
                            <input type="text" required placeholder="My awesome project" id="newProjectName" wire:model="newProjectName"
                                class="w-full h-12 px-4 py-3 text-sm border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-coollabs dark:focus:ring-warning" />
                        </div>
                        <x-forms.button type="submit" class="box-boarding w-full">Create Project</x-forms.button>
                    </form>
                </x-slot:actions>
                <x-slot:explanation>
                    <div class="space-y-4">
                        <div class="flex items-start gap-3">
                            <div class="w-2 h-2 rounded-full bg-neutral-400 dark:bg-neutral-500 mt-2 flex-shrink-0"></div>
                            <p><strong class="text-neutral-900 dark:text-white">Organization:</strong> Projects help you organize your applications, databases, and services.</p>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="w-2 h-2 rounded-full bg-neutral-400 dark:bg-neutral-500 mt-2 flex-shrink-0"></div>
                            <p><strong class="text-neutral-900 dark:text-white">Environments:</strong> Each project can have multiple environments like staging and production.</p>
                        </div>
                    </div>
                </x-slot:explanation>
            </x-boarding-step>
        @elseif ($currentState === 'create-resource')
            <x-boarding-step title="You're all set!">
                <x-slot:question>
                    Your server and project are ready. Let's create your first application, database, or service.
                </x-slot:question>
                <x-slot:actions>
                    <div class="space-y-4">
                        <x-forms.button class="box-boarding text-base px-8 py-4 text-neutral-900 dark:text-white" wire:click="showNewResource">
                            Create your first resource
                        </x-forms.button>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400 text-center">
                            You can deploy applications from Git, set up databases, or add services like WordPress.
                        </p>
                    </div>
                </x-slot:actions>
                <x-slot:explanation>
                    <p>A resource could be an application, a database or a service (like WordPress).</p>
                </x-slot:explanation>
            </x-boarding-step>
        @endif
    </div>

    <!-- Fixed footer at bottom -->
    <div class="fixed bottom-0 left-0 right-0 bg-white/80 dark:bg-neutral-900/80 backdrop-blur-sm border-t border-neutral-200 dark:border-neutral-800">
        <div class="max-w-6xl mx-auto px-8 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-6">
                    <button class="text-sm text-neutral-500 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-200 transition-colors" wire:click='skipBoarding'>
                        Skip onboarding
                    </button>
                    <button class="text-sm text-neutral-500 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-200 transition-colors" wire:click='restartBoarding'>
                        Restart
                    </button>
                </div>

                <div class="flex items-center gap-4">
                    <x-modal-input title="Need help?">
                        <x-slot:content>
                            <div class="w-full text-center cursor-pointer hover:underline dark:hover:text-white py-2"
                                title="Get help or send feedback">
                                Questions? We're here to help.
                            </div>
                        </x-slot:content>
                        <livewire:help />
                    </x-modal-input>

                    <!-- Theme toggle -->
                    <button
                        x-data="{
                            theme: localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'),
                            toggleTheme() {
                                this.theme = this.theme === 'dark' ? 'light' : 'dark';
                                localStorage.setItem('theme', this.theme);
                                if (this.theme === 'dark') {
                                    document.documentElement.classList.add('dark');
                                } else {
                                    document.documentElement.classList.remove('dark');
                                }
                            }
                        }"
                        @click="toggleTheme()"
                        class="p-2 rounded-lg text-neutral-500 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors"
                        title="Toggle theme">
                        <svg x-show="theme === 'dark'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        <svg x-show="theme === 'light'" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>
