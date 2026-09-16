<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Event;
use App\Models\Inquiry;
use App\Models\MassSchedule;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

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
        // Dynamic validation per subject
        $subject   = $request->input('subject', '');
        $rules     = \App\Services\InquirySubjectConfig::rulesFor($subject);
        $validated = $request->validate($rules);

        // Store user-uploaded attachments
        $storedAttachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('inquiries/user', 'public');
                $storedAttachments[] = [
                    'original_name' => $file->getClientOriginalName(),
                    'path'          => $path,
                    'mime'          => $file->getMimeType(),
                    'size'          => $file->getSize(),
                ];
            }
        }

        // ── 1. Save inquiry to DB FIRST — mail failure must never lose the record ──
        $inquiry = Inquiry::create([
            'name'           => $validated['name'],
            'email'          => $validated['email'],
            'phone'          => $validated['phone'] ?? null,
            'subject'        => $validated['subject'],
            'message'        => $validated['message'],
            'preferred_date' => $validated['preferred_date'] ?? null,
            'preferred_time' => $validated['preferred_time'] ?? null,
            'attachments'    => $storedAttachments ?: null,
            'status'         => 'new',
        ]);

        // ── 2. Send notification email to parish (non-fatal) ──
        // Resend/Brevo block unverified sender domains — log the error gracefully
        // so the parishioner still gets the success message and the record is saved.
        $mailWarning = null;
        try {
            $parishEmail = config('parish.email');
            if (!$parishEmail || !filter_var($parishEmail, FILTER_VALIDATE_EMAIL)) {
                $parishEmail = config('mail.from.address');
            }
            \Mail::to($parishEmail)->send(new \App\Mail\InquiryMail($validated));
        } catch (\Exception $e) {
            \Log::error('InquiryMail failed (inquiry already saved): ' . $e->getMessage(), [
                'inquiry_id' => $inquiry->id,
            ]);
            // Don't crash the request — inquiry is already in the database.
        }

        // ── 3. Notify admin users via bell notification (non-fatal) ──
        try {
            $admins = User::role(['super_admin', 'parish_secretary', 'finance_officer'])->get();
            if ($admins->isNotEmpty()) {
                Notification::send(
                    $admins,
                    new \App\Notifications\AdminInquiryNotification(
                        $validated['name'],
                        $validated['email'],
                        $validated['subject'],
                        $validated['message'],
                        $inquiry->id
                    )
                );
            }
        } catch (\Exception $e) {
            \Log::warning('AdminInquiryNotification failed: ' . $e->getMessage(), [
                'inquiry_id' => $inquiry->id,
            ]);
        }

        return back()->with('success', 'Your inquiry has been received. We will get back to you shortly.');
    }

    public function announcements(Request $request)
    {
        $query = Announcement::published()->orderByDesc('published_at');

        if ($category = $request->get('category')) {
            $query->where('category', $category);
        }

        $announcements = $query->paginate(12)->withQueryString();

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
        $upcomingEvents = Event::published()
            ->where(function ($q) {
                $q->where('event_start', '>=', now())
                  ->orWhere(function ($q2) {
                      $q2->where('event_start', '<', now())
                         ->where(function ($q3) {
                             $q3->whereNull('event_end')
                                ->orWhere('event_end', '>=', now());
                         });
                  });
            })
            ->orderBy('event_start')
            ->paginate(9);

        $featuredEvent = Event::published()
            ->featured()
            ->where(function ($q) {
                $q->where('event_start', '>=', now())
                  ->orWhere(function ($q2) {
                      $q2->where('event_start', '<', now())
                         ->where(function ($q3) {
                             $q3->whereNull('event_end')
                                ->orWhere('event_end', '>=', now());
                         });
                  });
            })
            ->orderBy('event_start')
            ->first();

        // Fall back to featured without date filter if none found
        if (!$featuredEvent) {
            $featuredEvent = Event::published()->featured()->orderByDesc('event_start')->first();
        }

        $pastEvents = Event::published()
            ->where('event_start', '<', now())
            ->where(function ($q) {
                $q->whereNotNull('event_end')->where('event_end', '<', now());
            })
            ->orderByDesc('event_start')
            ->take(6)
            ->get();

        return view('public.events', compact('upcomingEvents', 'featuredEvent', 'pastEvents'));
    }

    public function event(Event $event)
    {
        if ($event->status !== 'published') {
            abort(404);
        }
        $relatedEvents = Event::published()
            ->where('category', $event->category)
            ->where('id', '!=', $event->id)
            ->orderBy('event_start')
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
