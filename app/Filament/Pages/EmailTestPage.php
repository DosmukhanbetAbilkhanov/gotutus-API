<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Mail\TestMail;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Mail;

class EmailTestPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-envelope';

    protected static string | \UnitEnum | null $navigationGroup = 'System';

    protected static ?int $navigationSort = 20;

    protected static ?string $title = 'Email Test';

    protected static ?string $slug = 'email-test';

    protected string $view = 'filament.pages.email-test';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'recipient' => config('services.mobizon.admin_email') ?: 'administrator@tanys.app',
            'message' => 'This is a test email to verify delivery is working correctly.',
        ]);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                TextInput::make('recipient')
                    ->label('Recipient Email')
                    ->email()
                    ->required(),
                Textarea::make('message')
                    ->label('Message')
                    ->required()
                    ->rows(4),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [];
    }

    public function sendTestEmail(): void
    {
        $formData = $this->form->getState();

        try {
            Mail::to($formData['recipient'])->queue(new TestMail($formData['message']));

            Notification::make()
                ->title('Test email queued for ' . $formData['recipient'])
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Failed to send email')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
