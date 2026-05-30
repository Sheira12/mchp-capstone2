<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Certificate;
use App\Models\Parishioner;
use App\Models\SacramentalRecord;
use App\Services\CertificateService;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    public function __construct(private CertificateService $certificateService) {}

    public function index(Request $request)
    {
        $query = Certificate::with(['parishioner', 'issuedBy']);

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->get('search')) {
            $query->whereHas('parishioner', fn($q) => $q->search($search))
                  ->orWhere('certificate_number', 'like', "%{$search}%");
        }

        $certificates = $query->orderByDesc('issued_date')
            ->paginate(20)
            ->withQueryString();

        return view('admin.certificates.index', compact('certificates'));
    }

    public function create(Request $request)
    {
        $parishioner = $request->get('parishioner_id')
            ? Parishioner::find($request->get('parishioner_id'))
            : null;

        $sacramentalRecord = $request->get('sacramental_record_id')
            ? SacramentalRecord::find($request->get('sacramental_record_id'))
            : null;

        return view('admin.certificates.create', compact('parishioner', 'sacramentalRecord'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'parishioner_id'        => ['required', 'exists:parishioners,id'],
            'sacramental_record_id' => ['nullable', 'exists:sacramental_records,id'],
            'type'                  => ['required', 'string'],
            'issued_date'           => ['required', 'date'],
            'purpose'               => ['nullable', 'string', 'max:255'],
            'notes'                 => ['nullable', 'string'],
        ]);

        // Treat empty string as null for optional FK
        if (empty($validated['sacramental_record_id'])) {
            $validated['sacramental_record_id'] = null;
        }

        $validated['issued_by'] = auth()->id();

        $certificate = Certificate::create($validated);

        // Generate PDF and QR code — increase time limit for this operation
        set_time_limit(120);
        try {
            $this->certificateService->generate($certificate);
            $certificate->refresh();
            $message = 'Certificate created and generated successfully.';
        } catch (\Exception $e) {
            \Log::error('Certificate generation failed: ' . $e->getMessage());
            $message = 'Certificate created. PDF generation failed — use Regenerate to retry.';
        }

        AuditLog::record('create', $certificate, [], $certificate->toArray(), 'Certificate created');

        return redirect()->route('admin.certificates.show', $certificate)
            ->with('success', $message);
    }

    public function show(Certificate $certificate)
    {
        $certificate->load(['parishioner', 'sacramentalRecord', 'issuedBy', 'payment', 'qrCode']);
        return view('admin.certificates.show', compact('certificate'));
    }

    public function download(Certificate $certificate)
    {
        set_time_limit(120);
        if (!$certificate->file_path || !\Storage::disk('public')->exists($certificate->file_path)) {
            $this->certificateService->generate($certificate);
            $certificate->refresh();
        }

        AuditLog::record('download', $certificate, [], [], 'Certificate downloaded');

        return \Storage::disk('public')->download($certificate->file_path, $certificate->certificate_number . '.pdf');
    }

    public function regenerate(Certificate $certificate)
    {
        set_time_limit(120);
        try {
            $this->certificateService->generate($certificate);
            return back()->with('success', 'Certificate regenerated successfully.');
        } catch (\Exception $e) {
            \Log::error('Certificate regeneration failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Regeneration failed: ' . $e->getMessage()]);
        }
    }

    public function release(Certificate $certificate)
    {
        $certificate->update(['status' => 'released']);
        AuditLog::record('release', $certificate, ['status' => 'issued'], ['status' => 'released'], 'Certificate released');

        return back()->with('success', 'Certificate marked as released.');
    }

    public function batchPrint(Request $request)
    {
        $ids = $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['exists:certificates,id']])['ids'];
        $certificates = Certificate::whereIn('id', $ids)->with('parishioner')->get();

        return $this->certificateService->batchPdf($certificates);
    }

    public function edit(Certificate $certificate)
    {
        $certificate->load(['parishioner', 'sacramentalRecord']);
        return view('admin.certificates.edit', compact('certificate'));
    }

    public function update(Request $request, Certificate $certificate)
    {
        $validated = $request->validate([
            'type'                  => ['required', 'string'],
            'issued_date'           => ['required', 'date'],
            'purpose'               => ['nullable', 'string', 'max:255'],
            'notes'                 => ['nullable', 'string'],
            'status'                => ['required', 'in:draft,issued,released,revoked'],
        ]);

        $oldValues = $certificate->toArray();
        $certificate->update($validated);
        AuditLog::record('update', $certificate, $oldValues, $certificate->fresh()->toArray(), 'Certificate updated');

        return redirect()->route('admin.certificates.show', $certificate)->with('success', 'Certificate updated.');
    }

    public function destroy(Certificate $certificate)
    {
        if ($certificate->file_path) {
            \Storage::disk('public')->delete($certificate->file_path);
        }
        AuditLog::record('delete', $certificate, $certificate->toArray(), [], 'Certificate deleted');
        $certificate->delete();
        return redirect()->route('admin.certificates.index')->with('success', 'Certificate deleted.');
    }
}
