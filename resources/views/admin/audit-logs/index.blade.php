@extends('layouts.app')

@section('title', 'Audit Logs')
@section('page-title', 'Audit Logs')

@section('content')
<div class="py-6 space-y-4">

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Action</label>
                <select name="action" class="form-select text-sm">
                    <option value="">All Actions</option>
                    @foreach(['create','update','delete','verify','download','release','refund','void','login','logout'] as $action)
                    <option value="{{ $action }}" @selected(request('action') === $action)>{{ ucfirst($action) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-input text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-input text-sm">
            </div>
            <button type="submit" class="btn-secondary text-sm">Filter</button>
            @if(request()->hasAny(['action','date_from','date_to','user_id']))
                <a href="{{ route('admin.audit-logs.index') }}" class="btn-secondary text-sm">Clear</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500">
                    <th class="px-4 py-3 font-medium">Date/Time</th>
                    <th class="px-4 py-3 font-medium">User</th>
                    <th class="px-4 py-3 font-medium">Action</th>
                    <th class="px-4 py-3 font-medium">Model</th>
                    <th class="px-4 py-3 font-medium">Description</th>
                    <th class="px-4 py-3 font-medium">IP</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($logs as $log)
                @php
                    $actionColors = [
                        'create' => 'green', 'update' => 'blue', 'delete' => 'red',
                        'verify' => 'purple', 'download' => 'gray', 'release' => 'teal',
                        'refund' => 'orange', 'void' => 'red', 'login' => 'green', 'logout' => 'gray'
                    ];
                    $ac = $actionColors[$log->action] ?? 'gray';
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-500 text-xs whitespace-nowrap">
                        {{ $log->created_at->format('M d, Y') }}<br>
                        <span class="text-gray-400">{{ $log->created_at->format('g:i A') }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-700">{{ $log->user?->name ?? 'System' }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $ac }}-100 text-{{ $ac }}-800">
                            {{ ucfirst($log->action) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-600 text-xs">
                        {{ class_basename($log->auditable_type ?? '') }}
                        @if($log->auditable_id)
                            <span class="text-gray-400">#{{ $log->auditable_id }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-600 text-xs max-w-xs truncate">{{ $log->description ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-400 text-xs">{{ $log->ip_address ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-400">No audit logs found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection
