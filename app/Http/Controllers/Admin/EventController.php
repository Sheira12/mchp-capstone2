<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::with('creator');

        if ($search = $request->get('search')) {
            $query->where('title', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
        }

        if ($category = $request->get('category')) {
            $query->where('category', $category);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $events = $query->orderByDesc('event_start')->paginate(15)->withQueryString();

        return view('admin.events.index', compact('events'));
    }

    public function create()
    {
        $categories = Event::CATEGORIES;
        return view('admin.events.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'location'    => ['nullable', 'string', 'max:255'],
            'event_start' => ['required', 'date'],
            'event_end'   => ['nullable', 'date', 'after_or_equal:event_start'],
            'category'    => ['required', 'in:' . implode(',', array_keys(Event::CATEGORIES))],
            'status'      => ['required', 'in:draft,published,cancelled'],
            'is_featured' => ['boolean'],
            'image'       => ['nullable', 'image', 'max:10240'],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('events', 'public');
        }

        $event = Event::create([
            ...$validated,
            'image_path' => $imagePath,
            'is_featured' => $request->boolean('is_featured'),
            'created_by' => auth()->id(),
        ]);

        AuditLog::record('create', $event, [], $event->toArray(), 'Event created');

        return redirect()->route('admin.events.index')->with('success', 'Event created successfully.');
    }

    public function show(Event $event)
    {
        return view('admin.events.show', compact('event'));
    }

    public function edit(Event $event)
    {
        $categories = Event::CATEGORIES;
        return view('admin.events.edit', compact('event', 'categories'));
    }

    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'location'    => ['nullable', 'string', 'max:255'],
            'event_start' => ['required', 'date'],
            'event_end'   => ['nullable', 'date', 'after_or_equal:event_start'],
            'category'    => ['required', 'in:' . implode(',', array_keys(Event::CATEGORIES))],
            'status'      => ['required', 'in:draft,published,cancelled'],
            'is_featured' => ['boolean'],
            'image'       => ['nullable', 'image', 'max:10240'],
        ]);

        $old = $event->toArray();

        if ($request->hasFile('image')) {
            if ($event->image_path) {
                \Storage::disk('public')->delete($event->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('events', 'public');
        }

        $event->update([...$validated, 'is_featured' => $request->boolean('is_featured')]);

        AuditLog::record('update', $event, $old, $event->fresh()->toArray(), 'Event updated');

        return redirect()->route('admin.events.show', $event)->with('success', 'Event updated.');
    }

    public function destroy(Event $event)
    {
        if ($event->image_path) {
            \Storage::disk('public')->delete($event->image_path);
        }
        AuditLog::record('delete', $event, $event->toArray(), [], 'Event deleted');
        $event->delete();
        return redirect()->route('admin.events.index')->with('success', 'Event deleted.');
    }
}
