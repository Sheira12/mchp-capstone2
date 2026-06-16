@extends('layouts.public')
@section('title', 'Contact Us')

@push('styles')
<style>
.contact-hero {
    background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
    padding: 4rem 0 3rem;
    color: #fff;
    position: relative;
    overflow: hidden;
}
.contact-hero::before {
    content: '';
    position: absolute;
    top: -60px; right: -60px;
    width: 280px; height: 280px;
    background: radial-gradient(circle, rgba(96,165,250,0.18), transparent 70%);
}
.info-card {
    background: #fff;
    border-radius: 1.25rem;
    border: 1px solid #e8edf5;
    box-shadow: 0 4px 16px rgba(0,0,0,0.06);
    overflow: hidden;
}
.info-item {
    display: flex; align-items: flex-start; gap: 1rem;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #f1f5f9;
    transition: background 0.15s;
}
.info-item:last-child { border-bottom: none; }
.info-item:hover { background: #f8faff; }
.info-icon {
    width: 42px; height: 42px; border-radius: 10px;
    background: #eff6ff;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.form-field label { display: block; font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 6px; }
.form-field input, .form-field textarea, .form-field select {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 0.75rem;
    font-size: 0.9375rem;
    color: #111827;
    background: #fff;
    transition: border-color 0.15s, box-shadow 0.15s;
    outline: none;
    font-family: inherit;
}
.form-field input:focus, .form-field textarea:focus, .form-field select:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
}
</style>
@endpush

@section('content')

{{-- Hero --}}
<section class="contact-hero">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center">
            <p style="color:#bfdbfe;font-size:0.8125rem;font-weight:700;letter-spacing:0.15em;text-transform:uppercase;margin-bottom:0.5rem;">We're here to help</p>
            <h1 style="font-size:clamp(2rem,5vw,3rem);font-weight:800;margin-bottom:0.75rem;">Get in Touch</h1>
            <p style="color:#bfdbfe;font-size:1rem;max-width:520px;margin:0 auto;">
                Have a question about our services, sacraments, or need pastoral assistance?
                We'd love to hear from you.
            </p>
        </div>
    </div>
</section>

{{-- Contact cards strip --}}
<section style="background:#f8faff;border-bottom:1px solid #e8edf5;padding:1.5rem 0;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach([
                ['📍', '#eff6ff', '#2563eb', 'Address', config('parish.address')],
                ['📞', '#f0fdf4', '#16a34a', 'Phone', config('parish.phone')],
                ['✉️', '#fef3c7', '#d97706', 'Email', config('parish.email')],
                ['⏰', '#f5f3ff', '#7c3aed', 'Office Hours', 'Tue–Sun: 9AM–12NN, 2PM–5PM'],
            ] as $c)
            <div style="background:#fff;border-radius:1rem;border:1px solid #e8edf5;padding:1.25rem;display:flex;align-items:flex-start;gap:0.875rem;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
                <div style="width:40px;height:40px;border-radius:10px;background:{{ $c[1] }};display:flex;align-items:center;justify-content:center;font-size:1.125rem;flex-shrink:0;">{{ $c[0] }}</div>
                <div>
                    <p style="font-size:0.7rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:#94a3b8;margin-bottom:2px;">{{ $c[3] }}</p>
                    <p style="font-size:0.875rem;font-weight:600;color:#0f172a;">{{ $c[4] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Main content --}}
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">

        {{-- Left: Info --}}
        <div class="space-y-6">

            {{-- Parish info card --}}
            <div class="info-card">
                <div style="padding:1.25rem 1.5rem;background:linear-gradient(to right,#f0f4ff,#eff6ff);border-bottom:1px solid #e8edf5;">
                    <h2 style="font-size:1rem;font-weight:800;color:#1e3a8a;margin:0;">Parish Office</h2>
                </div>
                <div class="info-item">
                    <div class="info-icon"><svg style="width:18px;height:18px;color:#2563eb;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg></div>
                    <div>
                        <p style="font-weight:700;font-size:0.875rem;color:#0f172a;margin-bottom:2px;">Location</p>
                        <p style="font-size:0.875rem;color:#64748b;">{{ config('parish.address') }}</p>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-icon"><svg style="width:18px;height:18px;color:#16a34a;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg></div>
                    <div>
                        <p style="font-weight:700;font-size:0.875rem;color:#0f172a;margin-bottom:2px;">Telephone</p>
                        <p style="font-size:0.875rem;color:#64748b;">{{ config('parish.phone') }}</p>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-icon"><svg style="width:18px;height:18px;color:#d97706;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg></div>
                    <div>
                        <p style="font-weight:700;font-size:0.875rem;color:#0f172a;margin-bottom:2px;">Email</p>
                        <p style="font-size:0.875rem;color:#64748b;">{{ config('parish.email') }}</p>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-icon"><svg style="width:18px;height:18px;color:#7c3aed;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg></div>
                    <div>
                        <p style="font-weight:700;font-size:0.875rem;color:#0f172a;margin-bottom:2px;">Parish Priest</p>
                        <p style="font-size:0.875rem;color:#64748b;">{{ config('parish.priest') }}</p>
                    </div>
                </div>
            </div>

            {{-- Office hours --}}
            <div class="info-card">
                <div style="padding:1.25rem 1.5rem;background:linear-gradient(to right,#f0f4ff,#eff6ff);border-bottom:1px solid #e8edf5;">
                    <h2 style="font-size:1rem;font-weight:800;color:#1e3a8a;margin:0;">Office Hours</h2>
                </div>
                <div style="padding:1rem 1.5rem;">
                    <table style="width:100%;font-size:0.875rem;border-collapse:collapse;">
                        @foreach([
                            ['Monday', 'Closed', true],
                            ['Tuesday – Friday', '8:00 AM – 12:00 NN', false],
                            ['', '2:00 PM – 5:00 PM', false],
                            ['Saturday', '8:00 AM – 12:00 PM', false],
                            ['Sunday', 'After morning Masses', false],
                        ] as $row)
                        <tr style="border-bottom:1px solid #f1f5f9;">
                            <td style="padding:0.5rem 0;color:#64748b;font-weight:{{ $row[0] ? '500' : '400' }};">{{ $row[0] }}</td>
                            <td style="padding:0.5rem 0;font-weight:600;color:{{ $row[2] ? '#ef4444' : '#0f172a' }};text-align:right;">{{ $row[1] }}</td>
                        </tr>
                        @endforeach
                    </table>
                </div>
            </div>

            {{-- Mass schedule quick ref --}}
            <div style="background:linear-gradient(135deg,#1e3a8a,#312e81);border-radius:1.25rem;padding:1.5rem;color:#fff;">
                <h3 style="font-weight:800;font-size:0.9375rem;margin-bottom:0.875rem;display:flex;align-items:center;gap:8px;">
                    ⛪ Mass Schedule
                </h3>
                <div style="font-size:0.8125rem;color:#bfdbfe;line-height:1.9;">
                    <p><strong style="color:#fff;">Weekdays:</strong> 6:00 AM · 6:00 PM</p>
                    <p><strong style="color:#fff;">Sundays:</strong> 6:00 AM · 8:00 AM · 10:00 AM · 6:00 PM</p>
                </div>
                <a href="{{ route('services') }}"
                   style="display:inline-flex;align-items:center;gap:6px;margin-top:0.875rem;background:rgba(255,255,255,0.12);color:#fff;font-size:0.8125rem;font-weight:600;padding:0.5rem 1rem;border-radius:0.625rem;text-decoration:none;border:1px solid rgba(255,255,255,0.2);transition:background 0.2s;"
                   onmouseover="this.style.background='rgba(255,255,255,0.22)';"
                   onmouseout="this.style.background='rgba(255,255,255,0.12)';">
                    View all services →
                </a>
            </div>

        </div>

        {{-- Right: Inquiry form --}}
        <div>
            <div style="background:#fff;border-radius:1.5rem;border:1px solid #e8edf5;box-shadow:0 8px 32px rgba(0,0,0,0.08);overflow:hidden;">
                <div style="padding:1.5rem 2rem;background:linear-gradient(135deg,#1e3a8a,#2563eb);">
                    <h2 style="font-size:1.125rem;font-weight:800;color:#fff;margin:0 0 4px;">Send an Inquiry</h2>
                    <p style="color:#bfdbfe;font-size:0.8125rem;margin:0;">We'll respond within 24 hours on business days.</p>
                </div>

                {{-- Success message --}}
                @if(session('success'))
                <div style="margin:1.25rem 2rem 0;background:#f0fdf4;border:1.5px solid #86efac;border-radius:0.75rem;padding:1rem 1.25rem;display:flex;align-items:flex-start;gap:10px;">
                    <svg style="width:20px;height:20px;color:#16a34a;flex-shrink:0;margin-top:1px;" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <div>
                        <p style="font-weight:700;color:#166534;margin:0 0 2px;">Message Sent!</p>
                        <p style="font-size:0.875rem;color:#15803d;margin:0;">{{ session('success') }}</p>
                    </div>
                </div>
                @endif

                @if($errors->any())
                <div style="margin:1.25rem 2rem 0;background:#fef2f2;border:1.5px solid #fca5a5;border-radius:0.75rem;padding:1rem 1.25rem;">
                    <p style="font-weight:700;color:#dc2626;margin:0 0 6px;">Please fix these errors:</p>
                    @foreach($errors->all() as $error)
                    <p style="font-size:0.875rem;color:#b91c1c;margin:2px 0;">• {{ $error }}</p>
                    @endforeach
                </div>
                @endif

                <form method="POST" action="{{ route('contact.submit') }}" style="padding:1.5rem 2rem;display:flex;flex-direction:column;gap:1.25rem;">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="form-field">
                            <label>Your Name <span style="color:#ef4444;">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" required placeholder="Juan dela Cruz">
                        </div>
                        <div class="form-field">
                            <label>Phone Number</label>
                            <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="09XX-XXX-XXXX">
                        </div>
                    </div>

                    <div class="form-field">
                        <label>Email Address <span style="color:#ef4444;">*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}" required placeholder="your@email.com">
                    </div>

                    <div class="form-field">
                        <label>Subject <span style="color:#ef4444;">*</span></label>
                        <select name="subject" required>
                            <option value="">Select a subject…</option>
                            @foreach([
                                'General Inquiry',
                                'Mass Schedule',
                                'Sacrament Requirements',
                                'Booking / Appointment',
                                'Certificate Request',
                                'Donation / Support',
                                'Complaint / Feedback',
                                'Other',
                            ] as $s)
                            <option value="{{ $s }}" {{ old('subject') === $s ? 'selected' : '' }}>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-field">
                        <label>Message <span style="color:#ef4444;">*</span></label>
                        <textarea name="message" rows="5" required placeholder="How can we help you? Please provide as much detail as possible…">{{ old('message') }}</textarea>
                    </div>

                    <button type="submit"
                            style="width:100%;padding:1rem;background:linear-gradient(135deg,#1e3a8a,#2563eb);color:#fff;font-weight:700;font-size:1rem;border:none;border-radius:0.875rem;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:10px;box-shadow:0 4px 16px rgba(37,99,235,0.35);transition:all 0.2s;"
                            onmouseover="this.style.transform='translateY(-1px)';this.style.boxShadow='0 8px 24px rgba(37,99,235,0.45)';"
                            onmouseout="this.style.transform='';this.style.boxShadow='0 4px 16px rgba(37,99,235,0.35)';">
                        <svg style="width:18px;height:18px;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        Send Inquiry
                    </button>

                    <p style="text-align:center;font-size:0.8125rem;color:#94a3b8;margin:0;">
                        Or call us directly at <strong style="color:#374151;">{{ config('parish.phone') }}</strong>
                    </p>
                </form>
            </div>
        </div>

    </div>
</div>

@endsection
