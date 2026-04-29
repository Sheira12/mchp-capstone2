<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::with('createdBy')->orderByDesc('created_at')->paginate(20);
        return view('admin.announcements.index', compact('announcements'));
    }

    public function create()
    {
        return view('admin.announcements.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'content'      => ['required', 'string'],
            'category'     => ['required', 'string', 'max:50'],
            'image'        => ['nullable', 'image', 'max:2048'],
            'is_published' => ['boolean'],
            'expires_at'   => ['nullable', 'date', 'after:today'],
        ]);

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('announcements', 'public');
        }

        $validated['created_by']   = auth()->id();
        $validated['published_at'] = $request->boolean('is_published') ? now() : null;

        Announcement::create($validated);

        return redirect()->route('admin.announcements.index')->with('success', 'Announcement created.');
    }

    public function edit(Announcement $announcement)
    {
        return view('admin.announcements.edit', compact('announcement'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'content'      => ['required', 'string'],
            'category'     => ['required', 'string', 'max:50'],
            'image'        => ['nullable', 'image', 'max:2048'],
            'is_published' => ['boolean'],
            'expires_at'   => ['nullable', 'date'],
        ]);

        if ($request->hasFile('image')) {
            if ($announcement->image_path) Storage::disk('public')->delete($announcement->image_path);
            $validated['image_path'] = $request->file('image')->store('announcements', 'public');
        }

        if ($request->boolean('is_published') && !$announcement->published_at) {
            $validated['published_at'] = now();
        }

        $announcement->update($validated);

        return redirect()->route('admin.announcements.index')->with('success', 'Announcement updated.');
    }

    public function destroy(Announcement $announcement)
    {
        if ($announcement->image_path) Storage::disk('public')->delete($announcement->image_path);
        $announcement->delete();
        return redirect()->route('admin.announcements.index')->with('success', 'Announcement deleted.');
    }
}
