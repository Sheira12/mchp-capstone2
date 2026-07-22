<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Event;
use App\Models\MassSchedule;
use App\Models\Service;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function home()
    {
        $announcements = Announcement::published()
            ->orderByDesc('published_at')
            ->take(6)
            ->get();

        $massSchedules = MassSchedule::where('is_active', true)
            ->whereNull('special_date')
            ->orderBy('day_of_week')
            ->orderBy('time')
            ->get();

        return view('public.home', compact('announcements', 'massSchedules'));
    }

    public function about()
    {
        return view('public.about');
    }

    public function services()
    {
        $services = Service::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('category')
            ->get()
            ->groupBy('category');

        return view('public.services', compact('services'));
    }

    public function contact()
    {
        return view('public.contact');
    }

    public function submitInquiry(Request $request)
    {
        $validated = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'email'   => ['required', 'email'],
            'phone'   => ['nullable', 'string', 'max:20'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        // Resolve the parish email — fall back to MAIL_FROM_ADDRESS if not set
        $parishEmail = config('parish.email');
        if (!$parishEmail || !filter_var($parishEmail, FILTER_VALIDATE_EMAIL)) {
            $parishEmail = config('mail.from.address');
        }

        \Mail::to($parishEmail)->send(new \App\Mail\InquiryMail($validated));

        return back()->with('success', 'Your inquiry has been sent. We will get back to you shortly.');
    }

    public function announcements()
    {
        $announcements = Announcement::published()
            ->orderByDesc('published_at')
            ->paginate(12);

        return view('public.announcements', compact('announcements'));
    }

    public function announcement(Announcement $announcement)
    {
        if (!$announcement->is_published) {
            abort(404);
        }
        return view('public.announcement', compact('announcement'));
    }

    public function events()
    {
        $upcomingEvents = Event::published()->upcoming()->paginate(9);
        $featuredEvent  = Event::published()->featured()->upcoming()->first();

        return view('public.events', compact('upcomingEvents', 'featuredEvent'));
    }

    public function event(Event $event)
    {
        if ($event->status !== 'published') {
            abort(404);
        }
        $relatedEvents = Event::published()
            ->where('category', $event->category)
            ->where('id', '!=', $event->id)
            ->upcoming()
            ->take(3)
            ->get();

        return view('public.event', compact('event', 'relatedEvents'));
    }

    public function gallery(Request $request)
    {
        $album    = $request->get('album');
        $category = $request->get('category');

        $query = \App\Models\GalleryItem::orderBy('sort_order')->orderByDesc('created_at');

        if ($album) {
            $query->where('album', $album);
        } elseif ($category) {
            $query->where('category', $category);
        }

        $items      = $query->paginate(24)->withQueryString();
        $categories = \App\Models\GalleryItem::CATEGORIES;
        $albums     = \App\Models\GalleryItem::albumCounts();

        return view('public.gallery', compact('items', 'categories', 'albums', 'album', 'category'));
    }

    public function livestream()
    {
        $featured    = \App\Models\Livestream::active()->featured()->orderByDesc('created_at')->first();
        $livestreams = \App\Models\Livestream::active()->orderByDesc('created_at')->paginate(12);
        return view('public.livestream', compact('featured', 'livestreams'));
    }
}
