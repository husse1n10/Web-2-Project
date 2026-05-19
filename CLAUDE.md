# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Laravel 12 municipal e-services platform for Lebanon (**CedarGov**, deployed at [cedargov.app](https://cedargov.app)). Citizens submit service requests, pay (card via Stripe or crypto via NOWPayments), book appointments, track progress via QR. Office users manage incoming requests, services, appointments, feedback. Admins oversee municipalities, offices, users, identity verification, and reporting.

Built as a university project (PROG322-EC20, Web Programming 2, Antonine University) by a 5-person team using feature branches.

## Commands

### Development (three terminals)

```bash
php artisan serve --host=127.0.0.1 --port=8000   # Backend
npm run dev                                       # Vite dev server
php artisan queue:work                            # Queue worker
```

Or all at once:

```bash
composer dev    # Runs server, queue, logs, vite concurrently
```

### Database

```bash
php artisan migrate:fresh --seed    # Reset DB + demo data
php artisan storage:link            # Public storage symlink
```

### Testing

```bash
php artisan test                                  # All tests (13 currently)
php artisan test --filter=CitizenRequest          # Filter by name fragment
php artisan test tests/Feature/RoleMiddlewareTest.php
```

Tests run against SQLite in-memory (`phpunit.xml`). Use `Event::fake()` / `Notification::fake()` / `Storage::fake('private'|'public')` to skip Pusher / mail / file IO.

### Code Style

```bash
./vendor/bin/pint    # PSR-12 formatter
```

### Cache Clear (after .env changes)

```bash
php artisan optimize:clear   # config + views + routes + events
```

## Architecture

### Role-Based Multi-Portal System

Three user roles, each with its own controller, route group, and view directory:

| Role          | Controller                  | Routes prefix         | Views            |
| ------------- | --------------------------- | --------------------- | ---------------- |
| `admin`       | `Admin\AdminController`     | `/admin` (`admin.*`)  | `views/admin/`   |
| `office_user` | `Office\OfficeController`   | `/office` (`office.*`)| `views/office/`  |
| `citizen`     | `Citizen\CitizenController` | `/citizen` (`citizen.*`)| `views/citizen/`|

Auth lives in `Auth\AuthController` (login, register, social OAuth, 2FA, password reset, OCR-based ID extraction).

### Middleware

| Alias                          | Class                                       | Purpose                                              |
| ------------------------------ | ------------------------------------------- | ---------------------------------------------------- |
| `role:<role>`                  | `RoleMiddleware`                            | Enforce role; redirects + silent denials, no 403 leak |
| `citizen.profile.complete`     | `EnsureCitizenProfileComplete`              | Citizens must have name + national_id + id_document  |
| `citizen.identity.approved`    | `EnsureCitizenIdentityApproved`             | Citizens must have admin-approved identity           |
| (web group)                    | `SecurityHeadersMiddleware`                 | CSP, X-Frame-Options, etc.                           |
| (web group)                    | `SetLocale`                                 | Reads `session('locale')`, sets `App::setLocale()`   |

Webhooks (`webhooks/*`) are CSRF-exempt — see `bootstrap/app.php`.

### UI Layer

**Custom Bootstrap 5.3 + custom CSS** (NOT Sneat — the original Sneat templates were replaced 2026-04-02). Loaded via:

- `views/layouts/app.blade.php` — main authenticated layout: sidebar + topbar + breadcrumbs + toast stack + RTL support
- `views/layouts/blankLayout.blade.php` — auth-page shell (login / register / forgot-password)
- `resources/css/app.css` — design system tokens, Bootstrap overrides, role-themed CSS variables (`--es-primary`, `--es-input-border`, etc.). Loaded both via Vite **and** via `asset('css/app.css')` in the layout (`public/css/app.css` must be kept in sync).

Role-aware theming: `<body class="es-role-{admin|office_user|citizen|guest}">` swaps the CSS variable palette (admin = blue/slate, citizen = sky/blue, office = blue, guest = cream).

### Reusable Blade Components

In `views/components/`:
- Generic: `card`, `data-table`, `modal`, `stat-card`, `status-badge`, `status-pill`, `page-header`, `form-input`, `ui-button`, `empty-state`, `alert-box`, `auth-shell`
- Admin-specific: `admin.page-header`, `admin.stat-card`, `admin.table-toolbar`

### Service Layer

Business logic in `app/Services/`:

| Service           | Purpose                                                          |
| ----------------- | ---------------------------------------------------------------- |
| `PaymentService`  | Stripe Checkout sessions, NOWPayments invoices, currency FX      |
| `PdfService`      | DomPDF receipts, approval letters, certificates                  |
| `QrCodeService`   | Per-request QR generation (writes to public disk)                |
| `ChatbotService`  | Groq + Llama 3.3 chat; system-prompt-grounded to CedarGov features|

### Realtime & Notifications

- Broadcasting via Pusher (`laravel-echo` + `pusher-js` bundled by Vite)
- Events in `app/Events/`: `MessageSent`, `MessagesRead`, `ServiceRequestStatusUpdated`, `NewRequestSubmitted`, `RequestDocumentUploaded`, `AppointmentReminderBroadcast`, `SupportTicketMessageSent`
- Notifications in `app/Notifications/`: `RequestStatusUpdated`, `RequestResubmitted`, `AppointmentReminder`, `RegistrationConfirmation`, `PhoneVerificationNotification`, `NewSupportTicketNotification`, `SupportTicketReplyNotification`, `ResetPasswordNotification`
- Channels: `mail`, `database`, `broadcast` (most fan-outs use all three)
- Channel auth in `routes/channels.php` — admins always allowed; per-ticket / per-request channels checked against ownership

### Key Tables

- Core: `users`, `municipalities`, `offices`, `office_users` (pivot), `service_categories`, `services`, `service_requests`, `request_documents`, `request_status_logs`, `appointments`, `feedback`, `messages`
- Support: `support_tickets`, `support_ticket_messages`
- `service_requests.assigned_to` (nullable FK users) + `due_at` (timestamp) — added 2026-05-19 for staff assignment + SLA tracking
- Standard Laravel: `sessions`, `jobs`, `failed_jobs`, `cache`, `password_reset_tokens`

### Key Integrations

| Integration | Used For | Notes |
|-------------|----------|-------|
| Stripe | Card payments | Synchronous return URL + `POST /webhooks/stripe` (HMAC-SHA256) |
| NOWPayments | Crypto (BTC/ETH/USDT) | Hosted invoice + `POST /webhooks/nowpayments` (HMAC-SHA512, ksorted) |
| exchangerate-api | USD/LBP/EUR FX | 10-min cache + fallback rates |
| CoinGecko | Live crypto rates display | Free tier, no key required |
| Google + GitHub OAuth | Social login | via Laravel Socialite; OAuth users auto-verified email |
| Pusher | Realtime broadcasting | Falls back to `BROADCAST_CONNECTION=log` if not configured |
| Twilio WhatsApp | Phone OTP | Sandbox in dev — recipient opts in once via `join <code>` |
| Azure Document Intelligence | National ID OCR | Falls back to OCR.space if endpoint/key blank |
| OCR.space | Fallback ID OCR | Free tier |
| Groq | AI chatbot | Llama 3.3 70B, free tier; widget auto-hides if no key set |
| Google Maps | Office locator | Marker map on `citizen/offices/index` and `show` |
| DomPDF + SimpleSoftwareIO/qrcode | PDFs + QR | |

### Internationalisation

- `lang/en.json` + `lang/ar.json` — high-visibility UI strings (~45 keys)
- Switcher: `GET /locale/{lang}` writes `session('locale')`, supported `en|ar`
- Layout sets `<html dir="rtl">` when locale is `ar`; CSS overrides for RTL margin/sidebar/breadcrumb at top of `views/layouts/app.blade.php`
- Translate visible strings with `{{ __('Some string') }}` — the key is the English source

### Email Verification

- `User` model implements `MustVerifyEmail`
- `AuthController::register()` fires `event(new Registered($user))` → Laravel auto-sends signed verification email
- OAuth callback sets `email_verified_at = now()` so social signups skip the flow
- Non-blocking amber banner in the layout when unverified; routes: `verification.notice` / `verification.verify` / `verification.send`

### Appointment Slot Management

- `Office::availableSlotsForDate(Carbon $date): array` parses `working_hours` JSON (`{"mon": "08:00-16:00", "sat": "closed", ...}`) into 30-min slots, removes past slots if today, removes already-booked slots
- `GET /citizen/offices/{office}/slots?date=Y-m-d` returns `{date, slots}` JSON
- Citizen booking forms (in `citizen/requests/show.blade.php` modal and `citizen/offices/show.blade.php`) use vanilla JS data-attribute pattern (`data-slots-form` / `data-slots-date` / `data-slots-select`) to populate the dropdown on date change
- `CitizenController::bookAppointment()` re-validates server-side that the chosen slot is in `availableSlotsForDate()`

### CSV Export (admin)

- `AdminController::exportReport(string $type)` — `type` in `requests|payments|offices`
- Uses `streamDownload()` + Eloquent `lazy()` cursor (memory-safe for large datasets)
- Prepends UTF-8 BOM so Excel renders Arabic / accented names correctly
- Routes: `GET /admin/reports/export/{type}` — buttons live on `admin/reports.blade.php`

### Request Resubmission Flow

When an office sets status to `missing_documents` or `rejected`, the citizen sees an amber action card on `citizen/requests/show.blade.php` allowing them to:
- Upload replacement documents (multiple, ≤10 MB each, jpg/png/pdf)
- Add an optional note explaining the correction
- Submit → status returns to `pending`, `office_notes` cleared, `request_status_logs` row written, `RequestResubmitted` notification fans out to office users

Route: `POST /citizen/requests/{serviceRequest}/resubmit` (`citizen.requests.resubmit`), inside the `citizen.identity.approved + citizen.profile.complete` middleware group.

### Seeded Demo Accounts

- Admin: `admin@eservices.gov` / `password`
- Office Manager: `manager@beirut.gov.lb` / `password`
- Citizen: `citizen1@test.com` / `password`

## Branch Strategy

- `main` — stable / production (deploys to Heroku → cedargov.app)
- `dev` — integration branch
- Feature branches:
  - `feature/auth-realtime` (Rony — team lead, auth, realtime)
  - `feature/admin-panel` (Ahmad)
  - `feature/office-panel` (Hasouna)
  - `feature/citizen-portal` (Hussein — also owns the shared design system)
  - `feature/chat-maps-ui` (Maged)
- PRs go feature → `dev`, then `dev` → `main` after validation. Direct pushes to `main` are blocked.

## Common Gotchas

- **Blade `@js` quoting:** `@js($value)` outputs a single-quoted string. Use inside double-quoted attributes: `onclick="fn(@js($x))"`. For single-quoted attributes use `@json($x)` instead. Getting this wrong produces broken HTML + a JS `Unexpected end of input` error at runtime.
- **`onclick` Blade in VSCode:** the TypeScript parser misreads `{{ }}` inside `onclick=` as broken JS. These are false positives — verify the rendered HTML, not the IDE diagnostic.
- **CSS file duplication:** `resources/css/app.css` is bundled by Vite, but `public/css/app.css` is loaded directly via `asset()` in `views/layouts/app.blade.php` (with `?v={filemtime}` cache-buster). Keep both in sync when editing the design system.
- **Modals + global z-index:** never apply a global `> * { z-index: ... }` under a container that hosts `@yield('content')` — modals fixed-positioned inside that container will be trapped in its stacking context. Always exclude `.modal:not(...)` from such rules (see existing per-role rules in `views/layouts/app.blade.php`).
- **Form submit + `onsubmit="return confirm(...)"`:** any `addEventListener('submit', ...)` with side effects must check `event.defaultPrevented` first — otherwise a cancelled confirm still runs the listener and leaves the page in a broken "busy" state.
