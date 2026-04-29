@extends('layouts.portal')

@section('title', 'My Certificates')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-900">My Certificates</h1>
        <p class="text-sm text-gray-500 mt-1">View and download your official parish certificates</p>
    </div>

    @if($certificates->isEmpty())
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-16 text-center">
        <div class="w-20 h-20 bg-amber-50 rounded-full flex items-center justify-center mx-auto mb-5">
            <svg class="w-10 h-10 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        <h3 class="text-lg font-bold text-gray-900 mb-2">No certificates yet</h3>
        <p class="text-sm text-gray-500 mb-6 max-w-sm mx-auto">Certificates will appear here once issued by the parish office. Book a service to request one.</p>
        <a href="{{ route('parishioner.bookings.create') }}"
           class="inline-flex items-center gap-2 bg-blue-600 text-white font-bold px-6 py-3 rounded-xl hover:bg-blue-700 shadow-lg transition text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Book a Service
        </a>
    </div>
    @else

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        @foreach($certificates as $cert)
        @php
            $statusColors = ['draft'=>'gray','issued'=>'blue','released'=>'green'];
            $sc = $statusColors[$cert->status] ?? 'gray';
            $typeIcons = [
                'baptism' => ['bg'=>'#eff6ff','stroke'=>'#2563eb','path'=>'M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z'],
                'confirmation' => ['bg'=>'#f5f3ff','stroke'=>'#7c3aed','path'=>'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
                'marriage' => ['bg'=>'#fdf2f8','stroke'=>'#db2777','path'=>'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z'],
                'first_communion' => ['bg'=>'#f0fdf4','stroke'=>'#16a34a','path'=>'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
            ];
            $icon = $typeIcons[$cert->type] ?? ['bg'=>'#f8faff','stroke'=>'#2563eb','path'=>'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'];
        @endphp
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-lg transition overflow-hidden">
            {{-- Top color bar --}}
            <div class="h-1.5 bg-{{ $sc }}-400"></div>

            <div class="p-6">
                <div class="flex items-start gap-4 mb-4">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center flex-shrink-0"
                         style="background:{{ $icon['bg'] }};">
                        <svg class="w-7 h-7" fill="none" stroke="{{ $icon['stroke'] }}" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon['path'] }}"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="font-bold text-gray-900 capitalize">{{ str_replace('_', ' ', $cert->type) }} Certificate</h3>
                        <p class="text-xs text-gray-400 font-mono mt-0.5">{{ $cert->certificate_number }}</p>
                        <p class="text-xs text-gray-500 mt-1">Issued {{ $cert->issued_date->format('M d, Y') }}</p>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-{{ $sc }}-100 text-{{ $sc }}-700 flex-shrink-0">
                        {{ ucfirst($cert->status) }}
                    </span>
                </div>

                @if($cert->purpose)
                <p class="text-xs text-gray-500 mb-4 bg-gray-50 rounded-lg px-3 py-2">
                    <span class="font-semibold">Purpose:</span> {{ $cert->purpose }}
                </p>
                @endif

                <div class="flex items-center justify-between pt-4 border-t border-gray-50">
                    @if($cert->status === 'released' && $cert->file_path)
                    <a href="{{ route('parishioner.certificates.download', $cert) }}"
                       class="inline-flex items-center gap-2 bg-blue-600 text-white font-bold px-5 py-2.5 rounded-xl hover:bg-blue-700 shadow-md hover:shadow-lg transition text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Download PDF
                    </a>
                    @elseif($cert->status === 'issued')
                    <div class="flex items-center gap-2 text-blue-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="text-sm font-semibold">Ready for pickup</span>
                    </div>
                    @else
                    <div class="flex items-center gap-2 text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="text-sm">Processing</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div>{{ $certificates->links() }}</div>

    @endif

    {{-- Info Card --}}
    <div class="bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-100 rounded-2xl p-6">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 bg-amber-500 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <h3 class="font-bold text-gray-900 mb-1">Need a Certificate?</h3>
                <p class="text-sm text-gray-600 mb-3">Request a baptismal, confirmation, marriage, or other parish certificate by booking a service or contacting the parish office directly.</p>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('parishioner.bookings.create') }}"
                       class="inline-flex items-center gap-2 bg-amber-600 text-white font-semibold px-5 py-2 rounded-lg hover:bg-amber-700 transition text-sm">
                        Book a Service
                    </a>
                    <a href="{{ route('contact') }}"
                       class="inline-flex items-center gap-2 bg-white text-amber-700 border border-amber-200 font-semibold px-5 py-2 rounded-lg hover:bg-amber-50 transition text-sm">
                        Contact Parish Office
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
