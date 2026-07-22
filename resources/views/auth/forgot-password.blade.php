<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password — {{ config('parish.name') }}</title>
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
            </div>
            <h2 class="text-xl font-bold text-gray-900">Forgot your password?</h2>
            <p class="text-sm text-gray-500 mt-1">Enter your email and we'll send you a reset link.</p>
        </div>

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

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf
            <div>
                <label class="form-label">Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                       class="form-input w-full @error('email') border-red-400 @enderror"
                       placeholder="your@email.com">
            </div>
            <button type="submit" class="w-full btn-primary py-2.5">Send Reset Link</button>
        </form>

        <p class="text-center text-sm text-gray-500 mt-6">
            Remember your password?
            <a href="{{ route('login') }}" class="text-blue-600 hover:underline font-medium">Sign In</a>
        </p>
        <p class="text-center text-sm text-gray-400 mt-2">
            <a href="{{ route('home') }}" class="hover:underline">← Back to Parish Website</a>
        </p>
    </div>

    <p class="text-center text-xs text-white/50 mt-4">
        Mary Help of Christians Parish · Southville 1, Niugan, Cabuyao, Laguna
    </p>
</div>
</div>
</body>
</html>
