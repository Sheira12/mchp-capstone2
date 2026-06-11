<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\TwoFactorCodeMail;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole('parishioner');

        // Do NOT auto-login — redirect to login with success message
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

        // Attempt credentials without actually logging in
        if (!Auth::validate($credentials)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $user = User::where('email', $credentials['email'])->first();

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'email' => 'Your account has been deactivated. Please contact the parish office.',
            ]);
        }

        // ALL roles go through 2FA (paper requirement: Objective 6)
        // Parishioners go through 2FA
        $code = $user->generateTwoFactorCode();

        // Store pending user in session (not logged in yet)
        $request->session()->put('2fa_user_id', $user->id);
        $request->session()->put('2fa_remember', $request->boolean('remember'));

        // Determine available channels
        $hasEmail = !empty($user->email);
        $phone    = $user->parishioner?->contact_number;
        $hasSms   = $phone && SmsService::isValidPhNumber($phone);

        // Default channel: email
        $channel = 'email';

        // Send via email
        $emailSent = false;
        if ($hasEmail) {
            try {
                Mail::to($user->email)->send(new TwoFactorCodeMail($user, $code));
                $emailSent = true;
            } catch (\Exception $e) {
                Log::error('2FA email failed: ' . $e->getMessage());
            }
        }

        // Store channel info in session
        $request->session()->put('2fa_channel', $channel);
        $request->session()->put('2fa_has_sms', $hasSms);
        $request->session()->put('2fa_has_email', $hasEmail);

        // In local/dev mode, flash the code so it can be seen without email
        $devCode = null; // Never show code on screen — always send via email

        return redirect()->route('2fa.show')
            ->with('2fa_email', $this->maskEmail($user->email))
            ->with('2fa_phone', $hasSms ? $this->maskPhone($phone) : null)
            ->with('dev_code', $devCode)
            ->with('email_sent', $emailSent);
    }

    // ─────────────────────────────────────────────
    //  2FA  (Step 2 — OTP verification)
    // ─────────────────────────────────────────────

    public function show2fa(Request $request)
    {
        if (!$request->session()->has('2fa_user_id')) {
            return redirect()->route('login');
        }

        $user        = User::find($request->session()->get('2fa_user_id'));
        $maskedEmail = $request->session()->get('2fa_email')
            ?? ($user ? $this->maskEmail($user->email) : '');
        $maskedPhone = $request->session()->get('2fa_phone');
        $hasSms      = $request->session()->get('2fa_has_sms', false);
        $hasEmail    = $request->session()->get('2fa_has_email', true);
        $channel     = $request->session()->get('2fa_channel', 'email');
        $emailSent   = $request->session()->get('email_sent', false);
        $devCode     = $request->session()->get('dev_code');

        return view('auth.two-factor', compact(
            'maskedEmail', 'maskedPhone', 'hasSms', 'hasEmail', 'channel', 'emailSent', 'devCode'
        ));
    }

    public function switchChannel(Request $request)
    {
        if (!$request->session()->has('2fa_user_id')) {
            return redirect()->route('login');
        }

        $request->validate(['channel' => ['required', 'in:email,sms']]);
        $channel = $request->input('channel');

        $user = User::find($request->session()->get('2fa_user_id'));
        if (!$user) return redirect()->route('login');

        // Generate fresh code
        $code = $user->generateTwoFactorCode();
        $request->session()->put('2fa_channel', $channel);

        $sent = false;

        if ($channel === 'sms') {
            $phone = $user->parishioner?->contact_number;
            if ($phone && SmsService::isValidPhNumber($phone)) {
                $sms  = new SmsService();
                $sent = $sms->sendOtp($phone, $code, config('parish.name'));
            }
            $request->session()->put('2fa_phone', $this->maskPhone($phone ?? ''));
        } else {
            try {
                Mail::to($user->email)->send(new TwoFactorCodeMail($user, $code));
                $sent = true;
            } catch (\Exception $e) {
                Log::error('2FA switch email failed: ' . $e->getMessage());
            }
        }

        $devCode = null; // Never show code on screen

        return redirect()->route('2fa.show')
            ->with('2fa_email', $this->maskEmail($user->email))
            ->with('2fa_phone', $request->session()->get('2fa_phone'))
            ->with('dev_code', $devCode)
            ->with('email_sent', $sent)
            ->with('resent', 'Verification code sent via ' . strtoupper($channel) . '.');
    }

    public function verify2fa(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $userId = $request->session()->get('2fa_user_id');

        if (!$userId) {
            return redirect()->route('login')
                ->withErrors(['code' => 'Session expired. Please sign in again.']);
        }

        $user = User::find($userId);

        if (!$user) {
            return redirect()->route('login')
                ->withErrors(['code' => 'User not found. Please sign in again.']);
        }

        // Validate OTP
        if (!$user->validateTwoFactorCode($request->input('code'))) {
            return back()->withErrors([
                'code' => 'Invalid or expired verification code. Please try again.',
            ]);
        }

        // OTP valid — clear it, log the user in
        $user->clearTwoFactorCode();
        $user->update(['last_login_at' => now()]);

        $remember = $request->session()->pull('2fa_remember', false);
        $request->session()->forget('2fa_user_id');
        $request->session()->forget('2fa_email');

        Auth::login($user, $remember);
        $request->session()->regenerate();

        // Redirect based on role
        if ($user->hasRole(['super_admin', 'parish_secretary', 'finance_officer'])) {
            return redirect()->intended(route('admin.dashboard'));
        }

        return redirect()->intended(route('parishioner.dashboard'));    }

    public function resend2fa(Request $request)
    {
        $userId = $request->session()->get('2fa_user_id');

        if (!$userId) {
            return redirect()->route('login');
        }

        $user = User::find($userId);

        if (!$user) {
            return redirect()->route('login');
        }

        // Rate limit: only allow resend every 60 seconds (not before 60s have passed since last send)
        if ($user->two_factor_expires_at && $user->two_factor_expires_at->isFuture()) {
            $secondsSinceIssued = 600 - $user->two_factor_expires_at->diffInSeconds(now(), false);
            if ($secondsSinceIssued < 60) {
                return back()->withErrors(['code' => 'Please wait ' . (60 - (int)$secondsSinceIssued) . ' seconds before requesting a new code.']);
            }
        }

        $code = $user->generateTwoFactorCode();

        try {
            Mail::to($user->email)->send(new TwoFactorCodeMail($user, $code));
        } catch (\Exception $e) {
            \Log::error('2FA resend mail failed: ' . $e->getMessage());
        }

        return back()->with('resent', 'A new verification code has been sent to your email.');
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

    private function maskPhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);
        if (strlen($digits) >= 7) {
            return substr($digits, 0, 3) . str_repeat('*', strlen($digits) - 6) . substr($digits, -3);
        }
        return str_repeat('*', strlen($phone));
    }
}
