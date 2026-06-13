<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif; padding: 20px;">
    <h2 style="color: #e53e3e;">System Health Alert</h2>

    <p>The automated health check detected one or more problems that affect
       real-time updates and/or push notifications:</p>

    <ul>
        @foreach ($problems as $problem)
            <li style="margin-bottom: 6px;">{{ $problem }}</li>
        @endforeach
    </ul>

    <table style="border-collapse: collapse; margin: 16px 0;">
        <tr>
            <td style="padding: 4px 12px 4px 0; font-weight: bold;">Time:</td>
            <td>{{ now()->format('Y-m-d H:i:s') }}</td>
        </tr>
    </table>

    <p><strong>What to check:</strong></p>
    <ul>
        <li><strong>Reverb</strong>: <code>supervisorctl status reverb</code> — restart if down.</li>
        <li><strong>Queue</strong>: <code>php artisan horizon:status</code> — restart with <code>horizon:terminate</code>.</li>
        <li><strong>FCM</strong>: verify the credentials file and check the log for "FCM push failed".</li>
    </ul>

    <p style="color: #718096; font-size: 12px;">
        This is an automated alert from {{ config('app.name') }}. You will not be
        re-alerted for 30 minutes while the issue persists.
    </p>
</body>
</html>
