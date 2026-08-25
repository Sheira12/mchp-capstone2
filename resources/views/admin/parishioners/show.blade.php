@extends('layouts.app')

@section('title', $parishioner->full_name)
@section('page-title', 'Parishioner Profile')

@section('content')
<div class="py-6 space-y-5">

    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('admin.parishioners.index') }}" class="text-sm text-blue-600 hover:underline">← Back to Parishioners</a>
        <div class="flex gap-2">
            <a href="{{ route('admin.parishioners.soa', $parishioner) }}" class="btn-secondary text-sm flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                Statement of Account
            </a>
            <a href="{{ route('admin.sacramental-records.create', ['parishioner_id' => $parishioner->id]) }}" class="btn-secondary text-sm">+ Add Record</a>
            <a href="{{ route('admin.bookings.create') }}?parishioner_id={{ $parishioner->id }}" class="btn-secondary text-sm">+ New Booking</a>
            <a href="{{ route('admin.parishioners.edit', $parishioner) }}" class="btn-primary text-sm">Edit Profile</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Profile Card --}}
        <div class="space-y-5">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 text-center">
                @if($parishioner->photo_path)
                <img src="{{ Storage::url($parishioner->photo_path) }}" alt="{{ $parishioner->full_name }}" class="w-24 h-24 rounded-full mx-auto object-cover mb-4 border-4 border-gray-100">
                @else
                <div class="w-24 h-24 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-3xl mx-auto mb-4">
                    {{ substr($parishioner->first_name, 0, 1) }}{{ substr($parishioner->last_name, 0, 1) }}
                </div>
                @endif
                <h2 class="text-xl font-bold text-gray-900">{{ $parishioner->full_name }}</h2>
                @if($parishioner->is_head_of_family)
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mt-1">Head of Family</span>
                @endif
                <p class="text-gray-500 text-sm mt-2">{{ $parishioner->barangay ?? 'No barangay' }}</p>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium mt-2 {{ $parishioner->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                    {{ $parishioner->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>

            {{-- Contact Info --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="font-semibold text-gray-800 mb-3">Contact Information</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Phone</dt>
                        <dd class="font-medium">{{ $parishioner->contact_number ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Email</dt>
                        <dd class="font-medium text-xs">{{ $parishioner->email ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Birthdate</dt>
                        <dd class="font-medium">{{ $parishioner->birthdate?->format('M d, Y') ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Age</dt>
                        <dd class="font-medium">{{ $parishioner->age ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Gender</dt>
                        <dd class="font-medium">{{ ucfirst($parishioner->gender ?? '—') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Civil Status</dt>
                        <dd class="font-medium">{{ ucfirst($parishioner->civil_status ?? '—') }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Family --}}
            @if($parishioner->family)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="font-semibold text-gray-800 mb-3">Family</h3>
                <a href="{{ route('admin.families.show', $parishioner->family) }}" class="font-medium text-blue-700 hover:underline">{{ $parishioner->family->family_name }}</a>
                <p class="text-sm text-gray-500 mt-1">{{ $parishioner->relationship_to_head ?? 'Member' }}</p>
                <div class="mt-3 space-y-1">
                    @foreach($parishioner->family->members->where('id', '!=', $parishioner->id)->take(5) as $member)
                    <a href="{{ route('admin.parishioners.show', $member) }}" class="block text-sm text-gray-600 hover:text-blue-700">
                        {{ $member->full_name }}
                        @if($member->is_head_of_family)<span class="text-xs text-blue-500">(Head)</span>@endif
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Sacramental Records --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-800">Sacramental Records</h3>
                    <a href="{{ route('admin.sacramental-records.create', ['parishioner_id' => $parishioner->id]) }}" class="text-sm text-blue-600 hover:underline">+ Add</a>
                </div>
                @if($parishioner->sacramentalRecords->count())
                <div class="divide-y divide-gray-50">
                    @foreach($parishioner->sacramentalRecords as $record)
                    <div class="px-5 py-3 flex items-center justify-between">
                        <div>
                            <p class="font-medium text-sm text-gray-900">{{ $record->getTypeLabel() }}</p>
                            <p class="text-xs text-gray-400">{{ $record->date_administered->format('M d, Y') }} · {{ $record->celebrant }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($record->verified_at)
                            <span class="text-xs text-green-600">✓ Verified</span>
                            @endif
                            <a href="{{ route('admin.sacramental-records.show', $record) }}" class="text-xs text-blue-600 hover:underline">View</a>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="px-5 py-4 text-sm text-gray-400">No sacramental records yet.</p>
                @endif
            </div>

            {{-- Recent Bookings --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-800">Recent Bookings</h3>
                    <a href="{{ route('admin.bookings.index', ['search' => $parishioner->full_name]) }}" class="text-sm text-blue-600 hover:underline">View all</a>
                </div>
                @if($parishioner->bookings->count())
                <div class="divide-y divide-gray-50">
                    @foreach($parishioner->bookings as $booking)
                    @php $statusColors = ['pending' => 'amber', 'confirmed' => 'green', 'completed' => 'blue', 'cancelled' => 'red']; @endphp
                    <div class="px-5 py-3 flex items-center justify-between">
                        <div>
                            <p class="font-medium text-sm text-gray-900">{{ $booking->getTypeLabel() }}</p>
                            <p class="text-xs text-gray-400">{{ $booking->scheduled_date->format('M d, Y') }} · {{ $booking->reference_number }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="badge badge-{{ $booking->status }}">{{ $booking->getStatusLabel() }}</span>
                            <a href="{{ route('admin.bookings.show', $booking) }}" class="text-xs text-blue-600 hover:underline">View</a>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="px-5 py-4 text-sm text-gray-400">No bookings yet.</p>
                @endif
            </div>

            {{-- Certificates --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-800">Certificates</h3>
                    <a href="{{ route('admin.certificates.create', ['parishioner_id' => $parishioner->id]) }}" class="text-sm text-blue-600 hover:underline">+ Issue</a>
                </div>
                @if($parishioner->certificates->count())
                <div class="divide-y divide-gray-50">
                    @foreach($parishioner->certificates as $cert)
                    <div class="px-5 py-3 flex items-center justify-between">
                        <div>
                            <p class="font-medium text-sm text-gray-900">{{ $cert->getTypeLabel() }}</p>
                            <p class="text-xs text-gray-400">{{ $cert->certificate_number }} · {{ $cert->issued_date->format('M d, Y') }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="badge {{ $cert->status === 'released' ? 'badge-completed' : 'badge-pending' }}">{{ ucfirst($cert->status) }}</span>
                            <a href="{{ route('admin.certificates.download', $cert) }}" class="text-xs text-blue-600 hover:underline">PDF</a>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="px-5 py-4 text-sm text-gray-400">No certificates issued yet.</p>
                @endif
            </div>

            {{-- Profile Change History --}}
            @if($parishioner->profileChanges->count())
            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800">Change History</h3>
                </div>
                <div class="divide-y divide-gray-50 max-h-48 overflow-y-auto">
                    @foreach($parishioner->profileChanges as $change)
                    <div class="px-5 py-2 text-xs text-gray-600">
                        <span class="font-medium">{{ $change->changedBy?->name ?? 'System' }}</span>
                        changed <span class="font-medium">{{ $change->field_name }}</span>
                        from "<span class="text-red-600">{{ $change->old_value ?? 'empty' }}</span>"
                        to "<span class="text-green-600">{{ $change->new_value }}</span>"
                        <span class="text-gray-400">· {{ $change->created_at->diffForHumans() }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>
    </div>

    {{-- Delete --}}
    <div class="flex justify-end">
        <form action="{{ route('admin.parishioners.destroy', $parishioner) }}" method="POST"
              onsubmit="return confirm('Delete this parishioner profile? This cannot be undone.')">
            @csrf @method('DELETE')
            <button type="submit" class="btn-danger text-sm">Delete Profile</button>
        </form>
    </div>

</div>
@endsection
