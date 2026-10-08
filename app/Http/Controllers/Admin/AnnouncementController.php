<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::with('createdBy')
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->paginate(20);

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
            'image'        => ['nullable', 'image', 'max:5120'],
            'status'       => ['required', 'in:draft,published,scheduled'],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
            'expires_at'   => ['nullable', 'date', 'after:today'],
            'is_pinned'    => ['boolean'],
        ]);

        // ── Backslash fix: strip any trailing \ from title ──
        $validated['title'] = rtrim($validated['title'], '\\');

        // ── Status → is_published + published_at derivation ──
        [$validated['is_published'], $validated['published_at'], $validated['scheduled_at']] =
            $this->resolvePublishState($validated['status'], $validated['scheduled_at'] ?? null);

        $validated['created_by'] = auth()->id();
        $validated['is_pinned']  = $request->boolean('is_pinned');

        // ── Image upload to Supabase ──
        if ($request->hasFile('image')) {
            $result = $this->uploadImage($request);
            if ($result) {
                $validated['image_path'] = $result;
            } else {
                // Upload failed — inform admin but still save without image
                session()->flash('warning', 'Announcement saved, but image upload failed. You can add the image by editing.');
            }
        }

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
            'image'        => ['nullable', 'image', 'max:5120'],
            'status'       => ['required', 'in:draft,published,scheduled'],
            'scheduled_at' => ['nullable', 'date'],
            'expires_at'   => ['nullable', 'date'],
            'is_pinned'    => ['boolean'],
        ]);

        // ── Backslash fix ──
        $validated['title'] = rtrim($validated['title'], '\\');

        // ── Status resolution ──
        [$validated['is_published'], $validated['published_at'], $validated['scheduled_at']] =
            $this->resolvePublishState(
                $validated['status'],
                $validated['scheduled_at'] ?? null,
                $announcement->published_at
            );

        $validated['is_pinned'] = $request->boolean('is_pinned');

        // ── Image upload ──
        if ($request->hasFile('image')) {
            // Delete old image if stored on Supabase
            if ($announcement->image_path) {
                try {
                    Storage::disk('supabase')->delete($announcement->image_path);
                } catch (\Exception $e) {
                    Log::warning('Failed to delete old announcement image: ' . $e->getMessage());
                }
            }
            $result = $this->uploadImage($request);
            if ($result) {
                $validated['image_path'] = $result;
            } else {
                session()->flash('warning', 'Image upload failed. The existing image has been kept.');
                unset($validated['image_path']); // don't overwrite with null
            }
        }

        $announcement->update($validated);

        return redirect()->route('admin.announcements.index')->with('success', 'Announcement updated.');
    }

    public function destroy(Announcement $announcement)
    {
        if ($announcement->image_path) {
            try {
                Storage::disk('supabase')->delete($announcement->image_path);
            } catch (\Exception $e) {
                Log::warning('Failed to delete announcement image on destroy: ' . $e->getMessage());
            }
        }
        $announcement->delete();

        return redirect()->route('admin.announcements.index')->with('success', 'Announcement deleted.');
    }

    public function show(Announcement $announcement)
    {
        return view('admin.announcements.show', compact('announcement'));
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Derive is_published, published_at, and scheduled_at from the chosen status.
     *
     * @return array [bool $is_published, ?Carbon $published_at, ?Carbon $scheduled_at]
     */
    private function resolvePublishState(string $status, mixed $scheduledAt, mixed $existingPublishedAt = null): array
    {
        return match ($status) {
            'published' => [
                true,
                $existingPublishedAt ?? now(),
                null,
            ],
            'scheduled' => [
                false,
                $scheduledAt ? \Carbon\Carbon::parse($scheduledAt) : null,
                $scheduledAt ? \Carbon\Carbon::parse($scheduledAt) : null,
            ],
            default => [   // draft
                false,
                null,
                null,
            ],
        };
    }

    /**
     * Upload image to Supabase Storage.
     * Returns the Supabase storage path on success, null on failure.
     */
    private function uploadImage(Request $request): ?string
    {
        try {
            $file = $request->file('image');

            // Sanitise filename
            $ext      = $file->getClientOriginalExtension() ?: 'jpg';
            $filename = 'announcements/' . Str::uuid() . '.' . $ext;

            $path = $file->storeAs(
                dirname($filename),
                basename($filename),
                'supabase'
            );

            if ($path === false) {
                Log::error('Announcement image upload returned false', [
                    'original_name' => $file->getClientOriginalName(),
                    'size'          => $file->getSize(),
                ]);
                return null;
            }

            return $path;
        } catch (\Exception $e) {
            Log::error('Announcement image upload exception: ' . $e->getMessage());
            return null;
        }
    }
}
