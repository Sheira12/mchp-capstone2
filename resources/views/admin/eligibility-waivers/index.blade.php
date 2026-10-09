@extends('layouts.app')
@section('title', 'Eligibility Waivers')
@section('page-title', 'Eligibility Waivers')

@section('content')
<div class="py-6 space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Eligibility Waivers</h1>
            <p class="text-sm text-gray-500 mt-0.5">All waivers granted by super admins. Immutable audit trail.</p>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm font-medium">{{ session('success') }}</div>
    @endif

    <form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 flex gap-3 items-end flex-wrap">
        <div>
            <label class="form-label">Search Parishioner</label>
            <input type="text" name="search" value="{{ request('search') }}" class="form-input" placeholder="Name…">
        </div>
        <button type="submit" class="btn-primary text-sm">Search</button>
        <a href="{{ route('admin.eligibility-waivers.index') }}" class="btn-secondary text-sm">Clear</a>
    </form>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
        @if($waivers->isEmpty())
        <div class="py-16 text-center text-gray-400 text-sm">No waivers granted yet.</div>
        @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Parishioner</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Rule / Service</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Reason</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Granted By</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Granted At</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Expires</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($waivers as $w)
                    <tr class="hover:bg-gray-50 {{ $w->expires_at && $w->expires_at->isPast() ? 'opacity-50' : '' }}">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $w->parishioner?->full_name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <p class="font-semibold text-gray-900">{{ $w->rule?->name }}</p>
                            <p class="text-xs text-gray-500">{{ $w->rule?->service?->name }}</p>
                        </td>
                        <td class="px-4 py-3 text-gray-700 max-w-xs">{{ $w->reason }}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $w->waivedBy?->name }}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs whitespace-nowrap">{{ $w->waived_at?->format('M d, Y g:i A') }}</td>
                        <td class="px-4 py-3 text-xs">
                            @if($w->expires_at)
                                @if($w->expires_at->isPast())
                                <span class="text-red-500 font-semibold">Expired {{ $w->expires_at->format('M d, Y') }}</span>
                                @else
                                <span class="text-amber-600">{{ $w->expires_at->format('M d, Y') }}</span>
                                @endif
                            @else
                            <span class="text-gray-400">No expiry</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100">{{ $waivers->links() }}</div>
        @endif
    </div>
</div>
@endsection
