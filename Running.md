# Running Guide - CedarGov Platform

This guide is for team setup and local deployment.

## 1) Prerequisites

- PHP 8.2+
- Composer 2+
- Node.js 18+
- npm 9+
- MySQL 8+
- Git

## 2) Clone and Install

```bash
git clone https://github.com/RonyAbouEzzi/Web-2-Project.git
cd Web-2-Project
composer install
npm install
```

## 3) Environment Setup

1. Copy the environment file:

```bash
cp .env.example .env
```

On Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

2. Generate app key:

```bash
php artisan key:generate
```

3. Update `.env` with your local values.

## 4) Required .env Keys (Task 4)

### Database

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=eservices
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password
```

### Mail (Mailtrap for development)

```env
MAIL_MAILER=smtp
MAIL_SCHEME=null
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mail_username
MAIL_PASSWORD=your_mail_password
MAIL_FROM_ADDRESS=noreply@eservices.gov.lb
MAIL_FROM_NAME="CedarGov Platform"
```

### Social Login (Google + GitHub)

```env
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT_URI=http://127.0.0.1:8000/auth/google/callback

GITHUB_CLIENT_ID=your_github_client_id
GITHUB_CLIENT_SECRET=your_github_client_secret
GITHUB_REDIRECT_URI=http://127.0.0.1:8000/auth/github/callback
```

### Google Maps

```env
GOOGLE_MAPS_API_KEY=your_google_maps_api_key
```

### Stripe

```env
STRIPE_KEY=pk_test_your_stripe_public_key
STRIPE_SECRET=sk_test_your_stripe_secret_key
```

### SMS Reminders (Twilio)

```env
# Use log during development if you do not want to send real SMS
SMS_DRIVER=log
SMS_DEFAULT_COUNTRY_CODE=+961

# Switch SMS_DRIVER=twilio and fill these for real SMS
TWILIO_ACCOUNT_SID=your_twilio_account_sid
TWILIO_AUTH_TOKEN=your_twilio_auth_token
TWILIO_FROM=+1XXXXXXXXXX
```

### Pusher (Realtime)

```env
BROADCAST_CONNECTION=pusher
PUSHER_APP_ID=your_pusher_app_id
PUSHER_APP_KEY=your_pusher_app_key
PUSHER_APP_SECRET=your_pusher_app_secret
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```

If Pusher is not configured yet, keep:

```env
BROADCAST_CONNECTION=log
```

## 5) Database and Seed

```bash
php artisan migrate:fresh --seed
php artisan storage:link
```

Seeded demo credentials:

- Admin: `admin@eservices.gov` / `password`
- Office manager: `manager@beirut.gov.lb` / `password`
- Citizen: `citizen1@test.com` / `password`

## 6) Run the Project

Terminal 1:

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

Terminal 2:

```bash
npm run dev
```

Terminal 3 (queue worker):

```bash
php artisan queue:work
```

## 7) Service Setup References

### Google OAuth

- Google Cloud Console -> APIs & Services -> OAuth consent + OAuth Client ID
- Authorized redirect URI:
  - `http://127.0.0.1:8000/auth/google/callback`

### GitHub OAuth

- GitHub -> Settings -> Developer settings -> OAuth Apps -> New OAuth App
- Authorization callback URL:
  - `http://127.0.0.1:8000/auth/github/callback`

### Google Maps

- Enable Maps JavaScript API in Google Cloud
- Restrict key by API + local referrer as needed

### Stripe

- Use Stripe test keys from Dashboard -> Developers -> API keys

### Mailtrap

- Create an inbox in Mailtrap Email Sandbox
- Copy SMTP host/port/username/password into `.env`

### Twilio SMS

- Create a Twilio account and a Messaging-enabled phone number.
- Copy `Account SID`, `Auth Token`, and `From` number into `.env`.
- Use E.164 number format for users, for example `+96170123456`.

To test reminders quickly:

```bash
php artisan appointments:remind-upcoming
```

- `SMS_DRIVER=log`: SMS payload is written to `storage/logs/laravel.log`.
- `SMS_DRIVER=twilio`: real SMS is sent via Twilio.
- If `QUEUE_CONNECTION` is not `sync`, run `php artisan queue:work` to deliver notifications.

## 8) Cache Clear Commands (after .env changes)

```bash
php artisan optimize:clear
php artisan config:clear
```

## 9) Basic Validation Checklist

- Register/login works
- Google login works
- GitHub login works
- Password reset email is received in Mailtrap
- Appointment reminder SMS is logged/sent when appointment is confirmed or upcoming
- Office map preview loads (valid Maps key)
- Realtime features do not throw broadcast errors

## 10) Production Notes

For production deployment:

- `APP_ENV=production`
- `APP_DEBUG=false`
- Use real SMTP provider
- Use real Stripe live keys only in production environment
- Use process manager for queue workers (Supervisor/systemd)
- Do not commit `.env` or real secrets
