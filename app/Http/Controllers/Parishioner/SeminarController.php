<?php

namespace App\Http\Controllers\Parishioner;

use App\Http\Controllers\Controller;
use App\Models\Seminar;
use App\Models\SeminarRegistration;
use App\Models\Service;
use Illuminate\Http\Request;

class SeminarController extends Controller
{
    /** Upcoming seminars the parishioner can register for. */
    public function index(Request $request)
    {
        $parishioner = auth()->user()->parishioner;
        if (!$parishioner) {
            return redirect()->route('parishioner.profile')->with('info', 'Complete your profile first.');
        }

        $query = Seminar::with(['service', 'registrations'])
            ->where('status', 'scheduled')
            ->where('scheduled_at', '>', now());

        if ($slug = $request->get('service')) {
            $query->whereHas('service', fn($q) => $q->where('slug', $slug));
        }

        $seminars  = $query->orderBy('scheduled_at')->paginate(10)->withQueryString();
        $services  = Service::where('is_active', true)->orderBy('name')->get();

        // Which seminars this parishioner is already registered for
        $myRegs = SeminarRegistration::where('parishioner_id', $parishioner->id)
            ->pluck('status', 'seminar_id');

        return view('parishioner.seminars.index', compact('seminars', 'services', 'myRegs', 'parishioner'));
    }

    /** Register for a seminar. */
    public function register(Request $request, Seminar $seminar)
    {
        $parishioner = auth()->user()->parishioner;
        if (!$parishioner) { return redirect()->route('parishioner.profile'); }

        if (!$seminar->isOpen()) {
            return back()->withErrors(['error' => 'This seminar is no longer open for registration.']);
        }

        // Already registered?
        $existing = SeminarRegistration::where('seminar_id', $seminar->id)
            ->where('parishioner_id', $parishioner->id)
            ->first();

        if ($existing) {
            return back()->with('info', 'You are already registered for this seminar.');
        }

        SeminarRegistration::create([
            'seminar_id'     => $seminar->id,
            'parishioner_id' => $parishioner->id,
            'status'         => 'registered',
            'registered_at'  => now(),
        ]);

        // Notify parishioner
        try {
            $user = auth()->user();
            $user->notify(new \App\Notifications\EligibilityNotification(
                $parishioner,
                'seminar_registered',
                ['seminar' => $seminar->title, 'date' => $seminar->scheduled_at->format('F d, Y g:i A'), 'venue' => $seminar->venue]
            ));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Seminar registration notify failed: ' . $e->getMessage());
        }

        return back()->with('success', 'Registered for "' . $seminar->title . '"! See you on ' . $seminar->scheduled_at->format('M d, Y') . '.');
    }

    /** Cancel a registration. */
    public function cancel(Seminar $seminar)
    {
        $parishioner = auth()->user()->parishioner;

        $reg = SeminarRegistration::where('seminar_id', $seminar->id)
            ->where('parishioner_id', $parishioner->id)
            ->where('status', 'registered')
            ->firstOrFail();

        $reg->update(['status' => 'cancelled']);

        return back()->with('success', 'Registration cancelled.');
    }

    /** My seminar history. */
    public function myHistory()
    {
        $parishioner = auth()->user()->parishioner;
        if (!$parishioner) { return redirect()->route('parishioner.profile'); }

        $registrations = SeminarRegistration::where('parishioner_id', $parishioner->id)
            ->with('seminar.service')
            ->orderByDesc('registered_at')
            ->paginate(15);

        return view('parishioner.seminars.history', compact('registrations'));
    }
}
