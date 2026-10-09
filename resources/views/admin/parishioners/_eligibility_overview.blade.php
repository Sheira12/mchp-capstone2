{{--
  Eligibility overview partial — included on admin parishioner show page.
  Shows per-service eligibility status for this parishioner.
--}}
@php
    $eligibilityService = app(\App\Services\EligibilityService::class);
    $bookableServices   = \App\Models\Service::where('is_bookable', true)
        ->where('is_active', true)
        ->whereHas('eligibilityRules')
        ->orderBy('sort_order')
        ->get();
@endphp

@if($bookableServices->isNotEmpty())
<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <p class="font-bold text-gray-900">Eligibility Overview</p>
        <p class="text-xs text-gray-400">Per-service qualification status</p>
    </div>
    <div class="divide-y divide-gray-100">
        @foreach($bookableServices as $svc)
        @php
            try {
                $result = $eligibilityService->check($parishioner, $svc->slug);
            } catch (\Exception $e) {
                $result = null;
            }
        @endphp
        @if($result)
        <div class="px-5 py-4">
            <div class="flex items-start justify-between gap-4 mb-2">
                <div>
                    <p class="font-semibold text-gray-900 text-sm">{{ $svc->name }}</p>
                    @php $prog = $result->progress(); @endphp
                    <p class="text-xs text-gray-500">{{ $prog['satisfied'] }} / {{ $prog['total'] }} required criteria met</p>
                </div>
                @if($result->isEligible())
                <span class="shrink-0 inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700 border border-green-200">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    Eligible
                </span>
                @else
                <span class="shrink-0 inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700 border border-red-200">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    Not Eligible
                </span>
                @endif
            </div>

            {{-- Progress bar --}}
            <div class="w-full bg-gray-100 rounded-full h-1.5 mb-3">
                <div class="h-1.5 rounded-full {{ $result->isEligible() ? 'bg-green-500' : 'bg-blue-500' }}"
                     style="width:{{ $prog['pct'] }}%"></div>
            </div>

            {{-- Rule items --}}
            <div class="space-y-1">
                @foreach($result->items() as $item)
                <div class="flex items-start gap-2 text-xs">
                    @if($item->satisfied)
                    <svg class="w-3.5 h-3.5 text-green-500 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    @elseif($item->status === 'pending')
                    <svg class="w-3.5 h-3.5 text-yellow-500 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
                    @else
                    <svg class="w-3.5 h-3.5 text-red-500 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    @endif
                    <div class="flex-1">
                        <span class="{{ $item->required ? 'font-semibold text-gray-800' : 'text-gray-600' }}">
                            {{ $item->ruleName }}
                            @if(!$item->required)<span class="text-gray-400">(optional)</span>@endif
                        </span>
                        @if($item->isPlaceholder)
                        <span class="ml-1 text-amber-600 font-bold">⚠</span>
                        @endif
                        <span class="text-gray-500 ml-1">— {{ $item->message }}</span>

                        {{-- Waiver grant link for non-satisfied required rules --}}
                        @if(!$item->satisfied && $item->required && $item->status !== 'waived')
                        <a href="{{ route('admin.eligibility-waivers.create', ['parishioner_id' => $parishioner->id, 'rule_id' => $item->ruleId]) }}"
                           class="ml-2 text-blue-600 hover:underline font-semibold">Grant Waiver</a>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
        @endforeach
    </div>
</div>
@endif
