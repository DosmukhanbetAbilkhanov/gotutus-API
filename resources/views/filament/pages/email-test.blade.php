<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">Send Test Email</x-slot>
        <x-slot name="description">Send a plain text email to verify email delivery is working.</x-slot>

        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button wire:click="sendTestEmail" icon="heroicon-o-paper-airplane">
                Send Test Email
            </x-filament::button>
        </div>
    </x-filament::section>

    <x-filament::section>
        <x-slot name="heading">Current Mail Configuration</x-slot>

        <dl class="grid grid-cols-1 gap-2 sm:grid-cols-2">
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Mailer</dt>
                <dd class="text-sm text-gray-900 dark:text-white">{{ config('mail.default') }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">From Address</dt>
                <dd class="text-sm text-gray-900 dark:text-white">{{ config('mail.from.address') }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Admin Email</dt>
                <dd class="text-sm text-gray-900 dark:text-white">{{ config('services.mobizon.admin_email') ?: 'not set' }}</dd>
            </div>
        </dl>
    </x-filament::section>
</x-filament-panels::page>
