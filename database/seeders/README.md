# Database Seeders - Tanys.app

## Overview

This directory contains production-ready seeders for populating the database with test data.

## Available Seeders

### 1. **ServerSeeder** (Master Seeder)
**Runs all seeders in correct order.**

**What it seeds:**
- User types (client, admin, city_manager)
- Cities (Almaty, Astana, Aktobe, Aktau) with translations (en, ru, kk)
- Activity types (22 types) with translations
- Interests (25 interests) with translations
- Admin user
- Test users for Almaty and Astana (10 per city)
- Places with working hours for Almaty and Astana

**Usage:**
```bash
# Via Artisan
php artisan db:seed --class=ServerSeeder

# Via Route (development only)
curl http://tanys.test/api/v1/dev/seed
# or
curl https://tanys.app/api/v1/dev/seed
```

---

### 2. **AdminUserSeeder**
**Creates admin user.**

**Credentials:**
- Email: `dosmukhanbet@gmail.com`
- Phone: `+77078835953`
- Password: `password` (⚠️ CHANGE IMMEDIATELY!)

**Usage:**
```bash
php artisan db:seed --class=AdminUserSeeder
```

**Access:**
- Filament Admin Panel: `https://tanys.app/admin`
- API with Sanctum token

---

### 3. **TestUsersSeeder**
**Creates 10 test users each for Almaty and Astana (20 total).**

**User details:**
- 5 male, 5 female per city
- Verified phones
- Random interests assigned (3-7 per user)
- Trust scores: 3.5-5.0
- Ratings and attendance rates

**Email pattern:**
- Almaty: `test.user.almaty.01@tanys.app` through `test.user.almaty.10@tanys.app`
- Astana: `test.user.astana.01@tanys.app` through `test.user.astana.10@tanys.app`

**Password:** `password` (all users)

**Usage:**
```bash
php artisan db:seed --class=TestUsersSeeder
```

---

### 4. **PlacesSeeder**
**Creates sample places with working hours for Almaty and Astana.**

**What it creates:**
- Coffee shops
- Restaurants
- Bars & pubs
- Bowling centers
- Cinemas
- And more...

**Each place includes:**
- Translations (en, ru, kk)
- Coordinates
- Contact info (phone, Instagram)
- Activity type assignments
- Working hours (7 days)

**Usage:**
```bash
php artisan db:seed --class=PlacesSeeder
```

---

### 5. **ProductionSeeder**
**Seeds base data needed for the app to function.**

**Already included in ServerSeeder, but can run standalone:**

**What it seeds:**
- User types
- Cities with translations
- Activity types with translations
- Interests with translations
- Test users for Aktobe

**Usage:**
```bash
php artisan db:seed --class=ProductionSeeder
```

---

## Quick Start (Server Deployment)

### Step 1: SSH into server
```bash
ssh forge@tanys.app
cd /home/forge/tanys.app
```

### Step 2: Run master seeder
```bash
php artisan db:seed --class=ServerSeeder
```

### Step 3: Verify
```bash
# Check database counts
php artisan tinker
>>> DB::table('users')->count()
>>> DB::table('places')->count()
>>> DB::table('cities')->count()
```

---

## Alternative: Seed via Route

For quick testing during development:

```bash
# Check current database status
curl http://tanys.test/api/v1/dev/db-status

# Seed database
curl http://tanys.test/api/v1/dev/seed

# Verify
curl http://tanys.test/api/v1/dev/db-status
```

**Note:** Development routes only work in `local` and `staging` environments, **not production**.

---

## Testing After Seeding

### Test Admin Login

**Via Flutter App:**
1. Open app
2. Login with:
   - Phone: `+77078835953` or `87078835953`
   - Password: `password`
3. Should redirect to feed

**Via Filament:**
1. Go to `https://tanys.app/admin`
2. Login with:
   - Email: `dosmukhanbet@gmail.com`
   - Password: `password`
3. Should see admin dashboard

### Test Regular User Login

**Pick any test user:**
```
Email: test.user.almaty.01@tanys.app
Password: password
```

**Test features:**
1. Browse hangout feed (should show cities: Almaty, Astana, Aktobe, Aktau)
2. Filter by city (Almaty or Astana)
3. View places (should show seeded places)
4. Create hangout (should work with places)
5. View profile (should show interests)

---

## Seeder Features

### Idempotent
All seeders are **idempotent** - safe to run multiple times:
- Checks if data exists before inserting
- Updates missing translations if needed
- Skips existing records

### Production-Safe
- Uses transactions where appropriate
- Validates data before insertion
- Provides clear console output
- Shows what was created vs. already existed

### Translatable
All translatable entities include:
- English (`en`)
- Russian (`ru`)
- Kazakh (`kk`)

---

## Customization

### Adding More Test Users

Edit `TestUsersSeeder.php`:

```php
private function seedCityUsers(...) {
    $users = [
        // Add more users here
        [
            'name' => 'Your Name',
            'gender' => Gender::Male, // or Female
            'age' => 25,
            'bio' => 'Your bio',
        ],
    ];
}
```

### Adding More Places

Edit `PlacesSeeder.php`:

```php
private function getPlacesData(string $cityName): array {
    $places = [
        // Add more places here
        [
            'name' => ['en' => 'Name', 'ru' => 'Название', 'kk' => 'Атауы'],
            // ... rest of place data
        ],
    ];
}
```

### Adding New City

Edit `ProductionSeeder.php`:

```php
private function seedCities(): void {
    $cities = [
        // Existing cities...
        ['en' => 'Shymkent', 'ru' => 'Шымкент', 'kk' => 'Шымкент'],
    ];
}
```

Then update `TestUsersSeeder` and `PlacesSeeder` to include the new city.

---

## Troubleshooting

### Error: "Admin user type not found"
**Solution:** Run `ProductionSeeder` first:
```bash
php artisan db:seed --class=ProductionSeeder
```

### Error: "City not found"
**Solution:** Run `ProductionSeeder` first to seed cities.

### Error: "Duplicate entry"
**Solution:** User/place already exists. Seeders are idempotent, check if:
- Email already used
- Phone number already used
- Place name already exists in that city

### Want to start fresh?
```bash
# ⚠️ WARNING: This deletes ALL data!
php artisan migrate:fresh --seed --class=ServerSeeder
```

---

## Production Deployment Checklist

After deploying to server:

- [ ] SSH into server: `ssh forge@tanys.app`
- [ ] Navigate to project: `cd /home/forge/tanys.app`
- [ ] Run migrations: `php artisan migrate`
- [ ] Run seeder: `php artisan db:seed --class=ServerSeeder`
- [ ] Verify admin user: Check email for admin credentials
- [ ] Test login: Use admin credentials in app
- [ ] Test Filament: Access `/admin` panel
- [ ] Change admin password immediately!
- [ ] Test user registration flow
- [ ] Test creating hangout
- [ ] Test place selection
- [ ] Verify translations work (switch app language)

---

## Security Notes

### Admin Password
**⚠️ CRITICAL:** The default admin password is `password`.

**Change it immediately after first login:**

1. Login to admin panel
2. Go to profile settings
3. Update password to something secure

Or via Tinker:
```bash
php artisan tinker
>>> $admin = User::where('email', 'dosmukhanbet@gmail.com')->first();
>>> $admin->password = Hash::make('your-new-secure-password');
>>> $admin->save();
```

### Test Users
All test users have password: `password`

For production, consider:
1. Changing test user passwords
2. Marking test users clearly (email domain: `@tanys.app`)
3. Deleting test users after real users exist

### Development Routes
The `/api/v1/dev/*` routes are **automatically disabled in production**.

Controlled by:
```php
if (!app()->environment('production')) {
    // Dev routes here
}
```

---

## Database Stats After Seeding

| Entity | Count | Notes |
|--------|-------|-------|
| User Types | 3 | client, admin, city_manager |
| Cities | 4 | Almaty, Astana, Aktobe, Aktau |
| Activity Types | 22 | beer, coffee, cinema, etc. |
| Interests | 25 | sports, music, gaming, etc. |
| Admin Users | 1 | dosmukhanbet@gmail.com |
| Test Users | 20+ | 10 per city (Almaty, Astana) + Aktobe users |
| Places | 10+ | Sample places with working hours |
| Translations | 100s | All entities in 3 languages |

---

## Next Steps

1. ✅ Seed database with `ServerSeeder`
2. ✅ Verify admin login
3. ✅ Test user login with test accounts
4. ✅ Change admin password
5. ✅ Configure server (queue, reverb, scheduler)
6. ✅ Test real-time features
7. ✅ Deploy Flutter app with production config
8. ✅ Monitor logs for errors

---

## Support

**Seeder created:** 2026-06-01
**For:** tanys.app production deployment

**Contact:**
- Admin: dosmukhanbet@gmail.com
- Phone: +77078835953

**Documentation:**
- Main guide: `/Users/dos/Desktop/apps/companion-flutter/.ai/forge-server-setup-guide.md`
- API reference: `/Users/dos/Desktop/apps/companion-flutter/.ai/app-reference.md`
