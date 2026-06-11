<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif; padding: 20px;">
    <h2 style="color: #2b6cb0;">New Photo Verification</h2>

    <p>A user has submitted a posed selfie for photo verification and is waiting for review.</p>

    <table style="border-collapse: collapse; margin: 16px 0;">
        <tr>
            <td style="padding: 4px 12px 4px 0; font-weight: bold;">User:</td>
            <td>{{ $userName }}</td>
        </tr>
        <tr>
            <td style="padding: 4px 12px 4px 0; font-weight: bold;">City:</td>
            <td>{{ $cityName }}</td>
        </tr>
        <tr>
            <td style="padding: 4px 12px 4px 0; font-weight: bold;">Requested pose:</td>
            <td>{{ $poseLabel }}</td>
        </tr>
        <tr>
            <td style="padding: 4px 12px 4px 0; font-weight: bold;">Submitted:</td>
            <td>{{ now()->format('Y-m-d H:i:s') }}</td>
        </tr>
    </table>

    <p>
        <a href="{{ $reviewUrl }}"
           style="display: inline-block; background: #2b6cb0; color: #ffffff; padding: 10px 18px; border-radius: 6px; text-decoration: none;">
            Review verification
        </a>
    </p>

    <p style="color: #718096; font-size: 12px;">
        Compare the selfie against the user's approved profile photos and confirm the requested pose.
        This is an automated message from {{ config('app.name') }}.
    </p>
</body>
</html>
