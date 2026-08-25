<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — {{ config('parish.name') }}</title>
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

    <div class="text-center mb-8">
        @include('auth._logo')
    </div>

    <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-2xl p-8">
        <h2 class="text-xl font-bold text-gray-900 mb-6">Create Account</h2>

        @if($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm">
            @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf
            <div>
                <label class="form-label">Full Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="form-input w-full @error('name') border-red-400 @enderror"
                       placeholder="Juan dela Cruz">
            </div>
            <div>
                <label class="form-label">Email Address <span class="text-red-500">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       class="form-input w-full @error('email') border-red-400 @enderror"
                       placeholder="your@email.com">
            </div>
            <div>
                <label class="form-label">Password <span class="text-red-500">*</span></label>
                <input type="password" name="password" required minlength="8"
                       class="form-input w-full"
                       placeholder="Minimum 8 characters">
            </div>
            <div>
                <label class="form-label">Confirm Password <span class="text-red-500">*</span></label>
                <input type="password" name="password_confirmation" required
                       class="form-input w-full"
                       placeholder="Repeat password">
            </div>
            <button type="submit" class="w-full btn-primary py-2.5"
                    onclick="this.disabled=true;this.innerHTML='<span style=\'display:inline-flex;align-items:center;gap:6px\'><svg style=\'width:16px;height:16px;animation:spin 1s linear infinite\' fill=\'none\' viewBox=\'0 0 24 24\'><circle style=\'opacity:.25\' cx=\'12\' cy=\'12\' r=\'10\' stroke=\'currentColor\' stroke-width=\'4\'/><path style=\'opacity:.75\' fill=\'currentColor\' d=\'M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z\'/></svg>Creating account…</span>';this.form.submit();">
                Create Account
            </button>
        </form>

        <p class="text-center text-sm text-gray-500 mt-6">
            Already have an account?
            <a href="{{ route('login') }}" class="text-blue-600 hover:underline font-medium">Sign In</a>
        </p>
        <p class="text-center text-sm text-gray-400 mt-2">
            <a href="{{ route('home') }}" class="hover:underline">← Back to Parish Website</a>
        </p>
    </div>

    <p class="text-center text-xs text-white/50 mt-4">
        {{ config('parish.name') }} · {{ config('parish.address') }}
    </p>
</div>
</div>
</body>
</html>
