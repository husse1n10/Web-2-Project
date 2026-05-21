# CedarGov — Municipal Services Platform

A Laravel 12 web platform for **municipal e-services in Lebanon**.
Citizens submit service requests, pay online (card or crypto), book appointments, chat with offices, and track progress with QR codes. Office users manage requests, services, appointments, and feedback. Admins oversee municipalities, offices, users, and identity verification.

Built for **Web Programming 2 (PROG322-EC20)** at Université Antonine. Live deployment: **[cedargov.app](https://cedargov.app)**.

## Highlights

- **3 role-based portals** (Admin, Office, Citizen) — each with its own controller, route group, and views
- **Identity verification pipeline** — Azure / OCR.space document intelligence extracts data from national ID, admin approves/rejects
- **Two payment gateways** — Stripe (cards) with webhook + NOWPayments (BTC / ETH / USDT) with HMAC-SHA512 IPN
- **Live FX conversion** — service prices shown in USD / LBP / EUR via exchangerate-api with caching
- **Request resubmission workflow** — citizens with `missing_documents` or `rejected` status upload replacements and reopen the request
- **Real appointment slot picker** — office working hours drive a live 30-min slot dropdown (AJAX), already-booked slots are filtered out server-side
- **Staff assignment & SLA tracking** — office requests can be assigned to staff with a due date and an "Overdue" badge
- **Realtime chat + notifications** — Pusher-backed messaging between citizens and offices, status broadcasts, read receipts
- **In-app support tickets** — citizens raise tickets to admins with attachments + realtime replies
- **AI chatbot** — Groq + Llama 3.3 grounded on the platform's features (citizen sidebar widget)
- **QR-code tracking** — every request gets a QR; public `/track/{ref}` page works without login
- **PDF generation** — DomPDF receipts, approval letters, certificates
- **Multi-language (English + العربية)** — language switcher in the topbar with full RTL support
- **Admin CSV export** — requests, payments, and offices export to UTF-8 BOM CSVs (Excel-friendly for Arabic names)
- **WhatsApp phone verification** — Twilio WhatsApp sandbox for OTP
- **2FA (TOTP)** — Google Authenticator-compatible, optional per user
- **Email verification** — Laravel signed-link flow; OAuth signups auto-verified
- **Social login** — Google + GitHub OAuth via Laravel Socialite
- **Google Maps** — office locator with markers
- **Rate-limited auth** — throttling on login, register, 2FA, password reset, phone OTP, chatbot

## Tech Stack

### Backend
- PHP 8.2+
- Laravel 12
- PostgreSQL (production on Heroku) / MySQL or SQLite (local)

### Frontend
- Blade templates
- Bootstrap 5.3 (CDN) + custom design system in `resources/css/app.css`
- Vite (bundles JS + assets)
- Laravel Echo + Pusher JS (realtime)

### Integrations
- **Stripe** — card checkout + webhook
- **NOWPayments** — crypto hosted invoice + IPN webhook (BTC / ETH / USDT)
- **Google + GitHub OAuth** — Laravel Socialite
- **Pusher** — websocket broadcasting
- **Twilio WhatsApp** — phone OTP (sandbox in dev)
- **Azure Document Intelligence** + **OCR.space** — national ID extraction
- **Groq** — Llama 3.3 chatbot
- **exchangerate-api** — fiat FX
- **CoinGecko** — crypto price feeds
- **Google Maps API** — office locations
- **DomPDF + SimpleSoftwareIO/qrcode** — PDFs + QR codes

### Developer Tooling
- PHPUnit (13 tests, SQLite in-memory)
- Laravel Pint (PSR-12)
- Composer + npm + Vite

## Team Members

| Member  | Role                          | Branch                      |
| ------- | ----------------------------- | --------------------------- |
| Rony    | Team Lead, Auth + Realtime    | `feature/auth-realtime`     |
| Ahmad   | Admin Module                  | `feature/admin-panel`       |
| Hasouna | Office Module                 | `feature/office-panel`      |
| Hussein | Citizen Portal + Design System | `feature/citizen-portal`   |
| Maged   | Chat + Maps UI                | `feature/chat-maps-ui`      |

Core branches: `main` (production) and `dev` (integration). Feature branches PR into `dev`; `dev` merges into `main` after validation.

## Setup

Detailed local-setup notes are in [`Running.md`](Running.md).

### 1. Clone and install dependencies

```bash
git clone https://github.com/RonyAbouEzzi/Web-2-Project.git
cd Web-2-Project
composer install
npm install
```

### 2. Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

On Windows PowerShell:

```powershell
Copy-Item .env.example .env
php artisan key:generate
```

Set values for the integrations you want to enable (Stripe, NOWPayments, Google/GitHub OAuth, Pusher, Twilio, Groq, Azure / OCR.space, Google Maps). Anything you don't configure simply hides its feature in the UI.

### 3. Prepare database and storage

```bash
php artisan migrate:fresh --seed
php artisan storage:link
```

### 4. Run the application

Three terminals:

```bash
php artisan serve --host=127.0.0.1 --port=8000
npm run dev
php artisan queue:work
```

Or one command (uses `composer dev`):

```bash
composer dev
```

Application URL: `http://127.0.0.1:8000`

### 5. Seeded demo accounts

- **Admin:** `admin@eservices.gov` / `password`
- **Office Manager:** `manager@beirut.gov.lb` / `password`
- **Citizen:** `citizen1@test.com` / `password`

## Architecture (quick reference)

| Layer            | Where                                                          |
| ---------------- | -------------------------------------------------------------- |
| Routing          | `routes/web.php` (role-grouped: `admin.*`, `office.*`, `citizen.*`) |
| Role gates       | `App\Http\Middleware\RoleMiddleware` (alias `role:`)            |
| Citizen gates    | `EnsureCitizenProfileComplete`, `EnsureCitizenIdentityApproved` |
| Locale           | `App\Http\Middleware\SetLocale` (session-driven EN/AR)          |
| Service layer    | `app/Services/` — `PaymentService`, `PdfService`, `QrCodeService`, `ChatbotService` |
| Realtime events  | `app/Events/` — `MessageSent`, `ServiceRequestStatusUpdated`, `SupportTicketMessageSent`, etc. |
| Notifications    | `app/Notifications/` — mail + database + broadcast channels    |
| Translations     | `lang/en.json`, `lang/ar.json`                                  |

## Testing

```bash
php artisan test                        # all tests
php artisan test --filter=Submission    # subset
```

Tests run against SQLite in-memory (`phpunit.xml`). Pusher / mail / SMS are faked via `Event::fake()` and `Notification::fake()`.

## Public Endpoints

| Endpoint                     | Auth     | What                                           |
| ---------------------------- | -------- | ---------------------------------------------- |
| `/`                          | optional | Landing page + role redirect                   |
| `/track/{reference}`         | none     | QR-trackable status page (no login)            |
| `/locale/{lang}`             | none     | Switch language to EN or AR                    |
| `/auth/{provider}`           | guest    | Google or GitHub OAuth start                   |
| `/webhooks/stripe`           | none*    | Stripe checkout.session.completed              |
| `/webhooks/nowpayments`      | none*    | NOWPayments crypto IPN                         |

*Webhooks authenticate via HMAC signature, not session.

## Contribution Guidelines

### Branch and PR workflow

1. Never push directly to `main`.
2. `git fetch origin && git checkout <your-feature-branch> && git pull`
3. Commit and push:
   ```bash
   git add .
   git commit -m "feat: short description"
   git push origin <your-feature-branch>
   ```
4. Open a PR into `dev`.
5. Require at least one teammate review.
6. Merge `dev` into `main` only after validation.

### Pull request checklist

- Feature works end-to-end locally
- No debug/temp files
- No secrets committed (`.env`, keys, tokens)
- Migrations + seeds tested
- PR body includes change summary + test steps

## Security Notes

- `APP_DEBUG=true` only locally.
- Never commit real credentials or API secrets.
- Stripe / NOWPayments use test keys in dev; live keys only in production environment.
- Webhook secrets (`STRIPE_WEBHOOK_SECRET`, `NOWPAYMENTS_IPN_SECRET`) are required for payment confirmation in production.
- Rate limits applied to login, register, 2FA, password reset, phone OTP, and chatbot endpoints.

## License

University coursework project — not licensed for commercial reuse.
