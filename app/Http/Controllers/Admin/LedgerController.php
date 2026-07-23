<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LedgerEntry;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LedgerController extends Controller
{
    // ── Index / list ──────────────────────────────────────────
    public function index(Request $request)
    {
        $query = LedgerEntry::with('recorder')->orderByDesc('entry_date')->orderByDesc('id');

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }
        if ($cat = $request->get('category')) {
            $query->where('category', $cat);
        }
        if ($from = $request->get('from')) {
            $query->whereDate('entry_date', '>=', $from);
        }
        if ($to = $request->get('to')) {
            $query->whereDate('entry_date', '<=', $to);
        }

        $entries = $query->paginate(25)->withQueryString();

        // Summary for current filters
        $totalCredit = (clone $query->getQuery())->where('type', 'credit')->sum('amount');
        $totalDebit  = (clone $query->getQuery())->where('type', 'debit')->sum('amount');

        // Overall totals (no filter)
        $overallCredit = LedgerEntry::where('type', 'credit')->sum('amount');
        $overallDebit  = LedgerEntry::where('type', 'debit')->sum('amount');

        $categories = array_merge(
            array_keys(LedgerEntry::CREDIT_CATEGORIES),
            array_keys(LedgerEntry::DEBIT_CATEGORIES)
        );

        return view('admin.ledger.index', compact(
            'entries', 'totalCredit', 'totalDebit',
            'overallCredit', 'overallDebit', 'categories'
        ));
    }

    // ── Create ────────────────────────────────────────────────
    public function create()
    {
        return view('admin.ledger.create');
    }

    // ── Store ─────────────────────────────────────────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type'             => ['required', 'in:credit,debit'],
            'category'         => ['required', 'string', 'max:100'],
            'description'      => ['required', 'string', 'max:255'],
            'amount'           => ['required', 'numeric', 'min:0.01'],
            'entry_date'       => ['required', 'date'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes'            => ['nullable', 'string'],
        ]);

        $validated['recorded_by'] = auth()->id();

        LedgerEntry::create($validated);

        return redirect()->route('admin.ledger.index')
            ->with('success', ucfirst($validated['type']) . ' entry recorded.');
    }

    // ── Edit ──────────────────────────────────────────────────
    public function edit(LedgerEntry $ledger)
    {
        return view('admin.ledger.edit', compact('ledger'));
    }

    // ── Update ────────────────────────────────────────────────
    public function update(Request $request, LedgerEntry $ledger)
    {
        $validated = $request->validate([
            'type'             => ['required', 'in:credit,debit'],
            'category'         => ['required', 'string', 'max:100'],
            'description'      => ['required', 'string', 'max:255'],
            'amount'           => ['required', 'numeric', 'min:0.01'],
            'entry_date'       => ['required', 'date'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes'            => ['nullable', 'string'],
        ]);

        $ledger->update($validated);

        return redirect()->route('admin.ledger.index')->with('success', 'Entry updated.');
    }

    // ── Destroy ───────────────────────────────────────────────
    public function destroy(LedgerEntry $ledger)
    {
        $ledger->delete();
        return redirect()->route('admin.ledger.index')->with('success', 'Entry deleted.');
    }

    // ── Print-ready report page ───────────────────────────────
    public function report(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        $entries = LedgerEntry::with('recorder')
            ->whereBetween('entry_date', [$from, $to])
            ->orderBy('entry_date')
            ->orderBy('type')
            ->get();

        $totalCredit = $entries->where('type', 'credit')->sum('amount');
        $totalDebit  = $entries->where('type', 'debit')->sum('amount');
        $netBalance  = $totalCredit - $totalDebit;

        $byCategory = $entries->groupBy('category')->map(fn($g) => [
            'type'  => $g->first()->type,
            'total' => $g->sum('amount'),
            'count' => $g->count(),
        ])->sortByDesc('total');

        $parish    = [
            'name'    => config('parish.name'),
            'address' => config('parish.address'),
            'phone'   => config('parish.phone'),
            'email'   => config('parish.email'),
            'priest'  => config('parish.priest'),
        ];
        $printedAt = now()->format('F d, Y h:i A');

        if ($request->get('export') === 'pdf') {
            $pdf = Pdf::loadView('admin.ledger.report-pdf', compact(
                'entries', 'totalCredit', 'totalDebit', 'netBalance',
                'byCategory', 'parish', 'printedAt', 'from', 'to'
            ))
            ->setPaper('A4', 'portrait')
            ->setOption(['defaultFont' => 'DejaVu Sans', 'isHtml5ParserEnabled' => true]);

            return $pdf->download('financial-report-' . $from . '-to-' . $to . '.pdf');
        }

        return view('admin.ledger.report', compact(
            'entries', 'totalCredit', 'totalDebit', 'netBalance',
            'byCategory', 'parish', 'printedAt', 'from', 'to'
        ));
    }

    // ── AJAX: category list by type ───────────────────────────
    public function categories(Request $request)
    {
        $type = $request->get('type', 'credit');
        $cats = $type === 'credit'
            ? LedgerEntry::CREDIT_CATEGORIES
            : LedgerEntry::DEBIT_CATEGORIES;
        return response()->json($cats);
    }

    private function dateRange(Request $request): array
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to   = $request->get('to',   now()->endOfMonth()->toDateString());
        return [$from, $to];
    }
}
