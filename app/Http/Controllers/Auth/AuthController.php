<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\TwoFactorCodeMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // ─────────────────────────────────────────────
    //  REGISTER
    // ─────────────────────────────────────────────

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'email', 'unique:users'],
            'password'              => ['required', 'min:8', 'confirmed'],
            'password_confirmation' => ['required'],
        ]);

        $user = User::create([
            'name'     => trim($validated['name']),
            'email'    => strtolower(trim($validated['email'])),
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole('parishioner');

        return redirect()->route('login')
            ->with('status', 'Account created successfully! Please sign in to continue.');
    }

    // ─────────────────────────────────────────────
    //  LOGIN  (Step 1 — credentials)
    // ─────────────────────────────────────────────

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $credentials['email'] = strtolower(trim($credentials['email']));

        // Rate limit: 5 login attempts per minute per IP+email
        $throttleKey = 'login:' . $credentials['email'] . '|' . $request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        if (!Auth::validate($credentials)) {
            RateLimiter::hit($throttleKey, 60);
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($throttleKey);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => 'Your account has been deactivated. Please contact the parish office.',
            ]);
        }

        // Admins log in directly — no OTP required
        if ($user->hasRole(['super_admin', 'parish_secretary', 'finance_officer'])) {
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();
            $user->update(['last_login_at' => now()]);
            return redirect()->intended(route('admin.dashboard'));
        }

        // Parishioners — generate OTP and send via Resend HTTP API
        $plainCode = $user->generateTwoFactorCode();  // returns plaintext for email only

        // Store only what is needed in session — NEVER store the OTP
        $request->session()->put('2fa_user_id', $user->id);
        $request->session()->put('2fa_remember', $request->boolean('remember'));
        $request->session()->put('2fa_masked_email', $this->maskEmail($user->email));
        $request->session()->forget('2fa_attempts'); // reset attempt counter

        // Send via Resend HTTP API (MAIL_MAILER=resend in .env)
        try {
            Mail::to($user->email)->send(new TwoFactorCodeMail($user, $plainCode));
            Log::info('2FA OTP sent', ['user_id' => $user->id]);
        } catch (\Exception $e) {
            // Log the error without the OTP or API key
            Log::error('2FA email send failed', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            // Clear the generated OTP — user must retry
            $user->clearTwoFactorCode();
            $request->session()->forget(['2fa_user_id', '2fa_remember', '2fa_masked_email']);

            // Block login — do NOT show OTP — ask user to retry
            return back()->withErrors([
                'email' => 'Unable to send the verification code. Please try again in a moment.',
            ]);
        }

        return redirect()->route('2fa.show');
    }

    // ─────────────────────────────────────────────
    //  2FA — Show OTP form (parishioners only)
    // ─────────────────────────────────────────────

    public function show2fa(Request $request)
    {
        if (!$request->session()->has('2fa_user_id')) {
            return redirect()->route('login');
        }

        $maskedEmail = $request->session()->get('2fa_masked_email', '');

        // Pass only the masked email — no OTP, no dev code, nothing sensitive
        return view('auth.two-factor', compact('maskedEmail'));
    }

    // ─────────────────────────────────────────────
    //  2FA — Verify OTP (parishioners only)
    // ─────────────────────────────────────────────

    public function verify2fa(Request $request)
    {
        $request->validate(['code' => ['required', 'string', 'size:6', 'regex:/^\d{6}$/']]);

        $userId = $request->session()->get('2fa_user_id');
        if (!$userId) {
            return redirect()->route('login')
                ->withErrors(['code' => 'Session expired. Please sign in again.']);
        }

        // Rate limit: max 5 failed attempts per session (per OTP)
        $throttleKey = '2fa_verify:' . $userId;
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $request->session()->forget(['2fa_user_id', '2fa_remember', '2fa_masked_email']);
            return redirect()->route('login')
                ->withErrors(['email' => 'Too many failed attempts. Please sign in again.']);
        }

        $user = User::find($userId);
        if (!$user) {
            return redirect()->route('login')
                ->withErrors(['code' => 'User not found. Please sign in again.']);
        }

        if (!$user->validateTwoFactorCode($request->input('code'))) {
            RateLimiter::hit($throttleKey, 300); // 5 minute window
            return back()->withErrors(['code' => 'Invalid or expired verification code. Please try again.']);
        }

        // OTP valid — clear it immediately to prevent reuse
        RateLimiter::clear($throttleKey);
        $user->clearTwoFactorCode();
        $user->update(['last_login_at' => now()]);

        $remember = $request->session()->pull('2fa_remember', false);
        $request->session()->forget(['2fa_user_id', '2fa_masked_email', '2fa_attempts']);

        Auth::login($user, $remember);
        $request->session()->regenerate();

        Log::info('2FA verified, user logged in', ['user_id' => $user->id]);

        return redirect()->intended(route('parishioner.dashboard'));
    }

    // ─────────────────────────────────────────────
    //  2FA — Resend OTP
    // ─────────────────────────────────────────────

    public function resend2fa(Request $request)
    {
        $userId = $request->session()->get('2fa_user_id');
        if (!$userId) return redirect()->route('login');

        $user = User::find($userId);
        if (!$user) return redirect()->route('login');

        // Rate limit: 1 resend per 60 seconds per user
        $throttleKey = '2fa_resend:' . $userId;
        if (RateLimiter::tooManyAttempts($throttleKey, 1)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'code' => "Please wait {$seconds} seconds before requesting a new code.",
            ]);
        }

        // Generate fresh OTP — invalidates the previous one
        $plainCode = $user->generateTwoFactorCode();
        $request->session()->forget('2fa_attempts'); // reset verify attempt counter

        try {
            Mail::to($user->email)->send(new TwoFactorCodeMail($user, $plainCode));
            RateLimiter::hit($throttleKey, 60); // 60 second cooldown
            Log::info('2FA OTP resent', ['user_id' => $user->id]);
            return back()->with('resent', 'A new verification code has been sent to your email.');
        } catch (\Exception $e) {
            Log::error('2FA resend failed', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);
            // Clear OTP — do not expose it
            $user->clearTwoFactorCode();
            return back()->withErrors([
                'code' => 'Unable to send the verification code. Please go back and sign in again.',
            ]);
        }
    }

    // ─────────────────────────────────────────────
    //  LOGOUT
    // ─────────────────────────────────────────────

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }

    // ─────────────────────────────────────────────
    //  PASSWORD RESET
    // ─────────────────────────────────────────────

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = \Illuminate\Support\Facades\Password::sendResetLink(
            $request->only('email')
        );

        return $status === \Illuminate\Support\Facades\Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    public function showResetPassword(Request $request, string $token)
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->email]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'                 => ['required'],
            'email'                 => ['required', 'email'],
            'password'              => ['required', 'min:8', 'confirmed'],
            'password_confirmation' => ['required'],
        ]);

        $status = \Illuminate\Support\Facades\Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill(['password' => Hash::make($password)])
                     ->setRememberToken(\Illuminate\Support\Str::random(60));
                $user->save();
                event(new \Illuminate\Auth\Events\PasswordReset($user));
            }
        );

        return $status === \Illuminate\Support\Facades\Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    // ─────────────────────────────────────────────
    //  HELPERS
    // ─────────────────────────────────────────────

    private function maskEmail(string $email): string
    {
        [$local, $domain] = explode('@', $email, 2);
        $masked = substr($local, 0, 2) . str_repeat('*', max(0, strlen($local) - 2));
        return $masked . '@' . $domain;
    }
}
