<x-filament-panels::page>
    <x-filament::section>
        <div class="flex items-start gap-3">
            <x-filament::icon
                icon="heroicon-o-lock-closed"
                class="h-5 w-5 mt-0.5 text-primary-600 dark:text-primary-400"
            />
            <div>
                <p class="font-semibold text-gray-900 dark:text-white">
                    Read-only — design is managed in code
                </p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    The app design (Fresh Meet) is the single source of truth in
                    <code>AppDesignSetting::defaults()</code> and is applied with
                    <code>php artisan db:seed --class=AppDesignSettingSeeder</code>.
                    These values are shown for reference only and cannot be edited here.
                    To change the design, update the code and deploy.
                </p>
            </div>
        </div>
    </x-filament::section>

    <div class="mt-6">
        {{ $this->form }}
    </div>

    {{-- Color Preview --}}
    {{-- @if($this->data)
        <div class="mt-8">
            <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Color Preview</h3>
            <div class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 gap-3">
                @foreach([
                    'colors_primary' => 'Primary',
                    'colors_primaryLight' => 'Primary Light',
                    'colors_secondary' => 'Secondary',
                    'colors_backgroundLight' => 'Bg Light',
                    'colors_backgroundDark' => 'Bg Dark',
                    'colors_textPrimary' => 'Text Primary',
                    'colors_textSecondary' => 'Text Secondary',
                    'colors_textTertiary' => 'Text Tertiary',
                    'colors_inputBackground' => 'Input Bg',
                    'colors_border' => 'Border',
                    'colors_divider' => 'Divider',
                    'colors_success' => 'Success',
                    'colors_error' => 'Error',
                    'colors_warning' => 'Warning',
                    'colors_messageMine' => 'Msg Mine',
                    'colors_messageTheirs' => 'Msg Theirs',
                ] as $key => $label)
                    <div class="text-center">
                        <div
                            class="w-12 h-12 rounded-lg mx-auto border border-gray-200 dark:border-gray-700"
                            style="background-color: {{ $this->data[$key] ?? '#000' }}"
                        ></div>
                        <span class="text-xs text-gray-500 mt-1 block">{{ $label }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif --}}
</x-filament-panels::page>
