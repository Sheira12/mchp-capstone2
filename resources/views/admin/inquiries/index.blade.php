@extends('layouts.app')
@section('title', 'Inquiries')
@section('page-title', 'Inquiries')

@section('content')
<div class="py-6 space-y-4">

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        @foreach([
            ['All',         'gray',   $counts['all'],         ''],
            ['New',         'blue',   $counts['new'],         'new'],
            ['In Progress', 'purple', $counts['in_progress'], 'in_progress'],
            ['Replied',     'indigo', $counts['replied'],     'replied'],
            ['Resolved',    'green',  $counts['resolved'],    'resolved'],
            ['Closed',      'gray',   $counts['closed'],      'closed'],
        ] as [$label, $color, $count, $val])
        <a href="{{ route('admin.inquiries.index', $val ? ['status' => $val] : []) }}"
           class="bg-{{ $color }}-50 border border-{{ $color }}-200 rounded-xl p-3 text-center hover:shadow-md transition
               {{ request('status') === $val || (!request('status') && $val === '') ? 'ring-2 ring-' . $color . '-400' : '' }}">
            <p class="text-xs font-bold text-{{ $color }}-600 uppercase tracking-wide">{{ $label }}</p>
            <p class="text-xl font-bold text-{{ $color }}-700 mt-0.5">{{ $count }}</p>
        </a>
        @endforeach
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1" style="min-width:200px;">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search name, email or subject…"
                       class="form-input text-sm w-full">
            </div>
            <select name="status" class="form-select text-sm" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="new"         @selected(request('status')==='new')>New</option>
                <option value="read"        @selected(request('status')==='read')>Under Review</option>
                <option value="in_progress" @selected(request('status')==='in_progress')>In Progress</option>
                <option value="replied"     @selected(request('status')==='replied')>Replied</option>
                <option value="resolved"    @selected(request('status')==='resolved')>Resolved</option>
                <option value="closed"      @selected(request('status')==='closed')>Closed</option>
            </select>
            <button type="submit" class="btn-primary text-sm">Filter</button>
            @if(request()->hasAny(['search','status']))
            <a href="{{ route('admin.inquiries.index') }}" class="btn-secondary text-sm">Clear</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500">
                    <th class="px-4 py-3 font-medium whitespace-nowrap">From</th>
                    <th class="px-4 py-3 font-medium whitespace-nowrap">Subject</th>
                    <th class="px-4 py-3 font-medium whitespace-nowrap">Status</th>
                    <th class="px-4 py-3 font-medium whitespace-nowrap">Received</th>
                    <th class="px-4 py-3 font-medium whitespace-nowrap">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($inquiries as $inquiry)
                @php
                    $statusColors = [
                        'new'         => 'blue',
                        'read'        => 'amber',
                        'in_progress' => 'purple',
                        'replied'     => 'indigo',
                        'resolved'    => 'green',
                        'closed'      => 'gray',
                    ];
                    $statusLabels = \App\Models\Inquiry::STATUSES;
                    $sc = $statusColors[$inquiry->status] ?? 'gray';
                @endphp
                <tr class="hover:bg-gray-50 {{ $inquiry->status === 'new' ? 'font-semibold' : '' }}">
                    <td class="px-4 py-3">
                        <p class="text-gray-900 text-sm">{{ $inquiry->name }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $inquiry->email }}</p>
                        @if($inquiry->phone)
                        <p class="text-xs text-gray-400">{{ $inquiry->phone }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-700 max-w-xs">
                        <p class="truncate">{{ $inquiry->subject }}</p>
                        <p class="text-xs text-gray-400 truncate mt-0.5">{{ Str::limit($inquiry->message, 60) }}</p>
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold
                            bg-{{ $sc }}-100 text-{{ $sc }}-700 whitespace-nowrap">
                            <span class="w-1.5 h-1.5 rounded-full bg-{{ $sc }}-500"></span>
                            {{ $statusLabels[$inquiry->status] ?? ucfirst($inquiry->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs whitespace-nowrap">
                        {{ $inquiry->created_at->format('M d, Y') }}<br>
                        <span class="text-gray-400">{{ $inquiry->created_at->diffForHumans() }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <a href="{{ route('admin.inquiries.show', $inquiry) }}"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 text-xs font-semibold transition whitespace-nowrap">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            View & Reply
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-12 text-center text-gray-400">
                        <svg class="w-10 h-10 mx-auto mb-2 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        No inquiries found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
        @if($inquiries->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $inquiries->links() }}</div>
        @endif
    </div>

</div>
@endsection
