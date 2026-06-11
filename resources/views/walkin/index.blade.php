<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Walk-in Booking — Mary Help of Christians Parish</title>
    @vite(['resources/css/app.css'])
    <style>
        * { box-sizing: border-box; }
        body {
            background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 60%, #2563eb 100%);
            min-height: 100vh;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            padding: 1rem;
        }
        .kiosk-wrap {
            max-width: 780px;
            margin: 0 auto;
        }
        /* Header */
        .kiosk-header {
            text-align: center;
            padding: 1.5rem 1rem 1rem;
            color: #fff;
        }
        .kiosk-header img {
            width: 80px; height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid rgba(255,255,255,0.7);
            box-shadow: 0 4px 16px rgba(0,0,0,0.3);
            margin-bottom: 0.75rem;
        }
        .kiosk-header h1 {
            font-size: 1.375rem;
            font-weight: 800;
            margin: 0 0 4px;
        }
        .kiosk-header p {
            font-size: 0.875rem;
            color: rgba(191,219,254,0.9);
            margin: 0;
        }
        /* Card */
        .kiosk-card {
            background: #fff;
            border-radius: 1.25rem;
            box-shadow: 0 12px 40px rgba(0,0,0,0.2);
            overflow: hidden;
            margin-bottom: 1rem;
        }
        .kiosk-card-header {
            background: linear-gradient(to right, #f0f4ff, #e8f0fe);
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e8f0fe;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .step-badge {
            width: 32px; height: 32px;
            border-radius: 50%;
            background: #2563eb;
            color: #fff;
            font-weight: 800;
            font-size: 0.875rem;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .kiosk-card-header h2 {
            font-size: 1rem;
            font-weight: 700;
            color: #1e3a8a;
            margin: 0;
        }
        .kiosk-card-body {
            padding: 1.5rem;
        }
        /* Field groups */
        .field-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        @media (max-width: 560px) {
            .field-group { grid-template-columns: 1fr; }
        }
        .field-group.three {
            grid-template-columns: 1fr 1fr 1fr;
        }
        @media (max-width: 640px) {
            .field-group.three { grid-template-columns: 1fr 1fr; }
        }
        .field-group.full { grid-template-columns: 1fr; }
        .field {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .field label {
            font-size: 0.8125rem;
            font-weight: 600;
            color: #374151;
        }
        .field label span { color: #ef4444; }
        .field input,
        .field select,
        .field textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 0.625rem;
            font-size: 0.9375rem;
            color: #111827;
            background: #fff;
            transition: border-color 0.15s, box-shadow 0.15s;
            outline: none;
            -webkit-appearance: none;
        }
        .field input:focus,
        .field select:focus,
        .field textarea:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.12);
        }
        .field .error { color: #ef4444; font-size: 0.75rem; }
        /* Service grid */
        .service-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 0.75rem;
        }
        .service-card {
            border: 2px solid #e5e7eb;
            border-radius: 0.875rem;
            padding: 1rem;
            cursor: pointer;
            transition: all 0.2s;
            background: #fff;
            position: relative;
        }
        .service-card:hover { border-color: #93c5fd; background: #eff6ff; }
        .service-card.selected { border-color: #2563eb; background: #eff6ff; box-shadow: 0 0 0 3px rgba(37,99,235,0.12); }
        .service-card input[type=radio] {
            position: absolute; opacity: 0; width: 0; height: 0;
        }
        .service-icon { font-size: 1.75rem; margin-bottom: 6px; }
        .service-name { font-weight: 700; font-size: 0.9rem; color: #1e3a8a; margin-bottom: 2px; }
        .service-fee {
            display: inline-flex; align-items: center;
            background: #d1fae5; color: #065f46;
            font-size: 0.75rem; font-weight: 700;
            padding: 2px 8px; border-radius: 9999px;
        }
        .service-free {
            background: #f1f5f9; color: #64748b;
        }
        .service-cat {
            font-size: 0.7rem; font-weight: 700;
            letter-spacing: 0.1em; text-transform: uppercase;
            color: #94a3b8; margin-bottom: 0.5rem;
        }
        /* Submit button */
        .submit-btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #1e3a8a, #2563eb);
            color: #fff;
            font-weight: 800;
            font-size: 1.0625rem;
            border: none;
            border-radius: 0.875rem;
            cursor: pointer;
            box-shadow: 0 4px 16px rgba(37,99,235,0.4);
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .submit-btn:hover { transform: translateY(-1px); box-shadow: 0 8px 24px rgba(37,99,235,0.45); }
        /* Info banner */
        .info-banner {
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 0.875rem;
            padding: 1rem 1.25rem;
            color: #fff;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        /* Availability calendar mini */
        .date-hint {
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 4px;
            padding: 4px 8px;
            background: #f8faff;
            border-radius: 6px;
        }
    </style>
</head>
<body>
<div class="kiosk-wrap">

    {{-- Header --}}
    <div class="kiosk-header">
        <img src="{{ asset('images/parish-logo.png') }}" alt="MHC Parish"
             onerror="this.style.display='none'">
        <h1>Mary Help of Christians Parish</h1>
        <p>Walk-in Service Booking · Southville 1, Niugan, Cabuyao, Laguna</p>
    </div>

    {{-- Info banner --}}
    <div class="info-banner">
        <svg style="width:20px;height:20px;flex-shrink:0;margin-top:1px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>
            <p style="font-weight:700;margin:0 0 2px;">Walk-in Booking Form</p>
            <p style="font-size:0.8125rem;margin:0;color:rgba(255,255,255,0.85);">
                Fill out this form to book a parish service. No account needed.
                You will receive a printed stub with a QR code for verification.
                Your booking is subject to approval by the parish office.
            </p>
        </div>
    </div>

    {{-- Error summary --}}
    @if($errors->any())
    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:0.875rem;padding:1rem 1.25rem;margin-bottom:1rem;color:#991b1b;">
        <p style="font-weight:700;margin:0 0 6px;">Please fix the following:</p>
        <ul style="margin:0;padding-left:1.25rem;">
            @foreach($errors->all() as $error)<li style="font-size:0.875rem;">{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('walkin.store') }}" id="walkin-form">
        @csrf

        {{-- STEP 1: Personal Information --}}
        <div class="kiosk-card">
            <div class="kiosk-card-header">
                <div class="step-badge">1</div>
                <div>
                    <h2>Your Personal Information</h2>
                    <p style="font-size:0.8rem;color:#64748b;margin:0;">Please provide your name and contact details</p>
                </div>
            </div>
            <div class="kiosk-card-body">

                <div class="field-group three">
                    <div class="field">
                        <label>First Name <span>*</span></label>
                        <input type="text" name="first_name" value="{{ old('first_name') }}"
                               placeholder="Juan" required autofocus>
                        @error('first_name')<p class="error">{{ $message }}</p>@enderror
                    </div>
                    <div class="field">
                        <label>Middle Name</label>
                        <input type="text" name="middle_name" value="{{ old('middle_name') }}"
                               placeholder="Santos">
                    </div>
                    <div class="field">
                        <label>Last Name <span>*</span></label>
                        <input type="text" name="last_name" value="{{ old('last_name') }}"
                               placeholder="Dela Cruz" required>
                        @error('last_name')<p class="error">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="field-group">
                    <div class="field">
                        <label>Contact Number <span>*</span></label>
                        <input type="tel" name="contact_number" value="{{ old('contact_number') }}"
                               placeholder="09XX-XXX-XXXX" required>
                        @error('contact_number')<p class="error">{{ $message }}</p>@enderror
                    </div>
                    <div class="field">
                        <label>Email Address <span style="color:#94a3b8;font-weight:400;">(optional)</span></label>
                        <input type="email" name="email" value="{{ old('email') }}"
                               placeholder="your@email.com">
                    </div>
                </div>

                <div class="field-group">
                    <div class="field">
                        <label>Street Address</label>
                        <input type="text" name="address" value="{{ old('address') }}"
                               placeholder="Block, Lot, Street">
                    </div>
                    <div class="field">
                        <label>Barangay</label>
                        <input type="text" name="barangay" value="{{ old('barangay', 'Niugan') }}"
                               placeholder="e.g. Niugan">
                    </div>
                </div>

            </div>
        </div>

        {{-- STEP 2: Service Selection --}}
        <div class="kiosk-card">
            <div class="kiosk-card-header">
                <div class="step-badge">2</div>
                <div>
                    <h2>Select a Service</h2>
                    <p style="font-size:0.8rem;color:#64748b;margin:0;">Tap the service you want to book</p>
                </div>
            </div>
            <div class="kiosk-card-body">

                @foreach($services as $category => $categoryServices)
                <div class="service-cat">{{ $category }}</div>
                <div class="service-grid" style="margin-bottom:1.25rem;">
                    @foreach($categoryServices as $service)
                    @php
                        $icons = [
                            'baptism' => '💧', 'wedding' => '💍', 'funeral_mass' => '🕯️',
                            'house_blessing' => '🏠', 'car_blessing' => '🚗', 'business_blessing' => '🏪',
                            'sick_call' => '🙏', 'pre_baptismal' => '📚', 'pre_marriage' => '💑',
                            'confirmation_catechesis' => '✝️', 'mass_intention' => '⛪',
                        ];
                        $icon = $icons[$service->slug] ?? '📋';
                        $isSelected = old('booking_type') === $service->slug;
                    @endphp
                    <label class="service-card {{ $isSelected ? 'selected' : '' }}"
                           onclick="selectService(this, '{{ $service->slug }}')">
                        <input type="radio" name="booking_type" value="{{ $service->slug }}"
                               {{ $isSelected ? 'checked' : '' }} required>
                        <div class="service-icon">{{ $icon }}</div>
                        <div class="service-name">{{ $service->name }}</div>
                        @if($service->fee > 0)
                            <span class="service-fee">₱{{ number_format($service->fee, 0) }}</span>
                        @else
                            <span class="service-fee service-free">Free / Donation</span>
                        @endif
                    </label>
                    @endforeach
                </div>
                @endforeach

                @error('booking_type')
                <p class="error" style="font-size:0.875rem;margin-top:-0.5rem;">{{ $message }}</p>
                @enderror

            </div>
        </div>

        {{-- STEP 3: Schedule --}}
        <div class="kiosk-card">
            <div class="kiosk-card-header">
                <div class="step-badge">3</div>
                <div>
                    <h2>Preferred Schedule</h2>
                    <p style="font-size:0.8rem;color:#64748b;margin:0;">Choose your preferred date and time</p>
                </div>
            </div>
            <div class="kiosk-card-body">

                <div class="field-group">
                    <div class="field">
                        <label>Preferred Date <span>*</span></label>
                        <input type="date" name="scheduled_date"
                               value="{{ old('scheduled_date') }}"
                               min="{{ now()->toDateString() }}"
                               required>
                        <p class="date-hint">📅 Booking is subject to availability and approval</p>
                        @error('scheduled_date')<p class="error">{{ $message }}</p>@enderror
                    </div>
                    <div class="field">
                        <label>Preferred Time <span style="color:#94a3b8;font-weight:400;">(optional)</span></label>
                        <input type="time" name="scheduled_time" value="{{ old('scheduled_time') }}">
                        <p class="date-hint">⏰ Leave blank if time is flexible</p>
                    </div>
                </div>

                <div class="field-group full">
                    <div class="field">
                        <label>Additional Notes / Special Requests</label>
                        <textarea name="notes" rows="3"
                                  placeholder="e.g. Names of persons involved, special arrangements, requirements to bring...">{{ old('notes') }}</textarea>
                    </div>
                </div>

            </div>
        </div>

        {{-- Reminder --}}
        <div class="kiosk-card">
            <div class="kiosk-card-body" style="padding:1.25rem 1.5rem;">
                <div style="display:flex;align-items:flex-start;gap:0.875rem;">
                    <div style="width:40px;height:40px;border-radius:50%;background:#fef3c7;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1.25rem;">⚠️</div>
                    <div>
                        <p style="font-weight:700;color:#92400e;margin:0 0 6px;font-size:0.9375rem;">Important Reminders</p>
                        <ul style="margin:0;padding-left:1.25rem;color:#78350f;font-size:0.875rem;line-height:1.7;">
                            <li>Your booking request is <strong>subject to approval</strong> by the parish office.</li>
                            <li>Please bring the required documents for your chosen service.</li>
                            <li>After submitting, you will receive a <strong>QR stub</strong> — keep it for verification.</li>
                            <li>For sacraments, complete any required seminars before your scheduled date.</li>
                            <li>Payment can be settled at the parish office on the day of service.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- Submit --}}
        <button type="submit" class="submit-btn" id="submit-btn" onclick="confirmSubmit(event)">
            <svg style="width:20px;height:20px;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Submit Walk-in Booking Request
        </button>

        <p style="text-align:center;color:rgba(255,255,255,0.7);font-size:0.8125rem;margin-top:0.75rem;">
            After submitting, print your QR stub and present it at the parish office.
        </p>

    </form>

    <div style="text-align:center;padding:1rem 0 2rem;color:rgba(255,255,255,0.5);font-size:0.75rem;">
        © {{ date('Y') }} Mary Help of Christians Parish · All rights reserved
    </div>

</div>

<script>
function selectService(label, slug) {
    // Remove selected from all
    document.querySelectorAll('.service-card').forEach(c => c.classList.remove('selected'));
    // Add to clicked
    label.classList.add('selected');
    // Check the radio
    label.querySelector('input[type=radio]').checked = true;
}

function confirmSubmit(e) {
    const name = document.querySelector('[name=first_name]').value.trim()
                + ' ' + document.querySelector('[name=last_name]').value.trim();
    const service = document.querySelector('[name=booking_type]:checked');
    const date = document.querySelector('[name=scheduled_date]').value;

    if (!service) {
        e.preventDefault();
        alert('Please select a service before submitting.');
        return;
    }

    if (!date) {
        e.preventDefault();
        alert('Please select a preferred date.');
        return;
    }

    const serviceLabel = service.closest('.service-card').querySelector('.service-name').textContent;
    const formattedDate = new Date(date + 'T00:00:00').toLocaleDateString('en-PH', {
        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
    });

    if (!confirm(`Please confirm your booking:\n\n👤 Name: ${name}\n📋 Service: ${serviceLabel}\n📅 Date: ${formattedDate}\n\nProceed with booking?`)) {
        e.preventDefault();
    }
}
</script>
</body>
</html>
