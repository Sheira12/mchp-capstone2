# MCHP Capstone 2 — Panel Revision Changelog

**Parish:** Mary Help of Christians Parish, Niugan, Cabuyao, Laguna  
**System:** Parish Management System  
**Revision Date:** October 2026

This document maps each panel revision comment to the exact implementation — what was built, which files were changed, and how to demo it.

---

## Phase 1 — Requirements Pre-Processing Before Booking

**Panel comment:** *"Clients must first provide proof that they have the requirements applicable to the service they're requesting before they can proceed to booking."*

### What was implemented

| Item | Detail |
|------|--------|
| **Requirement templates per service** | Admin can define per-service requirement items (name, description, type: file / checkbox / text, required/optional, accepted file types). |
| **Seeded defaults** | Baptism (4 items), Wedding (10 items), Funeral Mass (2), Pre-Baptismal Seminar (2), Pre-Marriage Seminar (2), Confirmation Catechesis (3). |
| **New booking flow** | Service → Submit Requirements → (admin reviews) → All required items approved → Pick date & time → Confirm. |
| **Progress indicator** | Parishioner sees "3 of 5 requirements approved" progress bar. The "Proceed to Schedule Booking" button is locked until all required items are approved. |
| **File uploads** | All files go to Supabase Storage under `booking-requirements/{booking_id}/`. Never local disk. |
| **Server-side gate** | `BookingController@store` checks `requirements_approved_at` is set before allowing date selection — cannot be bypassed via direct POST. |
| **Come-back-later** | A pending booking stub is created when requirements are first submitted. Parishioner can come back and resubmit revised items. |
| **Admin review queue** | `/admin/booking-requirements` — filterable by service, status, date range. Admin can approve or request revision with remarks per item. "Approve All" button available. |
| **Admin requirement templates** | `/admin/services/{service}/requirements` — full CRUD to add/edit/deactivate requirement items per service. |
| **Email + in-portal notifications** | Parishioner notified via email (Brevo/Resend) and portal bell on: individual item approved, all items approved (unlocks booking), needs revision. Admin notified in portal bell on new submission. |
| **Audit trail** | Every approve/reject action is logged to `audit_logs`. |

### Files changed
- `database/migrations/2026_10_09_000001_create_service_requirements_table.php`
- `database/migrations/2026_10_09_000002_create_booking_requirements_table.php`
- `database/migrations/2026_10_09_000003_add_requirements_approved_at_to_bookings.php`
- `app/Models/ServiceRequirement.php` *(new)*
- `app/Models/BookingRequirement.php` *(new)*
- `app/Models/Service.php` — added `serviceRequirements()`, `requiresPreApproval()`
- `app/Models/Booking.php` — added `bookingRequirements()`, `requirementsApproved()`, `requirementsProgress()`
- `app/Http/Controllers/Admin/ServiceRequirementController.php` *(new)*
- `app/Http/Controllers/Parishioner/BookingController.php` — added `requirementsForm()`, `uploadRequirement()`, `bookingRequirements()`, gate in `store()`
- `app/Notifications/RequirementStatusNotification.php` *(new)*
- `app/Notifications/AdminBookingRequirementNotification.php` *(new)*
- `database/seeders/ServiceRequirementSeeder.php` *(new)*
- `resources/views/admin/booking-requirements/index.blade.php` *(new)*
- `resources/views/admin/booking-requirements/show.blade.php` *(new)*
- `resources/views/admin/services/requirements/index.blade.php` *(new)*
- `resources/views/admin/services/requirements/create.blade.php` *(new)*
- `resources/views/admin/services/requirements/edit.blade.php` *(new)*
- `resources/views/parishioner/bookings/requirements.blade.php` *(new)*
- `resources/views/layouts/app.blade.php` — Requirements Queue badge in admin sidebar
- `routes/web.php` — all new routes

### How to demo
1. Log in as parishioner → Book a Service → choose **Baptism**.
2. You are taken to the Requirements page instead of the date picker.
3. Upload a sample Birth Certificate image and check the seminar checkbox → Submit.
4. Log in as admin → Requirements Queue → review the submission → click **Approve**.
5. Log back in as parishioner → Requirements page now shows green progress → "Proceed to Schedule Booking" button unlocks.
6. Complete the booking normally.

---

## Phase 2 — Service Packages & Order of Payment

**Panel comment:** *"An order of payment with packages that clients see and choose before they pay."*

### What was implemented

| Item | Detail |
|------|--------|
| **Service packages** | Admin creates packages per service (name, description, inclusions list, price). Seeded: Wedding (3 tiers), Baptism (2), Funeral Mass (2), Confirmation (1), House Blessing (1), Pre-Cana (1). |
| **Package selection** | After requirements approved + slot chosen, parishioner picks a package (or Standard). Package cards show inclusions list and price. |
| **Order of Payment generation** | System generates an `Order` record with auto-number `OP-YYYY-NNNNN`, itemized line items from the chosen package, subtotal, total, 24-hour validity. Amount is ALWAYS from the package/service — user cannot alter it. |
| **On-screen OP document** | `/portal/bookings/{booking}/order-of-payment` — full order detail with client info, service, package, line items table, total, validity date, and Pay button. |
| **Downloadable OP PDF** | DomPDF template (`parishioner/orders/pdf.blade.php`) — same layout as on-screen, with signature lines. |
| **Payment tied to order** | The Pay button on the booking show page links through the Order of Payment. Payment amount is read from `order.total`, not user input. |
| **Order history** | `/portal/orders` — parishioner can see all past orders with status. |
| **Admin package management** | `/admin/packages` — full CRUD for service packages. |

### Known bugs fixed in this phase
- **OR PDF ₱ symbol** — already fixed (DejaVu Sans font + UTF-8 ₱ character via `"\xe2\x82\xb1"`).
- **OR PDF whitespace under signatures** — `sig-line.margin-top` reduced from `14pt` to `8pt`.
- **PayMongo success 500** — `success()` route is public (no auth middleware); polling page handles session loss gracefully.

### Files changed
- `database/migrations/2026_10_09_100001_create_service_packages_table.php`
- `database/migrations/2026_10_09_100002_create_orders_table.php`
- `database/migrations/2026_10_09_100003_add_order_package_to_bookings_and_payments.php`
- `app/Models/ServicePackage.php` *(new)*
- `app/Models/Order.php` *(new)*
- `app/Models/Service.php` — added `packages()`, `allPackages()`
- `app/Models/Booking.php` — added `package()`, `order()` relations + new fillable
- `app/Models/Payment.php` — added `order()` relation + `order_id` fillable
- `app/Http/Controllers/Admin/PackageController.php` *(new)*
- `app/Http/Controllers/Parishioner/OrderController.php` *(new)*
- `database/seeders/ServicePackageSeeder.php` *(new)*
- `resources/views/admin/packages/index.blade.php` *(new)*
- `resources/views/admin/packages/create.blade.php` *(new)*
- `resources/views/admin/packages/edit.blade.php` *(new)*
- `resources/views/admin/packages/_form.blade.php` *(new)*
- `resources/views/parishioner/orders/create.blade.php` *(new)*
- `resources/views/parishioner/orders/show.blade.php` *(new)*
- `resources/views/parishioner/orders/pdf.blade.php` *(new)*
- `resources/views/parishioner/orders/index.blade.php` *(new)*
- `resources/views/parishioner/bookings/show.blade.php` — Order of Payment + package buttons
- `resources/views/parishioner/payments/receipt-pdf.blade.php` — sig whitespace fix
- `resources/views/layouts/app.blade.php` — Packages link in admin sidebar
- `resources/views/layouts/portal.blade.php` — My Orders link in portal sidebar
- `routes/web.php`

### How to demo
1. Admin → `/admin/packages` → view Wedding packages (Standard / Classic / Premium).
2. As parishioner, complete a Wedding booking (requirements pre-approved).
3. On booking detail page → click **Generate Order of Payment (Choose Package)**.
4. Choose **Classic Package** → Generate → see the itemized OP on screen with ₱10,000 total.
5. Click **Download PDF** → verify border alignment, ₱ symbol, and no whitespace gap.
6. Click **Proceed to Payment** → complete payment normally.
7. Admin → `/admin/packages` → edit a package → verify changes reflect on next OP.

---

## Phase 3 — CMS Improvements

**Panel comment:** *"Improve the CMS."*

### What was implemented

| Item | Detail |
|------|--------|
| **Rich-text editor** | TipTap loaded from jsDelivr CDN (no npm build step needed). Toolbar: Bold, Italic, Underline, H1, H2, Bullet List, Numbered List, Blockquote, Clear Formatting. |
| **Draft / Publish / Schedule** | Three status options: Draft (not visible), Publish Now (goes live immediately), Schedule for Later (enter future date/time). |
| **Status migration** | `announcements.status` enum (draft/published/scheduled). Existing records backfilled from `is_published`. `scheduled_at` column added. |
| **Pin to top** | `is_pinned` boolean — pinned announcements appear at top of the index. |
| **Backslash bug fix** | `store()` and `update()` both call `rtrim($title, '\\')`. Migration also runs a one-time DB `RTRIM` on existing titles. |
| **Thumbnail fix** | `uploadImage()` helper returns `null` and flashes a `warning` session message if Supabase upload fails — admin is informed rather than silently missing the thumbnail. Old image is preserved on edit if new upload fails. |
| **External storage** | All announcement images use `supabase` disk — no local disk writes, so images survive Render redeploys. |
| **Service requirements in admin** | Accessible at `/admin/services/{service}/requirements` — staff can manage checklists without a developer. |
| **Packages in admin** | Accessible at `/admin/packages` — staff can maintain packages without a developer. |

### Files changed
- `database/migrations/2026_10_09_200001_add_status_to_announcements_table.php`
- `app/Models/Announcement.php` — new fields, constants, scopes, scopePublished updated
- `app/Http/Controllers/Admin/AnnouncementController.php` — full rewrite
- `resources/views/admin/announcements/create.blade.php` *(rewritten)*
- `resources/views/admin/announcements/edit.blade.php` *(rewritten)*
- `resources/views/admin/announcements/_form.blade.php` *(new shared partial)*
- `resources/views/admin/announcements/_tiptap_scripts.blade.php` *(new)*
- `resources/views/admin/announcements/index.blade.php` — status badge column

### How to demo
1. Admin → Announcements → New Announcement.
2. Type a title with a trailing `\` → save → verify it is stripped on save.
3. Use the TipTap toolbar to make text **bold** and add a bullet list.
4. Set status to **Schedule for Later** → enter a future date → Save as "Scheduled".
5. Verify the index shows a blue "Scheduled" badge.
6. Edit the announcement → change status to **Publish Now** → Save.
7. Visit the public `/announcements` page → verify it appears.
8. Upload a thumbnail → verify it persists after a Render redeploy (Supabase URL, not `/storage/`).

---

## Phase 4 — Data Analysis & Better Visualization

**Panel comment:** *"Improve the visualization beyond basic charts/graphs, and add data analysis."*

### What was implemented

| Item | Detail |
|------|--------|
| **Booking heatmap** | 7-day × 24-hour CSS grid — darker cells = more bookings at that day/time. Hover tooltip shows count. |
| **Sacrament trends** | Multi-line Chart.js chart, last 24 months, one line per service type. |
| **Monthly bookings + 3-month forecast** | Bar chart with 3-month moving average overlay (yellow line) and 3-month forecast extension (green dashed line). No ML — simple rolling average. |
| **Service demand ranking** | Horizontal bar chart — all-time bookings by service type. |
| **Revenue by service** | Doughnut chart — total paid revenue broken down by service type. |
| **Parishioner age groups** | Bar chart — Children, Teens, Young Adults, Adults, Middle-aged, Senior. |
| **Top 10 barangays** | Horizontal bar chart — parishioners by barangay. |
| **Plain-language insights** | Auto-generated sentences: peak month, most requested service, completion rate, total revenue, avg requirement approval time. |
| **CSV export** | `/admin/analytics/export` — downloads last 2,000 bookings as CSV. |
| **5-minute cache** | All analytics queries cached with `Cache::remember('analytics_full', 300)`. "Refresh" button busts cache. |
| **Efficient queries** | All aggregations done in SQL (no PHP collection loops for counting). Existing indexes reused. |

### Files changed
- `app/Http/Controllers/Admin/AnalyticsController.php` *(new)*
- `resources/views/admin/analytics/index.blade.php` *(new)*
- `resources/views/layouts/app.blade.php` — Analytics link in Finance section
- `routes/web.php` — `/admin/analytics`, `/admin/analytics/data`, `/admin/analytics/export`

### How to demo
1. Admin → **Analytics** (sidebar, Finance section).
2. Page loads → spinner → all charts render.
3. Hover over the heatmap → tooltip shows day + hour + count.
4. Click **Refresh** → cache busted → data reloads.
5. Click **Export CSV** → downloads `bookings-export-YYYYMMDD.csv`.
6. View **Key Insights** cards at the top for plain-language summaries.

---

## Previously Fixed (prior sessions, not regressed)

| Issue | Status |
|-------|--------|
| Duplicate "Register" button in hero | ✅ Fixed — removed hero Register CTA, navbar Register remains |
| BookingCalendar.vue 404 endpoint | ✅ `/api/booked-dates` wired correctly; parishioner calendar uses it |
| Off-site booking fields (`location_type`, `contact_person`, `contact_phone`) | ✅ Migration + model + booking form all complete |
| Booking conflict detection | ✅ Server-side check in both admin and parishioner BookingController |
| Certificate edit requests + PDF overrides | ✅ `cert_overrides` JSON applied in PDF generation |
| Walk-in kiosk booking | ✅ `/walk-in` public route with stub printing |
| PayMongo webhook dual-verification flow | ✅ Webhook sets `pending`; admin verifies → `paid` |

---

## Manual Test Checklist

### Phase 1 — Requirements
- [ ] Submit a file upload for a Baptism booking requirement
- [ ] Admin approves one item → parishioner receives in-portal notification
- [ ] Admin requests revision → parishioner receives email with remark
- [ ] All items approved → "Proceed to Schedule Booking" unlocks
- [ ] Attempt to POST `/portal/bookings` for Baptism without requirements → redirected to requirements page
- [ ] Admin deactivates a requirement template → it no longer appears for new bookings
- [ ] Admin adds a new requirement to Wedding → appears on next Wedding booking requirements page

### Phase 2 — Packages & Orders
- [ ] Admin creates a new package for Baptism with 3 inclusions
- [ ] Parishioner selects Premium Package → OP generated with correct total
- [ ] OP PDF downloaded → border aligned, ₱ symbol correct, no whitespace gap
- [ ] Payment initiated → amount matches OP total (cannot be altered)
- [ ] Payment paid → OP status updates to "Paid"
- [ ] Order expires after 24 hours → status shows "Expired"
- [ ] Portal → My Orders → all past orders visible

### Phase 3 — CMS
- [ ] Create announcement with title ending in `\` → verify `\` stripped on save
- [ ] Upload thumbnail → redeploy or restart → image still shows (Supabase URL)
- [ ] New announcement with no image → no silent failure, saved without thumbnail
- [ ] Schedule an announcement for tomorrow → public site doesn't show it yet → change to Published → appears immediately
- [ ] TipTap bold/italic/list formatting saves and renders on public announcements page
- [ ] Pin an announcement → it appears first in the admin index

### Phase 4 — Analytics
- [ ] Visit `/admin/analytics` → all 7 charts render within 3 seconds
- [ ] Heatmap hover shows correct day + time + count tooltip
- [ ] Monthly chart shows both historical bars and 3-month forecast (green dashed)
- [ ] Export CSV → opens in Excel with correct columns and data
- [ ] Key Insights shows meaningful data (not "0 bookings" if data exists)
- [ ] Click Refresh → spinner appears → data reloads

---

*Generated: October 2026 | MCHP Capstone 2 Revision Defense*
