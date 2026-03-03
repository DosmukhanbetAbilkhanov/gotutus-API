<?php

use App\Http\Controllers\Api\V1\ActivityTypeController;
use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\PasswordResetController;
use App\Http\Controllers\Api\V1\Auth\PhoneVerificationController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\BlockedUserController;
use App\Http\Controllers\Api\V1\CityController;
use App\Http\Controllers\Api\V1\ConversationController;
use App\Http\Controllers\Api\V1\DeviceTokenController;
use App\Http\Controllers\Api\V1\HangoutRequestController;
use App\Http\Controllers\Api\V1\JoinRequestController;
use App\Http\Controllers\Api\V1\MessageController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\PlaceController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\UserPhotoController;
use App\Http\Controllers\Api\V1\Admin\AdminPhotoController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Broadcasting Auth (Sanctum)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    Broadcast::routes();
});

/*
|--------------------------------------------------------------------------
| Public Routes (No Authentication)
|--------------------------------------------------------------------------
*/

Route::get('cities', [CityController::class, 'index']);
Route::get('activity-types', [ActivityTypeController::class, 'index']);
Route::get('hangout-requests', [HangoutRequestController::class, 'index']);
Route::get('hangout-requests/{hangoutRequest}', [HangoutRequestController::class, 'show']);

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::prefix('auth')->group(function () {
    Route::prefix('register')->group(function () {
        Route::post('send-code', [RegisterController::class, 'sendCode'])
            ->middleware('throttle:5,1');
        Route::post('verify-code', [RegisterController::class, 'verifyCode'])
            ->middleware('throttle:10,1');
        Route::post('complete', [RegisterController::class, 'complete'])
            ->middleware('throttle:5,1');
    });

    Route::prefix('password-reset')->middleware('throttle:5,1')->group(function () {
        Route::post('send-code', [PasswordResetController::class, 'sendCode']);
        Route::post('verify-code', [PasswordResetController::class, 'verifyCode']);
        Route::post('reset', [PasswordResetController::class, 'reset']);
    });

    Route::post('login', LoginController::class)
        ->middleware('throttle:5,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', LogoutController::class);

        Route::prefix('phone')->group(function () {
            Route::post('send-code', [PhoneVerificationController::class, 'sendCode'])
                ->middleware('throttle:3,1');
            Route::post('verify', [PhoneVerificationController::class, 'verify']);
        });
    });
});

/*
|--------------------------------------------------------------------------
| Protected Routes (Authenticated + Phone Verified)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'phone.verified'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | User Profile Routes
    |--------------------------------------------------------------------------
    */

    Route::get('users/{user}', [UserController::class, 'profile']);

    Route::prefix('user')->group(function () {
        Route::get('/', [UserController::class, 'show']);
        Route::put('/', [UserController::class, 'update']);

        // User photos
        Route::get('photos', [UserPhotoController::class, 'index']);
        Route::post('photos', [UserPhotoController::class, 'store']);
        Route::delete('photos/{photo}', [UserPhotoController::class, 'destroy']);

        // User's own hangouts and join requests
        Route::get('hangout-requests', [HangoutRequestController::class, 'myRequests']);
        Route::get('join-requests', [JoinRequestController::class, 'myJoinRequests']);
    });

    /*
    |--------------------------------------------------------------------------
    | Places Routes
    |--------------------------------------------------------------------------
    */

    Route::get('places', [PlaceController::class, 'index']);

    /*
    |--------------------------------------------------------------------------
    | Hangout Requests Routes
    |--------------------------------------------------------------------------
    */

    Route::apiResource('hangout-requests', HangoutRequestController::class)
        ->except(['index', 'show']);

    Route::post('hangout-requests/{hangoutRequest}/close', [HangoutRequestController::class, 'close']);
    Route::post('hangout-requests/{hangoutRequest}/complete', [HangoutRequestController::class, 'complete']);

    // Nested join request creation and listing
    Route::post('hangout-requests/{hangoutRequest}/join', [JoinRequestController::class, 'store']);
    Route::get('hangout-requests/{hangoutRequest}/join-requests', [JoinRequestController::class, 'index']);

    /*
    |--------------------------------------------------------------------------
    | Join Requests Routes
    |--------------------------------------------------------------------------
    */

    Route::prefix('join-requests/{joinRequest}')->group(function () {
        Route::post('approve', [JoinRequestController::class, 'approve']);
        Route::post('decline', [JoinRequestController::class, 'decline']);
        Route::post('confirm', [JoinRequestController::class, 'confirm']);
        Route::delete('/', [JoinRequestController::class, 'cancel']);
    });

    /*
    |--------------------------------------------------------------------------
    | Conversation & Message Routes
    |--------------------------------------------------------------------------
    */

    Route::get('conversations', [ConversationController::class, 'index']);
    Route::get('conversations/unread-count', [ConversationController::class, 'unreadCount']);
    Route::get('conversations/{conversation}', [ConversationController::class, 'show']);
    Route::get('conversations/{conversation}/messages', [MessageController::class, 'index']);
    Route::post('conversations/{conversation}/messages', [MessageController::class, 'store']);
    Route::delete('conversations/{conversation}/messages/{message}', [MessageController::class, 'destroy']);
    Route::post('conversations/{conversation}/read', [ConversationController::class, 'markAsRead']);
    Route::post('conversations/{conversation}/typing', [ConversationController::class, 'typing']);

    /*
    |--------------------------------------------------------------------------
    | Safety Routes (Blocking & Reporting)
    |--------------------------------------------------------------------------
    */

    Route::get('blocked-users', [BlockedUserController::class, 'index']);
    Route::post('blocked-users', [BlockedUserController::class, 'store']);
    Route::delete('blocked-users/{blockedUser}', [BlockedUserController::class, 'destroy']);

    Route::post('reports', [ReportController::class, 'store']);

    /*
    |--------------------------------------------------------------------------
    | Notification Routes
    |--------------------------------------------------------------------------
    */

    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
        Route::post('/{notification}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
    });

    /*
    |--------------------------------------------------------------------------
    | Device Token Routes
    |--------------------------------------------------------------------------
    */

    Route::post('device-tokens', [DeviceTokenController::class, 'store']);
    Route::delete('device-tokens', [DeviceTokenController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| Admin Routes (TODO: add admin middleware)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum'])->prefix('admin')->group(function () {
    Route::put('photos/{photo}/review', [AdminPhotoController::class, 'review']);
});
