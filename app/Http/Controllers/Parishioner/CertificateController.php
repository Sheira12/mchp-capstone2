<?php

namespace App\Http\Controllers\Parishioner;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use Illuminate\Support\Facades\Storage;

class CertificateController extends Controller
{
    public function index()
    {
        $parishioner  = auth()->user()->parishioner;
        $certificates = $parishioner?->certificates()->with('sacramentalRecord')->orderByDesc('issued_date')->paginate(10) ?? collect();

        return view('parishioner.certificates.index', compact('certificates'));
    }

    public function download(Certificate $certificate)
    {
        // Ensure the certificate belongs to the authenticated parishioner
        if ($certificate->parishioner_id !== auth()->user()->parishioner?->id) {
            abort(403);
        }

        if (!$certificate->file_path || !Storage::disk('public')->exists($certificate->file_path)) {
            app(\App\Services\CertificateService::class)->generate($certificate);
            $certificate->refresh();
        }

        return Storage::disk('public')->download(
            $certificate->file_path,
            $certificate->certificate_number . '.pdf'
        );
    }
}
