@extends('layouts.app')
@section('title', 'Certificates')
@section('page-title', 'Certificates')

@push('styles')
<style>
.cert-card { background:#fff; border:1px solid #e8edf5; border-radius:1rem; padding:1rem; transition:box-shadow 0.15s; }
.cert-card:hover { box-shadow:0 4px 16px rgba(0,0,0,0.07); }
#search-spinner { display:none; }
#search-spinner.visible { display:inline-block; }
</style>
@endpush

@section('content')
<div class="py-6 space-y-4">

    {{-- Unverified alert badge --}}
    @if(($unverifiedCount ?? 0) > 0)
    <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 flex items-center gap-3">
        <div class="w-8 h-8 bg-amber-400 rounded-lg flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
        </div>
        <div class="flex-1">
            <span class="font-bold text-amber-800 text-sm">
                {{ $unverifiedCount }} certificate{{ $unverifiedCount !== 1 ? 's' : '' }} pending record verification
            </span>
            <span class="text-amber-700 text-sm"> — review and verify the sacramental records for these requests.</span>
        </div>
        <a href="{{ route('admin.certificates.index', ['ver_status' => 'pending']) }}"
           class="text-xs font-bold text-amber-800 underline underline-offset-2 whitespace-nowrap">View pending →</a>
    </div>
    @endif

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <form id="cert-filter-form" method="GET" action="{{ route('admin.certificates.index') }}" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs text-gray-500 mb-1">
                    Search
                    <svg id="search-spinner" class="inline w-3 h-3 ml-1 animate-spin text-blue-500" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                    </svg>
                </label>
                <input type="text" id="cert-search" name="search" value="{{ request('search') }}"
                       class="form-input text-sm w-56" placeholder="Name, cert #, priest, sponsor…"
                       autocomplete="off">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Type</label>
                <select name="type" class="form-select text-sm" onchange="document.getElementById('cert-filter-form').submit()">
                    <option value="">All Types</option>
                    <option value="baptism"         @selected(request('type')==='baptism')>Baptism</option>
                    <option value="confirmation"    @selected(request('type')==='confirmation')>Confirmation</option>
                    <option value="marriage"        @selected(request('type')==='marriage')>Marriage</option>
                    <option value="first_communion" @selected(request('type')==='first_communion')>First Communion</option>
                    <option value="death_burial"    @selected(request('type')==='death_burial')>Death/Burial</option>
                    <option value="no_impediment"   @selected(request('type')==='no_impediment')>No Impediment</option>
                    <option value="membership"      @selected(request('type')==='membership')>Membership</option>
                    <option value="other"           @selected(request('type')==='other')>Other</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Status</label>
                <select name="status" class="form-select text-sm" onchange="document.getElementById('cert-filter-form').submit()">
                    <option value="">All Status</option>
                    <option value="draft"    @selected(request('status')==='draft')>Draft</option>
                    <option value="issued"   @selected(request('status')==='issued')>Issued</option>
                    <option value="released" @selected(request('status')==='released')>Released</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Record</label>
                <select name="ver_status" class="form-select text-sm" onchange="document.getElementById('cert-filter-form').submit()">
                    <option value="">All</option>
                    <option value="verified"   @selected(request('ver_status')==='verified')>✓ Verified</option>
                    <option value="pending"    @selected(request('ver_status')==='pending')>⏳ Pending</option>
                    <option value="unverified" @selected(request('ver_status')==='unverified')>⚠ No Record</option>
                </select>
            </div>
            <button type="submit" class="btn-secondary text-sm">Filter</button>
            @if(request()->hasAny(['search','type','status','ver_status']))
            <a href="{{ route('admin.certificates.index') }}" class="btn-secondary text-sm">Clear</a>
            @endif
            <div class="ml-auto">
                <a href="{{ route('admin.certificates.create') }}" class="btn-primary text-sm">+ New Certificate</a>
            </div>
        </form>
    </div>

    {{-- ── MOBILE CARDS ── --}}
    <div id="certs-list" class="space-y-3 lg:hidden">
        @forelse($certificates as $cert)
        @php
            $statusColors = ['draft'=>'gray','issued'=>'blue','released'=>'green'];
            $sc = $statusColors[$cert->status] ?? 'gray';
            $typeIcons = ['baptism'=>'💧','confirmation'=>'✝️','marriage'=>'💍','first_communion'=>'🕊️','death_burial'=>'🕯️'];
            $vr = $cert->record_verification_status ?? 'pending';
            $vrLabel = ['verified'=>'✓ Verified','unverified'=>'⚠ No Record','pending'=>'⏳ Pending'][$vr] ?? '';
            $vrColor = ['verified'=>'text-green-700 bg-green-50','unverified'=>'text-red-700 bg-red-50','pending'=>'text-amber-700 bg-amber-50'][$vr] ?? '';
        @endphp
        <div class="cert-card">
            <div class="flex items-start gap-3 mb-2">
                <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-content-center text-lg flex-shrink-0 flex items-center justify-center">
                    {{ $typeIcons[$cert->type] ?? '📜' }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-2 flex-wrap">
                        <div>
                            <a href="{{ route('admin.certificates.show', $cert) }}" class="font-semibold text-gray-900 hover:text-blue-700 text-sm capitalize">
                                {{ str_replace('_', ' ', $cert->type) }} Certificate
                            </a>
                            <p class="text-xs font-mono text-gray-400 mt-0.5">{{ $cert->certificate_number }}</p>
                        </div>
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-{{ $sc }}-100 text-{{ $sc }}-800">
                                {{ ucfirst($cert->status) }}
                            </span>
                            @if(in_array($cert->type, \App\Models\Certificate::REQUIRES_RECORD) && $vrLabel)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $vrColor }}">
                                {{ $vrLabel }}
                            </span>
                            @endif
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $cert->parishioner->full_name }} · {{ $cert->issued_date->format('M d, Y') }}</p>
                </div>
            </div>
            <div class="flex items-center gap-1.5 flex-wrap pt-2 border-t border-gray-100">
                <a href="{{ route('admin.certificates.show', $cert) }}" class="action-btn action-btn-view">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    View
                </a>
                <a href="{{ route('admin.certificates.download', $cert) }}" class="action-btn action-btn-green">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    PDF
                </a>
                @if($cert->status === 'issued')
                <form method="POST" action="{{ route('admin.certificates.release', $cert) }}" class="inline">
                    @csrf
                    <button type="submit" class="action-btn" style="background:#f5f3ff;color:#6d28d9;border-color:#ddd6fe;">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Release
                    </button>
                </form>
                @endif
                @if(in_array($cert->type, \App\Models\Certificate::REQUIRES_RECORD) && in_array($vr, ['pending','unverified']))
                <form method="POST" action="{{ route('admin.certificates.verify-record', $cert) }}" class="inline">
                    @csrf
                    <button type="submit" class="action-btn" style="background:#f0fdf4;color:#166534;border-color:#bbf7d0;"
                            onclick="return confirm('Verify record for this certificate?')">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/></svg>
                        Verify
                    </button>
                </form>
                @endif
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl border border-gray-100 p-10 text-center">
            <div class="text-4xl mb-3">🔍</div>
            <p class="text-gray-500 font-medium">No certificates found</p>
            <p class="text-sm text-gray-400 mt-1">Try adjusting your search or filters</p>
        </div>
        @endforelse
        @if($certificates->hasPages())
        <div class="bg-white rounded-xl border border-gray-100 px-4 py-3">{{ $certificates->links() }}</div>
        @endif
    </div>

    {{-- ── DESKTOP TABLE ── --}}
    <div id="certificates-table" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hidden lg:block">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500">
                    <th class="px-4 py-3 font-medium">Certificate #</th>
                    <th class="px-4 py-3 font-medium">Parishioner</th>
                    <th class="px-4 py-3 font-medium">Type</th>
                    <th class="px-4 py-3 font-medium">Issued Date</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3 font-medium">Record</th>
                    <th class="px-4 py-3 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($certificates as $cert)
                @php
                    $statusColors = ['draft'=>'gray','issued'=>'blue','released'=>'green'];
                    $sc = $statusColors[$cert->status] ?? 'gray';
                    $vr = $cert->record_verification_status ?? 'pending';
                    $vrLabel  = ['verified'=>'✓ Verified','unverified'=>'⚠ No Record','pending'=>'⏳ Pending'][$vr] ?? '—';
                    $vrClass  = ['verified'=>'bg-green-100 text-green-800','unverified'=>'bg-red-100 text-red-800','pending'=>'bg-amber-100 text-amber-800'][$vr] ?? '';
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-mono text-xs text-gray-700">{{ $cert->certificate_number }}</td>
                    <td class="px-4 py-3 font-medium text-gray-900">
                        <a href="{{ route('admin.certificates.show', $cert) }}" class="hover:text-blue-700">{{ $cert->parishioner->full_name }}</a>
                    </td>
                    <td class="px-4 py-3 text-gray-600 capitalize">{{ str_replace('_',' ',$cert->type) }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $cert->issued_date->format('M d, Y') }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $sc }}-100 text-{{ $sc }}-800">{{ ucfirst($cert->status) }}</span>
                    </td>
                    <td class="px-4 py-3">
                        @if(in_array($cert->type, \App\Models\Certificate::REQUIRES_RECORD))
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $vrClass }}">{{ $vrLabel }}</span>
                        @else
                        <span class="text-gray-300 text-xs">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <a href="{{ route('admin.certificates.show', $cert) }}" class="action-btn action-btn-view">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                View
                            </a>
                            <a href="{{ route('admin.certificates.download', $cert) }}" class="action-btn action-btn-green">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                PDF
                            </a>
                            @if($cert->status === 'issued')
                            <form method="POST" action="{{ route('admin.certificates.release', $cert) }}" class="inline">
                                @csrf
                                <button type="submit" class="action-btn" style="background:#f5f3ff;color:#6d28d9;border-color:#ddd6fe;">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Release
                                </button>
                            </form>
                            @endif
                            @if(in_array($cert->type, \App\Models\Certificate::REQUIRES_RECORD) && in_array($vr, ['pending','unverified']))
                            <form method="POST" action="{{ route('admin.certificates.verify-record', $cert) }}" class="inline">
                                @csrf
                                <button type="submit" class="action-btn" style="background:#f0fdf4;color:#166534;border-color:#bbf7d0;"
                                        onclick="return confirm('Verify record for this certificate?')">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/></svg>
                                    Verify
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-12 text-center">
                        <div class="text-4xl mb-2">🔍</div>
                        <p class="text-gray-500 font-medium">No certificates found</p>
                        <p class="text-sm text-gray-400 mt-1">Try a different search term or clear the filters</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
        @if($certificates->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $certificates->links() }}</div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const input   = document.getElementById('cert-search');
    const form    = document.getElementById('cert-filter-form');
    const spinner = document.getElementById('search-spinner');
    if (!input || !form) return;

    let timer;
    input.addEventListener('input', function () {
        clearTimeout(timer);
        spinner.classList.add('visible');
        timer = setTimeout(function () {
            // Update the URL with the current query string so pagination links
            // stay consistent, then submit the form normally (full page reload
            // is fine — no Livewire required for a standard Laravel app).
            form.submit();
        }, 300);
    });

    // Hide spinner once the page is fully loaded (handles back-navigation)
    window.addEventListener('pageshow', function () {
        spinner.classList.remove('visible');
    });
})();
</script>
@endpush
