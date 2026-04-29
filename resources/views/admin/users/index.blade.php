@extends('layouts.app')

@section('title', 'Users')
@section('page-title', 'Users')

@section('content')
<div class="py-6 space-y-4">

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
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.users.edit', $user) }}" class="text-blue-600 hover:underline text-xs">Edit</a>
                            @if($user->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.users.toggle-active', $user) }}">
                                @csrf
                                <button type="submit" class="text-amber-600 hover:underline text-xs">
                                    {{ ($user->is_active ?? true) ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                  onsubmit="return confirm('Delete this user?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline text-xs">Delete</button>
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
