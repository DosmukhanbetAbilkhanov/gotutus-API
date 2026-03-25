<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Form --}}
        <div>
            {{ $this->form }}
        </div>

        {{-- Action Buttons --}}
        <div class="flex gap-3">
            <x-filament::button wire:click="preview" color="gray">
                Preview
            </x-filament::button>

            @if($this->hasPreview)
                <x-filament::button wire:click="import" color="primary">
                    Import
                </x-filament::button>
            @endif
        </div>

        {{-- Preview Results --}}
        @if($this->previewData)
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-6 space-y-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Preview Results</h3>

                {{-- Summary Stats --}}
                <div class="grid grid-cols-4 gap-4">
                    <div class="rounded-lg bg-gray-50 dark:bg-gray-800 p-3 text-center">
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $this->previewData['total_rows'] }}</div>
                        <div class="text-sm text-gray-500">Total Rows</div>
                    </div>
                    <div class="rounded-lg bg-green-50 dark:bg-green-900/20 p-3 text-center">
                        <div class="text-2xl font-bold text-green-600">{{ $this->previewData['valid_count'] }}</div>
                        <div class="text-sm text-gray-500">Valid</div>
                    </div>
                    <div class="rounded-lg bg-yellow-50 dark:bg-yellow-900/20 p-3 text-center">
                        <div class="text-2xl font-bold text-yellow-600">{{ $this->previewData['warning_count'] }}</div>
                        <div class="text-sm text-gray-500">Warnings</div>
                    </div>
                    <div class="rounded-lg bg-red-50 dark:bg-red-900/20 p-3 text-center">
                        <div class="text-2xl font-bold text-red-600">{{ $this->previewData['error_count'] }}</div>
                        <div class="text-sm text-gray-500">Errors</div>
                    </div>
                </div>

                {{-- Sample Rows Table --}}
                @if(!empty($this->previewData['preview_rows']))
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-4 py-2">#</th>
                                    <th class="px-4 py-2">Name</th>
                                    <th class="px-4 py-2">Address</th>
                                    <th class="px-4 py-2">Phone</th>
                                    <th class="px-4 py-2">Working Hours</th>
                                    <th class="px-4 py-2">Lat</th>
                                    <th class="px-4 py-2">Lng</th>
                                    <th class="px-4 py-2">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($this->previewData['preview_rows'] as $row)
                                    <tr class="border-b border-gray-100 dark:border-gray-800">
                                        <td class="px-4 py-2 text-gray-500">{{ $row['row'] }}</td>
                                        <td class="px-4 py-2 text-gray-900 dark:text-white">{{ \Illuminate\Support\Str::limit($row['name'], 40) }}</td>
                                        <td class="px-4 py-2 text-gray-500">{{ \Illuminate\Support\Str::limit($row['address'] ?? '', 40) }}</td>
                                        <td class="px-4 py-2 text-gray-500">{{ $row['phone'] }}</td>
                                        <td class="px-4 py-2 text-gray-500">{{ \Illuminate\Support\Str::limit($row['working_hours'] ?? '', 30) }}</td>
                                        <td class="px-4 py-2 text-gray-500">{{ \Illuminate\Support\Str::limit($row['latitude'] ?? '', 10) }}</td>
                                        <td class="px-4 py-2 text-gray-500">{{ \Illuminate\Support\Str::limit($row['longitude'] ?? '', 10) }}</td>
                                        <td class="px-4 py-2">
                                            @if($row['status'] === 'OK')
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">OK</span>
                                            @elseif($row['status'] === 'Warning')
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">Warning</span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">Error</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                {{-- Warnings --}}
                @if(!empty($this->previewData['warnings']))
                    <div class="rounded-lg bg-yellow-50 dark:bg-yellow-900/10 border border-yellow-200 dark:border-yellow-800 p-4">
                        <h4 class="font-semibold text-yellow-800 dark:text-yellow-200 mb-2">Warnings</h4>
                        <ul class="list-disc list-inside text-sm text-yellow-700 dark:text-yellow-300 space-y-1 max-h-40 overflow-y-auto">
                            @foreach($this->previewData['warnings'] as $warning)
                                <li>{{ $warning }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Errors --}}
                @if(!empty($this->previewData['errors']))
                    <div class="rounded-lg bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-800 p-4">
                        <h4 class="font-semibold text-red-800 dark:text-red-200 mb-2">Errors</h4>
                        <ul class="list-disc list-inside text-sm text-red-700 dark:text-red-300 space-y-1 max-h-40 overflow-y-auto">
                            @foreach($this->previewData['errors'] as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @endif

        {{-- Import Results --}}
        @if($this->importResult)
            <div class="rounded-xl border border-green-200 dark:border-green-700 bg-green-50 dark:bg-green-900/10 p-6 space-y-4">
                <h3 class="text-lg font-semibold text-green-800 dark:text-green-200">Import Complete</h3>

                <div class="grid grid-cols-4 gap-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $this->importResult['total_rows'] }}</div>
                        <div class="text-sm text-gray-500">Total Rows</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600">{{ $this->importResult['imported'] }}</div>
                        <div class="text-sm text-gray-500">Imported</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-yellow-600">{{ $this->importResult['skipped'] }}</div>
                        <div class="text-sm text-gray-500">Skipped</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-red-600">{{ $this->importResult['failed'] }}</div>
                        <div class="text-sm text-gray-500">Failed</div>
                    </div>
                </div>

                @if(!empty($this->importResult['warnings']))
                    <div class="rounded-lg bg-yellow-50 dark:bg-yellow-900/10 border border-yellow-200 dark:border-yellow-800 p-4">
                        <h4 class="font-semibold text-yellow-800 dark:text-yellow-200 mb-2">Warnings</h4>
                        <ul class="list-disc list-inside text-sm text-yellow-700 dark:text-yellow-300 space-y-1 max-h-40 overflow-y-auto">
                            @foreach($this->importResult['warnings'] as $warning)
                                <li>{{ $warning }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(!empty($this->importResult['errors']))
                    <div class="rounded-lg bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-800 p-4">
                        <h4 class="font-semibold text-red-800 dark:text-red-200 mb-2">Errors</h4>
                        <ul class="list-disc list-inside text-sm text-red-700 dark:text-red-300 space-y-1 max-h-40 overflow-y-auto">
                            @foreach($this->importResult['errors'] as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @endif

        {{-- Recent Import History --}}
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recent Imports</h3>
            @php
                $recentImports = \App\Models\PlaceImport::with(['city.translations', 'user'])
                    ->latest()
                    ->take(10)
                    ->get();
            @endphp

            @if($recentImports->isEmpty())
                <p class="text-gray-500 text-sm">No imports yet.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-4 py-2">Date</th>
                                <th class="px-4 py-2">City</th>
                                <th class="px-4 py-2">Admin</th>
                                <th class="px-4 py-2">File</th>
                                <th class="px-4 py-2">Total</th>
                                <th class="px-4 py-2">Imported</th>
                                <th class="px-4 py-2">Skipped</th>
                                <th class="px-4 py-2">Failed</th>
                                <th class="px-4 py-2">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentImports as $import)
                                <tr class="border-b border-gray-100 dark:border-gray-800">
                                    <td class="px-4 py-2 text-gray-500">{{ $import->created_at->format('Y-m-d H:i') }}</td>
                                    <td class="px-4 py-2 text-gray-900 dark:text-white">
                                        @php
                                            $cityName = $import->city?->translations->firstWhere('language_code', 'en')?->name
                                                ?? $import->city?->translations->firstWhere('language_code', 'ru')?->name
                                                ?? 'Unknown';
                                        @endphp
                                        {{ $cityName }}
                                    </td>
                                    <td class="px-4 py-2 text-gray-500">{{ $import->user?->name ?? 'Unknown' }}</td>
                                    <td class="px-4 py-2 text-gray-500">{{ \Illuminate\Support\Str::limit($import->file_name, 30) }}</td>
                                    <td class="px-4 py-2">{{ $import->total_rows }}</td>
                                    <td class="px-4 py-2 text-green-600">{{ $import->imported_count }}</td>
                                    <td class="px-4 py-2 text-yellow-600">{{ $import->skipped_count }}</td>
                                    <td class="px-4 py-2 text-red-600">{{ $import->failed_count }}</td>
                                    <td class="px-4 py-2">
                                        @if($import->status === 'completed')
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">Completed</span>
                                        @elseif($import->status === 'processing')
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700">Processing</span>
                                        @elseif($import->status === 'failed')
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">Failed</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">{{ ucfirst($import->status) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
