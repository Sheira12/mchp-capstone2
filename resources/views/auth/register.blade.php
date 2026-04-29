<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — {{ config('parish.name') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-900 to-indigo-900 flex items-center justify-center p-4">
<div class="w-full max-w-md">

    <div class="text-center mb-8">
        <img src="{{ asset('images/parish-logo.png') }}" alt="Parish Logo" class="w-20 h-20 rounded-full mx-auto mb-4 object-cover border-4 border-white shadow-lg">
        <h1 class="text-white text-xl font-bold">Mary Help of Christians Parish</h1>
        <p class="text-blue-200 text-sm">Create your parishioner account</p>
    </div>

    <div class="bg-white rounded-2xl shadow-2xl p-8">
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
            <button type="submit" class="w-full btn-primary py-2.5">Create Account</button>
        </form>

        <p class="text-center text-sm text-gray-500 mt-6">
            Already have an account?
            <a href="{{ route('login') }}" class="text-blue-600 hover:underline font-medium">Sign In</a>
        </p>
        <p class="text-center text-sm text-gray-400 mt-2">
            <a href="{{ route('home') }}" class="hover:underline">← Back to Parish Website</a>
        </p>
    </div>
</div>
</body>
</html>
