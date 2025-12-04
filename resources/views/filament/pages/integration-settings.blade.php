<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Sonarr Section --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                        <div class="p-2 bg-orange-100 dark:bg-orange-900/30 rounded-lg mr-3">
                            <x-heroicon-o-tv class="h-6 w-6 text-orange-600 dark:text-orange-400" />
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Sonarr</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">TV Series Management</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <x-filament::button
                            color="gray"
                            size="sm"
                            wire:click="testSonarrConnection"
                            wire:loading.attr="disabled"
                        >
                            <x-heroicon-m-signal class="h-4 w-4 mr-1" />
                            Test Connection
                        </x-filament::button>
                    </div>
                </div>

                {{ $this->sonarrForm }}

                <div class="mt-4 p-4 rounded-lg {{ $this->sonarrConfigured ? 'bg-green-50 dark:bg-green-900/20' : 'bg-yellow-50 dark:bg-yellow-900/20' }}">
                    <div class="flex items-center">
                        @if($this->sonarrConfigured)
                            <x-heroicon-o-check-circle class="h-5 w-5 text-green-500 mr-2" />
                            <span class="text-sm text-green-700 dark:text-green-400">Sonarr is configured</span>
                        @else
                            <x-heroicon-o-exclamation-triangle class="h-5 w-5 text-yellow-500 mr-2" />
                            <span class="text-sm text-yellow-700 dark:text-yellow-400">Sonarr is not configured. Set environment variables to enable.</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Radarr Section --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center">
                        <div class="p-2 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg mr-3">
                            <x-heroicon-o-film class="h-6 w-6 text-yellow-600 dark:text-yellow-400" />
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Radarr</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Movie Management</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <x-filament::button
                            color="gray"
                            size="sm"
                            wire:click="testRadarrConnection"
                            wire:loading.attr="disabled"
                        >
                            <x-heroicon-m-signal class="h-4 w-4 mr-1" />
                            Test Connection
                        </x-filament::button>
                    </div>
                </div>

                {{ $this->radarrForm }}

                <div class="mt-4 p-4 rounded-lg {{ $this->radarrConfigured ? 'bg-green-50 dark:bg-green-900/20' : 'bg-yellow-50 dark:bg-yellow-900/20' }}">
                    <div class="flex items-center">
                        @if($this->radarrConfigured)
                            <x-heroicon-o-check-circle class="h-5 w-5 text-green-500 mr-2" />
                            <span class="text-sm text-green-700 dark:text-green-400">Radarr is configured</span>
                        @else
                            <x-heroicon-o-exclamation-triangle class="h-5 w-5 text-yellow-500 mr-2" />
                            <span class="text-sm text-yellow-700 dark:text-yellow-400">Radarr is not configured. Set environment variables to enable.</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Cache Management --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="p-2 bg-gray-100 dark:bg-gray-700 rounded-lg mr-3">
                            <x-heroicon-o-arrow-path class="h-6 w-6 text-gray-600 dark:text-gray-400" />
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Cache Management</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Clear cached API responses</p>
                        </div>
                    </div>
                    <x-filament::button
                        color="gray"
                        wire:click="clearCache"
                        wire:loading.attr="disabled"
                    >
                        <x-heroicon-m-trash class="h-4 w-4 mr-1" />
                        Clear Cache
                    </x-filament::button>
                </div>
            </div>
        </div>

        {{-- Environment Variables Help --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Environment Configuration</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                    Add the following variables to your <code class="px-1 py-0.5 bg-gray-100 dark:bg-gray-700 rounded">.env</code> file:
                </p>
                <div class="bg-gray-900 rounded-lg p-4 overflow-x-auto">
                    <pre class="text-sm text-gray-300"><code># Sonarr Configuration
SONARR_URL=http://localhost:8989
SONARR_API_KEY=your-sonarr-api-key

# Radarr Configuration
RADARR_URL=http://localhost:7878
RADARR_API_KEY=your-radarr-api-key</code></pre>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
