<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Office\OfficeController;
use App\Http\Controllers\Citizen\CitizenController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

// ── Public ────────────────────────────────────────────────────────────────────

Route::get('/', function () {
    if (!auth()->check()) {
        return view('welcome');
    }

    if (auth()->user()->role === 'citizen' && ($message = auth()->user()->citizenActionRestrictionMessage())) {
        return redirect()->route('citizen.dashboard')
            ->with(auth()->user()->hasCompletedCitizenProfile() ? 'warning' : 'info', $message);
    }

    return match (auth()->user()->role) {
        'admin' => redirect()->route('admin.dashboard'),
        'office_user' => redirect()->route('office.dashboard'),
        default => redirect()->route('citizen.dashboard'),
    };
})->name('home');
Route::get('/track/{reference}', [CitizenController::class, 'trackByQr'])->name('citizen.track');

// Language switcher — anyone (auth or guest) can switch locale.
Route::get('/locale/{lang}', function (string $lang) {
    if (in_array($lang, \App\Http\Middleware\SetLocale::SUPPORTED, true)) {
        session(['locale' => $lang]);
    }
    return back();
})->name('locale.switch');

// Public webhooks (no auth, no CSRF — see bootstrap/app.php for CSRF exclusion).
Route::post('/webhooks/nowpayments', [WebhookController::class, 'nowpayments'])->name('webhooks.nowpayments');
Route::post('/webhooks/stripe',      [WebhookController::class, 'stripe'])->name('webhooks.stripe');

// ── Auth ──────────────────────────────────────────────────────────────────────

Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',   [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register/id-extract', [AuthController::class, 'extractNationalIdDocument'])->name('register.id-extract');
    Route::post('/register',[AuthController::class, 'register'])->middleware('throttle:5,1');

    Route::get('/2fa',  [AuthController::class, 'show2FA'])->name('2fa.verify');
    Route::post('/2fa', [AuthController::class, 'verify2FA'])->middleware('throttle:10,1');

    // Password reset
    Route::get('/forgot-password',        [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password',       [AuthController::class, 'sendResetLink'])->name('password.email')->middleware('throttle:5,1');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password',        [AuthController::class, 'resetPassword'])->name('password.update');

    Route::get('/auth/{provider}',          [AuthController::class, 'redirectToProvider'])->name('social.redirect');
    Route::get('/auth/{provider}/callback', [AuthController::class, 'handleProviderCallback'])->name('social.callback');
    Route::get('/auth/social/password',     [AuthController::class, 'showSocialPasswordForm'])->name('social.password.form');
    Route::post('/auth/social/password',    [AuthController::class, 'storeSocialPassword'])->name('social.password.store');
});

// Email verification (built-in Laravel — uses signed URLs).
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (\Illuminate\Foundation\Auth\EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect()->route('home')->with('success', 'Your email has been verified.');
    })->middleware('signed')->name('verification.verify');

    Route::post('/email/verification-notification', function (\Illuminate\Http\Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('success', 'A fresh verification link has been sent.');
    })->middleware('throttle:6,1')->name('verification.send');
});

Route::middleware('auth')->group(function () {
    Route::post('/notifications/read-all', function () {
        auth()->user()->unreadNotifications->markAsRead();
        return response()->json(['success' => true]);
    })->name('notifications.readAll');
    Route::get('/security/2fa', [AuthController::class, 'show2FASettings'])->name('security.2fa');
    Route::post('/security/2fa/enable', [AuthController::class, 'enable2FA'])->name('security.2fa.enable');
    Route::post('/security/2fa/disable', [AuthController::class, 'disable2FA'])->name('security.2fa.disable');
    Route::post('/security/2fa/regenerate', [AuthController::class, 'regenerate2FASecret'])->name('security.2fa.regenerate');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// ── Admin ─────────────────────────────────────────────────────────────────────

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    Route::get('/municipalities',                   [AdminController::class, 'municipalities'])->name('municipalities');
    Route::post('/municipalities',                  [AdminController::class, 'storeMunicipality'])->name('municipalities.store');
    Route::put('/municipalities/{municipality}',    [AdminController::class, 'updateMunicipality'])->name('municipalities.update');
    Route::delete('/municipalities/{municipality}', [AdminController::class, 'destroyMunicipality'])->name('municipalities.destroy');

    Route::get('/offices',              [AdminController::class, 'offices'])->name('offices');
    Route::post('/offices',             [AdminController::class, 'storeOffice'])->name('offices.store');
    Route::put('/offices/{office}',     [AdminController::class, 'updateOffice'])->name('offices.update');
    Route::delete('/offices/{office}',  [AdminController::class, 'destroyOffice'])->name('offices.destroy');

    Route::get('/users',                        [AdminController::class, 'users'])->name('users');
    Route::post('/users/office-user',           [AdminController::class, 'createOfficeUser'])->name('users.office.create');
    Route::patch('/users/{user}',               [AdminController::class, 'updateUser'])->name('users.update');
    Route::patch('/users/{user}/toggle-status', [AdminController::class, 'toggleUserStatus'])->name('users.toggle');
    Route::get('/users/{user}/identity-document', [AdminController::class, 'downloadCitizenIdentityDocument'])->name('users.identity.document');
    Route::patch('/users/{user}/identity/approve', [AdminController::class, 'approveCitizenIdentity'])->name('users.identity.approve');
    Route::patch('/users/{user}/identity/reject', [AdminController::class, 'rejectCitizenIdentity'])->name('users.identity.reject');

    Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
    Route::get('/reports/export/{type}', [AdminController::class, 'exportReport'])
        ->whereIn('type', ['requests', 'payments', 'offices'])
        ->name('reports.export');

    // Support tickets
    Route::get('/support',                            [AdminController::class, 'supportIndex'])->name('support');
    Route::get('/support/{ticket}',                   [AdminController::class, 'supportShow'])->name('support.show');
    Route::post('/support/{ticket}/reply',            [AdminController::class, 'supportReply'])->name('support.reply');
    Route::patch('/support/{ticket}/close',           [AdminController::class, 'supportClose'])->name('support.close');
    Route::patch('/support/{ticket}/reopen',          [AdminController::class, 'supportReopen'])->name('support.reopen');
});

// ── Office ────────────────────────────────────────────────────────────────────

Route::middleware(['auth', 'role:office_user'])->prefix('office')->name('office.')->group(function () {
    Route::get('/dashboard', [OfficeController::class, 'dashboard'])->name('dashboard');
    Route::get('/requests/{serviceRequest}/messages', [OfficeController::class, 'getMessages'])
    ->name('messages.get');

    Route::get('/profile',  [OfficeController::class, 'editProfile'])->name('profile');
    Route::put('/profile',  [OfficeController::class, 'updateProfile'])->name('profile.update');

    Route::get('/services',               [OfficeController::class, 'services'])->name('services');
    Route::post('/services',              [OfficeController::class, 'storeService'])->name('services.store');
    Route::put('/services/{service}',     [OfficeController::class, 'updateService'])->name('services.update');
    Route::delete('/services/{service}',  [OfficeController::class, 'destroyService'])->name('services.destroy');

    Route::get('/requests',[OfficeController::class, 'requests'])->name('requests');
    Route::get('/requests/{serviceRequest}',          [OfficeController::class, 'showRequest'])->name('requests.show');
    Route::patch('/requests/{serviceRequest}/status', [OfficeController::class, 'updateRequestStatus'])->name('requests.status');
    Route::patch('/requests/{serviceRequest}/assign', [OfficeController::class, 'assignRequest'])->name('requests.assign');

    // PDF generation & download
    Route::get('/requests/{serviceRequest}/pdf/{type}', [OfficeController::class, 'downloadPdf'])->name('requests.pdf');

    Route::post('/requests/{serviceRequest}/messages', [OfficeController::class, 'sendMessage'])->name('messages.send');

    Route::get('/feedback',                  [OfficeController::class, 'feedback'])->name('feedback');
    Route::patch('/feedback/{feedback}/reply', [OfficeController::class, 'replyFeedback'])->name('feedback.reply');

    Route::get('/appointments',                 [OfficeController::class, 'appointments'])->name('appointments');
    Route::patch('/appointments/{appointment}', [OfficeController::class, 'updateAppointment'])->name('appointments.update');
    Route::post('/requests/{serviceRequest}/messages/read', [OfficeController::class, 'markMessagesRead'])->name('messages.read');
});

// ── Citizen ───────────────────────────────────────────────────────────────────

Route::middleware(['auth', 'role:citizen'])->prefix('citizen')->name('citizen.')->group(function () {
    Route::get('/dashboard', [CitizenController::class, 'dashboard'])->name('dashboard');
    Route::get('/requests/{serviceRequest}/messages', [CitizenController::class, 'getMessages'])->name('messages.get');

    // Profile
    Route::get('/profile',  [CitizenController::class, 'profile'])->name('profile');
    Route::put('/profile',  [CitizenController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/avatar', [CitizenController::class, 'updateAvatar'])->name('profile.avatar');
    Route::post('/profile/phone/send-otp',   [CitizenController::class, 'sendPhoneOtp'])
        ->middleware('throttle:5,1')
        ->name('profile.phone.send');
    Route::post('/profile/phone/verify-otp', [CitizenController::class, 'verifyPhoneOtp'])
        ->middleware('throttle:10,1')
        ->name('profile.phone.verify');
    Route::post('/profile/id-extract', [AuthController::class, 'extractNationalIdDocument'])->name('profile.id-extract');

    // Browse
    Route::get('/offices',          [CitizenController::class, 'browseOffices'])->name('offices');
    Route::get('/offices/{office}', [CitizenController::class, 'showOffice'])->name('offices.show');
    Route::get('/offices/{office}/slots', [CitizenController::class, 'availableSlots'])->name('offices.slots');
    Route::get('/services/{service}', [CitizenController::class, 'showService'])->name('services.show');

    Route::middleware(['citizen.identity.approved', 'citizen.profile.complete'])->group(function () {
        // Submit request
        Route::post('/services/{service}/request', [CitizenController::class, 'submitRequest'])->name('requests.submit');

        // Payment
        Route::post('/requests/{serviceRequest}/payment', [CitizenController::class, 'processPayment'])->name('payment.process');

        // Appointments
        Route::post('/appointments', [CitizenController::class, 'bookAppointment'])->name('appointments.book');
    });

    // Payment
    Route::get('/requests/{serviceRequest}/payment',  [CitizenController::class, 'showPayment'])->name('payment');
    Route::get('/requests/{serviceRequest}/payment/success',  [CitizenController::class, 'paymentSuccess'])->name('payment.success');
    Route::get('/requests/{serviceRequest}/payment/cancel',   [CitizenController::class, 'paymentCancel'])->name('payment.cancel');

    // Resubmit after missing documents / rejection
    Route::post('/requests/{serviceRequest}/resubmit', [CitizenController::class, 'resubmitDocuments'])->name('requests.resubmit');

    // Appointments follow-up
    Route::patch('/appointments/{appointment}/cancel', [CitizenController::class, 'cancelAppointment'])->name('appointments.cancel');

    // Feedback
    Route::post('/feedback', [CitizenController::class, 'submitFeedback'])->name('feedback.submit');
    Route::post('/requests/{serviceRequest}/messages/read', [CitizenController::class, 'markMessagesRead'])->name('messages.read');

    // My requests
    Route::get('/requests',                   [CitizenController::class, 'myRequests'])->name('requests');
    Route::get('/requests/{serviceRequest}',  [CitizenController::class, 'showRequest'])->name('requests.show');

    // Appointments & Payments (dedicated pages)
    Route::get('/appointments', [CitizenController::class, 'myAppointments'])->name('appointments');
    Route::get('/payments',     [CitizenController::class, 'myPayments'])->name('payments');

    // Messages
    Route::post('/requests/{serviceRequest}/messages', [CitizenController::class, 'sendMessage'])->name('messages.send');

    // Documents
    Route::get('/requests/{serviceRequest}/documents/{docId}', [CitizenController::class, 'downloadDocument'])->name('documents.download');

    // PDF receipt download
    Route::get('/requests/{serviceRequest}/receipt', [CitizenController::class, 'downloadReceipt'])->name('requests.receipt');

    // AI chatbot — 30 req/min per citizen
    Route::post('/chatbot', [CitizenController::class, 'chatbotAsk'])->middleware('throttle:30,1')->name('chatbot.ask');

    // Support tickets
    Route::get('/support',                  [CitizenController::class, 'supportIndex'])->name('support');
    Route::get('/support/create',           [CitizenController::class, 'supportCreate'])->name('support.create');
    Route::post('/support',                 [CitizenController::class, 'supportStore'])->name('support.store');
    Route::get('/support/{ticket}',         [CitizenController::class, 'supportShow'])->name('support.show');
    Route::post('/support/{ticket}/reply',  [CitizenController::class, 'supportReply'])->name('support.reply');
});
