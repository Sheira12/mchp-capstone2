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
                <div class="relative">
                    <input type="password" name="password" id="password-input" required
                           class="form-input w-full pr-10"
                           placeholder="••••••••">
                    <button type="button" id="toggle-password"
                            onclick="togglePassword()"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 focus:outline-none"
                            tabindex="-1" aria-label="Show/hide password">
                        {{-- Eye icon (shown when password is hidden) --}}
                        <svg id="eye-icon" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        {{-- Eye-slash icon (shown when password is visible) --}}
                        <svg id="eye-slash-icon" class="w-4 h-4 hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
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
    input.type    = showing ? 'password' : 'text';
    eyeOn.classList.toggle('hidden',  !showing);
    eyeOff.classList.toggle('hidden', showing);
}
</script>
</body>
</html>
