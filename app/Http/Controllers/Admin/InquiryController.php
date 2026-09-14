<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AdminInquiryReplyMail;
use App\Models\Inquiry;
use App\Services\InquirySubjectConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class InquiryController extends Controller
{
    public function index(Request $request)
    {
        $query = Inquiry::latest();

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        $inquiries = $query->paginate(20)->withQueryString();

        $counts = [
            'all'         => Inquiry::count(),
            'new'         => Inquiry::where('status', 'new')->count(),
            'in_progress' => Inquiry::where('status', 'in_progress')->count(),
            'replied'     => Inquiry::where('status', 'replied')->count(),
            'resolved'    => Inquiry::where('status', 'resolved')->count(),
            'closed'      => Inquiry::where('status', 'closed')->count(),
        ];

        return view('admin.inquiries.index', compact('inquiries', 'counts'));
    }

    public function show(Inquiry $inquiry)
    {
        // Mark as read if still new
        if ($inquiry->status === 'new') {
            $inquiry->update(['status' => 'read']);
        }

        $subjectConfig = InquirySubjectConfig::for($inquiry->subject);

        return view('admin.inquiries.show', compact('inquiry', 'subjectConfig'));
    }

    public function reply(Request $request, Inquiry $inquiry)
    {
        $allSubjects = implode(',', array_keys(InquirySubjectConfig::all()));

        $request->validate([
            'reply_subject'      => ['required', 'string', "in:{$allSubjects}"],
            'reply'              => ['required', 'string', 'max:5000'],
            'admin_attachments'  => ['nullable', 'array', 'max:5'],
            'admin_attachments.*'=> ['file', 'mimes:jpg,jpeg,png,pdf,doc,docx', 'max:5120'],
        ]);

        $adminName    = auth()->user()->name;
        $replyText    = $request->input('reply');
        $replySubject = $request->input('reply_subject');

        // Store admin-uploaded attachments
        $storedAttachments = [];
        if ($request->hasFile('admin_attachments')) {
            foreach ($request->file('admin_attachments') as $file) {
                $path = $file->store('inquiries/admin', 'public');
                $storedAttachments[] = [
                    'original_name' => $file->getClientOriginalName(),
                    'path'          => $path,
                    'mime'          => $file->getMimeType(),
                    'size'          => $file->getSize(),
                ];
            }
        }

        // Save reply to DB and mark as replied
        $inquiry->addReply($adminName, $replyText, $storedAttachments, $replySubject);

        // Send email to the inquirer
        try {
            Mail::to($inquiry->email, $inquiry->name)
                ->send(new AdminInquiryReplyMail($inquiry, $replyText, $adminName, $storedAttachments));
        } catch (\Exception $e) {
            \Log::error('Inquiry reply email failed: ' . $e->getMessage());
            return back()->with('warning', 'Reply saved, but the email could not be sent. Please check mail settings.');
        }

        return back()->with('success', 'Reply sent to ' . $inquiry->email . ' successfully.');
    }

    /**
     * Update inquiry status (in_progress, resolved, closed, read).
     * Does NOT change status when a reply is sent — that stays as 'replied'.
     */
    public function updateStatus(Request $request, Inquiry $inquiry)
    {
        $request->validate([
            'status' => ['required', 'string', 'in:new,read,replied,in_progress,resolved,closed'],
        ]);

        $inquiry->update(['status' => $request->status]);

        $labels = [
            'in_progress' => 'Marked as In Progress',
            'resolved'    => 'Marked as Resolved',
            'closed'      => 'Closed',
            'read'        => 'Marked as Read',
        ];

        return back()->with('success', $labels[$request->status] ?? 'Status updated.');
    }
    public function attachment(string $path)
    {
        // path is base64-encoded to avoid URL encoding issues with slashes
        $realPath = base64_decode($path);

        // Only allow paths inside inquiries/
        if (!str_starts_with($realPath, 'inquiries/')) {
            abort(403);
        }

        if (!Storage::disk('public')->exists($realPath)) {
            abort(404);
        }

        return Storage::disk('public')->response($realPath);
    }
}
