<?php

declare(strict_types=1);

namespace App\Support;

use App\Mail\ExceptionAlertMail;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

/**
 * Emails the admin about unhandled, actionable server errors (5xx / uncaught
 * exceptions). Expected client errors (validation, auth, 404, rate limit) are
 * ignored, and alerts are throttled per error signature to avoid floods.
 */
class ExceptionAlerter
{
    /** Re-alert window per unique error signature. */
    private const THROTTLE_MINUTES = 30;

    public static function maybeAlert(Throwable $e): void
    {
        // Don't alert from local/test — only deployed environments.
        if (app()->environment('local', 'testing')) {
            return;
        }

        if (self::isIgnorable($e)) {
            return;
        }

        $email = config('services.health.alert_email');
        if (empty($email)) {
            return;
        }

        // Throttle: one alert per error signature per window.
        $signature = 'exception_alert:'.md5(get_class($e).'|'.$e->getMessage().'|'.$e->getFile().':'.$e->getLine());
        if (! Cache::add($signature, true, now()->addMinutes(self::THROTTLE_MINUTES))) {
            return;
        }

        try {
            $request = request();
            Mail::to($email)->send(new ExceptionAlertMail(
                exceptionClass: get_class($e),
                message: $e->getMessage(),
                location: $e->getFile().':'.$e->getLine(),
                url: $request?->fullUrl(),
                method: $request?->method(),
                trace: collect(explode("\n", $e->getTraceAsString()))->take(15)->implode("\n"),
            ));
        } catch (Throwable $mailError) {
            Log::error('Failed to send exception alert email', ['error' => $mailError->getMessage()]);
        }
    }

    /**
     * Expected/handled client errors that should not page an admin.
     */
    private static function isIgnorable(Throwable $e): bool
    {
        if ($e instanceof ValidationException
            || $e instanceof AuthenticationException
            || $e instanceof AuthorizationException
            || $e instanceof ModelNotFoundException) {
            return true;
        }

        // Any HTTP exception below 500 (404, 403, 405, 419, 422, 429, ...).
        if ($e instanceof HttpExceptionInterface && $e->getStatusCode() < 500) {
            return true;
        }

        return false;
    }
}
