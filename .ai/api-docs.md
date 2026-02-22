# Companion API Documentation

Base URL: `https://companion.test/api/v1`

## Authentication

All protected endpoints require a Bearer token in the Authorization header:
```
Authorization: Bearer {token}
```

## Headers

| Header | Required | Description |
|--------|----------|-------------|
| `Authorization` | For protected routes | Bearer token from login/register |
| `Accept-Language` | Optional | Locale: `kz`, `ru`, `en` (default: `ru`) |
| `Content-Type` | For POST/PUT | `application/json` |

---

## Public Endpoints

### Get Cities
```
GET /cities
```

Returns list of active cities.

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Алматы"
    }
  ]
}
```

---

### Get Activity Types
```
GET /activity-types
```

Returns list of active activity types.

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "slug": "beer",
      "name": "Пиво",
      "icon": "beer-icon.png",
      "bg_photo": "beer-bg.jpg"
    }
  ]
}
```

---

## Authentication Endpoints

### Register (3-Step Flow)

Registration is a 3-step process: send code → verify code → complete profile.

#### Step 1: Send Registration Code
```
POST /auth/register/send-code
```

**Request Body:**
```json
{
  "phone": "+77001234567"
}
```

**Response (200):**
```json
{
  "message": "Verification code sent"
}
```

**Rate Limit:** 5 requests per minute

**Errors:**
- `422` — Invalid phone format or phone already registered (verified)

---

#### Step 2: Verify Registration Code
```
POST /auth/register/verify-code
```

**Request Body:**
```json
{
  "phone": "+77001234567",
  "code": "123456"
}
```

**Response (200):**
```json
{
  "message": "Phone verified",
  "data": {
    "verification_token": "550e8400-e29b-41d4-a716-446655440000"
  }
}
```

**Rate Limit:** 10 requests per minute

**Errors:**
- `422` with `error_code: "INVALID_CODE"` — Wrong or expired code

**Note:** The `verification_token` is valid for 30 minutes and must be used in Step 3.

---

#### Step 3: Complete Registration
```
POST /auth/register/complete
```

**Request Body:**
```json
{
  "verification_token": "550e8400-e29b-41d4-a716-446655440000",
  "name": "John Doe",
  "age": 25,
  "gender": "male",
  "email": "john@example.com",
  "city_id": 1,
  "password": "password123",
  "password_confirmation": "password123"
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `verification_token` | uuid | Yes | Token from Step 2 |
| `name` | string | Yes | Display name (max 255) |
| `age` | integer | Yes | User age (18–100) |
| `gender` | string | Yes | `male`, `female`, or `other` |
| `email` | string | No | Email address (unique) |
| `city_id` | integer | Yes | City ID |
| `password` | string | Yes | Min 8 characters |
| `password_confirmation` | string | Yes | Must match password |

**Response (201):**
```json
{
  "message": "Registration successful",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "age": 25,
      "gender": "male",
      "phone": "+77001234567",
      "city": { "id": 1, "name": "Алматы" },
      "phone_verified": true,
      "created_at": "2024-01-15T10:30:00Z"
    },
    "token": "1|abc123..."
  }
}
```

**Rate Limit:** 5 requests per minute

**Errors:**
- `422` with `error_code: "INVALID_TOKEN"` — Token expired or invalid
- `409` with `error_code: "PHONE_TAKEN"` — Phone was verified by another user during registration

---

### Password Reset (3-Step Flow)

Password reset follows the same SMS+OTP pattern as registration.

**Rate Limit:** 5 requests per minute (shared across all 3 endpoints)

#### Step 1: Send Password Reset Code
```
POST /auth/password-reset/send-code
```

**Request Body:**
```json
{
  "phone": "+77001234567"
}
```

**Response (200):**
```json
{
  "message": "Verification code sent"
}
```

**Errors:**
- `422` — Invalid phone format or phone not registered

---

#### Step 2: Verify Password Reset Code
```
POST /auth/password-reset/verify-code
```

**Request Body:**
```json
{
  "phone": "+77001234567",
  "code": "123456"
}
```

**Response (200):**
```json
{
  "message": "Phone verified",
  "data": {
    "reset_token": "550e8400-e29b-41d4-a716-446655440000"
  }
}
```

**Errors:**
- `422` with `error_code: "INVALID_CODE"` — Wrong or expired code

**Note:** The `reset_token` is valid for 10 minutes and must be used in Step 3.

---

#### Step 3: Reset Password
```
POST /auth/password-reset/reset
```

**Request Body:**
```json
{
  "phone": "+77001234567",
  "reset_token": "550e8400-e29b-41d4-a716-446655440000",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `phone` | string | Yes | Phone number (`+7XXXXXXXXXX`) |
| `reset_token` | uuid | Yes | Token from Step 2 |
| `password` | string | Yes | New password (min 8 characters) |
| `password_confirmation` | string | Yes | Must match password |

**Response (200):**
```json
{
  "message": "Password reset successfully"
}
```

**Errors:**
- `422` with `error_code: "INVALID_TOKEN"` — Token expired or invalid
- `404` — User not found

---

### Login
```
POST /auth/login
```

**Request Body:**
```json
{
  "phone": "+77001234567",
  "password": "password123",
  "device_name": "iPhone 15 Pro"
}
```

**Response (200):**
```json
{
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "age": 25,
      "gender": "male",
      "phone": "+77001234567",
      "city": { "id": 1, "name": "Алматы" },
      "phone_verified": true,
      "created_at": "2024-01-15T10:30:00Z"
    },
    "token": "2|xyz789...",
    "phone_verified": true
  }
}
```

**Error (401):**
```json
{
  "message": "Invalid credentials",
  "error_code": "INVALID_CREDENTIALS"
}
```

---

### Logout
```
POST /auth/logout
```

**Headers:** `Authorization: Bearer {token}`

**Response (200):**
```json
{
  "message": "Logged out successfully"
}
```

---

### Send Phone Verification Code
```
POST /auth/phone/send-code
```

**Headers:** `Authorization: Bearer {token}`

**Response (200):**
```json
{
  "message": "Verification code sent"
}
```

**Rate Limit:** 3 requests per minute

---

### Verify Phone
```
POST /auth/phone/verify
```

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "code": "123456"
}
```

**Response (200):**
```json
{
  "message": "Phone verified successfully"
}
```

**Error (422):**
```json
{
  "message": "Invalid verification code",
  "error_code": "INVALID_CODE"
}
```

---

## Protected Endpoints

All endpoints below require:
- `Authorization: Bearer {token}` header
- Phone must be verified (returns 403 with `PHONE_NOT_VERIFIED` if not)

---

## User Profile

### Get Current User
```
GET /user
```

**Response:**
```json
{
  "data": {
    "id": 1,
    "name": "John Doe",
    "age": 25,
    "gender": "male",
    "phone": "+77001234567",
    "email": "john@example.com",
    "city": { "id": 1, "name": "Алматы" },
    "photos": [
      { "id": 1, "url": "/storage/user-photos/abc.jpg", "is_approved": true }
    ],
    "phone_verified": true,
    "created_at": "2024-01-15T10:30:00Z"
  }
}
```

---

### Get User Profile
```
GET /users/{id}
```

Returns public profile information for a specific user.

**Response:**
```json
{
  "data": {
    "id": 2,
    "name": "Jane Doe",
    "age": 28,
    "gender": "female",
    "city": { "id": 1, "name": "Алматы" },
    "photos": [
      { "id": 1, "url": "/storage/user-photos/abc.jpg", "is_approved": true }
    ],
    "phone_verified": true,
    "created_at": "2024-01-15T10:30:00Z"
  }
}
```

**Note:** Private fields (`phone`, `email`) are not included when viewing another user's profile.

**Errors:**
- `404` — User not found

---

### Update Profile
```
PUT /user
```

**Request Body:**
```json
{
  "name": "John Smith",
  "city_id": 2
}
```

**Response:**
```json
{
  "message": "Profile updated",
  "data": { ... }
}
```

---

### Get User Photos
```
GET /user/photos
```

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "url": "/storage/user-photos/abc.jpg",
      "is_approved": true
    }
  ]
}
```

---

### Upload Photo
```
POST /user/photos
Content-Type: multipart/form-data
```

**Request Body:**
- `photo` (file, required): Image file (max 5MB, min 200x200px)

**Response (201):**
```json
{
  "message": "Photo uploaded",
  "data": {
    "id": 2,
    "url": "/storage/user-photos/xyz.jpg",
    "is_approved": false
  }
}
```

---

### Delete Photo
```
DELETE /user/photos/{photo_id}
```

**Response:**
```json
{
  "message": "Photo deleted"
}
```

---

## Places

### Get Places
```
GET /places
```

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `city_id` | int | Filter by city (default: user's city) |
| `activity_type_id` | int | Filter by activity type |

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Пивной бар",
      "address": "ул. Абая 10",
      "city": { "id": 1, "name": "Алматы" },
      "activity_types": [
        { "id": 1, "slug": "beer", "name": "Пиво" }
      ]
    }
  ]
}
```

---

## Hangout Requests

### Browse Hangout Requests
```
GET /hangout-requests
```

Returns open hangout requests in the specified city. **Authentication is optional.** When authenticated, excludes own and blocked users' requests.

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `city_id` | int | **Yes** | City to browse hangout requests in |
| `activity_type_id` | int | No | Filter by activity type |
| `date` | string | No | Filter by date (YYYY-MM-DD) |
| `page` | int | No | Page number for pagination |

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "user": {
        "id": 2,
        "name": "Jane Doe",
        "age": 28,
        "gender": "female",
        "photos": [{ "id": 1, "url": "...", "is_approved": true }]
      },
      "city": { "id": 1, "name": "Алматы" },
      "activity_type": { "id": 1, "slug": "beer", "name": "Пиво" },
      "place": { "id": 1, "name": "Пивной бар", "address": "..." },
      "date": "2024-01-20",
      "time": "18:00",
      "status": "open",
      "notes": "Looking for someone to chat",
      "join_requests_count": 3,
      "is_owner": false,
      "created_at": "2024-01-15T10:30:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 72
  }
}
```

---

### Create Hangout Request
```
POST /hangout-requests
```

**Request Body:**
```json
{
  "activity_type_id": 1,
  "date": "2024-01-20",
  "time": "18:00",
  "place_id": 1,
  "notes": "Looking for someone to chat"
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `activity_type_id` | int | Yes | Activity type ID |
| `date` | string | Yes | Date (YYYY-MM-DD, must be today or future) |
| `time` | string | No | Time (HH:MM) |
| `place_id` | int | No | Suggested place ID |
| `notes` | string | No | Additional notes (max 500 chars) |

**Response (201):**
```json
{
  "message": "Hangout request created",
  "data": { ... }
}
```

---

### Get Hangout Request
```
GET /hangout-requests/{id}
```

**Authentication is optional.** When authenticated, includes `is_owner` and `my_join_request` fields.

**Response:**
```json
{
  "data": {
    "id": 1,
    "user": { ... },
    "city": { ... },
    "activity_type": { ... },
    "place": { ... },
    "date": "2024-01-20",
    "time": "18:00",
    "status": "open",
    "notes": "...",
    "is_owner": false,
    "my_join_request": {
      "id": 5,
      "status": "pending",
      "message": "I'd love to join!"
    },
    "created_at": "2024-01-15T10:30:00Z"
  }
}
```

---

### Update Hangout Request
```
PUT /hangout-requests/{id}
```

Only the owner can update (and only while status is "open").

**Request Body:**
```json
{
  "date": "2024-01-21",
  "time": "19:00",
  "place_id": 2,
  "notes": "Updated notes"
}
```

**Response:**
```json
{
  "message": "Hangout request updated",
  "data": { ... }
}
```

---

### Cancel Hangout Request
```
DELETE /hangout-requests/{id}
```

Only the owner can cancel.

**Response:**
```json
{
  "message": "Hangout request cancelled"
}
```

---

### Get My Hangout Requests
```
GET /user/hangout-requests
```

Returns current user's hangout requests.

**Response:** Same as browse, but includes all statuses.

---

## Join Requests

### Send Join Request
```
POST /hangout-requests/{hangout_id}/join
```

**Request Body:**
```json
{
  "message": "I'd love to join!",
  "place_id": 2
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `message` | string | No | Message to the creator (max 300 chars) |
| `place_id` | int | No | Suggest a different place |

**Response (201):**
```json
{
  "message": "Join request sent",
  "data": {
    "id": 5,
    "user": { ... },
    "status": "pending",
    "message": "I'd love to join!",
    "suggested_place": { ... },
    "created_at": "2024-01-15T11:00:00Z"
  }
}
```

**Error (403):** Cannot join own request, already requested, or blocked.

---

### Get Join Requests for Hangout
```
GET /hangout-requests/{hangout_id}/join-requests
```

Only the hangout owner can view.

**Response:**
```json
{
  "data": [
    {
      "id": 5,
      "user": {
        "id": 3,
        "name": "Bob",
        "photos": [...]
      },
      "status": "pending",
      "message": "I'd love to join!",
      "suggested_place": null,
      "created_at": "2024-01-15T11:00:00Z"
    }
  ]
}
```

---

### Approve Join Request
```
POST /join-requests/{join_request_id}/approve
```

Only the hangout owner can approve (while status is "pending").

**Response:**
```json
{
  "message": "Join request approved",
  "data": { ... }
}
```

---

### Decline Join Request
```
POST /join-requests/{join_request_id}/decline
```

Only the hangout owner can decline (while status is "pending").

**Response:**
```json
{
  "message": "Join request declined"
}
```

---

### Confirm Participation
```
POST /join-requests/{join_request_id}/confirm
```

Only the join requester can confirm (after being approved).

**Response:**
```json
{
  "message": "Participation confirmed",
  "data": { ... }
}
```

**Note:** After confirmation:
- Hangout status changes to "matched"
- A conversation is created for messaging

---

### Cancel Join Request
```
DELETE /join-requests/{join_request_id}
```

Only the join requester can cancel (while pending or approved).

**Response:**
```json
{
  "message": "Join request cancelled"
}
```

---

### Get My Join Requests
```
GET /user/join-requests
```

Returns current user's sent join requests.

**Response:**
```json
{
  "data": [
    {
      "id": 5,
      "hangout_request": {
        "id": 1,
        "user": { ... },
        "activity_type": { ... },
        "date": "2024-01-20",
        "time": "18:00"
      },
      "status": "approved",
      "message": "I'd love to join!",
      "confirmed_at": null,
      "created_at": "2024-01-15T11:00:00Z"
    }
  ],
  "meta": { ... }
}
```

---

## Status Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request |
| 401 | Unauthenticated (invalid/missing token) |
| 403 | Forbidden (phone not verified, unauthorized action) |
| 404 | Not Found |
| 422 | Validation Error |
| 429 | Too Many Requests (rate limited) |

---

## Error Response Format

```json
{
  "message": "Human readable error message",
  "error_code": "MACHINE_READABLE_CODE",
  "errors": {
    "field_name": ["Validation error message"]
  }
}
```

---

## Hangout Request Status Values

| Status | Description |
|--------|-------------|
| `open` | Accepting join requests |
| `matched` | Someone confirmed participation |
| `completed` | Hangout finished |
| `cancelled` | Cancelled by owner |

---

## Join Request Status Values

| Status | Description |
|--------|-------------|
| `pending` | Waiting for owner approval |
| `approved` | Approved, waiting for user confirmation |
| `confirmed` | User confirmed participation |
| `declined` | Declined by owner |
| `cancelled` | Cancelled by requester |

---

## Conversations

### Get Conversations
```
GET /conversations
```

Returns list of user's conversations (from matched hangouts).

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "hangout_request": {
        "id": 1,
        "activity_type": { "id": 1, "slug": "beer", "name": "Пиво" },
        "date": "2024-01-20",
        "time": "18:00"
      },
      "other_user": {
        "id": 2,
        "name": "Jane Doe",
        "photos": [...]
      },
      "latest_message": {
        "id": 10,
        "message": "See you there!",
        "is_mine": false,
        "created_at": "2024-01-15T12:00:00Z"
      },
      "unread_count": 3,
      "created_at": "2024-01-15T11:30:00Z",
      "updated_at": "2024-01-15T12:00:00Z"
    }
  ],
  "meta": { ... }
}
```

`other_user` correctly returns the **other** participant (hangout owner sees the joiner, joiner sees the owner).

---

### Get Conversation
```
GET /conversations/{id}
```

**Response:**
```json
{
  "data": {
    "id": 1,
    "hangout_request": { ... },
    "other_user": { ... },
    "latest_message": { ... },
    "unread_count": 3,
    "created_at": "2024-01-15T11:30:00Z",
    "updated_at": "2024-01-15T12:00:00Z"
  }
}
```

**Error (403):** Not a participant in this conversation.

---

### Get Unread Message Count (Total)
```
GET /conversations/unread-count
```

Returns the total number of unread messages across all conversations.

**Response:**
```json
{
  "unread_count": 5
}
```

---

### Mark Conversation as Read
```
POST /conversations/{id}/read
```

Marks all messages in a conversation as read for the current user.

**Response:**
```json
{
  "message": "ok"
}
```

**Error (403):** Not a participant in this conversation.

---

## Messages

### Get Messages
```
GET /conversations/{conversation_id}/messages
```

Returns messages in a conversation (paginated, newest first).

**Response:**
```json
{
  "data": [
    {
      "id": 10,
      "user": {
        "id": 2,
        "name": "Jane Doe",
        "photos": [...]
      },
      "message": "See you there!",
      "is_mine": false,
      "created_at": "2024-01-15T12:00:00Z"
    },
    {
      "id": 9,
      "user": {
        "id": 1,
        "name": "John Doe",
        "photos": [...]
      },
      "message": "Great, looking forward to it!",
      "is_mine": true,
      "created_at": "2024-01-15T11:55:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 2,
    "per_page": 50,
    "total": 75
  }
}
```

---

### Send Message
```
POST /conversations/{conversation_id}/messages
```

**Request Body:**
```json
{
  "message": "Hello, looking forward to meeting you!"
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `message` | string | Yes | Message text (max 2000 chars) |

**Response (201):**
```json
{
  "message": "Message sent",
  "data": {
    "id": 11,
    "user": { ... },
    "message": "Hello, looking forward to meeting you!",
    "is_mine": true,
    "created_at": "2024-01-15T12:05:00Z"
  }
}
```

**Error (403):** Not a participant in this conversation.

---

## Blocked Users

### Get Blocked Users
```
GET /blocked-users
```

Returns list of users blocked by current user.

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "blocked_user": {
        "id": 5,
        "name": "Blocked Person",
        "photos": [...]
      },
      "created_at": "2024-01-10T09:00:00Z"
    }
  ],
  "meta": { ... }
}
```

---

### Block User
```
POST /blocked-users
```

**Request Body:**
```json
{
  "blocked_user_id": 5
}
```

**Response (201):**
```json
{
  "message": "User blocked",
  "data": {
    "id": 1,
    "blocked_user": { ... },
    "created_at": "2024-01-15T12:00:00Z"
  }
}
```

**Error (422):**
```json
{
  "message": "Cannot block yourself",
  "error_code": "CANNOT_BLOCK_SELF"
}
```

```json
{
  "message": "User already blocked",
  "error_code": "ALREADY_BLOCKED"
}
```

---

### Unblock User
```
DELETE /blocked-users/{id}
```

**Response:**
```json
{
  "message": "User unblocked"
}
```

---

## Reports

### Submit Report
```
POST /reports
```

Report a user for inappropriate behavior.

**Request Body:**
```json
{
  "reported_user_id": 5,
  "reason": "Inappropriate behavior during our hangout",
  "hangout_request_id": 1
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `reported_user_id` | int | Yes | ID of user being reported |
| `reason` | string | Yes | Reason for report (max 1000 chars) |
| `hangout_request_id` | int | No | Related hangout request ID |

**Response (201):**
```json
{
  "message": "Report submitted"
}
```

**Error (422):**
```json
{
  "message": "Cannot report yourself",
  "error_code": "CANNOT_REPORT_SELF"
}
```

---

## Workflow Summary

1. **User A** creates a hangout request (status: `open`)
2. **User B** sends a join request (status: `pending`)
3. **User A** approves the join request (status: `approved`)
4. **User B** confirms participation (status: `confirmed`)
5. Hangout becomes `matched`, conversation is created
6. Users can now message each other
