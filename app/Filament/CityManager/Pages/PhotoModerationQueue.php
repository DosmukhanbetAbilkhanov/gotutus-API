<?php

namespace App\Filament\CityManager\Pages;

use App\Enums\PhotoStatus;
use App\Models\UserPhoto;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Livewire\Attributes\Computed;

class PhotoModerationQueue extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-camera';

    protected static string | \UnitEnum | null $navigationGroup = 'Users';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Photo Moderation Queue';

    protected string $view = 'filament.pages.photo-moderation-queue';

    private function cityScoped()
    {
        return UserPhoto::whereHas('user', fn ($q) => $q->where('city_id', auth()->user()->city_id));
    }

    public static function getNavigationBadge(): ?string
    {
        $cityId = auth()->user()?->city_id;
        if (! $cityId) {
            return null;
        }

        $count = UserPhoto::whereHas('user', fn ($q) => $q->where('city_id', $cityId))
            ->where('status', PhotoStatus::Pending)
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    #[Computed]
    public function pendingPhotos()
    {
        return $this->cityScoped()
            ->with('user')
            ->where('status', PhotoStatus::Pending)
            ->orderBy('created_at', 'asc')
            ->limit(20)
            ->get();
    }

    #[Computed]
    public function pendingCount(): int
    {
        return $this->cityScoped()
            ->where('status', PhotoStatus::Pending)
            ->count();
    }

    public function approvePhoto(int $photoId): void
    {
        $photo = $this->cityScoped()->findOrFail($photoId);
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
        $photo = $this->cityScoped()->findOrFail($photoId);
        $photo->update([
            'status' => PhotoStatus::Rejected,
            'rejection_reason' => $reason ?? 'Rejected by city manager',
        ]);

        unset($this->pendingPhotos, $this->pendingCount);

        Notification::make()
            ->title('Photo rejected')
            ->danger()
            ->send();
    }
}
