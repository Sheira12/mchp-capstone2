@extends('layouts.app')

@section('title', 'Certificate')
@section('page-title', 'Certificate')

@section('content')
<div class="py-6 space-y-5">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.certificates.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Certificates</a>
        <div class="ml-auto flex gap-2">
            <a href="{{ route('admin.certificates.download', $certificate) }}" class="btn-secondary text-sm">⬇ Download PDF</a>
            <form method="POST" action="{{ route('admin.certificates.regenerate', $certificate) }}">
                @csrf
                <button type="submit" class="btn-secondary text-sm">↺ Regenerate</button>
            </form>
            @if($certificate->status === 'issued')
            <form method="POST" action="{{ route('admin.certificates.release', $certificate) }}">
                @csrf
                <button type="submit" class="btn-primary text-sm">Mark Released</button>
            </form>
            @endif
        </div>
    </div>

    {{-- Certificate Info --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-start justify-between mb-5">
            <div>
                <p class="font-mono text-sm text-gray-500 mb-1">{{ $certificate->certificate_number }}</p>
                <h2 class="text-xl font-bold text-gray-900">{{ $certificate->parishioner->full_name }}</h2>
                <p class="text-gray-500 capitalize">{{ str_replace('_', ' ', $certificate->type) }} Certificate</p>
            </div>
            @php
                $statusColors = ['draft' => 'gray', 'issued' => 'blue', 'released' => 'green'];
                $sc = $statusColors[$certificate->status] ?? 'gray';
            @endphp
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-{{ $sc }}-100 text-{{ $sc }}-800">
                {{ ucfirst($certificate->status) }}
            </span>
        </div>

        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div>
                <dt class="text-gray-500">Issued Date</dt>
                <dd class="font-medium text-gray-900 mt-0.5">{{ $certificate->issued_date->format('F d, Y') }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Issued By</dt>
                <dd class="font-medium text-gray-900 mt-0.5">{{ $certificate->issuedBy?->name ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Purpose</dt>
                <dd class="font-medium text-gray-900 mt-0.5">{{ $certificate->purpose ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">File</dt>
                <dd class="font-medium text-gray-900 mt-0.5">
                    @if($certificate->file_path)
                        <span class="text-green-600">✓ Generated</span>
                    @else
                        <span class="text-gray-400">Not generated</span>
                    @endif
                </dd>
            </div>
        </dl>

        @if($certificate->notes)
        <div class="mt-4 pt-4 border-t border-gray-100">
            <dt class="text-sm text-gray-500 mb-1">Notes</dt>
            <dd class="text-sm text-gray-700">{{ $certificate->notes }}</dd>
        </div>
        @endif
    </div>

    {{-- QR Code --}}
    @if($certificate->qrCode)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="font-semibold text-gray-800 mb-3">QR Code Verification</h3>
        <div class="flex items-center gap-6">
            <img src="{{ Storage::url($certificate->qrCode->qr_image_path) }}" alt="QR Code" class="w-32 h-32">
            <div>
                <p class="text-sm text-gray-600 mb-1">Verification URL:</p>
                <a href="{{ $certificate->qrCode->verification_url }}" target="_blank"
                   class="text-blue-600 hover:underline text-sm break-all">
                    {{ $certificate->qrCode->verification_url }}
                </a>
            </div>
        </div>
    </div>
    @endif

    {{-- Linked Sacramental Record --}}
    @if($certificate->sacramentalRecord)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="font-semibold text-gray-800 mb-3">Linked Sacramental Record</h3>
        <div class="flex items-center justify-between">
            <div>
                <p class="font-medium text-gray-900 capitalize">{{ str_replace('_', ' ', $certificate->sacramentalRecord->type) }}</p>
                <p class="text-sm text-gray-500">{{ $certificate->sacramentalRecord->date_administered->format('F d, Y') }}</p>
            </div>
            <a href="{{ route('admin.sacramental-records.show', $certificate->sacramentalRecord) }}" class="btn-secondary text-sm">View Record</a>
        </div>
    </div>
    @endif
</div>
@endsection
