<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    // ── Gallery index (all photos, filterable) ────────────────
    public function index(Request $request)
    {
        $query = GalleryItem::with('creator');

        if ($album = $request->get('album')) {
            $query->where('album', $album);
        } elseif ($cat = $request->get('category')) {
            $query->where('category', $cat);
        }

        $items  = $query->orderBy('sort_order')->orderByDesc('created_at')->paginate(24)->withQueryString();
        $albums = GalleryItem::albumCounts();

        return view('admin.gallery.index', compact('items', 'albums'));
    }

    // ── Album detail / manage page ────────────────────────────
    public function albumDetail(Request $request, string $album)
    {
        $items    = GalleryItem::where('album', $album)
                        ->orderByDesc('album_cover')
                        ->orderBy('sort_order')
                        ->orderBy('created_at')
                        ->get();
        $allAlbums = GalleryItem::albums();
        return view('admin.gallery.album-detail', compact('items', 'album', 'allAlbums'));
    }

    // ── Add more photos to an existing album ─────────────────
    public function addPhotos(Request $request, string $album)
    {
        $request->validate([
            'images'   => ['required', 'array', 'min:1', 'max:200'],
            'images.*' => ['image', 'max:102400'], // 100MB per file
            'category' => ['required', 'in:' . implode(',', array_keys(GalleryItem::CATEGORIES))],
        ]);

        $existingCount = GalleryItem::where('album', $album)->count();
        $count = 0;

        foreach ($request->file('images') as $i => $file) {
            $path = $file->store('gallery', 'public');
            GalleryItem::create([
                'title'       => null,
                'caption'     => null,
                'image_path'  => $path,
                'category'    => $request->input('category', 'general'),
                'album'       => $album,
                'album_cover' => false,
                'is_featured' => false,
                'sort_order'  => $existingCount + $i,
                'created_by'  => auth()->id(),
            ]);
            $count++;
        }

        return redirect()->route('admin.gallery.album-detail', $album)
            ->with('success', $count . ' photo(s) added to album "' . $album . '".');
    }

    // ── Bulk update multiple photos at once ───────────────────
    public function bulkUpdate(Request $request, string $album)
    {
        $request->validate([
            'photos'                => ['required', 'array'],
            'photos.*.id'           => ['required', 'exists:gallery_items,id'],
            'photos.*.title'        => ['nullable', 'string', 'max:255'],
            'photos.*.caption'      => ['nullable', 'string'],
            'photos.*.sort_order'   => ['nullable', 'integer'],
            'photos.*.album_cover'  => ['nullable', 'boolean'],
            'photos.*.is_featured'  => ['nullable', 'boolean'],
            'photos.*.replace'      => ['nullable', 'image', 'max:51200'],
        ]);

        $coverSet = false;

        foreach ($request->input('photos', []) as $idx => $photoData) {
            $item = GalleryItem::find($photoData['id']);
            if (!$item || $item->album !== $album) continue;

            $data = [
                'title'       => $photoData['title'] ?? null,
                'caption'     => $photoData['caption'] ?? null,
                'sort_order'  => (int)($photoData['sort_order'] ?? 0),
                'is_featured' => isset($photoData['is_featured']),
                'album_cover' => isset($photoData['album_cover']) && !$coverSet,
            ];

            if (isset($photoData['album_cover']) && !$coverSet) {
                $coverSet = true;
            }

            // Replace image if new file uploaded
            $files = $request->file('photos');
            if (isset($files[$idx]['replace'])) {
                Storage::disk('public')->delete($item->image_path);
                $data['image_path'] = $files[$idx]['replace']->store('gallery', 'public');
            }

            $item->update($data);
        }

        // If a cover was set, clear others
        if ($coverSet) {
            $coverId = collect($request->input('photos'))->firstWhere('album_cover', '1')['id'] ?? null;
            if ($coverId) {
                GalleryItem::where('album', $album)->where('id', '!=', $coverId)->update(['album_cover' => false]);
            }
        }

        return redirect()->route('admin.gallery.album-detail', $album)
            ->with('success', 'Photos updated successfully.');
    }

    // ── Bulk delete selected photos ───────────────────────────
    public function bulkDelete(Request $request)
    {
        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['exists:gallery_items,id']]);

        $album = null;
        $items = GalleryItem::whereIn('id', $request->input('ids'))->get();
        foreach ($items as $item) {
            $album = $album ?? $item->album;
            Storage::disk('public')->delete($item->image_path);
            $item->delete();
        }

        if ($album) {
            return redirect()->route('admin.gallery.album-detail', $album)
                ->with('success', count($request->input('ids')) . ' photo(s) deleted.');
        }
        return redirect()->route('admin.gallery.index')
            ->with('success', count($request->input('ids')) . ' photo(s) deleted.');
    }

    // ── Create (upload form) ──────────────────────────────────
    public function create()
    {
        $albums = GalleryItem::albums();
        return view('admin.gallery.create', compact('albums'));
    }

    // ── Store (upload) ────────────────────────────────────────
    public function store(Request $request)
    {
        $v = $request->validate([
            'title'       => ['nullable', 'string', 'max:255'],
            'caption'     => ['nullable', 'string'],
            'category'    => ['required', 'in:' . implode(',', array_keys(GalleryItem::CATEGORIES))],
            'album'       => ['nullable', 'string', 'max:120'],
            'album_new'   => ['nullable', 'string', 'max:120'],
            'is_featured' => ['boolean'],
            'sort_order'  => ['integer'],
            'images'      => ['required', 'array', 'min:1', 'max:200'],
            'images.*'    => ['image', 'max:102400'], // 100MB per file
        ]);

        // Resolve album name — use new album if provided
        $album = trim($request->input('album_new') ?: $request->input('album', ''));

        $count = 0;
        foreach ($request->file('images') as $i => $file) {
            $path = $file->store('gallery', 'public');
            GalleryItem::create([
                'title'       => $v['title'] ?? null,
                'caption'     => $v['caption'] ?? null,
                'image_path'  => $path,
                'category'    => $v['category'],
                'album'       => $album ?: null,
                'album_cover' => ($i === 0 && $album), // first photo becomes album cover
                'is_featured' => $request->boolean('is_featured'),
                'sort_order'  => ($v['sort_order'] ?? 0) + $i,
                'created_by'  => auth()->id(),
            ]);
            $count++;
        }

        return redirect()->route('admin.gallery.index')
            ->with('success', $count . ' photo(s) uploaded' . ($album ? " to album \"{$album}\"" : '') . '.');
    }

    // ── Edit ──────────────────────────────────────────────────
    public function edit(GalleryItem $gallery)
    {
        $albums = GalleryItem::albums();
        return view('admin.gallery.edit', compact('gallery', 'albums'));
    }

    // ── Update ────────────────────────────────────────────────
    public function update(Request $request, GalleryItem $gallery)
    {
        $v = $request->validate([
            'title'       => ['nullable', 'string', 'max:255'],
            'caption'     => ['nullable', 'string'],
            'category'    => ['required', 'in:' . implode(',', array_keys(GalleryItem::CATEGORIES))],
            'album'       => ['nullable', 'string', 'max:120'],
            'album_new'   => ['nullable', 'string', 'max:120'],
            'album_cover' => ['boolean'],
            'is_featured' => ['boolean'],
            'sort_order'  => ['integer'],
            'image'       => ['nullable', 'image', 'max:102400'], // 100MB
        ]);

        $album = trim($request->input('album_new') ?: $request->input('album', ''));

        $data = [
            'title'       => $v['title'] ?? null,
            'caption'     => $v['caption'] ?? null,
            'category'    => $v['category'],
            'album'       => $album ?: null,
            'album_cover' => $request->boolean('album_cover'),
            'is_featured' => $request->boolean('is_featured'),
            'sort_order'  => $v['sort_order'] ?? 0,
        ];

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($gallery->image_path);
            $data['image_path'] = $request->file('image')->store('gallery', 'public');
        }

        $gallery->update($data);

        return redirect()->route('admin.gallery.index')
            ->with('success', 'Photo updated.');
    }

    // ── Set album cover ───────────────────────────────────────
    public function setCover(Request $request, GalleryItem $gallery)
    {
        if ($gallery->album) {
            // Remove existing cover for this album
            GalleryItem::where('album', $gallery->album)->update(['album_cover' => false]);
            $gallery->update(['album_cover' => true]);
        }
        return back()->with('success', 'Album cover updated.');
    }

    // ── Delete album (all photos in it) ──────────────────────
    public function deleteAlbum(Request $request)
    {
        $album = $request->input('album');
        if (!$album) return back()->with('error', 'No album specified.');

        $items = GalleryItem::where('album', $album)->get();
        foreach ($items as $item) {
            Storage::disk('public')->delete($item->image_path);
            $item->delete();
        }

        return redirect()->route('admin.gallery.index')
            ->with('success', "Album \"{$album}\" and all its photos deleted.");
    }

    // ── Destroy single photo ──────────────────────────────────
    public function destroy(GalleryItem $gallery)
    {
        Storage::disk('public')->delete($gallery->image_path);
        $gallery->delete();
        return redirect()->route('admin.gallery.index')->with('success', 'Photo deleted.');
    }
}
