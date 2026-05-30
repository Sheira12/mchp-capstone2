<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MassSchedule;
use Illuminate\Http\Request;

class MassScheduleController extends Controller
{
    public function index()
    {
        $schedules = MassSchedule::orderBy('day_of_week')->orderBy('time')->get();
        return view('admin.mass-schedules.index', compact('schedules'));
    }

    public function create()
    {
        return view('admin.mass-schedules.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'day_of_week'   => ['nullable', 'integer', 'between:0,6'],
            'time'          => ['required', 'date_format:H:i'],
            'language'      => ['required', 'string', 'max:50'],
            'celebrant'     => ['nullable', 'string', 'max:255'],
            'is_active'     => ['boolean'],
            'notes'         => ['nullable', 'string'],
            'special_date'  => ['nullable', 'date'],
            'special_title' => ['nullable', 'string', 'max:255'],
        ]);

        MassSchedule::create($validated);

        return redirect()->route('admin.mass-schedules.index')->with('success', 'Mass schedule added.');
    }

    public function edit(MassSchedule $massSchedule)
    {
        return view('admin.mass-schedules.edit', compact('massSchedule'));
    }

    public function update(Request $request, MassSchedule $massSchedule)
    {
        $validated = $request->validate([
            'day_of_week'   => ['nullable', 'integer', 'between:0,6'],
            'time'          => ['required', 'date_format:H:i'],
            'language'      => ['required', 'string', 'max:50'],
            'celebrant'     => ['nullable', 'string', 'max:255'],
            'is_active'     => ['boolean'],
            'notes'         => ['nullable', 'string'],
            'special_date'  => ['nullable', 'date'],
            'special_title' => ['nullable', 'string', 'max:255'],
        ]);

        $massSchedule->update($validated);

        return redirect()->route('admin.mass-schedules.index')->with('success', 'Schedule updated.');
    }

    public function destroy(MassSchedule $massSchedule)
    {
        $massSchedule->delete();
        return redirect()->route('admin.mass-schedules.index')->with('success', 'Schedule deleted.');
    }

    public function show(MassSchedule $massSchedule)
    {
        return redirect()->route('admin.mass-schedules.edit', $massSchedule);
    }
}
