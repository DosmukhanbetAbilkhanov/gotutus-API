<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif; padding: 20px;">
    <h2>Test Email</h2>
    <p>{{ $messageText }}</p>
    <p style="color: #718096; font-size: 12px;">Sent from {{ config('app.name') }} at {{ now()->format('Y-m-d H:i:s') }}</p>
</body>
</html>
