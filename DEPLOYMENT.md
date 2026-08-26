# MHC Parish System — Free Deployment Guide
## Stack: Railway (PHP App) + Supabase (PostgreSQL Database)

Both are 100% free. No credit card required for either.

---

## STEP 1 — Set up Supabase (Free PostgreSQL Database)

1. Go to **https://supabase.com** → Sign up (free)
2. Click **"New Project"**
   - Name: `mhc-parish`
   - Password: create a strong password (save it!)
   - Region: Southeast Asia (Singapore)
3. Wait ~2 minutes for the project to be created
4. Go to **Project Settings → Database**
5. Under **"Connection string"** → select **"URI"** tab
6. Copy the full connection string — it looks like:
   ```
   postgresql://postgres:[PASSWORD]@db.xxxxxxxxxxxx.supabase.co:5432/postgres
   ```
7. Note these individual values (you'll need them in Railway):
   - **Host**: `db.xxxxxxxxxxxx.supabase.co`
   - **Port**: `5432`
   - **Database**: `postgres`
   - **Username**: `postgres`
   - **Password**: the password you set

---

## STEP 2 — Set up Railway (Free PHP Hosting)

1. Go to **https://railway.app** → Sign up with GitHub (free, no credit card)
2. Click **"New Project"** → **"Deploy from GitHub repo"**
3. Select your repo: `Sheira12/mchp-capstone2`
4. Railway will auto-detect the `nixpacks.toml` and start building

### Set Environment Variables in Railway

Go to your service → **Variables** tab → Add all these:

```
APP_NAME=MHC Parish System
APP_ENV=production
APP_KEY=                          ← generate below
APP_DEBUG=false
APP_URL=                          ← fill after deploy (Railway gives you a URL)
APP_TIMEZONE=Asia/Manila

DB_CONNECTION=pgsql
DB_HOST=db.xxxxxxxxxxxx.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=your_supabase_password

CACHE_DRIVER=file
SESSION_DRIVER=cookie
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=local
LOG_CHANNEL=stderr
LOG_LEVEL=error

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_gmail@gmail.com
MAIL_PASSWORD=your_gmail_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=cumpioaries07@gmail.com
MAIL_FROM_NAME=MHC Parish System

PAYMONGO_PUBLIC_KEY=pk_test_YOUR_PAYMONGO_PUBLIC_KEY
PAYMONGO_SECRET_KEY=sk_test_YOUR_PAYMONGO_SECRET_KEY
PAYMONGO_WEBHOOK_SECRET=whsec_xxxxxxxxxxxx

GCASH_NUMBER=09369454812
GCASH_NAME=Aries Cumpio
MAYA_NUMBER=09918276384
MAYA_NAME=Aries Cumpio

PARISH_NAME=Mary Help of Christians Parish
PARISH_ADDRESS=Southville 1, Niugan, Cabuyao, Laguna
PARISH_PHONE=(049) 5668994
PARISH_EMAIL=mhcparish@gmail.com
PARISH_PRIEST=Rev. Fr. Erwin S. Sanchez

SEMAPHORE_API_KEY=your-semaphore-api-key
```

### Generate APP_KEY

In Railway terminal (or locally) run:
```bash
php artisan key:generate --show
```
Copy the output (starts with `base64:`) and paste it as `APP_KEY`.

---

## STEP 3 — Get your Railway URL and update APP_URL

1. After first deploy, Railway gives you a URL like:
   `https://mhc-parish-production.up.railway.app`
2. Go back to Variables and set:
   ```
   APP_URL=https://mhc-parish-production.up.railway.app
   PAYMONGO_SUCCESS_URL=https://mhc-parish-production.up.railway.app/payment/success
   PAYMONGO_CANCEL_URL=https://mhc-parish-production.up.railway.app/payment/cancel
   QR_VERIFICATION_BASE_URL=https://mhc-parish-production.up.railway.app/verify
   ```
3. Railway will auto-redeploy

---

## STEP 4 — Seed the initial admin users

After the app is running, open the Railway terminal:
```bash
php artisan db:seed --class=RolesAndPermissionsSeeder --force
php artisan db:seed --class=AdminUserSeeder --force
php artisan db:seed --class=MassScheduleSeeder --force
php artisan db:seed --class=ServiceSeeder --force
```

Then for demo data (optional):
```bash
php artisan db:seed --class=DemoDataSeeder --force
php artisan db:seed --class=DemoUsersSeeder --force
```

---

## STEP 5 — Important Notes

### File Uploads (Photos, Proof of Payment)
Railway has an **ephemeral filesystem** — uploaded files are deleted on redeploy.
For a capstone demo this is fine. For production, you would use AWS S3 or Supabase Storage.

### Admin Login
```
Email:    admin@mhcparish.ph        (Super Admin)
Email:    secretary@mhcparish.ph    (Parish Secretary)
Email:    finance@mhcparish.ph      (Finance Officer)
Password: Admin@123456  (all three)
```

### Demo Parishioner Logins
```
aries.cumpio@gmail.com       Password@123
maricel.santos@gmail.com     Password@123
roberto.garcia@gmail.com     Password@123
lourdes.villanueva@gmail.com Password@123
danilo.mendoza@gmail.com     Password@123
```

---

## Troubleshooting

| Problem | Fix |
|---------|-----|
| `500 Server Error` | Check Railway logs, set `APP_DEBUG=true` temporarily |
| `DB connection failed` | Verify Supabase host/password in Railway variables |
| `Class not found` | `composer dump-autoload` in Railway terminal |
| `Key not set` | Add APP_KEY to Railway variables |
| Migrations fail | Run `php artisan migrate --force` in Railway terminal |

---

## Free Tier Limits

| Service | Limit |
|---------|-------|
| Railway | $5 free credit/month (~500 hours) |
| Supabase | 500MB database, 2 projects |
| Both | No credit card required |
