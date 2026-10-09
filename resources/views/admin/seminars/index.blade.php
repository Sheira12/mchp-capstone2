@extends('layouts.app')
@section('title', 'Seminars')
@section('page-title', 'Seminars')

@section('content')
<div class="py-6 space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Seminars</h1>
            <p class="text-sm text-gray-500 mt-0.5">Schedule and manage seminars required for eligibility.</p>
        </div>
        <a href="{{ route('admin.seminars.create') }}" class="btn-primary text-sm">+ New Seminar</a>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm font-medium">{{ session('success') }}</div>
    @endif

    {{-- Filters --}}
    <form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 flex flex-wrap gap-3 items-end">
        <div>
            <label class="form-label">Service</label>
            <select name="service_id" class="form-select">
                <option value="">All Services</option>
                @foreach($services as $svc)
                <option value="{{ $svc->id }}" @selected(request('service_id')==$svc->id)>{{ $svc->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="">All</option>
                @foreach(\App\Models\Seminar::STATUSES as $val => $lbl)
                <option value="{{ $val }}" @selected(request('status')===$val)>{{ $lbl }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn-primary text-sm">Filter</button>
        <a href="{{ route('admin.seminars.index') }}" class="btn-secondary text-sm">Clear</a>
    </form>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
        @if($seminars->isEmpty())
        <div class="py-16 text-center text-gray-400">
            <p class="font-medium">No seminars yet.</p>
            <a href="{{ route('admin.seminars.create') }}" class="mt-2 inline-block text-blue-600 hover:underline text-sm">Schedule the first one</a>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Title</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Service</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Date & Time</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Venue</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Registered</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Status</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($seminars as $sem)
                    @php $sc = ['scheduled'=>'blue','completed'=>'green','cancelled'=>'red'][$sem->status] ?? 'gray'; @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-semibold text-gray-900">{{ $sem->title }}</td>
                        <td class="px-4 py-3 text-gray-600 text-xs">{{ $sem->service?->name }}</td>
                        <td class="px-4 py-3 text-gray-700 text-xs whitespace-nowrap">
                            {{ $sem->scheduled_at->format('M d, Y') }}<br>
                            <span class="text-gray-400">{{ $sem->scheduled_at->format('g:i A') }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-600 text-xs">{{ $sem->venue ?? '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="font-bold text-gray-900">{{ $sem->registrations->whereIn('status',['registered','attended'])->count() }}</span>
                            <span class="text-gray-400">/{{ $sem->capacity }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold bg-{{ $sc }}-100 text-{{ $sc }}-700">
                                {{ $sem->getStatusLabel() }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.seminars.show', $sem) }}" class="text-blue-600 hover:underline text-sm">Manage</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100">{{ $seminars->links() }}</div>
        @endif
    </div>
</div>
@endsection
