<?php

/**
 * TEST ROUTES — Remove before production!
 *
 * Usage (open in browser):
 *   /test/notify/{userId}/join-request-received
 *   /test/notify/{userId}/join-request-approved
 *   /test/notify/{userId}/join-request-declined
 *   /test/notify/{userId}/join-request-confirmed
 *   /test/notify/{userId}/new-message
 *   /test/notify/{userId}/system-announcement
 *   /test/notify/{userId}/all          ← sends all types at once
 *
 * Examples:
 *   /test/notify/11/join-request-received
 *   /test/notify/16/all
 */

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Route;

Route::prefix('test/notify/{userId}')->group(function () {

    Route::get('join-request-received', function (int $userId) {
        $user = User::findOrFail($userId);
        $notification = app(NotificationService::class)->send(
            user: $user,
            type: 'join_request_received',
            title: 'New Join Request',
            body: 'Anna wants to join your Beach Volleyball hangout!',
            data: [
                'hangout_request_id' => 1,
                'join_request_id' => 1,
                'sender_id' => 99,
            ],
        );
        return response()->json(['ok' => true, 'notification' => $notification]);
    });

    Route::get('join-request-approved', function (int $userId) {
        $user = User::findOrFail($userId);
        $notification = app(NotificationService::class)->send(
            user: $user,
            type: 'join_request_approved',
            title: 'Request Approved',
            body: 'Max approved your join request for Yoga session!',
            data: [
                'hangout_request_id' => 1,
                'join_request_id' => 1,
            ],
        );
        return response()->json(['ok' => true, 'notification' => $notification]);
    });

    Route::get('join-request-declined', function (int $userId) {
        $user = User::findOrFail($userId);
        $notification = app(NotificationService::class)->send(
            user: $user,
            type: 'join_request_declined',
            title: 'Request Declined',
            body: 'Max declined your join request for Coffee meetup.',
            data: [
                'hangout_request_id' => 1,
                'join_request_id' => 1,
            ],
        );
        return response()->json(['ok' => true, 'notification' => $notification]);
    });

    Route::get('join-request-confirmed', function (int $userId) {
        $user = User::findOrFail($userId);
        $notification = app(NotificationService::class)->send(
            user: $user,
            type: 'join_request_confirmed',
            title: 'Meetup Confirmed!',
            body: 'Max confirmed the Dinner meetup — you can now chat!',
            data: [
                'hangout_request_id' => 1,
                'join_request_id' => 1,
            ],
        );
        return response()->json(['ok' => true, 'notification' => $notification]);
    });

    Route::get('new-message', function (int $userId) {
        $user = User::findOrFail($userId);
        $notification = app(NotificationService::class)->send(
            user: $user,
            type: 'new_message',
            title: 'Anna',
            body: 'Hey, see you at 5pm! Looking forward to it 😊',
            data: [
                'conversation_id' => 1,
                'message_id' => 1,
                'sender_id' => 99,
            ],
        );
        return response()->json(['ok' => true, 'notification' => $notification]);
    });

    Route::get('system-announcement', function (int $userId) {
        $user = User::findOrFail($userId);
        $notification = app(NotificationService::class)->send(
            user: $user,
            type: 'system_announcement',
            title: 'New Feature Available!',
            body: 'Group hangouts are now available. Invite up to 5 friends!',
            data: [],
        );
        return response()->json(['ok' => true, 'notification' => $notification]);
    });

    Route::get('all', function (int $userId) {
        $user = User::findOrFail($userId);
        $service = app(NotificationService::class);
        $notifications = [];

        $notifications[] = $service->send(
            user: $user,
            type: 'join_request_received',
            title: 'New Join Request',
            body: 'Anna wants to join your Beach Volleyball hangout!',
            data: ['hangout_request_id' => 1, 'join_request_id' => 1, 'sender_id' => 99],
        );

        $notifications[] = $service->send(
            user: $user,
            type: 'join_request_approved',
            title: 'Request Approved',
            body: 'Max approved your join request for Yoga session!',
            data: ['hangout_request_id' => 2, 'join_request_id' => 2],
        );

        $notifications[] = $service->send(
            user: $user,
            type: 'join_request_confirmed',
            title: 'Meetup Confirmed!',
            body: 'Max confirmed the Dinner meetup — you can now chat!',
            data: ['hangout_request_id' => 3, 'join_request_id' => 3],
        );

        $notifications[] = $service->send(
            user: $user,
            type: 'new_message',
            title: 'Anna',
            body: 'Hey, see you at 5pm! Looking forward to it 😊',
            data: ['conversation_id' => 1, 'message_id' => 1, 'sender_id' => 99],
        );

        $notifications[] = $service->send(
            user: $user,
            type: 'system_announcement',
            title: 'New Feature Available!',
            body: 'Group hangouts are now available. Invite up to 5 friends!',
            data: [],
        );

        return response()->json([
            'ok' => true,
            'count' => count($notifications),
            'notifications' => $notifications,
        ]);
    });
});
