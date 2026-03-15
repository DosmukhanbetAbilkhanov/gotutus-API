<?php

namespace App\Filament\Pages;

use App\Enums\ReportStatus;
use App\Enums\UserStatus;
use App\Filament\Resources\UserResource;
use App\Models\Report;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;

class ReportReviewPage extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-shield-check';

    protected static string | \UnitEnum | null $navigationGroup = 'Users & Safety';

    protected static ?int $navigationSort = 5;

    protected static ?string $title = 'Report Review';

    protected string $view = 'filament.pages.report-review';

    #[Computed]
    public function pendingReports()
    {
        return Report::with(['reporter', 'reportedUser', 'hangoutRequest'])
            ->where('status', ReportStatus::Pending)
            ->orderBy('created_at', 'asc')
            ->limit(20)
            ->get();
    }

    #[Computed]
    public function pendingCount(): int
    {
        return Report::where('status', ReportStatus::Pending)->count();
    }

    public function dismissReport(int $reportId): void
    {
        $report = Report::findOrFail($reportId);
        $report->update([
            'status' => ReportStatus::Dismissed,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        unset($this->pendingReports, $this->pendingCount);

        Notification::make()
            ->title('Report dismissed')
            ->success()
            ->send();
    }

    public function suspendUser(int $reportId): void
    {
        $report = Report::with('reportedUser')->findOrFail($reportId);
        $report->reportedUser->update(['status' => UserStatus::Suspended]);
        $report->reportedUser->tokens()->delete();
        $report->update([
            'status' => ReportStatus::ActionTaken,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'admin_notes' => ($report->admin_notes ? $report->admin_notes . "\n" : '') . 'User suspended via review page.',
        ]);

        unset($this->pendingReports, $this->pendingCount);

        Notification::make()
            ->title('User suspended and report resolved')
            ->success()
            ->send();
    }

    public function banUser(int $reportId): void
    {
        $report = Report::with('reportedUser')->findOrFail($reportId);
        $report->reportedUser->update(['status' => UserStatus::Banned]);
        $report->reportedUser->tokens()->delete();
        $report->update([
            'status' => ReportStatus::ActionTaken,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'admin_notes' => ($report->admin_notes ? $report->admin_notes . "\n" : '') . 'User banned via review page.',
        ]);

        unset($this->pendingReports, $this->pendingCount);

        Notification::make()
            ->title('User banned and report resolved')
            ->success()
            ->send();
    }

    public static function getUserViewUrl(int $userId): string
    {
        return UserResource::getUrl('view', ['record' => $userId]);
    }
}
