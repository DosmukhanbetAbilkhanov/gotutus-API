# Companion API Implementation Plan

## Overview

Implement a RESTful API for the Companion mobile app (Flutter) using Laravel 12 with Sanctum authentication. The API enables users to find companions for casual hangouts in their city.

---

## Phase 1: Foundation

### 1.1 Install Sanctum & Configure Authentication [DONE]
```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

### 1.2 Create Enums [DONE]
- [x] `app/Enums/UserStatus.php` - active, suspended, banned
- [x] `app/Enums/HangoutRequestStatus.php` - open, matched, completed, cancelled
- [x] `app/Enums/JoinRequestStatus.php` - pending, approved, confirmed, declined, cancelled

### 1.3 Create Middleware [DONE]
- [x] `app/Http/Middleware/SetLocale.php` - Set locale from Accept-Language header (kz, ru, en)
- [x] `app/Http/Middleware/EnsurePhoneIsVerified.php` - Block unverified phone access

### 1.4 Configure bootstrap/app.php [DONE]
- [x] Add API routing with `api/v1` prefix
- [x] Register middleware aliases
- [x] Configure JSON exception rendering

### 1.5 Create HasTranslations Trait [DONE]
- [x] `app/Traits/HasTranslations.php` - Locale-aware attribute access for translated models

### 1.6 Create Base Models [DONE]
| Model | Key Relationships | Status |
|-------|-------------------|--------|
| City | translations, users, places | Done |
| CityTranslation | city | Done |
| ActivityType | translations, places, hangoutRequests | Done |
| ActivityTypeTranslation | activityType | Done |
| User (update) | city, photos, hangoutRequests, joinRequests, blockedUsers | Done |

### 1.7 SMS Service (Mobizon) [DONE]
- [x] `app/Services/MobizonSmsService.php` - Send SMS via Mobizon API
- [x] Add to `.env.example`: `MOBIZON_API_KEY`, `MOBIZON_API_URL`
- [x] Add to `config/services.php`: mobizon configuration

### 1.8 Auth Controllers [DONE]
- [x] `Auth/RegisterController.php` - POST /auth/register
- [x] `Auth/LoginController.php` - POST /auth/login
- [x] `Auth/LogoutController.php` - POST /auth/logout
- [x] `Auth/PhoneVerificationController.php` - POST /auth/phone/send-code, /auth/phone/verify

### 1.9 Auth Form Requests [DONE]
- [x] `RegisterRequest.php` - name, phone, password, city_id
- [x] `LoginRequest.php` - phone, password, device_name
- [x] `VerifyPhoneRequest.php` - code

### 1.10 Base Resources [DONE]
- [x] `UserResource.php`, `CityResource.php`, `ActivityTypeResource.php`, `UserPhotoResource.php`

### 1.11 Reference Data Controllers [DONE]
- [x] `CityController.php` - GET /cities (public)
- [x] `ActivityTypeController.php` - GET /activity-types (public)

### 1.12 API Routes [DONE]
- [x] `routes/api.php` - Phase 1 routes configured

---

## Phase 1 Status: COMPLETED

---

## Phase 2: Core Features

### 2.1 Create Models [DONE]
| Model | Key Relationships | Status |
|-------|-------------------|--------|
| Place | city, translations, activityTypes | Done |
| PlaceTranslation | place | Done |
| UserPhoto | user | Done |
| HangoutRequest | user, city, activityType, place, joinRequests, conversation | Done |
| JoinRequest | hangoutRequest, user, place | Done |
| Conversation | hangoutRequest, messages | Done |

### 2.2 Create Policies [DONE]
- [x] `HangoutRequestPolicy.php` - update/delete own, view in same city, join permissions
- [x] `JoinRequestPolicy.php` - approve/decline as owner, confirm as requester

### 2.3 Form Requests [DONE]
- [x] `StoreHangoutRequest.php` - activity_type_id, date, time?, place_id?, notes?
- [x] `UpdateHangoutRequest.php` - date?, time?, place_id?, notes?
- [x] `StoreJoinRequestRequest.php` - place_id?, message?
- [x] `UpdateProfileRequest.php` - name?, city_id?
- [x] `StoreUserPhotoRequest.php` - photo (image)

### 2.4 Resources [DONE]
- [x] `PlaceResource.php`, `UserPhotoResource.php`
- [x] `HangoutRequestResource.php`, `JoinRequestResource.php`

### 2.5 User Controllers [DONE]
- [x] `UserController.php` - GET/PUT /user
- [x] `UserPhotoController.php` - GET/POST/DELETE /user/photos

### 2.6 Place Controller [DONE]
- [x] `PlaceController.php` - GET /places?city_id=&activity_type_id=

### 2.7 Hangout Request Controller [DONE]
- [x] `HangoutRequestController.php`
  - GET /hangout-requests (browse by city, filter by activity/date)
  - POST /hangout-requests
  - GET /hangout-requests/{id}
  - PUT /hangout-requests/{id}
  - DELETE /hangout-requests/{id}
  - GET /user/hangout-requests (my requests)

### 2.8 Join Request Controller [DONE]
- [x] `JoinRequestController.php`
  - POST /hangout-requests/{id}/join
  - GET /hangout-requests/{id}/join-requests
  - POST /join-requests/{id}/approve
  - POST /join-requests/{id}/decline
  - POST /join-requests/{id}/confirm
  - DELETE /join-requests/{id}
  - GET /user/join-requests

---

## Phase 2 Status: COMPLETED

---

## Phase 3: Communication & Safety

### 3.1 Create Models [DONE]
| Model | Key Relationships | Status |
|-------|-------------------|--------|
| Message | conversation, user | Done |
| Report | reporter, reportedUser, hangoutRequest | Done |
| BlockedUser | user, blockedUser | Done |

### 3.2 Policies [DONE]
- [x] `ConversationPolicy.php` - view only if participant
- [x] `MessagePolicy.php` - send only if participant

### 3.3 Form Requests [DONE]
- [x] `StoreMessageRequest.php` - message (required, max 2000)
- [x] `StoreReportRequest.php` - reported_user_id, reason, hangout_request_id?
- [x] `StoreBlockedUserRequest.php` - blocked_user_id

### 3.4 Resources [DONE]
- [x] `ConversationResource.php`, `MessageResource.php`, `BlockedUserResource.php`

### 3.5 Conversation Controller [DONE]
- [x] `ConversationController.php`
  - GET /conversations
  - GET /conversations/{id}

### 3.6 Message Controller [DONE]
- [x] `MessageController.php`
  - GET /conversations/{id}/messages
  - POST /conversations/{id}/messages

### 3.7 Safety Controllers [DONE]
- [x] `BlockedUserController.php` - GET/POST/DELETE /blocked-users
- [x] `ReportController.php` - POST /reports

### 3.8 Integrate Blocking [DONE]
- [x] `scopeExcludeBlockedUsers` in HangoutRequest model (already implemented in Phase 2)
- [x] Block check in `HangoutRequestPolicy::join()` (already implemented in Phase 2)

---

## Phase 3 Status: COMPLETED

---

## Phase 4: Testing & Polish

### 4.1 Create Factories
All models need factories for testing

### 4.2 Feature Tests (Pest)
- Auth flow tests
- Hangout CRUD tests
- Join request workflow tests
- Messaging tests
- Blocking behavior tests

### 4.3 Run Pint
```bash
vendor/bin/pint --dirty
```

---

## Route Summary

```
Public:
  GET    /cities
  GET    /activity-types

Auth:
  POST   /auth/register
  POST   /auth/login
  POST   /auth/logout
  POST   /auth/phone/send-code
  POST   /auth/phone/verify

Protected (auth + phone verified):
  GET    /user
  PUT    /user
  GET    /user/photos
  POST   /user/photos
  DELETE /user/photos/{id}
  GET    /user/hangout-requests
  GET    /user/join-requests

  GET    /places

  GET    /hangout-requests
  POST   /hangout-requests
  GET    /hangout-requests/{id}
  PUT    /hangout-requests/{id}
  DELETE /hangout-requests/{id}
  POST   /hangout-requests/{id}/join
  GET    /hangout-requests/{id}/join-requests

  POST   /join-requests/{id}/approve
  POST   /join-requests/{id}/decline
  POST   /join-requests/{id}/confirm
  DELETE /join-requests/{id}

  GET    /conversations
  GET    /conversations/{id}
  GET    /conversations/{id}/messages
  POST   /conversations/{id}/messages

  GET    /blocked-users
  POST   /blocked-users
  DELETE /blocked-users/{id}
  POST   /reports
```

---

## Files to Create/Modify

### Config
- `bootstrap/app.php` - API routing & middleware
- `config/sanctum.php` - Sanctum config
- `config/services.php` - Add Mobizon configuration

### Services (1 file)
- `app/Services/MobizonSmsService.php`

### Enums (3 files)
- `app/Enums/UserStatus.php`
- `app/Enums/HangoutRequestStatus.php`
- `app/Enums/JoinRequestStatus.php`

### Middleware (2 files)
- `app/Http/Middleware/SetLocale.php`
- `app/Http/Middleware/EnsurePhoneIsVerified.php`

### Traits (1 file)
- `app/Traits/HasTranslations.php`

### Models (14 files)
- Update: `User.php`
- Create: `City.php`, `CityTranslation.php`, `ActivityType.php`, `ActivityTypeTranslation.php`, `Place.php`, `PlaceTranslation.php`, `UserPhoto.php`, `HangoutRequest.php`, `JoinRequest.php`, `Conversation.php`, `Message.php`, `Report.php`, `BlockedUser.php`

### Controllers (14 files in `app/Http/Controllers/Api/V1/`)
- Auth: `RegisterController.php`, `LoginController.php`, `LogoutController.php`, `PhoneVerificationController.php`
- Resources: `UserController.php`, `UserPhotoController.php`, `CityController.php`, `ActivityTypeController.php`, `PlaceController.php`, `HangoutRequestController.php`, `JoinRequestController.php`, `ConversationController.php`, `MessageController.php`, `BlockedUserController.php`, `ReportController.php`

### Form Requests (11 files)
- Auth: `RegisterRequest.php`, `LoginRequest.php`, `VerifyPhoneRequest.php`
- User: `UpdateProfileRequest.php`, `StoreUserPhotoRequest.php`
- Hangout: `StoreHangoutRequest.php`, `UpdateHangoutRequest.php`
- Join: `StoreJoinRequestRequest.php`
- Message: `StoreMessageRequest.php`
- Safety: `StoreReportRequest.php`, `StoreBlockedUserRequest.php`

### Resources (9 files)
- `UserResource.php`, `UserPhotoResource.php`, `CityResource.php`, `ActivityTypeResource.php`, `PlaceResource.php`, `HangoutRequestResource.php`, `JoinRequestResource.php`, `ConversationResource.php`, `MessageResource.php`

### Policies (4 files)
- `HangoutRequestPolicy.php`, `JoinRequestPolicy.php`, `ConversationPolicy.php`, `MessagePolicy.php`

### Routes (1 file)
- `routes/api.php`

---

## Verification

1. **Run migrations**: `php artisan migrate:fresh`
2. **Run Pint**: `vendor/bin/pint --dirty`
3. **Run tests**: `php artisan test`
4. **Manual API testing** via Postman/Insomnia:
   - Register user → Verify phone → Login → Get token
   - Create hangout request
   - Browse hangout requests (different user, same city)
   - Send join request → Approve → Confirm
   - Verify conversation created, send messages
   - Test blocking prevents visibility
