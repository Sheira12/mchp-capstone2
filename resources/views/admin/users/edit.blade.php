@extends('layouts.app')

@section('title', 'Edit User')
@section('page-title', 'Edit User')

@section('content')
<div class="py-6 max-w-xl">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-4">
            @csrf @method('PUT')

            <div>
                <label class="form-label">Full Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                       class="form-input w-full @error('name') border-red-400 @enderror">
                @error('name')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label">Email Address <span class="text-red-500">*</span></label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                       class="form-input w-full @error('email') border-red-400 @enderror">
                @error('email')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label">New Password <span class="text-gray-400 text-xs">(leave blank to keep current)</span></label>
                <input type="password" name="password" minlength="8"
                       class="form-input w-full @error('password') border-red-400 @enderror"
                       placeholder="Minimum 8 characters">
                @error('password')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label">Confirm New Password</label>
                <input type="password" name="password_confirmation" class="form-input w-full">
            </div>

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

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary">Save Changes</button>
                <a href="{{ route('admin.users.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
