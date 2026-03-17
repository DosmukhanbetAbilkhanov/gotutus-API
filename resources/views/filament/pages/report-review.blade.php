<x-filament-panels::page>
    <div style="margin-bottom: 1rem;">
        <p style="font-size: 1.125rem; font-weight: 600; color: #374151;">
            {{ $this->pendingCount }} report(s) pending review
        </p>
    </div>

    @if($this->pendingReports->isEmpty())
        <div style="display: flex; align-items: center; justify-content: center; padding: 3rem; background: white; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="text-align: center;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 3rem; height: 3rem; margin: 0 auto; color: #22c55e;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <h3 style="margin-top: 0.5rem; font-size: 1.125rem; font-weight: 500; color: #111827;">All caught up!</h3>
                <p style="margin-top: 0.25rem; font-size: 0.875rem; color: #6b7280;">No reports pending review.</p>
            </div>
        </div>
    @else
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            @foreach($this->pendingReports as $report)
                <div style="background: white; border-radius: 0.75rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 1.5rem;" wire:key="report-{{ $report->id }}">
                    <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1rem;">
                        <div>
                            <h3 style="font-size: 1.125rem; font-weight: 600; color: #111827;">
                                Report #{{ $report->id }}
                            </h3>
                            <p style="font-size: 0.875rem; color: #6b7280;">
                                {{ $report->created_at?->diffForHumans() ?? 'Unknown date' }}
                            </p>
                        </div>
                        <span style="display: inline-flex; align-items: center; border-radius: 9999px; background: #fef3c7; padding: 0.125rem 0.625rem; font-size: 0.75rem; font-weight: 500; color: #92400e;">
                            Pending
                        </span>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 1rem;">
                        <div>
                            <p style="font-size: 0.875rem; font-weight: 500; color: #6b7280;">Reporter</p>
                            <a href="{{ self::getUserViewUrl($report->reporter_id) }}" style="font-size: 0.875rem; color: #4f46e5; text-decoration: none;" target="_blank">
                                {{ $report->reporter?->name ?? 'Unknown' }}
                            </a>
                        </div>
                        <div>
                            <p style="font-size: 0.875rem; font-weight: 500; color: #6b7280;">Reported User</p>
                            <a href="{{ self::getUserViewUrl($report->reported_user_id) }}" style="font-size: 0.875rem; color: #4f46e5; text-decoration: none;" target="_blank">
                                {{ $report->reportedUser?->name ?? 'Unknown' }}
                                @if($report->reportedUser)
                                    <span style="margin-left: 0.25rem; font-size: 0.75rem; color: #9ca3af;">({{ $report->reportedUser->status?->value ?? 'unknown' }})</span>
                                @endif
                            </a>
                        </div>
                    </div>

                    @if($report->hangout_request_id)
                        <div style="margin-bottom: 1rem;">
                            <p style="font-size: 0.875rem; font-weight: 500; color: #6b7280;">Related Hangout</p>
                            <p style="font-size: 0.875rem; color: #111827;">Hangout #{{ $report->hangout_request_id }}</p>
                        </div>
                    @endif

                    <div style="margin-bottom: 1rem;">
                        <p style="font-size: 0.875rem; font-weight: 500; color: #6b7280;">Reason</p>
                        <p style="font-size: 0.875rem; color: #111827; margin-top: 0.25rem; white-space: pre-wrap;">{{ $report->reason }}</p>
                    </div>

                    <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; padding-top: 1rem; border-top: 1px solid #e5e7eb;">
                        <button
                            wire:click="dismissReport({{ $report->id }})"
                            wire:loading.attr="disabled"
                            wire:confirm="Are you sure you want to dismiss this report?"
                            style="display: inline-flex; align-items: center; gap: 0.25rem; border-radius: 0.5rem; background: #f3f4f6; padding: 0.5rem 0.75rem; font-size: 0.875rem; font-weight: 600; color: #374151; border: none; cursor: pointer;"
                            onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='#f3f4f6'"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width: 1rem; height: 1rem;">
                                <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                            </svg>
                            Dismiss
                        </button>
                        <button
                            wire:click="suspendUser({{ $report->id }})"
                            wire:loading.attr="disabled"
                            wire:confirm="Are you sure you want to suspend this user?"
                            style="display: inline-flex; align-items: center; gap: 0.25rem; border-radius: 0.5rem; background: #fef3c7; padding: 0.5rem 0.75rem; font-size: 0.875rem; font-weight: 600; color: #92400e; border: none; cursor: pointer;"
                            onmouseover="this.style.background='#fde68a'" onmouseout="this.style.background='#fef3c7'"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width: 1rem; height: 1rem;">
                                <path fill-rule="evenodd" d="M2.25 10a7.75 7.75 0 1 1 15.5 0 7.75 7.75 0 0 1-15.5 0ZM7 8.25a.75.75 0 0 1 .75.75v2a.75.75 0 0 1-1.5 0V9A.75.75 0 0 1 7 8.25Zm6 0a.75.75 0 0 1 .75.75v2a.75.75 0 0 1-1.5 0V9a.75.75 0 0 1 .75-.75Z" clip-rule="evenodd" />
                            </svg>
                            Suspend User
                        </button>
                        <button
                            wire:click="banUser({{ $report->id }})"
                            wire:loading.attr="disabled"
                            wire:confirm="Are you sure you want to ban this user? This will also revoke their access tokens."
                            style="display: inline-flex; align-items: center; gap: 0.25rem; border-radius: 0.5rem; background: #fee2e2; padding: 0.5rem 0.75rem; font-size: 0.875rem; font-weight: 600; color: #991b1b; border: none; cursor: pointer;"
                            onmouseover="this.style.background='#fecaca'" onmouseout="this.style.background='#fee2e2'"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width: 1rem; height: 1rem;">
                                <path fill-rule="evenodd" d="M5.965 4.904a9.461 9.461 0 0 1 9.131 9.131l-9.131-9.131ZM4.904 5.965a9.461 9.461 0 0 0 9.131 9.131L4.904 5.965ZM10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Z" clip-rule="evenodd" />
                            </svg>
                            Ban User
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-filament-panels::page>
