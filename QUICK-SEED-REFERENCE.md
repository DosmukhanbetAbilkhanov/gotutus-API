# 🚀 Quick Seeder Reference Card

## One Command to Seed Everything

```bash
ssh forge@tanys.app
cd /home/forge/tanys.app
php artisan db:seed --class=ServerSeeder
```

---

## Admin Login

```
Email: dosmukhanbet@gmail.com
Phone: +77078835953 (or 87078835953)
Password: password
```

⚠️ **Change password after first login!**

---

## Test Users

```
Almaty: test.user.almaty.01@tanys.app (through .10)
Astana: test.user.astana.01@tanys.app (through .10)
Password: password (all users)
```

---

## Dev Routes (Non-Production Only)

```bash
# Seed via HTTP
curl http://tanys.test/api/v1/dev/seed

# Check database
curl http://tanys.test/api/v1/dev/db-status
```

---

## What Gets Created

- ✅ 1 Admin user
- ✅ 20+ Test users (Almaty, Astana, Aktobe)
- ✅ 4 Cities (Almaty, Astana, Aktobe, Aktau)
- ✅ 10+ Places with working hours
- ✅ 22 Activity types
- ✅ 25 Interests
- ✅ All with 3 language translations (en, ru, kk)

---

## Individual Seeders

```bash
php artisan db:seed --class=AdminUserSeeder     # Just admin
php artisan db:seed --class=TestUsersSeeder     # Just test users
php artisan db:seed --class=PlacesSeeder        # Just places
```

---

## Verify Seeding

```bash
php artisan tinker
>>> DB::table('users')->count()
>>> DB::table('places')->count()
>>> DB::table('cities')->count()
```

---

## Full Documentation

- `database/seeders/README.md` - Complete guide
- `SEEDER-SETUP.md` - Detailed setup instructions
