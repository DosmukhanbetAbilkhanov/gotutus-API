<x-filament-panels::page>
    <form wire:submit="save">
        <div>
            {{ $this->form }}
        </div>

        <div class="mt-6">
            <x-filament::button type="submit" size="lg">
                Save Settings
            </x-filament::button>
        </div>
    </form>

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
