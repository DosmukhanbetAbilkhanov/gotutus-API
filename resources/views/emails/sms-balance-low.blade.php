<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif; padding: 20px;">
    <h2 style="color: #e53e3e;">SMS Service Alert</h2>

    <p>SMS sending failed due to <strong>insufficient balance</strong> on Mobizon.</p>

    <table style="border-collapse: collapse; margin: 16px 0;">
        <tr>
            <td style="padding: 4px 12px 4px 0; font-weight: bold;">Phone:</td>
            <td>{{ $phone }}</td>
        </tr>
        <tr>
            <td style="padding: 4px 12px 4px 0; font-weight: bold;">Error:</td>
            <td>{{ $errorMessage }}</td>
        </tr>
        <tr>
            <td style="padding: 4px 12px 4px 0; font-weight: bold;">Time:</td>
            <td>{{ now()->format('Y-m-d H:i:s') }}</td>
        </tr>
    </table>

    <p><strong>Action required:</strong> Top up the Mobizon balance immediately to prevent users from being unable to register or reset passwords.</p>

    <p style="color: #718096; font-size: 12px;">This is an automated alert from {{ config('app.name') }}.</p>
</body>
</html>
