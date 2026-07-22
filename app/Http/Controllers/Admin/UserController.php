<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\TwoFactorCodeMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with('roles')
            ->when($request->get('search'), fn($q, $s) => $q->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%"))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users'],
            'password' => ['required', 'min:8', 'confirmed'],
            'role'     => ['required', 'exists:roles,name'],
        ]);

        $user = User::create([
            'name'      => trim($validated['name']),
            'email'     => strtolower(trim($validated['email'])),
            'password'  => Hash::make($validated['password']),
            'is_active' => true,
        ]);

        $user->assignRole($validated['role']);

        // Send welcome / credential email
        $this->sendCredentialEmail($user, $validated['password']);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created and login credentials sent to ' . $user->email . '.');
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'min:8', 'confirmed'],
            'role'     => ['required', 'exists:roles,name'],
        ]);

        $newEmail    = strtolower(trim($validated['email']));
        $emailChanged = $newEmail !== $user->email;
        $passChanged  = !empty($validated['password']);

        // Build update data
        $updateData = [
            'name'  => trim($validated['name']),
            'email' => $newEmail,
        ];
        if ($passChanged) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        // If email or password changed — clear any pending 2FA codes
        if ($emailChanged || $passChanged) {
            $updateData['two_factor_code']       = null;
            $updateData['two_factor_expires_at'] = null;
        }

        $user->update($updateData);
        $user->syncRoles([$validated['role']]);

        // Notify the user at their NEW email about the changes
        if ($emailChanged || $passChanged) {
            $this->sendCredentialUpdateEmail($user, $passChanged ? $validated['password'] : null, $emailChanged);
        }

        $msg = 'User updated successfully.';
        if ($emailChanged || $passChanged) {
            $msg .= ' Updated credentials sent to ' . $user->email . '.';
        }

        return redirect()->route('admin.users.index')->with('success', $msg);
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'You cannot delete your own account.']);
        }
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User deleted.');
    }

    public function show(User $user)
    {
        $user->load('roles');
        return view('admin.users.show', compact('user'));
    }

    public function toggleActive(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'You cannot deactivate your own account.']);
        }
        $user->update(['is_active' => !$user->is_active]);
        return back()->with('success', 'User status updated.');
    }

    // ── Send welcome email with credentials ───────────────────
    private function sendCredentialEmail(User $user, string $plainPassword): void
    {
        try {
            Mail::send([], [], function ($message) use ($user, $plainPassword) {
                $message->to($user->email, $user->name)
                    ->subject('Your MHC Parish System Account')
                    ->html($this->buildCredentialEmailHtml(
                        $user,
                        $plainPassword,
                        false,
                        false
                    ));
            });
        } catch (\Exception $e) {
            Log::error('Credential email failed for ' . $user->email . ': ' . $e->getMessage());
        }
    }

    // ── Send update email when email/password changes ─────────
    private function sendCredentialUpdateEmail(User $user, ?string $newPassword, bool $emailChanged): void
    {
        try {
            Mail::send([], [], function ($message) use ($user, $newPassword, $emailChanged) {
                $message->to($user->email, $user->name)
                    ->subject('Your MHC Parish System Credentials Updated')
                    ->html($this->buildCredentialEmailHtml(
                        $user,
                        $newPassword,
                        $emailChanged,
                        true
                    ));
            });
        } catch (\Exception $e) {
            Log::error('Update email failed for ' . $user->email . ': ' . $e->getMessage());
        }
    }

    private function buildCredentialEmailHtml(User $user, ?string $password, bool $emailChanged, bool $isUpdate): string
    {
        $role     = ucwords(str_replace('_', ' ', $user->getRoleNames()->first() ?? 'Staff'));
        $appName  = config('app.name', 'MHC Parish System');
        $appUrl   = config('app.url');
        $parish   = config('parish.name', 'Mary Help of Christians Parish');
        $action   = $isUpdate ? 'updated' : 'created';

        $changes = [];
        if ($emailChanged) $changes[] = 'Login email changed to: <strong>' . $user->email . '</strong>';
        if ($password)     $changes[] = 'New password: <strong>' . $password . '</strong>';

        $changesHtml = $changes
            ? '<ul style="margin:8px 0;padding-left:20px;">' . implode('', array_map(fn($c) => "<li style='margin:4px 0;'>$c</li>", $changes)) . '</ul>'
            : '';

        return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family:Arial,sans-serif;background:#f4f4f4;margin:0;padding:20px;">
<div style="max-width:560px;margin:0 auto;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.1);">
  <div style="background:#1d4ed8;padding:24px;text-align:center;">
    <h1 style="color:#fff;margin:0;font-size:20px;">{$parish}</h1>
    <p style="color:#93c5fd;margin:4px 0 0;font-size:13px;">Parish Management System</p>
  </div>
  <div style="padding:28px 32px;">
    <h2 style="color:#1e293b;margin:0 0 8px;">Hello, {$user->name}!</h2>
    <p style="color:#475569;margin:0 0 20px;">
      Your {$appName} account has been <strong>{$action}</strong>.
      Here are your login details:
    </p>

    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-left:4px solid #1d4ed8;border-radius:4px;padding:16px 20px;margin-bottom:20px;">
      <table style="width:100%;border-collapse:collapse;">
        <tr><td style="padding:4px 0;color:#64748b;width:90px;">Role:</td><td style="padding:4px 0;font-weight:bold;color:#1e293b;">{$role}</td></tr>
        <tr><td style="padding:4px 0;color:#64748b;">Email:</td><td style="padding:4px 0;font-weight:bold;color:#1e293b;">{$user->email}</td></tr>
        {$this->passwordRow($password)}
      </table>
      {$changesHtml}
    </div>

    <p style="color:#475569;margin:0 0 20px;">
      After logging in, you will be asked to enter a <strong>6-digit verification code</strong>
      sent to this email address. Please keep access to this inbox.
    </p>

    <div style="text-align:center;margin:24px 0;">
      <a href="{$appUrl}/login"
         style="background:#1d4ed8;color:#fff;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:14px;">
        Login to Parish System
      </a>
    </div>

    <p style="color:#94a3b8;font-size:12px;margin:16px 0 0;border-top:1px solid #f1f5f9;padding-top:16px;">
      If you did not request this, please contact the parish administrator immediately.<br>
      This is an automated message from {$parish}.
    </p>
  </div>
</div>
</body>
</html>
HTML;
    }

    private function passwordRow(?string $password): string
    {
        if (!$password) return '';
        return "<tr><td style='padding:4px 0;color:#64748b;'>Password:</td><td style='padding:4px 0;font-weight:bold;color:#1e293b;font-family:monospace;'>{$password}</td></tr>";
    }
}


