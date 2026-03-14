<?php

namespace App\Filament\Pages;

use App\Enums\PhotoStatus;
use App\Models\UserPhoto;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Livewire\Attributes\Computed;

class PhotoModerationQueue extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-camera';

    protected static string | \UnitEnum | null $navigationGroup = 'Users & Safety';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Photo Moderation Queue';

    protected string $view = 'filament.pages.photo-moderation-queue';

    public static function getNavigationBadge(): ?string
    {
        $count = UserPhoto::where('status', PhotoStatus::Pending)->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    #[Computed]
    public function pendingPhotos()
    {
        return UserPhoto::with('user')
            ->where('status', PhotoStatus::Pending)
            ->orderBy('created_at', 'asc')
            ->limit(20)
            ->get();
    }

    #[Computed]
    public function pendingCount(): int
    {
        return UserPhoto::where('status', PhotoStatus::Pending)->count();
    }

    public function approvePhoto(int $photoId): void
    {
        $photo = UserPhoto::findOrFail($photoId);
        $photo->update([
            'status' => PhotoStatus::Approved,
            'rejection_reason' => null,
        ]);

        unset($this->pendingPhotos, $this->pendingCount);

        Notification::make()
            ->title('Photo approved')
            ->success()
            ->send();
    }

    public function rejectPhoto(int $photoId, ?string $reason = null): void
    {
        $photo = UserPhoto::findOrFail($photoId);
        $photo->update([
            'status' => PhotoStatus::Rejected,
            'rejection_reason' => $reason ?? 'Rejected by admin',
        ]);

        unset($this->pendingPhotos, $this->pendingCount);

        Notification::make()
            ->title('Photo rejected')
            ->danger()
            ->send();
    }
}
