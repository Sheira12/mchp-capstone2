@extends('layouts.app')
@section('title', 'User — ' . $user->name)
@section('page-title', 'User Details')

@section('content')
<div class="py-6 max-w-3xl space-y-5">

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.users.index') }}" class="text-sm text-blue-600 hover:underline flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            All Users
        </a>
        <div class="flex gap-2">
            <a href="{{ route('admin.users.edit', $user) }}" class="btn-secondary text-sm">Edit</a>
            @if($user->id !== auth()->id())
            <form action="{{ route('admin.users.toggle-active', $user) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="btn-secondary text-sm {{ $user->is_active ? 'text-red-600' : 'text-green-600' }}">
                    {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                </button>
            </form>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-6 py-5 flex items-center gap-4">
            <div class="w-14 h-14 rounded-full bg-white/20 flex items-center justify-center text-white font-bold text-2xl">
                {{ substr($user->name, 0, 1) }}
            </div>
            <div>
                <h2 class="text-xl font-bold text-white">{{ $user->name }}</h2>
                <p class="text-blue-200 text-sm">{{ $user->email }}</p>
                <div class="flex items-center gap-2 mt-1">
                    @foreach($user->roles as $role)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-white/20 text-white">
                        {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                    </span>
                    @endforeach
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold {{ $user->is_active ? 'bg-green-400/30 text-green-100' : 'bg-red-400/30 text-red-100' }}">
                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>
        </div>
        <div class="p-6">
            <dl class="grid grid-cols-2 gap-x-6 gap-y-4 text-sm">
                <div>
                    <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">User ID</dt>
                    <dd class="font-mono text-gray-700">#{{ $user->id }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Registered</dt>
                    <dd class="text-gray-700">{{ $user->created_at->format('F d, Y') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Last Login</dt>
                    <dd class="text-gray-700">{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Linked Parishioner</dt>
                    <dd>
                        @if($user->parishioner)
                        <a href="{{ route('admin.parishioners.show', $user->parishioner) }}" class="text-blue-600 hover:underline font-medium">
                            {{ $user->parishioner->full_name }}
                        </a>
                        @else
                        <span class="text-gray-400 italic">None</span>
                        @endif
                    </dd>
                </div>
            </dl>
        </div>
    </div>

</div>
@endsection
