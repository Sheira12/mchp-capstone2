<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    private array $faqs = [
        'mass_schedule' => [
            'patterns' => ['mass', 'schedule', 'misa', 'simbahan', 'time', 'oras'],
            'response' => "Our regular Mass schedules are:\n\n**Weekdays (Mon-Sat):**\n• 6:00 AM - Filipino\n• 6:00 PM - Filipino\n\n**Sundays:**\n• 6:00 AM - Filipino\n• 8:00 AM - Filipino\n• 10:00 AM - English\n• 6:00 PM - Filipino\n\nFor special Masses and feast days, please check our announcements.",
        ],
        'baptism' => [
            'patterns' => ['baptism', 'binyag', 'baptize', 'christening'],
            'response' => "**Requirements for Baptism:**\n\n1. Birth Certificate of the child\n2. Marriage Certificate of parents (if married in the Church)\n3. Completed Pre-Baptismal Seminar\n4. Godparents must be confirmed Catholics\n\n**Fee:** ₱500.00\n\nPlease attend our Pre-Baptismal Seminar first. You can book it through our online booking system or visit the parish office.",
        ],
        'marriage' => [
            'patterns' => ['marriage', 'kasal', 'wedding', 'pre-cana', 'pre-marriage'],
            'response' => "**Requirements for Church Wedding:**\n\n1. Baptismal Certificate (issued within 6 months)\n2. Confirmation Certificate\n3. Certificate of No Impediment\n4. Pre-Marriage Seminar (Pre-Cana) completion\n5. Civil Marriage Certificate (or plan to have civil wedding)\n6. Canonical Interview with the priest\n\nPlease contact the parish office at least **6 months before** your intended wedding date.\n\n**Fee:** Starting at ₱3,000.00",
        ],
        'confirmation' => [
            'patterns' => ['confirmation', 'kumpil', 'confirm'],
            'response' => "**Confirmation Requirements:**\n\n1. Baptismal Certificate\n2. First Communion Certificate\n3. Completion of Confirmation Catechesis\n4. Sponsor (Ninong/Ninang) who is a confirmed Catholic\n\nConfirmation is usually administered once a year. Please watch for announcements on the schedule.",
        ],
        'office_hours' => [
            'patterns' => ['office', 'hours', 'open', 'bukas', 'oras ng opisina', 'contact'],
            'response' => "**Parish Office Hours:**\n\n• Monday to Friday: 8:00 AM - 12:00 PM, 1:00 PM - 5:00 PM\n• Saturday: 8:00 AM - 12:00 PM\n• Sunday: After morning Masses\n\n**Contact:**\n📞 +63 49 XXX XXXX\n📧 mhcparish@gmail.com\n📍 Southville 1, Niugan, Cabuyao, Laguna",
        ],
        'house_blessing' => [
            'patterns' => ['house blessing', 'bless', 'pagpapala', 'car blessing', 'business blessing'],
            'response' => "**House/Car/Business Blessing:**\n\nYou can book a blessing through our online booking system or visit the parish office.\n\n**Suggested Donation:** ₱300.00 - ₱500.00\n\nPlease book at least **3 days in advance** to ensure availability.",
        ],
        'certificate' => [
            'patterns' => ['certificate', 'sertipiko', 'document', 'copy'],
            'response' => "**Parish Certificates Available:**\n\n• Certificate of Baptism\n• Certificate of Confirmation\n• Certificate of Marriage\n• Certificate of First Communion\n\n**Fee:** ₱100.00 per certificate\n\nYou can request certificates through our online portal (if you have an account) or visit the parish office. Processing takes 1-3 working days.",
        ],
        'booking' => [
            'patterns' => ['book', 'schedule', 'appointment', 'reserve', 'mag-book'],
            'response' => "You can book parish services online! Here's how:\n\n1. **Create an account** or log in to the parish portal\n2. Go to **Book a Service**\n3. Select the service you need\n4. Choose your preferred date and time\n5. Submit and wait for confirmation\n\nOr visit the parish office during office hours.",
        ],
    ];

    public function chat(Request $request)
    {
        $request->validate([
            'message'    => ['required', 'string', 'max:500'],
            'session_id' => ['required', 'string'],
        ]);

        $message   = $request->get('message');
        $sessionId = $request->get('session_id');

        // Save user message
        ChatMessage::create([
            'session_id' => $sessionId,
            'sender'     => 'user',
            'message'    => $message,
            'ip_address' => $request->ip(),
        ]);

        // Process intent
        [$response, $intent] = $this->processMessage($message);

        // Save bot response
        ChatMessage::create([
            'session_id' => $sessionId,
            'sender'     => 'bot',
            'message'    => $response,
            'intent'     => $intent,
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'message' => $response,
            'intent'  => $intent,
        ]);
    }

    public function escalate(Request $request)
    {
        $request->validate([
            'session_id' => ['required', 'string'],
            'message'    => ['required', 'string'],
        ]);

        // Mark messages as escalated
        ChatMessage::where('session_id', $request->get('session_id'))
            ->update(['is_escalated' => true]);

        // Notify parish staff
        \Mail::to(config('parish.email'))->send(
            new \App\Mail\ChatEscalationMail($request->get('session_id'), $request->get('message'))
        );

        return response()->json([
            'message' => "Your inquiry has been forwarded to our parish staff. We'll get back to you as soon as possible. You can also reach us at " . config('parish.phone') . " during office hours.",
        ]);
    }

    private function processMessage(string $message): array
    {
        $lower = strtolower($message);

        // Greeting
        if (preg_match('/\b(hello|hi|good morning|good afternoon|good evening|kumusta|magandang)\b/i', $lower)) {
            return [
                "Hello! Welcome to Mary Help of Christians Parish. How can I help you today?\n\nYou can ask me about:\n• Mass schedules\n• Sacrament requirements (Baptism, Marriage, Confirmation)\n• Office hours\n• Booking services\n• Certificates",
                'greeting',
            ];
        }

        // Check FAQs
        foreach ($this->faqs as $intent => $faq) {
            foreach ($faq['patterns'] as $pattern) {
                if (str_contains($lower, $pattern)) {
                    return [$faq['response'], $intent];
                }
            }
        }

        // Fallback
        return [
            "I'm sorry, I didn't quite understand that. Here are some things I can help you with:\n\n• **Mass schedules** - Ask about Mass times\n• **Baptism** - Requirements and booking\n• **Marriage** - Wedding requirements\n• **Confirmation** - Requirements\n• **Office hours** - When we're open\n• **Certificates** - How to get parish documents\n• **Booking** - How to book services\n\nOr type **'talk to staff'** to connect with our parish team.",
            'fallback',
        ];
    }
}
