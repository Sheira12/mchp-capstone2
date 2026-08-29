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

        $credentials['email'] = strtolower(trim($credentials['email']));

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

        // Admins (super_admin, parish_secretary, finance_officer) log in directly — no OTP
        if ($user->hasRole(['super_admin', 'parish_secretary', 'finance_officer'])) {
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();
            $user->update(['last_login_at' => now()]);
            return redirect()->intended(route('admin.dashboard'));
        }

        // Parishioners go through email OTP verification
        $code = $user->generateTwoFactorCode();

        $request->session()->put('2fa_user_id', $user->id);
        $request->session()->put('2fa_remember', $request->boolean('remember'));

        // Send OTP email
        $emailSent = false;
        try {
            Mail::to($user->email)->send(new TwoFactorCodeMail($user, $code));
            $emailSent = true;
        } catch (\Exception $e) {
            Log::error('2FA email failed: ' . $e->getMessage());
        }

        // If email fails, store code in session so it can be shown on screen
        $request->session()->put('dev_code', $emailSent ? null : $code);

        return redirect()->route('2fa.show')
            ->with('2fa_email', $this->maskEmail($user->email))
            ->with('email_sent', $emailSent);
    }

    // ─────────────────────────────────────────────
    //  2FA — Show OTP form (parishioners only)
    // ─────────────────────────────────────────────

    public function show2fa(Request $request)
    {
        if (!$request->session()->has('2fa_user_id')) {
            return redirect()->route('login');
        }

        $user        = User::find($request->session()->get('2fa_user_id'));
        $maskedEmail = $request->session()->get('2fa_email')
            ?? ($user ? $this->maskEmail($user->email) : '');
        $emailSent   = $request->session()->get('email_sent', false);
        $devCode     = $request->session()->get('dev_code');

        return view('auth.two-factor', compact('maskedEmail', 'emailSent', 'devCode'));
    }

    // ─────────────────────────────────────────────
    //  2FA — Verify OTP (parishioners only)
    // ─────────────────────────────────────────────

    public function verify2fa(Request $request)
    {
        $request->validate(['code' => ['required', 'string', 'size:6']]);

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

        if (!$user->validateTwoFactorCode($request->input('code'))) {
            return back()->withErrors(['code' => 'Invalid or expired code. Please try again.']);
        }

        $user->clearTwoFactorCode();
        $user->update(['last_login_at' => now()]);

        $remember = $request->session()->pull('2fa_remember', false);
        $request->session()->forget(['2fa_user_id', '2fa_email', 'dev_code']);

        Auth::login($user, $remember);
        $request->session()->regenerate();

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

        $code     = $user->generateTwoFactorCode();
        $sent     = false;

        try {
            Mail::to($user->email)->send(new TwoFactorCodeMail($user, $code));
            $sent = true;
        } catch (\Exception $e) {
            Log::error('2FA resend failed: ' . $e->getMessage());
        }

        $request->session()->put('dev_code', $sent ? null : $code);

        return back()
            ->with('resent', $sent
                ? 'A new verification code has been sent to your email.'
                : 'Email unavailable.')
            ->with('email_sent', $sent);
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
