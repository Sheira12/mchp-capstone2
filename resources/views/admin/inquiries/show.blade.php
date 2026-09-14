@extends('layouts.app')
@section('title', 'Inquiry #' . $inquiry->id . ' — ' . $inquiry->subject)
@section('page-title', 'Inquiry Details')

@php
    use App\Services\InquirySubjectConfig;
    use Carbon\Carbon;

    $subjects    = array_keys(InquirySubjectConfig::all());
    $adminUser   = auth()->user();
    $replyCount  = count($inquiry->replies ?? []);

    // Status display config — includes all DB values + new ones
    $statusCfg = [
        'new'         => ['label' => 'New',                'dot' => 'bg-blue-500',   'bg' => 'bg-blue-100',   'text' => 'text-blue-700'],
        'read'        => ['label' => 'Under Review',       'dot' => 'bg-amber-500',  'bg' => 'bg-amber-100',  'text' => 'text-amber-700'],
        'in_progress' => ['label' => 'In Progress',        'dot' => 'bg-purple-500', 'bg' => 'bg-purple-100', 'text' => 'text-purple-700'],
        'replied'     => ['label' => 'Replied',            'dot' => 'bg-indigo-500', 'bg' => 'bg-indigo-100', 'text' => 'text-indigo-700'],
        'resolved'    => ['label' => 'Resolved',           'dot' => 'bg-green-500',  'bg' => 'bg-green-100',  'text' => 'text-green-700'],
        'closed'      => ['label' => 'Closed',             'dot' => 'bg-gray-500',   'bg' => 'bg-gray-100',   'text' => 'text-gray-600'],
    ];
    $sc = $statusCfg[$inquiry->status] ?? $statusCfg['read'];

    // Workflow steps
    $workflowSteps = [
        ['key' => 'new',         'label' => 'Submitted'],
        ['key' => 'read',        'label' => 'Under Review'],
        ['key' => 'in_progress', 'label' => 'In Progress'],
        ['key' => 'replied',     'label' => 'Replied'],
        ['key' => 'resolved',    'label' => 'Resolved'],
        ['key' => 'closed',      'label' => 'Closed'],
    ];
    $statusOrder = array_column($workflowSteps, 'key');
    $currentIdx  = array_search($inquiry->status, $statusOrder);
    if ($currentIdx === false) $currentIdx = 0;

    // Quick reply templates
    $quickReplies = [
        ''  => '— Select a quick reply template —',
        'received'   => 'Your inquiry has been received and is currently being reviewed. We will get back to you shortly.',
        'documents'  => 'Please review the requirements and submit the necessary documents so we can continue processing your request.',
        'info'       => 'Please provide the missing information/documents so we can continue processing your request.',
        'approved'   => 'Your inquiry has been approved. Please proceed to the parish office to complete the necessary arrangements.',
        'declined'   => 'Your appointment request could not be accommodated at this time. Please contact the parish office directly for further assistance.',
        'schedule'   => 'Please note that the parish office is open Monday to Saturday, 8:00 AM – 5:00 PM. You may visit us in person or call ' . config('parish.phone', 'the parish office') . ' for more details.',
    ];

    // Subject icon map
    $subjectIcons = [
        'General Inquiry'       => '💬',
        'Mass Schedule'         => '⛪',
        'Sacrament Requirements'=> '✝️',
        'Booking / Appointment' => '📅',
        'Certificate Request'   => '📜',
        'Donation / Support'    => '🙏',
        'Complaint / Feedback'  => '📝',
        'Other'                 => '📌',
    ];
    $subjectIcon = $subjectIcons[$inquiry->subject] ?? '📌';

    // Build merged timeline (original message + all replies), sorted by time
    $timeline = [];
    $timeline[] = [
        'type'        => 'user',
        'sender'      => $inquiry->name,
        'role'        => 'Parishioner',
        'message'     => $inquiry->message,
        'sent_at'     => $inquiry->created_at,
        'attachments' => $inquiry->attachments ?? [],
        'subject'     => $inquiry->subject,
    ];
    foreach (($inquiry->replies ?? []) as $reply) {
        $timeline[] = [
            'type'        => 'admin',
            'sender'      => $reply['admin_name'] ?? 'Admin',
            'role'        => 'Parish Administrator',
            'message'     => $reply['message'] ?? '',
            'sent_at'     => Carbon::parse($reply['sent_at']),
            'attachments' => $reply['attachments'] ?? [],
            'subject'     => $reply['reply_subject'] ?? $inquiry->subject,
        ];
    }
    // Sort by sent_at ascending
    usort($timeline, fn($a, $b) => $a['sent_at'] <=> $b['sent_at']);

    // Group timeline by date for date separators
    $timelineByDay = collect($timeline)->groupBy(fn($item) => Carbon::parse($item['sent_at'])->format('Y-m-d'));

    // All attachments (user + all admin replies) for sidebar
    $allAttachments = $inquiry->attachments ?? [];
    foreach (($inquiry->replies ?? []) as $reply) {
        foreach (($reply['attachments'] ?? []) as $att) {
            $att['_from'] = $reply['admin_name'] ?? 'Admin';
            $allAttachments[] = $att;
        }
    }

    // File type icon helper
    function fileTypeIcon(string $mime): string {
        if (str_contains($mime, 'pdf')) return '📄';
        if (str_contains($mime, 'image')) return '🖼️';
        if (str_contains($mime, 'word') || str_contains($mime, 'document')) return '📝';
        return '📎';
    }

    // Determine which status actions to show
    $showInProgress = !in_array($inquiry->status, ['in_progress', 'resolved', 'closed']);
    $showResolve    = !in_array($inquiry->status, ['resolved', 'closed']);
    $showClose      = $inquiry->status !== 'closed';
    $showReopen     = in_array($inquiry->status, ['resolved', 'closed']);
@endphp

@push('styles')
<style>
/* ── Inquiry Detail Page ───────────────────────────────────────── */
.inq-bubble-user {
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 1rem;
    border-top-left-radius: 0.25rem;
}
.inq-bubble-admin {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 1rem;
    border-top-right-radius: 0.25rem;
}
.inq-avatar {
    width:38px; height:38px; border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    font-weight:700; font-size:0.875rem; flex-shrink:0;
}
.inq-date-sep {
    display:flex; align-items:center; gap:0.75rem;
    color:#94a3b8; font-size:0.7rem; font-weight:600;
    text-transform:uppercase; letter-spacing:0.06em; margin: 1rem 0;
}
.inq-date-sep::before, .inq-date-sep::after {
    content:''; flex:1; height:1px; background:#e2e8f0;
}
.workflow-step { display:flex; flex-direction:column; align-items:center; gap:4px; flex:1; min-width:0; }
.workflow-dot  { width:32px; height:32px; border-radius:50%; display:flex; align-items:center; justify-content:center; border:2px solid #e2e8f0; background:#fff; transition:all 0.2s; font-size:0.75rem; flex-shrink:0; }
.workflow-dot.done   { background:#2563eb; border-color:#2563eb; color:#fff; }
.workflow-dot.active { background:#fff; border-color:#2563eb; color:#2563eb; box-shadow:0 0 0 3px #dbeafe; }
.workflow-label { font-size:0.65rem; text-align:center; white-space:nowrap; line-height:1.2; }
.workflow-line { flex:1; height:2px; background:#e2e8f0; margin-top:-16px; }
.workflow-line.done { background:#2563eb; }
.sidebar-card { background:#fff; border:1px solid #e8edf5; border-radius:0.875rem; overflow:hidden; }
.sidebar-card-header { padding:0.75rem 1rem; border-bottom:1px solid #f1f5f9; background:#f8fafc; }
.sidebar-card-body   { padding:0.875rem 1rem; }
.action-status-btn {
    display:inline-flex; align-items:center; gap:6px; width:100%;
    padding:0.5rem 0.875rem; border-radius:0.5rem; font-size:0.8125rem;
    font-weight:600; border:1.5px solid transparent; cursor:pointer;
    transition:all 0.15s; text-align:left; margin-bottom:0.375rem;
}
.att-chip {
    display:inline-flex; align-items:center; gap:6px;
    padding:4px 10px; border-radius:0.5rem; font-size:0.75rem;
    font-weight:600; text-decoration:none; transition:all 0.15s;
    border:1px solid; max-width:100%;
}
/* Textarea grow */
#reply-textarea { min-height:100px; }
</style>
@endpush

@section('content')
<div class="py-5">

    {{-- ── Back + Page Header ── --}}
    <div class="flex items-center justify-between mb-5 flex-wrap gap-3">
        <a href="{{ route('admin.inquiries.index') }}"
           class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-blue-700 font-medium transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Inquiries
        </a>
        <div class="flex items-center gap-2">
            <span class="text-xs text-gray-400 font-mono">INQ-{{ str_pad($inquiry->id, 5, '0', STR_PAD_LEFT) }}</span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold {{ $sc['bg'] }} {{ $sc['text'] }}">
                <span class="w-1.5 h-1.5 rounded-full {{ $sc['dot'] }}"></span>
                {{ $sc['label'] }}
            </span>
        </div>
    </div>

    {{-- ── Compact Inquiry Header Card ── --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm mb-5 overflow-hidden">
        <div class="px-5 py-4 flex flex-wrap items-start gap-4">
            {{-- Avatar + sender --}}
            <div class="flex items-center gap-3 flex-shrink-0">
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-100 to-indigo-100 flex items-center justify-center text-blue-700 font-bold text-lg border-2 border-blue-100">
                    {{ strtoupper(substr($inquiry->name, 0, 1)) }}
                </div>
                <div>
                    <p class="font-bold text-gray-900 text-sm leading-tight">{{ $inquiry->name }}</p>
                    <a href="mailto:{{ $inquiry->email }}" class="text-xs text-blue-600 hover:underline">{{ $inquiry->email }}</a>
                    @if($inquiry->phone)
                    <p class="text-xs text-gray-400 mt-0.5">{{ $inquiry->phone }}</p>
                    @endif
                </div>
            </div>

            {{-- Divider --}}
            <div class="hidden sm:block w-px h-12 bg-gray-100 self-center"></div>

            {{-- Metadata chips --}}
            <div class="flex flex-wrap gap-2 items-center flex-1 min-w-0">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-indigo-50 text-indigo-700 border border-indigo-100 rounded-lg text-xs font-semibold whitespace-nowrap">
                    {{ $subjectIcon }} {{ $inquiry->subject }}
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-gray-50 text-gray-600 border border-gray-100 rounded-lg text-xs whitespace-nowrap">
                    <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    {{ $inquiry->created_at->format('M d, Y · g:i A') }}
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-gray-50 text-gray-600 border border-gray-100 rounded-lg text-xs whitespace-nowrap">
                    <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                    {{ $replyCount }} {{ $replyCount === 1 ? 'reply' : 'replies' }}
                </span>
                @if($inquiry->attachments && count($inquiry->attachments))
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-gray-50 text-gray-600 border border-gray-100 rounded-lg text-xs whitespace-nowrap">
                    <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                    {{ count($inquiry->attachments) }} file(s)
                </span>
                @endif
                @if($inquiry->replied_at)
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-green-50 text-green-700 border border-green-100 rounded-lg text-xs whitespace-nowrap">
                    Last reply {{ $inquiry->replied_at->diffForHumans() }}
                </span>
                @endif
            </div>
        </div>

        {{-- Booking appointment strip (shown only for Booking / Appointment inquiries) --}}
        @if($inquiry->preferred_date || $inquiry->preferred_time)
        <div class="px-5 py-3 bg-blue-50 border-t border-blue-100 flex flex-wrap items-center gap-6">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <div>
                    <p class="text-xs font-semibold text-blue-500 uppercase tracking-wide">Preferred Date</p>
                    <p class="text-sm font-bold text-blue-800">{{ $inquiry->preferred_date?->format('F d, Y') ?? '—' }}</p>
                </div>
            </div>
            @if($inquiry->preferred_time)
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div>
                    <p class="text-xs font-semibold text-blue-500 uppercase tracking-wide">Preferred Time</p>
                    <p class="text-sm font-bold text-blue-800">{{ $inquiry->preferred_time }}</p>
                </div>
            </div>
            @endif
            <a href="{{ route('admin.bookings.create') }}" target="_blank"
               class="ml-auto inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Create Booking
            </a>
        </div>
        @endif
    </div>

    {{-- ── Workflow Progress Tracker ── --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-5 py-4 mb-5">
        <p class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-4">Inquiry Progress</p>
        <div class="flex items-start">
            @php
            $shortLabels = [
                'new'         => 'Submitted',
                'read'        => 'Reviewing',
                'in_progress' => 'In Progress',
                'replied'     => 'Replied',
                'resolved'    => 'Resolved',
                'closed'      => 'Closed',
            ];
            @endphp
            @foreach($workflowSteps as $i => $step)
            @php $stepIdx = array_search($step['key'], $statusOrder); $isDone = $currentIdx > $stepIdx; $isActive = $currentIdx === $stepIdx; @endphp
            <div class="workflow-step">
                <div class="workflow-dot {{ $isDone ? 'done' : ($isActive ? 'active' : '') }}">
                    @if($isDone)
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    @else
                    <span style="font-size:0.7rem;font-weight:700;">{{ $i + 1 }}</span>
                    @endif
                </div>
                <p class="workflow-label {{ $isActive ? 'font-bold text-blue-700' : ($isDone ? 'text-blue-500 font-semibold' : 'text-gray-400') }}">
                    {{ $shortLabels[$step['key']] ?? $step['label'] }}
                </p>
            </div>
            @if(!$loop->last)
            <div class="workflow-line {{ $isDone ? 'done' : '' }}" style="margin-top:16px;"></div>
            @endif
            @endforeach
        </div>
    </div>

    {{-- ── Requirements (shown once, based on original subject) ── --}}
    @if(!empty($subjectConfig['requirements']))
    <div class="bg-white rounded-xl border border-indigo-100 shadow-sm mb-5 overflow-hidden">
        <div class="px-5 py-3 bg-indigo-50 border-b border-indigo-100 flex items-center gap-2">
            <span class="text-base">📋</span>
            <p class="text-xs font-bold text-indigo-700 uppercase tracking-wide">Requirements for "{{ $inquiry->subject }}"</p>
        </div>
        <div class="px-5 py-4">
            <ul class="space-y-2.5">
                @foreach($subjectConfig['requirements'] as $req)
                <li class="flex items-start gap-3">
                    <span class="mt-0.5 w-4 h-4 rounded border-2 border-gray-300 flex-shrink-0 bg-white"></span>
                    <span class="text-sm text-gray-700 leading-snug">{{ $req }}</span>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    {{-- ── Main Two-Column Layout ── --}}
    <div class="flex flex-col lg:flex-row gap-5 items-start">

        {{-- ═══ LEFT: Conversation + Reply ═══ --}}
        <div class="flex-1 min-w-0 space-y-4">

            {{-- Conversation Card --}}
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wide">Conversation</p>
                    <span class="text-xs text-gray-400">{{ count($timeline) }} message(s)</span>
                </div>

                <div class="px-5 py-5 space-y-1">
                    @foreach($timelineByDay as $date => $dayMessages)
                    {{-- Date separator --}}
                    <div class="inq-date-sep">
                        {{ Carbon::parse($date)->isToday() ? 'Today' : (Carbon::parse($date)->isYesterday() ? 'Yesterday' : Carbon::parse($date)->format('F d, Y')) }}
                    </div>

                    @foreach($dayMessages as $msg)
                    @php $isAdmin = $msg['type'] === 'admin'; @endphp

                    <div class="flex items-end gap-2.5 mb-4 {{ $isAdmin ? 'flex-row-reverse' : '' }}">
                        {{-- Avatar --}}
                        <div class="inq-avatar flex-shrink-0 {{ $isAdmin ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700' }}">
                            {{ strtoupper(substr($msg['sender'], 0, 1)) }}
                        </div>

                        {{-- Bubble --}}
                        <div class="flex-1 min-w-0 {{ $isAdmin ? 'flex flex-col items-end' : '' }}">
                            {{-- Sender + time --}}
                            <div class="flex items-center gap-2 mb-1 {{ $isAdmin ? 'flex-row-reverse' : '' }}">
                                <span class="text-xs font-bold text-gray-800">{{ $msg['sender'] }}</span>
                                <span class="text-xs text-gray-400 bg-gray-50 px-1.5 py-0.5 rounded">{{ $msg['role'] }}</span>
                                <span class="text-xs text-gray-400">{{ Carbon::parse($msg['sent_at'])->format('g:i A') }}</span>
                            </div>

                            {{-- Reply subject tag (admin only) --}}
                            @if($isAdmin && !empty($msg['subject']))
                            <span class="mb-1.5 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-50 text-indigo-600 border border-indigo-100">
                                {{ $subjectIcon }} {{ $msg['subject'] }}
                            </span>
                            @endif

                            {{-- Message bubble --}}
                            <div class="{{ $isAdmin ? 'inq-bubble-admin' : 'inq-bubble-user' }} px-4 py-3 max-w-xl">
                                <p class="text-sm text-gray-800 leading-relaxed whitespace-pre-wrap">{{ $msg['message'] }}</p>
                            </div>

                            {{-- Attachments --}}
                            @if(!empty($msg['attachments']))
                            <div class="mt-2 flex flex-wrap gap-1.5 {{ $isAdmin ? 'justify-end' : '' }}">
                                @foreach($msg['attachments'] as $att)
                                @php
                                    $encodedPath = base64_encode($att['path']);
                                    $icon = str_contains($att['mime'] ?? '', 'image') ? '🖼️' : (str_contains($att['mime'] ?? '', 'pdf') ? '📄' : '📎');
                                @endphp
                                <a href="{{ route('admin.inquiries.attachment', $encodedPath) }}" target="_blank"
                                   class="att-chip {{ $isAdmin ? 'bg-green-50 text-green-700 border-green-200 hover:bg-green-100' : 'bg-blue-50 text-blue-700 border-blue-200 hover:bg-blue-100' }}">
                                    <span>{{ $icon }}</span>
                                    <span class="truncate max-w-32">{{ $att['original_name'] }}</span>
                                    <span class="opacity-60 flex-shrink-0">{{ round(($att['size'] ?? 0)/1024, 1) }}KB</span>
                                </a>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                    @endforeach
                </div>
            </div>

            {{-- ── Reply Box Card ── --}}
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100 bg-gray-50 flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                    <p class="text-xs font-bold text-gray-600 uppercase tracking-wide">Reply to
                        <span class="normal-case text-blue-600 font-semibold">{{ $inquiry->email }}</span>
                    </p>
                </div>

                <form method="POST" action="{{ route('admin.inquiries.reply', $inquiry) }}"
                      enctype="multipart/form-data" id="reply-form" class="px-5 py-5 space-y-4">
                    @csrf

                    {{-- Quick reply templates --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1.5">Quick Reply Template</label>
                        <select id="quick-reply-select" class="form-select text-sm w-full" onchange="applyQuickReply(this.value)">
                            @foreach($quickReplies as $key => $text)
                            <option value="{{ $text }}">{{ strlen($text) > 70 ? substr($text, 0, 70) . '…' : $text }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Subject --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Subject <span class="text-red-500">*</span></label>
                        <select name="reply_subject" id="reply-subject"
                                class="form-select text-sm w-full @error('reply_subject') border-red-400 @enderror"
                                onchange="handleReplySubjectChange(this.value)">
                            @foreach($subjects as $s)
                            <option value="{{ $s }}" {{ old('reply_subject', $inquiry->subject) === $s ? 'selected' : '' }}>{{ $s }}</option>
                            @endforeach
                        </select>
                        @error('reply_subject')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Message textarea --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">
                            Reply Message <span class="text-red-500">*</span>
                        </label>
                        <textarea name="reply" id="reply-textarea" rows="6"
                                  class="form-input w-full text-sm resize-y @error('reply') border-red-400 @enderror"
                                  placeholder="Write your reply here…">{{ old('reply') }}</textarea>
                        @error('reply')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-400 mt-1 text-right">
                            <span id="char-count">0</span> / 5000
                        </p>
                    </div>

                    {{-- Attachments --}}
                    <div id="reply-attachments-section">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">
                            Attachments
                            <span class="font-normal text-gray-400 ml-1">(optional · JPG, PNG, PDF, DOC, DOCX · max 5 MB · up to 5 files)</span>
                        </label>
                        <div class="border-2 border-dashed border-gray-200 rounded-lg p-3 hover:border-blue-300 transition">
                            <input type="file" name="admin_attachments[]" id="file-input" multiple
                                   accept=".jpg,.jpeg,.png,.pdf,.doc,.docx"
                                   class="block w-full text-sm text-gray-600 cursor-pointer
                                          file:mr-3 file:py-1.5 file:px-3 file:border-0 file:text-xs file:font-semibold
                                          file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 file:rounded-md"
                                   onchange="updateFileList(this)">
                            <div id="file-list" class="mt-2 space-y-1 hidden"></div>
                        </div>
                        @error('admin_attachments') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        @error('admin_attachments.*') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Send button row --}}
                    <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                        <p class="text-xs text-gray-400">
                            <svg class="w-3.5 h-3.5 inline mr-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            Sending to <strong>{{ $inquiry->email }}</strong>
                        </p>
                        <button type="submit" id="send-btn"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg id="send-icon" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                            <svg id="send-spinner" class="w-4 h-4 animate-spin hidden" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-dasharray="30 60"/></svg>
                            <span id="send-label">Send Reply</span>
                        </button>
                    </div>
                </form>
            </div>

        </div>{{-- end left column --}}

        {{-- ═══ RIGHT: Sidebar ═══ --}}
        <div class="w-full lg:w-72 xl:w-80 flex-shrink-0 space-y-4">

            {{-- Inquiry Information --}}
            <div class="sidebar-card">
                <div class="sidebar-card-header">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wide">Inquiry Information</p>
                </div>
                <div class="sidebar-card-body space-y-3">
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Reference</p>
                        <p class="text-sm font-mono font-bold text-gray-800">INQ-{{ str_pad($inquiry->id, 5, '0', STR_PAD_LEFT) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Status</p>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold {{ $sc['bg'] }} {{ $sc['text'] }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $sc['dot'] }}"></span>
                            {{ $sc['label'] }}
                        </span>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Inquiry Type</p>
                        <p class="text-sm font-semibold text-gray-700">{{ $subjectIcon }} {{ $inquiry->subject }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Submitted</p>
                        <p class="text-sm text-gray-700">{{ $inquiry->created_at->format('M d, Y') }}</p>
                        <p class="text-xs text-gray-400">{{ $inquiry->created_at->format('g:i A') }} · {{ $inquiry->created_at->diffForHumans() }}</p>
                    </div>
                    @if($inquiry->preferred_date)
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Preferred Date</p>
                        <p class="text-sm text-gray-700">{{ $inquiry->preferred_date->format('M d, Y') }}</p>
                    </div>
                    @endif
                    @if($inquiry->preferred_time)
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Preferred Time</p>
                        <p class="text-sm text-gray-700">{{ $inquiry->preferred_time }}</p>
                    </div>
                    @endif
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Assigned To</p>
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-xs">
                                {{ substr($adminUser->name, 0, 1) }}
                            </div>
                            <p class="text-sm text-gray-700 font-medium">{{ $adminUser->name }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Attachments Sidebar --}}
            @if(count($allAttachments))
            <div class="sidebar-card">
                <div class="sidebar-card-header flex items-center justify-between">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wide">Attachments</p>
                    <span class="text-xs text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded-full">{{ count($allAttachments) }}</span>
                </div>
                <div class="sidebar-card-body space-y-2">
                    @foreach($allAttachments as $att)
                    @php
                        $encodedPath = base64_encode($att['path']);
                        $icon = str_contains($att['mime'] ?? '', 'image') ? '🖼️' : (str_contains($att['mime'] ?? '', 'pdf') ? '📄' : '📎');
                    @endphp
                    <a href="{{ route('admin.inquiries.attachment', $encodedPath) }}" target="_blank"
                       class="flex items-center gap-2.5 p-2 rounded-lg bg-gray-50 hover:bg-blue-50 border border-gray-100 hover:border-blue-200 transition group">
                        <span class="text-lg flex-shrink-0">{{ $icon }}</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold text-gray-700 group-hover:text-blue-700 truncate leading-tight">{{ $att['original_name'] }}</p>
                            <p class="text-xs text-gray-400">{{ round(($att['size'] ?? 0)/1024, 1) }} KB @if(!empty($att['_from'])) · {{ $att['_from'] }} @endif</p>
                        </div>
                        <svg class="w-3.5 h-3.5 text-gray-300 group-hover:text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Actions --}}
            <div class="sidebar-card">
                <div class="sidebar-card-header">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wide">Actions</p>
                </div>
                <div class="sidebar-card-body">

                    @if($showInProgress)
                    <form method="POST" action="{{ route('admin.inquiries.updateStatus', $inquiry) }}">
                        @csrf
                        <input type="hidden" name="status" value="in_progress">
                        <button type="submit" class="action-status-btn bg-purple-50 text-purple-700 border-purple-200 hover:bg-purple-100">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Mark as In Progress
                        </button>
                    </form>
                    @endif

                    @if($showResolve)
                    <form method="POST" action="{{ route('admin.inquiries.updateStatus', $inquiry) }}">
                        @csrf
                        <input type="hidden" name="status" value="resolved">
                        <button type="submit" class="action-status-btn bg-green-50 text-green-700 border-green-200 hover:bg-green-100">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Mark as Resolved
                        </button>
                    </form>
                    @endif

                    @if($showClose)
                    <form method="POST" action="{{ route('admin.inquiries.updateStatus', $inquiry) }}">
                        @csrf
                        <input type="hidden" name="status" value="closed">
                        <button type="submit" class="action-status-btn bg-gray-50 text-gray-600 border-gray-200 hover:bg-gray-100">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            Close Inquiry
                        </button>
                    </form>
                    @endif

                    @if($showReopen)
                    <form method="POST" action="{{ route('admin.inquiries.updateStatus', $inquiry) }}">
                        @csrf
                        <input type="hidden" name="status" value="read">
                        <button type="submit" class="action-status-btn bg-amber-50 text-amber-700 border-amber-200 hover:bg-amber-100">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Reopen Inquiry
                        </button>
                    </form>
                    @endif

                    {{-- Booking shortcut for appointment inquiries --}}
                    @if($inquiry->subject === 'Booking / Appointment')
                    <div class="border-t border-gray-100 mt-2 pt-3">
                        <a href="{{ route('admin.bookings.create') }}" target="_blank"
                           class="action-status-btn bg-blue-50 text-blue-700 border-blue-200 hover:bg-blue-100">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Create Booking
                        </a>
                    </div>
                    @endif

                    {{-- Certificate shortcut --}}
                    @if($inquiry->subject === 'Certificate Request')
                    <div class="border-t border-gray-100 mt-2 pt-3">
                        <a href="{{ route('admin.certificates.create') }}" target="_blank"
                           class="action-status-btn bg-indigo-50 text-indigo-700 border-indigo-200 hover:bg-indigo-100">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Create Certificate
                        </a>
                    </div>
                    @endif
                </div>
            </div>

        </div>{{-- end sidebar --}}

    </div>{{-- end two-column --}}

</div>
@endsection

@push('scripts')
<script>
const REPLY_SUBJECT_CONFIG = {!! \App\Services\InquirySubjectConfig::forJs() !!};

// Quick reply template selector
function applyQuickReply(text) {
    if (!text) return;
    const ta = document.getElementById('reply-textarea');
    ta.value = text;
    updateCharCount(text);
    ta.focus();
    document.getElementById('quick-reply-select').selectedIndex = 0;
}

// Subject change — control attachment visibility
function handleReplySubjectChange(subject) {
    const cfg    = REPLY_SUBJECT_CONFIG[subject];
    const attSec = document.getElementById('reply-attachments-section');
    if (!cfg) return;
    attSec.style.display = cfg.attachment ? 'block' : 'none';
}

// Character counter
function updateCharCount(val) {
    const el = document.getElementById('char-count');
    if (el) el.textContent = val.length;
}

// File list display
function updateFileList(input) {
    const listEl = document.getElementById('file-list');
    listEl.innerHTML = '';
    if (!input.files.length) { listEl.classList.add('hidden'); return; }
    listEl.classList.remove('hidden');
    Array.from(input.files).forEach(f => {
        const d = document.createElement('div');
        d.className = 'flex items-center gap-2 text-xs text-gray-600 bg-gray-50 px-2 py-1 rounded';
        d.innerHTML = `<span class="text-base">${f.type.includes('image') ? '🖼️' : f.type.includes('pdf') ? '📄' : '📎'}</span>
                       <span class="truncate flex-1">${f.name}</span>
                       <span class="text-gray-400 flex-shrink-0">${(f.size/1024).toFixed(1)} KB</span>`;
        listEl.appendChild(d);
    });
}

// Prevent duplicate submission + loading state
document.getElementById('reply-form')?.addEventListener('submit', function(e) {
    const btn    = document.getElementById('send-btn');
    const icon   = document.getElementById('send-icon');
    const spin   = document.getElementById('send-spinner');
    const label  = document.getElementById('send-label');
    const ta     = document.getElementById('reply-textarea');

    if (!ta.value.trim()) { e.preventDefault(); ta.focus(); return; }

    btn.disabled = true;
    icon.classList.add('hidden');
    spin.classList.remove('hidden');
    label.textContent = 'Sending…';
});

// Textarea character counter
document.getElementById('reply-textarea')?.addEventListener('input', function() {
    updateCharCount(this.value);
});

// Init on load
document.addEventListener('DOMContentLoaded', function () {
    const sel = document.getElementById('reply-subject');
    if (sel) handleReplySubjectChange(sel.value);
    const ta = document.getElementById('reply-textarea');
    if (ta) updateCharCount(ta.value);
});
</script>
@endpush
