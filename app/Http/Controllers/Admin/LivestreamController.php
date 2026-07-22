<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Livestream;
use Illuminate\Http\Request;

class LivestreamController extends Controller
{
    public function index()
    {
        $livestreams = Livestream::with('creator')->orderByDesc('created_at')->paginate(15);
        return view('admin.livestreams.index', compact('livestreams'));
    }

    public function create() { return view('admin.livestreams.create'); }

    public function store(Request $request)
    {
        $v = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'youtube_url'  => ['required', 'string', 'max:500'],
            'type'         => ['required', 'in:live,upcoming,recorded'],
            'scheduled_at' => ['nullable', 'date'],
            'is_active'    => ['boolean'],
            'is_featured'  => ['boolean'],
        ]);

        $v['youtube_id']  = Livestream::extractYoutubeId($v['youtube_url']);
        $v['is_active']   = $request->boolean('is_active', true);
        $v['is_featured'] = $request->boolean('is_featured');
        $v['created_by']  = auth()->id();

        Livestream::create($v);
        return redirect()->route('admin.livestreams.index')->with('success', 'Livestream/video added.');
    }

    public function edit(Livestream $livestream) { return view('admin.livestreams.edit', compact('livestream')); }

    public function update(Request $request, Livestream $livestream)
    {
        $v = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'youtube_url'  => ['required', 'string', 'max:500'],
            'type'         => ['required', 'in:live,upcoming,recorded'],
            'scheduled_at' => ['nullable', 'date'],
            'is_active'    => ['boolean'],
            'is_featured'  => ['boolean'],
        ]);

        $v['youtube_id']  = Livestream::extractYoutubeId($v['youtube_url']);
        $v['is_active']   = $request->boolean('is_active');
        $v['is_featured'] = $request->boolean('is_featured');

        $livestream->update($v);
        return redirect()->route('admin.livestreams.index')->with('success', 'Video updated.');
    }

    public function destroy(Livestream $livestream)
    {
        $livestream->delete();
        return redirect()->route('admin.livestreams.index')->with('success', 'Video deleted.');
    }
}
