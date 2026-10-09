<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Seminar;
use App\Models\SeminarRegistration;
use App\Models\Service;
use Illuminate\Http\Request;

class SeminarController extends Controller
{
    public function index(Request $request)
    {
        $query = Seminar::with(['service', 'registrations']);

        if ($svc = $request->get('service_id')) {
            $query->where('service_id', $svc);
        }
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $seminars = $query->orderByDesc('scheduled_at')->paginate(20)->withQueryString();
        $services = Service::where('is_active', true)->orderBy('name')->get();

        return view('admin.seminars.index', compact('seminars', 'services'));
    }

    public function create()
    {
        $services = Service::where('is_active', true)->orderBy('name')->get();
        return view('admin.seminars.create', compact('services'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_id'   => ['required', 'exists:services,id'],
            'title'        => ['required', 'string', 'max:255'],
            'scheduled_at' => ['required', 'date'],
            'venue'        => ['nullable', 'string', 'max:255'],
            'speaker'      => ['nullable', 'string', 'max:255'],
            'capacity'     => ['required', 'integer', 'min:1'],
            'notes'        => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['created_by'] = auth()->id();
        $validated['status']     = 'scheduled';

        Seminar::create($validated);

        return redirect()->route('admin.seminars.index')->with('success', 'Seminar scheduled.');
    }

    public function show(Seminar $seminar)
    {
        $seminar->load(['service', 'registrations.parishioner', 'registrations.markedBy']);
        return view('admin.seminars.show', compact('seminar'));
    }

    public function edit(Seminar $seminar)
    {
        $services = Service::where('is_active', true)->orderBy('name')->get();
        return view('admin.seminars.edit', compact('seminar', 'services'));
    }

    public function update(Request $request, Seminar $seminar)
    {
        $validated = $request->validate([
            'service_id'   => ['required', 'exists:services,id'],
            'title'        => ['required', 'string', 'max:255'],
            'scheduled_at' => ['required', 'date'],
            'venue'        => ['nullable', 'string', 'max:255'],
            'speaker'      => ['nullable', 'string', 'max:255'],
            'capacity'     => ['required', 'integer', 'min:1'],
            'status'       => ['required', 'in:scheduled,completed,cancelled'],
            'notes'        => ['nullable', 'string', 'max:1000'],
        ]);

        $seminar->update($validated);

        return redirect()->route('admin.seminars.show', $seminar)->with('success', 'Seminar updated.');
    }

    /**
     * Mark attendance for a single registration (attended / absent).
     */
    public function markAttendance(Request $request, Seminar $seminar, SeminarRegistration $registration)
    {
        $request->validate(['status' => ['required', 'in:attended,absent,registered']]);

        $update = ['status' => $request->status, 'marked_by' => auth()->id()];

        if ($request->status === 'attended' && !$registration->attended_at) {
            $update['attended_at'] = now();
        }

        $registration->update($update);

        // Log audit
        \App\Models\AuditLog::create([
            'user_id'    => auth()->id(),
            'action'     => 'seminar_attendance_marked',
            'model_type' => SeminarRegistration::class,
            'model_id'   => $registration->id,
            'new_values' => ['status' => $request->status],
            'ip_address' => $request->ip(),
        ]);

        // Notify parishioner if attended
        if ($request->status === 'attended') {
            try {
                $user = \App\Models\User::where('parishioner_id', $registration->parishioner_id)->first();
                if ($user) {
                    $user->notify(new \App\Notifications\EligibilityNotification(
                        $registration->parishioner,
                        'seminar_attended',
                        ['seminar' => $seminar->title, 'service' => $seminar->service?->name]
                    ));
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Seminar attendance notify failed: ' . $e->getMessage());
            }
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'status' => $request->status]);
        }

        return back()->with('success', 'Attendance updated.');
    }

    /**
     * Bulk-mark all registered attendees as "attended" and set seminar to completed.
     */
    public function completeAll(Request $request, Seminar $seminar)
    {
        $seminar->registrations()
            ->where('status', 'registered')
            ->update([
                'status'      => 'attended',
                'attended_at' => now(),
                'marked_by'   => auth()->id(),
            ]);

        $seminar->update(['status' => 'completed']);

        return back()->with('success', 'All registered attendees marked as attended. Seminar completed.');
    }

    /**
     * QR-based attendance check-in via token.
     */
    public function checkIn(Request $request)
    {
        $token = $request->get('token');
        $reg   = SeminarRegistration::where('qr_token', strtoupper($token))->with(['seminar', 'parishioner'])->first();

        if (!$reg) {
            return response()->json(['success' => false, 'message' => 'Invalid QR code.'], 404);
        }

        if ($reg->hasAttended()) {
            return response()->json([
                'success'   => true,
                'already'   => true,
                'message'   => $reg->parishioner->full_name . ' has already been checked in.',
                'name'      => $reg->parishioner->full_name,
                'seminar'   => $reg->seminar->title,
            ]);
        }

        $reg->update([
            'status'      => 'attended',
            'attended_at' => now(),
            'marked_by'   => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'already' => false,
            'message' => 'Check-in successful!',
            'name'    => $reg->parishioner->full_name,
            'seminar' => $reg->seminar->title,
        ]);
    }
}
