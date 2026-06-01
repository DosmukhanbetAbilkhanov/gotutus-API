# ✅ Database Seeders Created

**Date:** 2026-06-01
**For:** tanys.app server deployment

---

## What Was Created

### 4 New Seeders

1. **`AdminUserSeeder.php`**
   - Creates admin user: dosmukhanbet@gmail.com
   - Phone: +77078835953
   - Password: `password` (CHANGE THIS!)

2. **`TestUsersSeeder.php`**
   - Creates 20 test users (10 each for Almaty and Astana)
   - Email pattern: `test.user.{city}.{01-10}@tanys.app`
   - All passwords: `password`
   - With random interests assigned

3. **`PlacesSeeder.php`**
   - Creates sample places for Almaty and Astana
   - Includes working hours (7 days)
   - Coffee shops, restaurants, bars, bowling, cinema, etc.
   - With translations (en, ru, kk)

4. **`ServerSeeder.php`** (Master Seeder)
   - Runs all seeders in correct order
   - Seeds everything needed for testing
   - Shows formatted output with credentials

### Development Routes

**Added to `routes/api.php`:**

- `GET /api/v1/dev/seed` - Run ServerSeeder via HTTP
- `GET /api/v1/dev/db-status` - Check database counts

**Security:** Only available in non-production environments

### Documentation

- **`database/seeders/README.md`** - Complete seeder documentation

---

## Quick Start (3 Steps)

### Option A: Via Artisan (Recommended)

```bash
# SSH into server
ssh forge@tanys.app
cd /home/forge/tanys.app

# Run master seeder
php artisan db:seed --class=ServerSeeder

# Done! 🎉
```

### Option B: Via HTTP Route (Development)

```bash
# Local development
curl http://tanys.test/api/v1/dev/seed

# Or staging server (if not in production mode)
curl https://tanys.app/api/v1/dev/seed
```

### Option C: Individual Seeders

```bash
# Seed only specific parts
php artisan db:seed --class=AdminUserSeeder
php artisan db:seed --class=TestUsersSeeder
php artisan db:seed --class=PlacesSeeder
```

---

## What Gets Seeded

| Item | Count | Details |
|------|-------|---------|
| **Cities** | 4 | Almaty, Astana, Aktobe, Aktau (with translations) |
| **Admin User** | 1 | dosmukhanbet@gmail.com |
| **Test Users** | 20+ | 10 per city (Almaty, Astana) + Aktobe users |
| **Places** | 10+ | Sample venues with working hours |
| **Activity Types** | 22 | beer, coffee, cinema, etc. |
| **Interests** | 25 | sports, music, gaming, etc. |
| **Translations** | All | English, Russian, Kazakh |

---

## Admin Credentials

**🔑 Login Details:**

```
Email: dosmukhanbet@gmail.com
Phone: +77078835953 (or 87078835953)
Password: password
```

**⚠️ CRITICAL:** Change password immediately after first login!

**Access Points:**
- Flutter app: Login with phone/password
- Filament admin: `https://tanys.app/admin`
- API: Use Sanctum token

---

## Test User Credentials

**All test users have password:** `password`

**Almaty Users:**
```
test.user.almaty.01@tanys.app
test.user.almaty.02@tanys.app
...
test.user.almaty.10@tanys.app
```

**Astana Users:**
```
test.user.astana.01@tanys.app
test.user.astana.02@tanys.app
...
test.user.astana.10@tanys.app
```

**Details:**
- 5 male, 5 female per city
- Ages: 24-32
- Trust scores: 3.5-5.0
- Random interests assigned
- Phone verified
- Public offer accepted

---

## Testing After Seeding

### 1. Verify Database

```bash
php artisan tinker
>>> DB::table('users')->count()
# Should return 20+
>>> DB::table('places')->count()
# Should return 10+
>>> DB::table('cities')->count()
# Should return 4
```

### 2. Test Admin Login

**Via App:**
1. Open Flutter app
2. Login: `87078835953` / `password`
3. Should see feed

**Via Filament:**
1. Go to `https://tanys.app/admin`
2. Login: `dosmukhanbet@gmail.com` / `password`
3. Should see dashboard

### 3. Test Regular User

**Login:**
```
Phone: Use any test user email
Password: password
```

**Test:**
- Browse feed
- Filter by city (Almaty/Astana)
- View places
- Create hangout
- Check profile & interests

---

## Production Deployment Checklist

After seeding:

- [ ] SSH: `ssh forge@tanys.app`
- [ ] Navigate: `cd /home/forge/tanys.app`
- [ ] Migrate: `php artisan migrate`
- [ ] Seed: `php artisan db:seed --class=ServerSeeder`
- [ ] Verify admin user exists
- [ ] Test admin login
- [ ] **Change admin password immediately!**
- [ ] Test user registration
- [ ] Test creating hangout
- [ ] Verify places show up
- [ ] Test translations (switch language)

---

## Server Configuration (Next Steps)

After seeding, configure these services:

1. **Queue Workers** (15 min)
   - Forge Dashboard → Daemons
   - Command: `php artisan queue:work database --sleep=3 --tries=3 --max-time=3600`

2. **Scheduler** (5 min)
   - Forge Dashboard → Scheduler
   - Command: `php artisan schedule:run`
   - Frequency: Every Minute

3. **Reverb (WebSocket)** (20 min)
   - Setup Reverb daemon
   - Configure `.env` with secure keys
   - Open port 8080 or setup Nginx proxy

4. **FCM (Push Notifications)** (10 min)
   - Upload Firebase credentials
   - Set `FIREBASE_CREDENTIALS` in `.env`

**Full Guide:** `.ai/forge-server-setup-guide.md`

---

## Troubleshooting

### "User type not found"
**Run ProductionSeeder first:**
```bash
php artisan db:seed --class=ProductionSeeder
```

### "City not found"
**Same as above** - ProductionSeeder creates cities

### "Duplicate entry"
**Seeders are idempotent** - already exists, safe to ignore

### Want to start fresh?
```bash
# ⚠️ WARNING: Deletes ALL data!
php artisan migrate:fresh --seed --seeder=ServerSeeder
```

### Check what's in database
```bash
# Via route
curl http://tanys.test/api/v1/dev/db-status

# Via Tinker
php artisan tinker
>>> DB::table('users')->count()
>>> DB::table('places')->count()
```

---

## Security Warnings

### 🔒 Change Admin Password
Default password is `password` - **change immediately!**

```bash
php artisan tinker
>>> $admin = User::where('email', 'dosmukhanbet@gmail.com')->first();
>>> $admin->password = Hash::make('your-secure-password');
>>> $admin->save();
```

### 🔒 Dev Routes Disabled in Production
The `/api/v1/dev/*` routes only work in:
- `local` environment
- `staging` environment

**Not accessible in production** (controlled by `app()->environment()`)

### 🔒 Test Users
All test users have weak passwords. Consider:
- Changing passwords
- Deleting after real users exist
- Keeping them marked clearly (`@tanys.app` domain)

---

## Files Created

```
database/seeders/
├── AdminUserSeeder.php      ← Admin user
├── TestUsersSeeder.php      ← 20 test users
├── PlacesSeeder.php         ← Sample places
├── ServerSeeder.php         ← Master seeder (runs all)
└── README.md                ← Full documentation

routes/
└── api.php                  ← Added dev routes (seed, db-status)
```

---

## Sample Output

When you run `ServerSeeder`, you'll see:

```
╔═══════════════════════════════════════════════════════════╗
║         TANYS.APP SERVER SEEDER                          ║
╚═══════════════════════════════════════════════════════════╝

📦 Step 1: Seeding base data...
  User types: 0 created, 3 already existed.
  Cities: 0 created, 4 already existed.
  Activity types: 0 created, 0 translations added.
  Interests: 0 created, 0 translations added.
  Aktobe users: 0 created, 10 already existed.

👤 Step 2: Creating admin user...
✓ Admin user created successfully!
  Email: dosmukhanbet@gmail.com
  Phone: +77078835953
  Password: password
  ⚠️  CHANGE PASSWORD IMMEDIATELY!

👥 Step 3: Creating test users...
  Almaty: 10 users created.
  Astana: 10 users created.

🏢 Step 4: Creating places...
  Almaty: 5 places created.
  Astana: 5 places created.

╔═══════════════════════════════════════════════════════════╗
║         ✓ SERVER SEEDING COMPLETE!                       ║
╚═══════════════════════════════════════════════════════════╝

🔑 Admin Credentials:
   Email: dosmukhanbet@gmail.com
   Phone: +77078835953
   Password: password
   ⚠️  CHANGE PASSWORD IMMEDIATELY!

👥 Test Users:
   Almaty: test.user.almaty.01@tanys.app (through .10)
   Astana: test.user.astana.01@tanys.app (through .10)
   Password: password (for all test users)
```

---

## Next Steps

1. ✅ **Run seeder on server**
   ```bash
   ssh forge@tanys.app
   cd /home/forge/tanys.app
   php artisan db:seed --class=ServerSeeder
   ```

2. ✅ **Test admin login**
   - App or Filament panel

3. ✅ **Change admin password**
   - Via profile settings or Tinker

4. ✅ **Configure server services**
   - Follow `.ai/forge-server-setup-guide.md`
   - Setup queue, scheduler, Reverb, FCM

5. ✅ **Deploy Flutter app**
   - Build with production config
   - Test real-time features

---

## Documentation References

- **Seeder README:** `database/seeders/README.md`
- **Server Setup:** `.ai/forge-server-setup-guide.md`
- **Quick Checklist:** `.ai/quick-deployment-checklist.md`
- **App Reference:** `.ai/app-reference.md`

---

**Created:** 2026-06-01
**For:** tanys.app production deployment
**Status:** ✅ Ready to deploy
