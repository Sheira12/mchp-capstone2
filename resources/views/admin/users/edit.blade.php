@extends('layouts.app')
@section('title', 'Edit User')
@section('page-title', 'Edit User')

@section('content')
<div class="py-6 max-w-xl space-y-4">

    {{-- Info banner --}}
    <div class="bg-blue-50 border border-blue-200 rounded-xl px-4 py-3 flex items-start gap-3">
        <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <div class="text-sm text-blue-800">
            <p class="font-semibold mb-0.5">Editing: {{ $user->name }}</p>
            <p>Current email: <span class="font-mono font-bold">{{ $user->email }}</span></p>
            <p class="mt-1 text-blue-600">If you change the email or password, the updated credentials will automatically be sent to the new email address.</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-5" id="edit-user-form">
            @csrf @method('PUT')

            {{-- Name --}}
            <div>
                <label class="form-label">Full Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                       class="form-input w-full @error('name') border-red-400 @enderror"
                       placeholder="Full name">
                @error('name')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            {{-- Email --}}
            <div>
                <label class="form-label">
                    Email Address <span class="text-red-500">*</span>
                    <span id="email-changed-badge" class="hidden ml-2 px-2 py-0.5 bg-amber-100 text-amber-700 text-xs font-bold rounded-full">Changed — code will be sent here</span>
                </label>
                <input type="email" name="email" id="email-input"
                       value="{{ old('email', $user->email) }}" required
                       class="form-input w-full @error('email') border-red-400 @enderror"
                       placeholder="user@gmail.com"
                       data-original="{{ $user->email }}"
                       oninput="checkEmailChanged(this)">
                @error('email')<p class="form-error">{{ $message }}</p>@enderror
                <p class="text-xs text-gray-400 mt-1">
                    Use a real, accessible email address for this account.
                </p>
            </div>

            {{-- Password --}}
            <div>
                <label class="form-label">
                    New Password
                    <span class="text-gray-400 text-xs font-normal">(leave blank to keep current)</span>
                </label>
                <div class="relative">
                    <input type="password" name="password" id="password-input" minlength="8"
                           class="form-input w-full pr-10 @error('password') border-red-400 @enderror"
                           placeholder="Minimum 8 characters"
                           oninput="checkPasswordStrength(this)">
                    <button type="button" onclick="togglePw('password-input', 'pw-eye')"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <svg id="pw-eye" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
                {{-- Password strength bar --}}
                <div id="pw-strength-wrap" class="hidden mt-2">
                    <div class="flex gap-1 mb-1">
                        <div id="pw-bar-1" class="h-1.5 flex-1 rounded-full bg-gray-200"></div>
                        <div id="pw-bar-2" class="h-1.5 flex-1 rounded-full bg-gray-200"></div>
                        <div id="pw-bar-3" class="h-1.5 flex-1 rounded-full bg-gray-200"></div>
                        <div id="pw-bar-4" class="h-1.5 flex-1 rounded-full bg-gray-200"></div>
                    </div>
                    <p id="pw-strength-label" class="text-xs text-gray-400"></p>
                </div>
                @error('password')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            {{-- Confirm Password --}}
            <div>
                <label class="form-label">Confirm New Password</label>
                <div class="relative">
                    <input type="password" name="password_confirmation" id="confirm-input"
                           class="form-input w-full pr-10"
                           placeholder="Re-enter new password">
                    <button type="button" onclick="togglePw('confirm-input', 'confirm-eye')"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <svg id="confirm-eye" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
                <p id="match-msg" class="text-xs mt-1 hidden"></p>
            </div>

            {{-- Role --}}
            <div>
                <label class="form-label">Role <span class="text-red-500">*</span></label>
                <select name="role" required class="form-select w-full @error('role') border-red-400 @enderror">
                    @foreach($roles as $role)
                    <option value="{{ $role->name }}" @selected(old('role', $user->getRoleNames()->first()) === $role->name)>
                        {{ ucwords(str_replace('_', ' ', $role->name)) }}
                    </option>
                    @endforeach
                </select>
                @error('role')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            {{-- Change summary preview --}}
            <div id="change-summary" class="hidden bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 text-sm text-amber-800">
                <p class="font-semibold mb-1">⚠ You are about to change:</p>
                <ul id="change-list" class="list-disc list-inside space-y-0.5"></ul>
                <p class="mt-2 text-xs text-amber-600">Updated credentials will be sent to the email address above.</p>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary" id="save-btn">Save Changes</button>
                <a href="{{ route('admin.users.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
const originalEmail = document.getElementById('email-input')?.dataset.original || '';

function checkEmailChanged(input) {
    const changed = input.value.trim().toLowerCase() !== originalEmail.toLowerCase();
    document.getElementById('email-changed-badge').classList.toggle('hidden', !changed);
    updateChangeSummary();
}

function checkPasswordStrength(input) {
    const val = input.value;
    const wrap = document.getElementById('pw-strength-wrap');
    const label = document.getElementById('pw-strength-label');
    if (!val) { wrap.classList.add('hidden'); updateChangeSummary(); return; }
    wrap.classList.remove('hidden');

    let score = 0;
    if (val.length >= 8)            score++;
    if (/[A-Z]/.test(val))          score++;
    if (/[0-9]/.test(val))          score++;
    if (/[^A-Za-z0-9]/.test(val))   score++;

    const colors = ['bg-red-400','bg-orange-400','bg-yellow-400','bg-green-500'];
    const labels = ['Weak','Fair','Good','Strong'];
    for (let i = 1; i <= 4; i++) {
        const bar = document.getElementById('pw-bar-' + i);
        bar.className = 'h-1.5 flex-1 rounded-full ' + (i <= score ? colors[score-1] : 'bg-gray-200');
    }
    label.textContent = 'Password strength: ' + (labels[score-1] || 'Too short');
    label.className = 'text-xs ' + ['text-red-500','text-orange-500','text-yellow-600','text-green-600'][score-1];

    // Check match
    const confirm = document.getElementById('confirm-input').value;
    if (confirm) checkMatch();
    updateChangeSummary();
}

document.getElementById('confirm-input')?.addEventListener('input', checkMatch);
function checkMatch() {
    const pw  = document.getElementById('password-input').value;
    const cfg = document.getElementById('confirm-input').value;
    const msg = document.getElementById('match-msg');
    if (!cfg) { msg.classList.add('hidden'); return; }
    msg.classList.remove('hidden');
    if (pw === cfg) {
        msg.textContent = '✓ Passwords match';
        msg.className = 'text-xs mt-1 text-green-600';
    } else {
        msg.textContent = '✗ Passwords do not match';
        msg.className = 'text-xs mt-1 text-red-500';
    }
}

function updateChangeSummary() {
    const emailInput = document.getElementById('email-input');
    const pwInput    = document.getElementById('password-input');
    const summary    = document.getElementById('change-summary');
    const list       = document.getElementById('change-list');

    const changes = [];
    if (emailInput && emailInput.value.trim().toLowerCase() !== originalEmail.toLowerCase()) {
        changes.push('Login email → <strong>' + emailInput.value + '</strong>');
    }
    if (pwInput && pwInput.value.length > 0) {
        changes.push('Password will be changed');
    }

    if (changes.length) {
        list.innerHTML = changes.map(c => '<li>' + c + '</li>').join('');
        summary.classList.remove('hidden');
    } else {
        summary.classList.add('hidden');
    }
}

function togglePw(inputId, eyeId) {
    const input = document.getElementById(inputId);
    input.type = input.type === 'password' ? 'text' : 'password';
}
</script>
@endsection
