<x-filament-panels::page>
    <div class="space-y-6">
        {{-- System Stats --}}
        <x-filament::section>
            <x-slot name="heading">System Overview</x-slot>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="p-4 bg-primary-50 dark:bg-primary-900/20 rounded-lg">
                    <div class="text-sm font-medium text-primary-600 dark:text-primary-400">Total Users</div>
                    <div class="text-2xl font-bold text-primary-700 dark:text-primary-300">{{ \App\Models\User::count() }}</div>
                </div>
                <div class="p-4 bg-success-50 dark:bg-success-900/20 rounded-lg">
                    <div class="text-sm font-medium text-success-600 dark:text-success-400">Active Streams</div>
                    <div class="text-2xl font-bold text-success-700 dark:text-success-300">{{ \App\Models\Stream::where('is_active', true)->count() }}</div>
                </div>
                <div class="p-4 bg-warning-50 dark:bg-warning-900/20 rounded-lg">
                    <div class="text-sm font-medium text-warning-600 dark:text-warning-400">Total Bouquets</div>
                    <div class="text-2xl font-bold text-warning-700 dark:text-warning-300">{{ \App\Models\Bouquet::count() }}</div>
                </div>
                <div class="p-4 bg-info-50 dark:bg-info-900/20 rounded-lg">
                    <div class="text-sm font-medium text-info-600 dark:text-info-400">EPG Programs</div>
                    <div class="text-2xl font-bold text-info-700 dark:text-info-300">{{ \App\Models\EpgProgram::count() }}</div>
                </div>
            </div>
        </x-filament::section>

        {{-- Backups --}}
        <x-filament::section>
            <x-slot name="heading">Available Backups</x-slot>
            
            @php $backups = $this->getBackups(); @endphp
            
            @if(count($backups) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b dark:border-gray-700">
                                <th class="text-left py-2 px-4">Filename</th>
                                <th class="text-left py-2 px-4">Size</th>
                                <th class="text-left py-2 px-4">Created</th>
                                <th class="text-right py-2 px-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($backups as $backup)
                                <tr class="border-b dark:border-gray-700">
                                    <td class="py-2 px-4">{{ $backup['filename'] }}</td>
                                    <td class="py-2 px-4">{{ number_format($backup['size'] / 1024, 2) }} KB</td>
                                    <td class="py-2 px-4">{{ \Carbon\Carbon::createFromTimestamp($backup['created_at'])->diffForHumans() }}</td>
                                    <td class="py-2 px-4 text-right">
                                        <x-filament::button 
                                            color="danger" 
                                            size="xs"
                                            wire:click="deleteBackup('{{ $backup['path'] }}')"
                                            wire:confirm="Are you sure you want to delete this backup?"
                                        >
                                            Delete
                                        </x-filament::button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                    <x-heroicon-o-archive-box class="w-12 h-12 mx-auto mb-4 opacity-50" />
                    <p>No backups available. Click "Create Backup" to create one.</p>
                </div>
            @endif
        </x-filament::section>

        {{-- System Info --}}
        <x-filament::section>
            <x-slot name="heading">System Information</x-slot>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <dl class="space-y-2">
                        <div class="flex justify-between">
                            <dt class="text-gray-500 dark:text-gray-400">Laravel Version</dt>
                            <dd class="font-medium">{{ app()->version() }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500 dark:text-gray-400">PHP Version</dt>
                            <dd class="font-medium">{{ PHP_VERSION }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500 dark:text-gray-400">Environment</dt>
                            <dd class="font-medium">{{ app()->environment() }}</dd>
                        </div>
                    </dl>
                </div>
                <div>
                    <dl class="space-y-2">
                        <div class="flex justify-between">
                            <dt class="text-gray-500 dark:text-gray-400">Database</dt>
                            <dd class="font-medium">{{ config('database.default') }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500 dark:text-gray-400">Cache Driver</dt>
                            <dd class="font-medium">{{ config('cache.default') }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500 dark:text-gray-400">Queue Driver</dt>
                            <dd class="font-medium">{{ config('queue.default') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
