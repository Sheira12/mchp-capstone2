@extends('layouts.app')
@section('title', 'New User')
@section('page-title', 'New User')

@section('content')
<div class="py-6 max-w-xl space-y-4">

    {{-- Info --}}
    <div class="bg-blue-50 border border-blue-200 rounded-xl px-4 py-3 flex items-start gap-3">
        <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <div class="text-sm text-blue-800">
            <p class="font-semibold">Creating a new admin/staff account</p>
            <p class="mt-0.5 text-blue-600">Login credentials (email + password) will be automatically sent to the user's email after the account is created. Make sure the email address is correct and accessible.</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-5">
            @csrf

            <div>
                <label class="form-label">Full Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="form-input w-full @error('name') border-red-400 @enderror"
                       placeholder="e.g. Juan dela Cruz">
                @error('name')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label">Email Address <span class="text-red-500">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       class="form-input w-full @error('email') border-red-400 @enderror"
                       placeholder="user@gmail.com">
                <p class="text-xs text-gray-400 mt-1">
                    Use a real, accessible Gmail address for this account.
                </p>
                @error('email')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label">Password <span class="text-red-500">*</span></label>
                <div class="relative">
                    <input type="password" name="password" id="pw1" required minlength="8"
                           class="form-input w-full pr-10 @error('password') border-red-400 @enderror"
                           placeholder="Minimum 8 characters"
                           oninput="strengthCheck(this)">
                    <button type="button" onclick="togglePw('pw1')"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
                <div id="strength-wrap" class="hidden mt-2">
                    <div class="flex gap-1 mb-1">
                        <div id="s1" class="h-1.5 flex-1 rounded-full bg-gray-200"></div>
                        <div id="s2" class="h-1.5 flex-1 rounded-full bg-gray-200"></div>
                        <div id="s3" class="h-1.5 flex-1 rounded-full bg-gray-200"></div>
                        <div id="s4" class="h-1.5 flex-1 rounded-full bg-gray-200"></div>
                    </div>
                    <p id="strength-label" class="text-xs text-gray-400"></p>
                </div>
                @error('password')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label">Confirm Password <span class="text-red-500">*</span></label>
                <div class="relative">
                    <input type="password" name="password_confirmation" id="pw2" required
                           class="form-input w-full pr-10"
                           placeholder="Re-enter password"
                           oninput="matchCheck()">
                    <button type="button" onclick="togglePw('pw2')"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
                <p id="match-msg" class="text-xs mt-1 hidden"></p>
            </div>

            <div>
                <label class="form-label">Role <span class="text-red-500">*</span></label>
                <select name="role" required class="form-select w-full @error('role') border-red-400 @enderror">
                    <option value="">Select role…</option>
                    @foreach($roles as $role)
                    <option value="{{ $role->name }}" @selected(old('role') === $role->name)>
                        {{ ucwords(str_replace('_', ' ', $role->name)) }}
                    </option>
                    @endforeach
                </select>
                @error('role')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Create User & Send Credentials
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
function togglePw(id) {
    const el = document.getElementById(id);
    el.type = el.type === 'password' ? 'text' : 'password';
}

function strengthCheck(input) {
    const val = input.value;
    const wrap = document.getElementById('strength-wrap');
    if (!val) { wrap.classList.add('hidden'); return; }
    wrap.classList.remove('hidden');

    let score = 0;
    if (val.length >= 8)          score++;
    if (/[A-Z]/.test(val))        score++;
    if (/[0-9]/.test(val))        score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    const colors  = ['bg-red-400','bg-orange-400','bg-yellow-400','bg-green-500'];
    const labels  = ['Weak','Fair','Good','Strong'];
    const lColors = ['text-red-500','text-orange-500','text-yellow-600','text-green-600'];

    for (let i = 1; i <= 4; i++) {
        document.getElementById('s'+i).className = 'h-1.5 flex-1 rounded-full ' + (i <= score ? colors[score-1] : 'bg-gray-200');
    }
    const lbl = document.getElementById('strength-label');
    lbl.textContent = 'Strength: ' + (labels[score-1] || 'Too short');
    lbl.className = 'text-xs ' + (lColors[score-1] || 'text-gray-400');
}

function matchCheck() {
    const pw  = document.getElementById('pw1').value;
    const cfg = document.getElementById('pw2').value;
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
</script>
@endsection
