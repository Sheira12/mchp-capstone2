@extends('layouts.portal')
@section('title', 'My Seminar History')

@section('content')
<div class="space-y-5 max-w-3xl w-full">

    <div class="flex items-center gap-3">
        <a href="{{ route('parishioner.seminars.index') }}"
           class="w-9 h-9 rounded-lg bg-white border border-gray-200 flex items-center justify-center hover:bg-gray-50">
            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">My Seminar History</h1>
            <p class="text-sm text-gray-500 mt-0.5">All seminars you have registered for or attended.</p>
        </div>
    </div>

    @if($registrations->isEmpty())
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm py-14 text-center text-gray-400">
        <p class="font-medium">No seminar history yet.</p>
        <a href="{{ route('parishioner.seminars.index') }}" class="mt-3 inline-block text-blue-600 hover:underline text-sm">Browse upcoming seminars</a>
    </div>
    @else
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left font-semibold text-gray-600">Seminar</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-600">Service</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-600">Date</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-600">Status</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-600">Attended</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($registrations as $reg)
                    @php $sc = ['registered'=>'blue','attended'=>'green','absent'=>'red','cancelled'=>'gray'][$reg->status] ?? 'gray'; @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3 font-semibold text-gray-900">{{ $reg->seminar?->title }}</td>
                        <td class="px-5 py-3 text-gray-600 text-xs">{{ $reg->seminar?->service?->name }}</td>
                        <td class="px-5 py-3 text-gray-600 text-xs whitespace-nowrap">
                            {{ $reg->seminar?->scheduled_at?->format('M d, Y') }}
                        </td>
                        <td class="px-5 py-3">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold bg-{{ $sc }}-100 text-{{ $sc }}-700">
                                {{ $reg->getStatusLabel() }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-gray-500 text-xs">
                            {{ $reg->attended_at?->format('M d, Y g:i A') ?? '—' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3 border-t border-gray-100">{{ $registrations->links() }}</div>
    </div>
    @endif

</div>
@endsection
