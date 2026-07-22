<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password — {{ config('parish.name') }}</title>
    @vite(['resources/css/app.css'])
    <style>
        .auth-bg { background-image: url('/images/church-bg.jpg'); background-size: cover; background-position: center; background-attachment: fixed; }
        .auth-overlay { background: linear-gradient(135deg, rgba(15,23,80,0.82) 0%, rgba(30,58,138,0.78) 50%, rgba(49,46,129,0.82) 100%); }
    </style>
</head>
<body class="min-h-screen auth-bg">
<div class="min-h-screen auth-overlay flex items-center justify-center p-4">
<div class="w-full max-w-md">

    @include('auth._logo')

    <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-2xl p-8">

        <div class="text-center mb-6">
            <div class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <h2 class="text-xl font-bold text-gray-900">Set New Password</h2>
            <p class="text-sm text-gray-500 mt-1">Choose a strong password for your account.</p>
        </div>

        @if($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm">
            @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div>
                <label class="form-label">Email Address</label>
                <input type="email" name="email" value="{{ old('email', $email ?? '') }}" required
                       class="form-input w-full @error('email') border-red-400 @enderror"
                       placeholder="your@email.com">
            </div>
            <div>
                <label class="form-label">New Password</label>
                <input type="password" name="password" required minlength="8"
                       class="form-input w-full @error('password') border-red-400 @enderror"
                       placeholder="Minimum 8 characters">
            </div>
            <div>
                <label class="form-label">Confirm New Password</label>
                <input type="password" name="password_confirmation" required
                       class="form-input w-full"
                       placeholder="Repeat new password">
            </div>
            <button type="submit" class="w-full btn-primary py-2.5">Reset Password</button>
        </form>

        <p class="text-center text-sm text-gray-500 mt-6">
            <a href="{{ route('login') }}" class="text-blue-600 hover:underline">← Back to Sign In</a>
        </p>
    </div>

    <p class="text-center text-xs text-white/50 mt-4">
        Mary Help of Christians Parish · Southville 1, Niugan, Cabuyao, Laguna
    </p>
</div>
</div>
</body>
</html>
