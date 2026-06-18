<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif; padding: 20px;">
    <h2 style="color: #e53e3e;">Unhandled Server Error</h2>

    <p>An unhandled exception occurred in {{ config('app.name') }} ({{ app()->environment() }}).</p>

    <table style="border-collapse: collapse; margin: 16px 0;">
        <tr><td style="padding: 4px 12px 4px 0; font-weight: bold; vertical-align: top;">Type:</td><td><code>{{ $exceptionClass }}</code></td></tr>
        <tr><td style="padding: 4px 12px 4px 0; font-weight: bold; vertical-align: top;">Message:</td><td>{{ $message }}</td></tr>
        <tr><td style="padding: 4px 12px 4px 0; font-weight: bold; vertical-align: top;">Where:</td><td><code>{{ $location }}</code></td></tr>
        @if ($method && $url)
        <tr><td style="padding: 4px 12px 4px 0; font-weight: bold; vertical-align: top;">Request:</td><td><code>{{ $method }} {{ $url }}</code></td></tr>
        @endif
        <tr><td style="padding: 4px 12px 4px 0; font-weight: bold; vertical-align: top;">Time:</td><td>{{ now()->format('Y-m-d H:i:s') }}</td></tr>
    </table>

    @if ($trace)
    <p style="font-weight: bold;">Stack trace (top frames):</p>
    <pre style="background: #f7fafc; padding: 12px; border-radius: 6px; font-size: 12px; overflow-x: auto;">{{ $trace }}</pre>
    @endif

    <p style="color: #718096; font-size: 12px;">
        Automated alert. You will not be re-alerted for this same error for 30 minutes.
        Full details are in <code>storage/logs/laravel.log</code>.
    </p>
</body>
</html>
