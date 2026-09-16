<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — {{ config('parish.name') }}</title>
    @vite(['resources/css/app.css'])
    <style>
        .auth-bg {
            background-image: url('{{ asset('images/church-bg.jpg') }}');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        .auth-overlay {
            background: linear-gradient(135deg, rgba(15,23,80,0.82) 0%, rgba(30,58,138,0.78) 50%, rgba(49,46,129,0.82) 100%);
        }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body class="min-h-screen auth-bg">
<div class="min-h-screen auth-overlay flex items-center justify-center p-4">
<div class="w-full max-w-md">

    {{-- Logo --}}
    @include('auth._logo')

    {{-- Card --}}
    <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-2xl p-8">
        <h2 class="text-xl font-bold text-gray-900 mb-6">Sign In</h2>

    @if(session('status'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 rounded-lg px-4 py-3 text-sm flex items-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('status') }}
        </div>
        @endif

        @if($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm">
            @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf
            <div>
                <label class="form-label">Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                       class="form-input w-full @error('email') border-red-400 @enderror"
                       placeholder="your@email.com">
            </div>
            <div>
                <label class="form-label">Password</label>
                <div style="position:relative;">
                    <input type="password" name="password" id="password-input" required
                           class="form-input w-full"
                           style="padding-right:2.5rem;"
                           placeholder="••••••••">
                    <button type="button"
                            onclick="togglePassword()"
                            style="position:absolute;right:0.625rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;padding:4px;color:#9ca3af;display:flex;align-items:center;justify-content:center;line-height:0;"
                            tabindex="-1"
                            aria-label="Toggle password visibility">
                        {{-- Eye open (shown when password is hidden) --}}
                        <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                        {{-- Eye off / slash (shown when password is visible) --}}
                        <svg id="eye-slash-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" style="display:none;">
                            <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/>
                            <line x1="1" y1="1" x2="23" y2="23"/>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" name="remember" class="rounded border-gray-300 text-blue-600">
                    Remember me
                </label>
                <a href="{{ route('password.request') }}" class="text-sm text-blue-600 hover:underline">Forgot password?</a>
            </div>
            <button type="submit" id="login-btn" class="w-full btn-primary py-2.5"
                    onclick="this.disabled=true;this.innerHTML='<span style=\'display:inline-flex;align-items:center;gap:6px\'><svg style=\'width:16px;height:16px;animation:spin 1s linear infinite\' fill=\'none\' viewBox=\'0 0 24 24\'><circle style=\'opacity:.25\' cx=\'12\' cy=\'12\' r=\'10\' stroke=\'currentColor\' stroke-width=\'4\'/><path style=\'opacity:.75\' fill=\'currentColor\' d=\'M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z\'/></svg>Signing in…</span>';this.form.submit();">
                Sign In
            </button>
        </form>

        <p class="text-center text-sm text-gray-500 mt-6">
            Don't have an account?
            <a href="{{ route('register') }}" class="text-blue-600 hover:underline font-medium">Register</a>
        </p>
        <p class="text-center text-sm text-gray-400 mt-2">
            <a href="{{ route('home') }}" class="hover:underline">← Back to Parish Website</a>
        </p>
    </div>

    {{-- Parish credit at bottom --}}
    <p class="text-center text-xs text-white/50 mt-4">
        {{ config('parish.name') }} · {{ config('parish.address') }}
    </p>
</div>
</div>
</body>
</html>

<script>
function togglePassword() {
    const input   = document.getElementById('password-input');
    const eyeOn   = document.getElementById('eye-icon');
    const eyeOff  = document.getElementById('eye-slash-icon');
    const showing = input.type === 'text';
    input.type           = showing ? 'password' : 'text';
    eyeOn.style.display  = showing ? ''      : 'none';
    eyeOff.style.display = showing ? 'none'  : '';
}
</script>
</body>
</html>
