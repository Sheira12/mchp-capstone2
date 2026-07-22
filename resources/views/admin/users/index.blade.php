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

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <form method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}"
                   class="form-input text-sm w-64" placeholder="Search name or email…">
            <button type="submit" class="btn-secondary text-sm">Search</button>
            @if(request('search'))
                <a href="{{ route('admin.users.index') }}" class="btn-secondary text-sm">Clear</a>
            @endif
        </form>
        <a href="{{ route('admin.users.create') }}" class="btn-primary text-sm">+ New User</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
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
                            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-sm font-bold text-blue-700">
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
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Inactive</span>
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
                                    @if($user->is_active ?? true)
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                    Deactivate
                                    @else
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Activate
                                    @endif
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
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-400">No users found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection
