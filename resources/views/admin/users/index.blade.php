@extends('layouts.app')
@section('title', 'Users')
@section('page-title', 'Users')

@section('content')
<div class="py-6 space-y-4">

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- Header / search --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <form method="GET" class="flex gap-2 flex-1">
            <div class="relative flex-1 max-w-xs">
                <div class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/></svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}"
                       class="form-input text-sm pl-9 w-full" placeholder="Search name or email…">
            </div>
            <button type="submit" class="btn-secondary text-sm">Search</button>
            @if(request('search'))
            <a href="{{ route('admin.users.index') }}" class="btn-secondary text-sm">Clear</a>
            @endif
        </form>
        <a href="{{ route('admin.users.create') }}" class="btn-primary text-sm whitespace-nowrap">+ New User</a>
    </div>

    {{-- ── MOBILE CARDS ── --}}
    <div class="space-y-3 lg:hidden">
        @forelse($users as $user)
        <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm">
            <div class="flex items-start gap-3 mb-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-100 to-indigo-100 flex items-center justify-center text-blue-700 font-bold text-sm flex-shrink-0">
                    {{ substr($user->name, 0, 1) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="font-semibold text-gray-900 text-sm">
                                {{ $user->name }}
                                @if($user->id === auth()->id())
                                <span class="text-xs text-blue-500 font-normal">(you)</span>
                                @endif
                            </p>
                            <p class="text-xs text-gray-400 mt-0.5 truncate">{{ $user->email }}</p>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold flex-shrink-0
                            {{ ($user->is_active ?? true) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ ($user->is_active ?? true) ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <div class="flex flex-wrap gap-1 mt-1.5">
                        @foreach($user->roles as $role)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                            {{ str_replace('_', ' ', $role->name) }}
                        </span>
                        @endforeach
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Joined {{ $user->created_at->format('M d, Y') }}</p>
                </div>
            </div>
            <div class="flex items-center gap-1.5 flex-wrap pt-2 border-t border-gray-100">
                <a href="{{ route('admin.users.edit', $user) }}" class="action-btn action-btn-edit">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit
                </a>
                @if($user->id !== auth()->id())
                <form method="POST" action="{{ route('admin.users.toggle-active', $user) }}" class="inline">
                    @csrf
                    <button type="submit" class="action-btn {{ ($user->is_active ?? true) ? 'action-btn-warn' : 'action-btn-green' }}">
                        {{ ($user->is_active ?? true) ? 'Deactivate' : 'Activate' }}
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline"
                      onsubmit="return confirm('Delete this user permanently?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="action-btn action-btn-delete">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Delete
                    </button>
                </form>
                @endif
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl border border-gray-100 p-10 text-center text-gray-400">No users found.</div>
        @endforelse
        @if($users->hasPages())
        <div class="bg-white rounded-xl border border-gray-100 px-4 py-3">{{ $users->links() }}</div>
        @endif
    </div>

    {{-- ── DESKTOP TABLE ── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hidden lg:block">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500">
                    <th class="px-4 py-3 font-medium">Name</th>
                    <th class="px-4 py-3 font-medium">Email</th>
                    <th class="px-4 py-3 font-medium">Role</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3 font-medium">Joined</th>
                    <th class="px-4 py-3 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($users as $user)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-sm font-bold text-blue-700 flex-shrink-0">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                            <span class="font-medium text-gray-900">{{ $user->name }}</span>
                            @if($user->id === auth()->id())
                            <span class="text-xs text-blue-500">(you)</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $user->email }}</td>
                    <td class="px-4 py-3">
                        @foreach($user->roles as $role)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                            {{ str_replace('_', ' ', $role->name) }}
                        </span>
                        @endforeach
                    </td>
                    <td class="px-4 py-3">
                        @if($user->is_active ?? true)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>Active
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>Inactive
                        </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $user->created_at->format('M d, Y') }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <a href="{{ route('admin.users.edit', $user) }}" class="action-btn action-btn-edit">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                Edit
                            </a>
                            @if($user->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.users.toggle-active', $user) }}" class="inline">
                                @csrf
                                <button type="submit" class="action-btn {{ ($user->is_active ?? true) ? 'action-btn-warn' : 'action-btn-green' }}">
                                    {{ ($user->is_active ?? true) ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline"
                                  onsubmit="return confirm('Delete this user permanently?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="action-btn action-btn-delete">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    Delete
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100">{{ $users->links() }}</div>
    </div>
</div>
@endsection
