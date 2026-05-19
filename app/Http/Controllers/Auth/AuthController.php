<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\RegistrationConfirmation;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{Auth, Hash, Http, Log, Password};
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use PragmaRX\Google2FA\Google2FA;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Throwable;

class AuthController extends Controller
{
    // ── Registration ─────────────────────────────────────────────
    public function showRegister()
    {
        // Already logged-in users go straight to their dashboard
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $rules = [
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'national_id' => 'required|string|max:20',
            'phone'    => 'nullable|string|max:20',
        ];

        if ($request->filled('first_name') || $request->filled('last_name')) {
            $rules['first_name'] = 'required|string|max:50';
            $rules['last_name']  = 'required|string|max:50';
        } else {
            $rules['name'] = 'required|string|max:100';
        }

        $docField = $request->hasFile('national_id_document') ? 'national_id_document' : 'national_id_doc';
        $rules[$docField] = 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120';

        $data = $request->validate($rules);

        $docPath = null;
        if ($request->hasFile($docField)) {
            $docPath = $request->file($docField)->store('id_documents', 'private');
        }

        $fullName = isset($data['first_name'])
            ? trim($data['first_name'] . ' ' . $data['last_name'])
            : $data['name'];

        $user = User::create([
            'name'        => $fullName,
            'email'       => $data['email'],
            'password'    => Hash::make($data['password']),
            'national_id' => $data['national_id'],
            'phone'       => $data['phone'] ?? null,
            'id_document' => $docPath,   // ← correct column name
            'citizen_verification_status' => 'pending',
            'role'        => 'citizen',
            'is_active'   => true,
        ]);

        $user->notify(new RegistrationConfirmation());

        // Triggers SendEmailVerificationNotification → email with signed verify link.
        event(new Registered($user));

        Auth::login($user);
        $request->session()->regenerate();
        return $this->redirectByRole($user)
                    ->with('success', 'Welcome to E-Services! Check your inbox to verify your email.');
    }

    public function extractNationalIdDocument(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'national_id_document' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $endpoint = (string) config('services.azure_document_intelligence.endpoint');
        $key = (string) config('services.azure_document_intelligence.key');
        $apiVersion = (string) config('services.azure_document_intelligence.api_version', '2024-11-30');
        $timeout = (int) config('services.azure_document_intelligence.timeout_seconds', 20);
        $pollAttempts = max(1, (int) config('services.azure_document_intelligence.poll_attempts', 15));
        $pollIntervalMs = max(200, (int) config('services.azure_document_intelligence.poll_interval_ms', 900));

        /** @var UploadedFile $file */
        $file = $validated['national_id_document'];

        if (blank($endpoint) || blank($key)) {
            if (filled(config('services.ocr_space.api_key'))) {
                return $this->extractNationalIdWithOcrSpace($file);
            }

            return response()->json([
                'message' => 'ID extraction is not configured. Add Azure keys or OCR_SPACE_API_KEY in .env.',
            ], 422);
        }

        $binary = file_get_contents($file->getRealPath());
        $mimeType = $file->getMimeType() ?: 'application/octet-stream';

        $analyzeUrl = rtrim($endpoint, '/') . '/documentintelligence/documentModels/prebuilt-idDocument:analyze?api-version=' . urlencode($apiVersion);

        try {
            $analyzeResponse = Http::withHeaders([
                'Ocp-Apim-Subscription-Key' => $key,
            ])
            ->timeout($timeout)
            ->withBody($binary, $mimeType)
            ->post($analyzeUrl);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'Could not connect to Azure Document Intelligence.',
            ], 500);
        }

        if (!$analyzeResponse->successful()) {
            return response()->json([
                'message' => $this->extractAzureErrorMessage($analyzeResponse->json()) ?? 'Azure rejected the document analysis request.',
            ], 422);
        }

        $analyzeResult = $analyzeResponse->json('analyzeResult');
        $operationLocation = $analyzeResponse->header('Operation-Location') ?: $analyzeResponse->header('operation-location');

        if (!$analyzeResult) {
            if (blank($operationLocation)) {
                return response()->json([
                    'message' => 'Azure response was missing the operation location.',
                ], 422);
            }

            $analyzeResult = $this->pollAzureAnalysisResult($operationLocation, $key, $timeout, $pollAttempts, $pollIntervalMs);
        }

        if (!is_array($analyzeResult)) {
            return response()->json([
                'message' => 'Azure analysis did not complete. Try a clearer front-side ID image.',
            ], 422);
        }

        $document = data_get($analyzeResult, 'documents.0', []);
        $fields = is_array(data_get($document, 'fields')) ? data_get($document, 'fields') : [];

        $firstName = $this->extractAzureFieldString($fields, [
            'FirstName', 'GivenName', 'GivenNames',
        ]);

        $lastName = $this->extractAzureFieldString($fields, [
            'LastName', 'Surname', 'FamilyName',
        ]);

        $nationalId = $this->extractAzureFieldString($fields, [
            'DocumentNumber', 'NationalId', 'NationalID', 'IdNumber', 'IDNumber',
        ]);

        if ($nationalId) {
            $nationalId = preg_replace('/\D/u', '', $this->normalizeUnicodeDigits($nationalId));
        }

        $content = (string) data_get($analyzeResult, 'content', '');
        if (!$nationalId && $content !== '') {
            $nationalId = $this->extractIdFromContent($content);
        }

        if (!$firstName || !$lastName) {
            $fullName = $this->extractAzureFieldString($fields, ['FullName', 'Name']);
            if ($fullName) {
                $parts = preg_split('/\s+/u', trim($fullName), -1, PREG_SPLIT_NO_EMPTY) ?: [];
                if (!$firstName && isset($parts[0])) {
                    $firstName = $parts[0];
                }
                if (!$lastName && count($parts) > 1) {
                    $lastName = $parts[count($parts) - 1];
                }
            }
        }

        $payload = [
            'national_id' => $nationalId,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'document_type' => data_get($document, 'docType'),
            'confidence' => data_get($document, 'confidence'),
        ];

        $hasExtractedValue = filled($nationalId) || filled($firstName) || filled($lastName);

        return response()->json([
            'message' => $hasExtractedValue
                ? 'ID fields extracted successfully.'
                : 'No clear fields were found. Please fill the form manually.',
            'data' => $payload,
        ]);
    }

    // ── Login ─────────────────────────────────────────────────────
    public function showLogin()
    {
        // Already logged-in users go straight to their dashboard
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'These credentials do not match our records.'])
                ->onlyInput('email');
        }

        $user = Auth::user();

        if (!$user->is_active) {
            Auth::logout();
            return back()->withErrors(['email' => 'Your account has been deactivated. Contact support.']);
        }

        // 2FA check
        if ($user->two_factor_secret && $user->two_factor_enabled) {
            session(['2fa_user_id' => $user->id]);
            Auth::logout();
            return redirect()->route('2fa.verify');
        }

        $request->session()->regenerate();
        return $this->redirectByRole($user);
    }

    // ── 2FA ───────────────────────────────────────────────────────
    public function show2FA()
    {
        if (!session('2fa_user_id')) return redirect()->route('login');
        return view('auth.2fa');
    }

    public function verify2FA(Request $request)
    {
        // Accept both "otp" and legacy "otp_code" from the 2FA form.
        $request->merge([
            'otp' => $request->input('otp', $request->input('otp_code')),
        ]);

        $request->validate(['otp' => 'required|digits:6']);
        $user = User::findOrFail(session('2fa_user_id'));

        $g2fa = new Google2FA();
        if (!$g2fa->verifyKey($user->two_factor_secret, $request->otp)) {
            return back()->withErrors(['otp' => 'Invalid code. Please try again.']);
        }

        session()->forget('2fa_user_id');
        Auth::login($user);
        $request->session()->regenerate();
        return $this->redirectByRole($user);
    }

    public function show2FASettings()
    {
        $user = Auth::user();

        if ($user->two_factor_enabled && $user->two_factor_secret) {
            session()->forget('2fa_setup_secret');

            return view('auth.2fa-settings', [
                'isEnabled' => true,
                'manualKey' => null,
                'qrSvg'     => null,
            ]);
        }

        $secret = session('2fa_setup_secret');
        if (!$secret) {
            $secret = (new Google2FA())->generateSecretKey();
            session(['2fa_setup_secret' => $secret]);
        }

        $issuer = config('app.name', 'E-Services');
        $label = rawurlencode($issuer . ':' . $user->email);
        $issuerEncoded = rawurlencode($issuer);
        $otpAuthUrl = "otpauth://totp/{$label}?secret={$secret}&issuer={$issuerEncoded}&algorithm=SHA1&digits=6&period=30";

        return view('auth.2fa-settings', [
            'isEnabled' => false,
            'manualKey' => $secret,
            'qrSvg'     => QrCode::format('svg')->size(220)->margin(1)->generate($otpAuthUrl),
        ]);
    }

    public function enable2FA(Request $request)
    {
        $request->merge([
            'otp' => $request->input('otp', $request->input('otp_code')),
        ]);

        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $user = Auth::user();
        $secret = session('2fa_setup_secret');

        if (!$secret) {
            return redirect()->route('security.2fa')
                ->withErrors(['otp' => '2FA setup expired. Please scan the QR code again.']);
        }

        $g2fa = new Google2FA();
        if (!$g2fa->verifyKey($secret, $request->input('otp'))) {
            return back()->withErrors(['otp' => 'Invalid code. Please try again.']);
        }

        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_enabled' => true,
        ])->save();

        session()->forget('2fa_setup_secret');

        return redirect()->route('security.2fa')
            ->with('success', 'Two-factor authentication has been enabled.');
    }

    public function disable2FA(Request $request)
    {
        $request->merge([
            'otp' => $request->input('otp', $request->input('otp_code')),
        ]);

        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $user = Auth::user();

        if (!$user->two_factor_enabled || !$user->two_factor_secret) {
            return redirect()->route('security.2fa')
                ->withErrors(['otp' => 'Two-factor authentication is not enabled.']);
        }

        $g2fa = new Google2FA();
        if (!$g2fa->verifyKey($user->two_factor_secret, $request->input('otp'))) {
            return back()->withErrors(['otp' => 'Invalid code. Please try again.']);
        }

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_enabled' => false,
        ])->save();

        session()->forget('2fa_setup_secret');

        return redirect()->route('security.2fa')
            ->with('success', 'Two-factor authentication has been disabled.');
    }

    public function regenerate2FASecret()
    {
        $user = Auth::user();

        if ($user->two_factor_enabled) {
            return redirect()->route('security.2fa')
                ->withErrors(['otp' => 'Disable 2FA first before generating a new secret.']);
        }

        session()->forget('2fa_setup_secret');

        return redirect()->route('security.2fa')
            ->with('info', 'A new authenticator secret has been generated.');
    }

    // ── Social OAuth ──────────────────────────────────────────────
    public function redirectToProvider(string $provider)
    {
        abort_unless(in_array($provider, ['google', 'github']), 404);

        try {
            $driver = Socialite::driver($provider);

            if ($provider === 'github') {
                // Request e-mail scope to improve success rate for GitHub sign-in.
                $driver = $driver->scopes(['read:user', 'user:email']);
            }

            return $driver->redirect();
        } catch (Throwable $e) {
            report($e);

            return redirect()->route('login')
                ->withErrors(['email' => 'Unable to start social login. Please check OAuth configuration.']);
        }
    }

    public function handleProviderCallback(string $provider)
    {
        try {
            abort_unless(in_array($provider, ['google', 'github']), 404);

            $driver = Socialite::driver($provider);
            if ($provider === 'github') {
                $driver = $driver->scopes(['read:user', 'user:email']);
            }

            $socialUser = $driver->user();
        } catch (Throwable $e) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Social login failed. Please try again.']);
        }

        $email = $socialUser->getEmail();
        if (!$email) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Your social account has no accessible email address.']);
        }

        $user = User::where('email', $email)->first();

        if ($user) {
            if (!$user->is_active) {
                return redirect()->route('login')
                    ->withErrors(['email' => 'Your account has been deactivated.']);
            }

            $user->forceFill([
                'social_provider' => $provider,
                'social_id'       => $socialUser->getId(),
                'avatar'          => $socialUser->getAvatar() ?: $user->avatar,
            ]);

            if (is_null($user->email_verified_at)) {
                $user->email_verified_at = now();
            }

            $user->save();

            if (blank($user->password)) {
                session([
                    'social_password_setup' => [
                        'mode'     => 'link-existing',
                        'user_id'  => $user->id,
                        'provider' => $provider,
                        'email'    => $user->email,
                        'name'     => $user->name,
                    ],
                ]);

                return redirect()->route('social.password.form')
                    ->with('info', 'Set a password to complete your account setup.');
            }

            Auth::login($user);
            session()->regenerate();
            return $this->redirectByRole($user);
        }

        session([
            'social_password_setup' => [
                'mode'      => 'create-new',
                'provider'  => $provider,
                'social_id' => $socialUser->getId(),
                'email'     => $email,
                'name'      => $socialUser->getName() ?: 'Social User',
                'avatar'    => $socialUser->getAvatar(),
            ],
        ]);

        return redirect()->route('social.password.form')
            ->with('info', 'Choose a password to finish creating your account.');
    }

    public function showSocialPasswordForm()
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }

        $payload = session('social_password_setup');
        if (!$payload) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Your social signup session expired. Please try again.']);
        }

        return view('auth.social-password', [
            'social' => $payload,
        ]);
    }

    public function storeSocialPassword(Request $request)
    {
        $payload = session('social_password_setup');
        if (!$payload) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Your social signup session expired. Please try again.']);
        }

        $data = $request->validate([
            'password' => 'required|min:8|confirmed',
        ]);

        if (($payload['mode'] ?? null) === 'link-existing') {
            $user = User::find($payload['user_id'] ?? 0);

            if (!$user) {
                session()->forget('social_password_setup');
                return redirect()->route('login')
                    ->withErrors(['email' => 'Account was not found. Please sign in again.']);
            }

            $user->password = Hash::make($data['password']);
            $user->save();
        } else {
            $user = User::create([
                'name'            => $payload['name'] ?? 'Social User',
                'email'           => $payload['email'],
                'password'        => Hash::make($data['password']),
                'role'            => 'citizen',
                'social_provider' => $payload['provider'] ?? null,
                'social_id'       => $payload['social_id'] ?? null,
                'avatar'          => $payload['avatar'] ?? null,
                'citizen_verification_status' => 'pending',
                'is_active'       => true,
                'email_verified_at' => now(),
            ]);

            $user->notify(new RegistrationConfirmation());
        }

        session()->forget('social_password_setup');

        if (!$user->is_active) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Your account has been deactivated.']);
        }

        Auth::login($user);
        $request->session()->regenerate();
        return $this->redirectByRole($user)
            ->with('success', 'Password saved successfully.');
    }

    // ── Password Reset ────────────────────────────────────────────
    public function showForgotPassword()
    {
        if (Auth::check()) return $this->redirectByRole(Auth::user());
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        try {
            $status = Password::sendResetLink($request->only('email'));
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors([
                'email' => 'Unable to send reset email. Check SMTP settings and try again.',
            ]);
        }

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    public function showResetPassword(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill(['password' => Hash::make($password)])
                     ->setRememberToken(Str::random(60));
                $user->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')
                ->with('success', 'Password reset successfully. Please sign in.')
            : back()->withErrors(['email' => __($status)]);
    }

    // ── Logout ────────────────────────────────────────────────────
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')
            ->with('success', 'You have been signed out.');
    }

    // ── Role redirect helper ──────────────────────────────────────
    private function pollAzureAnalysisResult(string $operationLocation, string $key, int $timeout, int $maxAttempts, int $intervalMs): ?array
    {
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            usleep($intervalMs * 1000);

            try {
                $response = Http::withHeaders([
                    'Ocp-Apim-Subscription-Key' => $key,
                ])->timeout($timeout)->get($operationLocation);
            } catch (Throwable $e) {
                report($e);
                return null;
            }

            if (!$response->successful()) {
                Log::warning('Azure ID extraction poll request failed.', [
                    'status' => $response->status(),
                    'attempt' => $attempt,
                ]);
                continue;
            }

            $status = strtolower((string) $response->json('status'));

            if ($status === 'succeeded') {
                $result = $response->json('analyzeResult');
                return is_array($result) ? $result : null;
            }

            if ($status === 'failed') {
                Log::warning('Azure ID extraction reported failed status.', [
                    'attempt' => $attempt,
                    'error' => $this->extractAzureErrorMessage($response->json()),
                ]);
                return null;
            }
        }

        return null;
    }

    private function extractAzureFieldString(array $fields, array $keys): ?string
    {
        foreach ($keys as $key) {
            $field = $fields[$key] ?? null;
            if (!is_array($field)) {
                continue;
            }

            $value = $field['valueString'] ?? $field['content'] ?? null;
            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return null;
    }

    private function extractIdFromContent(string $content): ?string
    {
        $normalizedContent = $this->normalizeUnicodeDigits($content);
        $content = trim((string) preg_replace('/\s+/u', ' ', $normalizedContent));

        if ($content === '') {
            return null;
        }

        // Prefer lines that explicitly mention ID number labels.
        $labelLineCandidates = $this->extractLabeledIdCandidates($normalizedContent);
        if (!empty($labelLineCandidates)) {
            return $this->normalizeLikelyNationalId($labelLineCandidates[0]);
        }

        if (preg_match('/\bLB[-\s]?[0-9A-Z]{6,15}\b/i', $content, $match) === 1) {
            return strtoupper(str_replace(' ', '', $match[0]));
        }

        preg_match_all('/\d[\d\-\s]{6,20}\d/u', $content, $matches);
        $candidates = collect($matches[0] ?? [])
            ->map(fn (string $value) => preg_replace('/\D/u', '', $value))
            ->map(fn (?string $value) => is_string($value) ? $this->normalizeLikelyNationalId($value) : null)
            ->filter(function (?string $value): bool {
                if (!is_string($value) || $value === '') {
                    return false;
                }

                $length = strlen($value);
                if ($length < 6 || $length > 12) {
                    return false;
                }

                // Ignore likely dates (e.g. DOB) such as YYYYMMDD.
                return !$this->looksLikeDateDigits($value);
            })
            ->sortByDesc(fn (string $value) => strlen($value))
            ->values();

        return $candidates->first();
    }

    private function extractAzureErrorMessage(?array $payload): ?string
    {
        if (!is_array($payload)) {
            return null;
        }

        $message = data_get($payload, 'error.message');
        return is_string($message) && trim($message) !== '' ? trim($message) : null;
    }

    private function extractNationalIdWithOcrSpace(UploadedFile $file): JsonResponse
    {
        $endpoint = (string) config('services.ocr_space.endpoint', 'https://api.ocr.space/parse/image');
        $apiKey = (string) config('services.ocr_space.api_key');
        $language = (string) config('services.ocr_space.language', 'auto');
        $engine = (string) config('services.ocr_space.engine', '2');
        $timeout = (int) config('services.ocr_space.timeout_seconds', 20);

        try {
            $response = Http::withHeaders([
                'apikey' => $apiKey,
            ])->timeout($timeout)->asMultipart()->post($endpoint, [
                ['name' => 'language', 'contents' => $language],
                ['name' => 'OCREngine', 'contents' => $engine],
                ['name' => 'isOverlayRequired', 'contents' => 'false'],
                ['name' => 'scale', 'contents' => 'true'],
                [
                    'name' => 'file',
                    'contents' => fopen($file->getRealPath(), 'r'),
                    'filename' => $file->getClientOriginalName(),
                ],
            ]);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'message' => 'Could not connect to OCR service.',
            ], 500);
        }

        if (!$response->successful()) {
            return response()->json([
                'message' => 'OCR service rejected the request.',
            ], 422);
        }

        $body = $response->json();
        if (!is_array($body)) {
            return response()->json([
                'message' => 'OCR service returned an invalid response.',
            ], 422);
        }

        if (($body['IsErroredOnProcessing'] ?? false) === true) {
            $errorMessage = data_get($body, 'ErrorMessage');
            $message = is_array($errorMessage) ? implode(' ', $errorMessage) : (string) $errorMessage;
            if (blank($message)) {
                $message = (string) data_get($body, 'ErrorDetails', 'Could not parse this ID image.');
            }

            return response()->json([
                'message' => trim($message) ?: 'Could not parse this ID image.',
            ], 422);
        }

        $content = collect(data_get($body, 'ParsedResults', []))
            ->map(fn ($result) => is_array($result) ? (string) ($result['ParsedText'] ?? '') : '')
            ->filter(fn (string $value) => trim($value) !== '')
            ->implode("\n");

        $nationalId = $this->extractIdFromContent($content);
        $firstName = $this->extractValueByPatterns($content, [
            '/(?:First\s*Name|Given\s*Name)\s*[:\-]?\s*([^\r\n]+)/iu',
            '/\x{0627}\x{0644}\x{0627}\x{0633}\x{0645}\s*[:\-]?\s*([^\r\n]+)/u',
        ]);
        $lastName = $this->extractValueByPatterns($content, [
            '/(?:Last\s*Name|Surname|Family\s*Name)\s*[:\-]?\s*([^\r\n]+)/iu',
            '/\x{0627}\x{0644}\x{0634}\x{0647}\x{0631}\x{0629}\s*[:\-]?\s*([^\r\n]+)/u',
        ]);

        $payload = [
            'national_id' => $nationalId,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'document_type' => null,
            'confidence' => null,
        ];

        $hasExtractedValue = filled($nationalId) || filled($firstName) || filled($lastName);

        return response()->json([
            'message' => $hasExtractedValue
                ? 'ID fields extracted successfully.'
                : 'No clear fields were found. Please fill the form manually.',
            'data' => $payload,
        ]);
    }

    private function extractValueByPatterns(string $content, array $patterns): ?string
    {
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content, $match) === 1 && isset($match[1])) {
                $value = trim((string) preg_replace('/\s+/u', ' ', (string) $match[1]));
                if ($value !== '') {
                    return $value;
                }
            }
        }

        return null;
    }

    private function normalizeUnicodeDigits(string $value): string
    {
        return (string) preg_replace_callback('/[\x{0660}-\x{0669}\x{06F0}-\x{06F9}]/u', function (array $match): string {
            $ord = mb_ord($match[0], 'UTF-8');
            if ($ord >= 0x0660 && $ord <= 0x0669) {
                return (string) ($ord - 0x0660);
            }

            return (string) ($ord - 0x06F0);
        }, $value);
    }

    private function extractLabeledIdCandidates(string $content): array
    {
        $lines = preg_split('/\R/u', $content) ?: [];
        $candidates = [];

        foreach ($lines as $line) {
            $line = trim((string) $line);
            if ($line === '') {
                continue;
            }

            $hasIdLabel = preg_match('/(?:\bID\b|\bID\s*No\b|National\s*ID|Document\s*No|\x{0631}\x{0642}\x{0645}\s*(?:\x{0627}\x{0644}\x{0647}\x{0648}\x{064A}\x{0629}|\x{0627}\x{0644}\x{0628}\x{0637}\x{0627}\x{0642}\x{0629})?)/iu', $line) === 1;
            if (!$hasIdLabel) {
                continue;
            }

            preg_match_all('/\d[\d\-\s\/]{4,20}\d/u', $line, $matches);
            foreach ($matches[0] ?? [] as $raw) {
                $digits = preg_replace('/\D/u', '', $raw);
                if (!is_string($digits) || $digits === '') {
                    continue;
                }

                $digits = $this->normalizeLikelyNationalId($digits);
                $length = strlen($digits);
                if ($length < 6 || $length > 12) {
                    continue;
                }

                if ($this->looksLikeDateDigits($digits)) {
                    continue;
                }

                $candidates[] = $digits;
            }
        }

        usort($candidates, fn (string $a, string $b) => strlen($b) <=> strlen($a));
        return array_values(array_unique($candidates));
    }

    private function normalizeLikelyNationalId(string $digits): string
    {
        $digits = preg_replace('/\D/u', '', $digits) ?: '';

        // OCR often prepends 02 before the actual Lebanese ID number.
        if (strlen($digits) >= 13 && str_starts_with($digits, '02')) {
            $digits = substr($digits, 2);
        }

        // If still too long, keep the right-most 12 digits.
        if (strlen($digits) > 12) {
            $digits = substr($digits, -12);
        }

        return $digits;
    }

    private function looksLikeDateDigits(string $digits): bool
    {
        if (strlen($digits) !== 8) {
            return false;
        }

        $year = (int) substr($digits, 0, 4);
        $month = (int) substr($digits, 4, 2);
        $day = (int) substr($digits, 6, 2);

        return $year >= 1900 && $year <= 2100 && $month >= 1 && $month <= 12 && $day >= 1 && $day <= 31;
    }

    private function redirectByRole(User $user): \Illuminate\Http\RedirectResponse
    {
        if ($user->role === 'citizen' && !$user->hasCompletedCitizenProfile()) {
            return redirect()->route('citizen.profile')
                ->with('info', 'Please complete your profile before submitting requests.');
        }

        if ($user->role === 'citizen' && !$user->hasVerifiedCitizenIdentity()) {
            $message = $user->isCitizenIdentityRejected()
                ? 'Your National ID document was rejected. Upload a corrected document and wait for admin approval.'
                : 'Your National ID document is pending admin validation. You can use the portal after approval.';

            return redirect()->route('citizen.profile')->with('warning', $message);
        }

        return match ($user->role) {
            'admin'       => redirect()->route('admin.dashboard'),
            'office_user' => redirect()->route('office.dashboard'),
            default       => redirect()->route('citizen.dashboard'),
        };
    }
}
