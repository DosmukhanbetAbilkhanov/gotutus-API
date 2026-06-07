# Troubleshooting Guide - Tanys.app

## Issue: Activity Types Not Loading in Frontend

### Symptoms
- Cannot create hangout (no activity types shown)
- Activity type selector is empty
- API returns empty array for `/activity-types`

### Root Cause
The `ActivityTypeController` caches results for **1 hour**. After seeding new data, the old empty cache is still served.

### Solution

**Step 1: Clear cache on server**
```bash
ssh forge@tanys.app
cd /home/forge/tanys.app
php artisan cache:clear
php artisan config:clear
```

**Step 2: Verify via API**
```bash
curl https://tanys.app/api/v1/activity-types | jq
```

Expected output:
```json
{
  "data": [
    {
      "id": 1,
      "slug": "coffee",
      "name": "Coffee",
      "icon": "☕",
      "is_active": true
    },
    ...
  ]
}
```

**Step 3: Test in app**
- Open Flutter app
- Try creating a hangout
- Activity types should now appear

---

## Issue: Empty Cities List

### Solution
```bash
# Check database
php artisan tinker
>>> DB::table('cities')->count()

# If 0, run seeder
php artisan db:seed --class=MinimalDataSeeder

# Clear cache
php artisan cache:clear
```

---

## Issue: Cannot Login

### Possible Causes

**1. User doesn't exist**
```bash
php artisan tinker
>>> User::where('email', 'your@email.com')->first()
# or
>>> User::where('phone', '+77078835953')->first()
```

**2. Phone format mismatch**
Try both formats:
- `+77078835953`
- `87078835953`

**3. Wrong password**
Reset via Tinker:
```bash
php artisan tinker
>>> $user = User::where('email', 'dosmukhanbet@gmail.com')->first();
>>> $user->password = Hash::make('newpassword');
>>> $user->save();
```

---

## Issue: Places Not Showing

### Check Database
```bash
php artisan tinker
>>> DB::table('places')->count()
# Should be > 0

>>> Place::with('translations')->first()
# Should show place with translations
```

### Verify Translations
```bash
>>> DB::table('place_translations')->where('language_code', 'en')->count()
```

### Seed Places
```bash
php artisan db:seed --class=PlacesSeeder
php artisan cache:clear
```

---

## Issue: Interests Not Loading

### Check Database
```bash
php artisan tinker
>>> Interest::active()->count()
>>> DB::table('interest_translations')->count()
```

### Seed Interests
```bash
php artisan db:seed --class=MinimalDataSeeder
php artisan cache:clear
```

---

## Issue: Seeder Fails

### Common Errors

**1. "String data, right truncated" (Working hours)**
Fixed in latest `PlacesSeeder.php` - update the file.

**2. "Foreign key constraint fails"**
Run seeders in order:
```bash
php artisan db:seed --class=MinimalDataSeeder  # First
php artisan db:seed --class=AdminUserSeeder    # Second
php artisan db:seed --class=TestUsersSeeder    # Third
php artisan db:seed --class=PlacesSeeder       # Fourth
```

**3. "User type not found"**
Run `MinimalDataSeeder` first - it creates user types.

---

## Issue: WebSocket Not Connecting

### Check Reverb Status
```bash
sudo supervisorctl status reverb
# Should show: RUNNING

# Check logs
tail -50 /home/forge/tanys.app/storage/logs/reverb.log
```

### Restart Reverb
```bash
sudo supervisorctl restart reverb
```

### Verify Port Open
```bash
sudo ufw status | grep 8080
# Should show: 8080/tcp ALLOW
```

---

## Issue: Queue Jobs Not Processing

### Check Queue Worker
```bash
sudo supervisorctl status queue-worker
# Should show: RUNNING

# Check logs
tail -50 /home/forge/tanys.app/storage/logs/queue-worker.log
```

### Process Queue Manually
```bash
php artisan queue:work --once
```

### Restart Queue Workers
```bash
sudo supervisorctl restart queue-worker:*
```

---

## Issue: Push Notifications Not Sending

### Check Firebase Credentials
```bash
ls -la /home/forge/tanys.app/storage/app/firebase-credentials.json
# Should exist and have correct permissions

# Check .env
cat .env | grep FIREBASE
# Should show path to credentials file
```

### Test FCM
```bash
php artisan tinker
>>> $user = User::first();
>>> $user->notify(new \App\Notifications\TestNotification());
# Check for errors
```

---

## Diagnostic Commands

### Database Status
```bash
php artisan tinker
>>> echo "Users: " . User::count() . "\n";
>>> echo "Cities: " . DB::table('cities')->count() . "\n";
>>> echo "Activity Types: " . ActivityType::active()->count() . "\n";
>>> echo "Interests: " . Interest::active()->count() . "\n";
>>> echo "Places: " . Place::count() . "\n";
>>> echo "Hangouts: " . HangoutRequest::count() . "\n";
```

### Cache Status
```bash
# List cache keys (if using Redis)
redis-cli KEYS "*"

# Clear specific cache
php artisan cache:forget 'activity_types:active'
```

### View All Seeders
```bash
ls -la database/seeders/*.php
```

### Check API Endpoints
```bash
# Activity types
curl https://tanys.app/api/v1/activity-types | jq '.data | length'

# Cities
curl https://tanys.app/api/v1/cities | jq '.data | length'

# Interests
curl https://tanys.app/api/v1/interests | jq '.data | length'
```

---

## Fresh Start (Nuclear Option)

**⚠️ WARNING: This deletes ALL data!**

```bash
# Backup database first!
php artisan db:backup  # If configured

# Fresh migration + seed
php artisan migrate:fresh --seed --seeder=ServerSeeder

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Restart services
sudo supervisorctl restart all
```

---

## Quick Health Check Script

Save as `health-check.sh`:
```bash
#!/bin/bash

echo "=== Tanys.app Health Check ==="
echo ""

echo "📊 Database:"
php artisan tinker --execute="
echo 'Users: ' . \App\Models\User::count() . PHP_EOL;
echo 'Cities: ' . \DB::table('cities')->count() . PHP_EOL;
echo 'Activity Types: ' . \App\Models\ActivityType::active()->count() . PHP_EOL;
echo 'Places: ' . \App\Models\Place::count() . PHP_EOL;
"

echo ""
echo "🔧 Services:"
sudo supervisorctl status | grep -E "(reverb|queue)"

echo ""
echo "🌐 API Endpoints:"
echo -n "Activity types: "
curl -s https://tanys.app/api/v1/activity-types | jq -r '.data | length'
echo -n "Cities: "
curl -s https://tanys.app/api/v1/cities | jq -r '.data | length'

echo ""
echo "✅ Health check complete!"
```

Run:
```bash
chmod +x health-check.sh
./health-check.sh
```

---

## Common Cache Clear Commands

```bash
# Clear application cache
php artisan cache:clear

# Clear config cache
php artisan config:clear

# Clear route cache
php artisan route:clear

# Clear view cache
php artisan view:clear

# Clear all Laravel caches
php artisan optimize:clear

# Rebuild caches (production)
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Support Checklist

When reporting issues, provide:

1. **Error message** (exact text)
2. **Laravel logs:** `tail -50 storage/logs/laravel.log`
3. **Server logs:** `tail -50 storage/logs/reverb.log`
4. **Database counts:** Run diagnostic commands above
5. **API response:** `curl https://tanys.app/api/v1/activity-types`
6. **Steps to reproduce**
7. **Expected vs actual behavior**

---

## Quick Reference

| Issue | Quick Fix |
|-------|-----------|
| Empty activity types | `php artisan cache:clear` |
| Can't login | Check phone format: `+7...` or `8...` |
| Seeder fails | Run `MinimalDataSeeder` first |
| WebSocket down | `sudo supervisorctl restart reverb` |
| Queue stuck | `sudo supervisorctl restart queue-worker:*` |
| After any seeding | Always run `php artisan cache:clear` |

---

**Last Updated:** 2026-06-02
**For:** tanys.app production deployment
