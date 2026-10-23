# Mary Help of Christians Parish — Parish Management System
## User Manual

**Version:** 1.0 · **Prepared for:** Capstone 2 Project Documentation  
**System URL (Production):** https://mchp-capstone2.onrender.com  
**System URL (Local):** http://127.0.0.1:8000

---

## Table of Contents

1. [System Overview](#1-system-overview)
2. [User Roles](#2-user-roles)
3. [Public Website](#3-public-website)
4. [Getting Started — Registration and Login](#4-getting-started--registration-and-login)
5. [Parishioner Portal](#5-parishioner-portal)
   - 5.1 Dashboard
   - 5.2 My Profile
   - 5.3 Book a Service
   - 5.4 My Requirements & Eligibility
   - 5.5 Seminars
   - 5.6 Certificates
   - 5.7 Payments
   - 5.8 My Family
6. [Admin Panel — Parish Secretary](#6-admin-panel--parish-secretary)
   - 6.1 Dashboard
   - 6.2 Parishioners
   - 6.3 Bookings
   - 6.4 Requirements Queue
   - 6.5 Eligibility Verifications
   - 6.6 Certificates
   - 6.7 Sacramental Records
   - 6.8 Seminars
   - 6.9 Content Management
7. [Admin Panel — Finance Officer](#7-admin-panel--finance-officer)
   - 7.1 Payments
   - 7.2 Credit & Debit Ledger
   - 7.3 Reports
   - 7.4 Analytics
8. [Admin Panel — Super Admin](#8-admin-panel--super-admin)
   - 8.1 User Management
   - 8.2 System Settings
   - 8.3 Audit Logs
   - 8.4 Eligibility Waivers
9. [Walk-in Kiosk](#9-walk-in-kiosk)
10. [QR Code Verification](#10-qr-code-verification)
11. [Troubleshooting](#11-troubleshooting)

---

## 1. System Overview

The Parish Management System is a web-based platform that digitizes the operations of the Mary Help of Christians Parish in Southville 1, Niugan, Cabuyao, Laguna. It enables parishioners to book services, request certificates, pay fees, and track their requirements online — while giving parish staff the tools to manage all of these from a centralized admin panel.

**Key capabilities:**

- Online booking for sacraments, blessings, seminars, and Mass intentions
- Document requirement submission and approval workflow
- Digital certificate generation and download (Baptism, Confirmation, Marriage, and more)
- Online payments via GCash, Maya, credit/debit card, or cash
- Parish announcements, events, gallery, and livestream management
- Comprehensive financial reporting and ledger management
- QR code verification for certificates and booking stubs
- Walk-in booking kiosk for front-desk use without a parishioner account

---

## 2. User Roles

The system has four roles. Access is determined automatically based on your assigned role.

| Role | Who | What they can do |
|---|---|---|
| **Parishioner** | Registered community members | Book services, request certificates, manage payments, declare eligibility documents |
| **Parish Secretary** | Parish office staff | Manage bookings, approve requirement documents, process certificates, handle sacramental records |
| **Finance Officer** | Parish finance staff | Verify payments, manage ledger, generate financial reports |
| **Super Admin** | System administrator | Full access including user management, system settings, and audit logs |

---

## 3. Public Website

The public website is accessible to anyone without logging in at the homepage.

### Pages Available

**Home** — Parish introduction, quick-action buttons (Our Services, Walk-in Booking, My Portal), latest announcements, upcoming events, gallery preview.

**About** — Parish history, mission and vision, parish staff information, office hours and contact details.

**Services** — Complete listing of all bookable services grouped by category: Sacraments, Sacramentals, Seminars, Mass, Certificates.

**Mass Schedule** — Weekly and special Mass schedule.

**Announcements** — Parish bulletins, news, and updates. Click any card to read the full announcement.

**Events** — Upcoming and past parish events.

**Gallery** — Photo albums from Masses, sacraments, and community gatherings.

**Livestream** — Embedded YouTube livestream for online Mass participation.

**Contact** — Inquiry form with file attachment support. All submitted inquiries go directly to the parish office inbox.

### Chatbot Assistant

A chat widget appears at the bottom-right of every public page. Type your question in natural language. It can answer common questions about:
- Mass schedules
- Sacrament requirements
- Office hours
- How to book a service
- Certificate requests

If your question cannot be answered automatically, click **"Talk to Staff"** to escalate to a human. The parish office will receive an email with your message.

---

## 4. Getting Started — Registration and Login

### 4.1 Creating an Account

1. Click **Register** in the top navigation bar.
2. Fill in your full name, email address, and password.
3. Click **Register**.
4. You will be logged in immediately and redirected to the parishioner portal.
5. Complete your profile at **My Profile** to unlock full functionality.

### 4.2 Logging In

1. Click **Login** in the navigation bar.
2. Enter your email and password.
3. A **6-digit verification code** (OTP) will be sent to your email. Enter it on the next screen.
4. The code expires in 5 minutes. Click **Resend Code** if needed.
5. After successful verification you will be redirected to your portal dashboard.

### 4.3 Forgot Password

1. Click **Login → Forgot your password?**
2. Enter your registered email address and click **Send Reset Link**.
3. Open the email you receive and click the reset link (valid for 60 minutes).
4. Enter and confirm your new password.

### 4.4 Logging Out

Click the **Sign Out** button in the sidebar (parishioner portal) or click the logout icon next to your name in the admin panel.

---

## 5. Parishioner Portal

Access your portal by logging in and clicking **My Portal** on the public website. The portal address is `/portal/dashboard`.

### 5.1 Dashboard

The dashboard gives you a real-time summary of your activity:

- **Upcoming bookings** — confirmed bookings due soon
- **Recent bookings** — status overview (Pending / Confirmed / Completed / Cancelled)
- **Payments** — latest transactions and outstanding balances
- **Certificates** — recently requested or issued certificates
- **Notifications bell** — in-app alerts for booking confirmations, payment status, requirement approvals, and more

### 5.2 My Profile

Go to **My Profile** in the sidebar to manage your personal information.

**Fields you can update:**
- Full name, gender, date of birth
- Contact number
- Complete address (street, barangay, city, province, postal code)
- Profile photo (upload from your device)
- Emergency contact

Click **Save Changes** after editing. Some changes (e.g. civil status) are logged for auditing.

> **Important:** A complete profile is required before you can submit a booking or request a certificate. If your profile is incomplete, a warning will appear in the sidebar.

### 5.3 Book a Service

**Step 1 — Select a service**

1. Go to **Book a Service** in the sidebar.
2. Choose the service you need from the list. Services are grouped by category:

   | Category | Services |
   |---|---|
   | **Sacraments** | Baptism, Wedding, Funeral Mass |
   | **Sacramentals** | House Blessing, Car Blessing, Business Blessing, Sick Call / Anointing |
   | **Seminars** | Pre-Baptismal Seminar, Pre-Marriage (Pre-Cana), Confirmation Catechesis |
   | **Mass** | Mass Intention |

3. Services you are not yet eligible for will appear with an eligibility notice. Click **Check Eligibility** to see what you need to complete.

**Step 2 — Submit requirements (if required)**

Some services require document submissions before you can book a date. If the service shows "Requirements must be submitted first":

1. You will be taken to the **Requirements Submission** page.
2. For each listed requirement, click **Upload** or fill in the requested information.
3. Click **Submit** for each item.
4. The parish office (Parish Secretary) will review your submissions. You will receive an email and an in-app notification when each item is approved.
5. Once all required items are approved, you can proceed to pick a date.

> **Note:** If you previously submitted a document through **My Requirements** and it was already verified by the parish secretary, it will be automatically counted as approved — you do not need to re-upload it.

**Step 3 — Pick a date**

1. The calendar shows date availability:
   - **Green** — available, no bookings yet
   - **Yellow** — has some bookings, still available
   - **Red** — fully booked, choose another date
2. Click an available date to select it.
3. Optionally enter a preferred time and service location details.
4. Click **Submit Booking**.

**After submission:**

- Your booking status will be **Pending** until the parish secretary confirms it.
- You will receive an email notification when the booking is confirmed or cancelled.
- A unique booking reference number (e.g. `BK-6AD86DE4`) is assigned automatically.

**Cancelling a booking:**

You can cancel a **Pending** booking from **My Bookings → View → Cancel Booking**. Provide a reason for cancellation.

### 5.4 My Requirements & Eligibility

These two sections work together to track your eligibility for parish services.

**My Requirements**

Go to **My Requirements** to declare and submit documents that prove you have completed certain prerequisites (e.g., attended a seminar, received a sacrament, have a certificate on file).

1. Click **Declare** next to a requirement.
2. Fill in the date completed, venue, and upload a proof document (photo or scan).
3. Click **Save Declaration**.
4. Status will show **Pending Verification** until the parish secretary approves it.
5. Once verified, this automatically satisfies the corresponding requirement on any of your pending booking stubs.

**My Eligibility**

Go to **My Eligibility** for a checklist view across all services. This shows:
- Which services you are fully eligible for (green checkmark)
- Which services require additional steps (amber)
- Specific missing requirements with links to declare them

### 5.5 Seminars

1. Go to **Seminars** to see upcoming required formation programs.
2. Click **Register** to enroll in a seminar.
3. You will receive a confirmation and a reminder closer to the date.
4. Your attendance history is visible under **Seminars → My History**.

> Completing a required seminar automatically updates your eligibility for the associated service.

### 5.6 Certificates

**Requesting a certificate:**

1. Go to **My Certificates → Request a Certificate**.
2. Select the certificate type:
   - Certificate of Baptism
   - Certificate of Confirmation
   - Certificate of Marriage
   - Certificate of First Communion
   - Certificate of Death/Burial
   - Certificate of No Impediment
   - Certificate of Parish Membership
3. Select the relevant sacramental record (for sacrament-type certificates) or fill in the required details.
4. Specify the purpose (e.g., "For civil registration", "For school enrollment").
5. Click **Submit Request**.

**Certificate statuses:**

| Status | Meaning |
|---|---|
| **Draft** | Submitted, awaiting admin processing |
| **Issued** | Certificate generated, pending release |
| **Released** | Ready to download |

**Downloading your certificate:**

Once the status shows **Released**, click **Download PDF**. The PDF includes a QR code that can be scanned to verify its authenticity.

> Sacrament-type certificates (Baptism, Confirmation, Marriage, First Communion, Death/Burial) require the parish office to verify the matching sacramental record before they can be released.

**Requesting a correction:**

If your released certificate has an error, click **Request Correction** and describe what needs to be changed. The parish secretary will review and reissue.

### 5.7 Payments

**Paying for a booking:**

1. Go to **My Bookings** and select the booking.
2. Click **Pay Now** or navigate to **Payments → Pay**.
3. Choose your payment method:

   | Method | How it works |
   |---|---|
   | **GCash** | Redirected to PayMongo/GCash authentication |
   | **Maya** | Redirected to PayMongo/Maya authentication |
   | **Credit/Debit Card** | Secure 3D card form; may require OTP from your bank |
   | **Cash** | Declare intent to pay cash; bring the amount to the parish office |
   | **Upload Proof** | Submit a screenshot of your bank transfer or e-wallet payment |

4. After GCash/Maya payment, you will be redirected back to the success page automatically.
5. For cash and proof-of-payment submissions, status will show **Pending** until the finance officer verifies.

**Viewing receipts:**

Paid transactions show a **View Receipt** link. You can also download a PDF receipt from **Payments → Receipt → Download PDF**.

**Orders of Payment:**

For bookings that require a formal billing document, go to **My Orders** or **Booking → Generate Order of Payment**. The PDF can be brought to the parish office.

### 5.8 My Family

Track your family unit within the parish.

1. Go to **My Family**.
2. Click **Create Family Record** and enter the family name.
3. Click **Add Family Member** to link other registered parishioners to your family.
4. Members can be removed at any time.

---

## 6. Admin Panel — Parish Secretary

Access the admin panel by logging in with a Parish Secretary account. The URL is `/admin/dashboard`.

### 6.1 Dashboard

The dashboard shows at-a-glance statistics:
- Total and pending bookings
- Recent booking activity
- Pending requirement reviews (Requirements Queue badge)
- Pending eligibility verifications
- Upcoming scheduled bookings
- Quick export options

### 6.2 Parishioners

**Searching and filtering:**
- Use the search bar at the top to find by name, email, or contact number.
- Filter by status (active/inactive), gender, or barangay.

**Viewing a parishioner record:**
- Click any row to open the full profile.
- Profile includes personal info, bookings history, certificates, payments, family, and eligibility status.

**Creating a parishioner manually:**
1. Click **Add Parishioner**.
2. Fill in all required fields.
3. Click **Save**.

**Statement of Account:**
- Open a parishioner's profile and click **Statement of Account** to view all their financial transactions.
- Click **Download PDF** to export the SOA.

**Families:**
Navigate to **Families** in the sidebar to view and manage family records. You can create, edit, and delete family groups and manage member links.

### 6.3 Bookings

**Booking list:**
- Filter by status, service type, date range, or search by reference number / parishioner name.
- Colored status badges: Pending (yellow), Confirmed (green), Completed (blue), Cancelled (red).

**Confirming a booking:**
1. Click the booking to open its detail page.
2. Review the booking information and any attached requirements.
3. Click **Confirm Booking** and optionally add admin notes.
4. The parishioner receives an email and in-app notification.

**Completing a booking:**
After the service is performed, open the booking and click **Mark as Completed**.

**Cancelling a booking:**
Click **Cancel Booking**, provide a reason, and confirm. The parishioner is notified.

**Booking Calendar:**
Click **Calendar** in the Services section of the sidebar to see a monthly calendar view of all pending and confirmed bookings. Navigate between months using the arrows.

**QR Scanner:**
Click **Walk-in Kiosk** (gold button) to open the kiosk page, or navigate to **Bookings → QR Scanner** to scan a parishioner's booking QR code and instantly verify their booking details.

**Printing a booking stub:**
From any booking's detail page, click **Print Stub** to generate a printable QR slip to give to the parishioner.

### 6.4 Requirements Queue

The Requirements Queue shows all parishioner-submitted documents for bookings that require pre-approval.

**Understanding the queue:**

- **Pending** — recently submitted, awaiting your review
- **Needs Revision** — you requested corrections; awaiting parishioner resubmission
- **Approved** — document accepted

**Filtering:**
Filter by status, service type, or submission date range.

**Reviewing a submission:**
1. Click the **Review** link for any row.
2. The detail page shows the parishioner's uploaded file or text submission.
3. Use the preview to verify the document.
4. Click **Approve** to accept the submission.
5. If the document is incorrect or incomplete, click **Request Revision**, enter a remark explaining what is needed, and click **Send Revision Request**. The parishioner receives a notification.

**Approve All:**
If all items for a booking are in order, click **Approve All Requirements** on the booking's requirements detail page to approve every item at once. This automatically sets the booking's requirements gate, allowing the parishioner to proceed to pick a date.

> **Note:** Only the Parish Secretary can approve or reject requirements. The Super Admin sees this queue in read-only mode. The Finance Officer cannot access this section.

### 6.5 Eligibility Verifications

This section handles self-declared eligibility documents submitted through the parishioner's **My Requirements** flow.

**Reviewing a declaration:**
1. Navigate to **Eligibility Verifications** in the sidebar.
2. Pending submissions show an orange badge count.
3. Click **Review** on any submission.
4. The detail page shows the declared requirement, date completed, venue, and uploaded proof.
5. Click **Verify** to approve or **Reject** with a reason.

> When you verify a declaration, any matching pending booking requirement for the same parishioner is automatically approved too — the parishioner does not need to re-submit the same document.

**Staff Recording:**
Navigate to a specific parishioner's profile and click **Record Requirement** to manually record a requirement as completed on their behalf (e.g., when a parishioner completes a seminar in person and you need to update the system).

### 6.6 Certificates

**Certificate list:**
- Filter by type, status, or date range.
- Status flow: Draft → Issued → Released

**Processing a certificate request:**
1. Open a certificate request from the list.
2. Review the requested details.
3. For sacrament-type certificates, click **Verify Record** to confirm the matching sacramental record exists. The system will search automatically.
4. Once verified, click **Download** or **Regenerate** to produce the PDF.
5. When ready to hand over, click **Release** to change the status to Released and notify the parishioner.

**Edit requests:**
Navigate to **Certificate Edit Requests** in the sidebar to review correction requests from parishioners. Click **Approve** to accept a correction (the certificate is regenerated) or **Reject** with a reason.

**Batch Print:**
Select multiple certificates using the checkboxes and click **Batch Print** to download them all as a combined PDF.

### 6.7 Sacramental Records

Sacramental Records are the permanent register of sacraments received by parishioners.

**Adding a record:**
1. Go to **Sacramental Records → Add Record**.
2. Select the record type: Baptism, Confirmation, Marriage, First Communion, Death/Burial.
3. Fill in all required details (names, dates, minister, witnesses).
4. Click **Save**.

**Verifying a record:**
After adding a record, click **Verify** to mark it as confirmed in the parish registry. Verified records can be linked to certificate requests.

**Searching:**
Use the search bar to find records by parishioner name, record number, or date range.

### 6.8 Seminars

**Managing seminars:**
1. Go to **Seminars → Add Seminar** to create a new seminar.
2. Fill in the title, description, schedule, capacity, and location.
3. Registered parishioners appear in the attendance list.

**Marking attendance:**
For each registered parishioner, click the **Present** button (or scan their QR code via **QR Check-In**) to record attendance.

**Completing a seminar:**
Click **Complete All** to mark all registered parishioners as having attended. This updates their eligibility records automatically.

### 6.9 Content Management

All CMS sections work similarly: a list view → create/edit form → save.

**Announcements:**
Create, edit, publish, or unpublish announcements. Toggle **Publish Immediately** when creating to make it visible at once. Use **Bulk Actions** to publish or unpublish multiple announcements.

**Events:**
Add event listings with a title, description, date/time, location, category, and cover image. Events appear on the public Events page.

**Mass Schedules:**
Define recurring Mass schedules (day, time, type, priest) that appear on the public Mass Schedule page.

**Gallery:**
Create albums and upload photos. Set a cover photo for each album. You can bulk-delete or bulk-update photos within an album.

**Livestream:**
Add YouTube video IDs or livestream links that appear on the public Livestream page. Set the active/inactive status to control what is shown.

**Inquiries:**
All contact form submissions appear here. Click any inquiry to read the full message and attachment. Click **Reply** to send an email response directly from the system. Update the status (New → In Progress → Resolved) to track follow-up.

---

## 7. Admin Panel — Finance Officer

The Finance Officer has exclusive access to the payment verification workflow, ledger, reports, and analytics.

### 7.1 Payments

**Payment list:**
- Filter by status (pending, paid, failed, refunded), payment method, or date.
- Debit payments (parishioner pays the parish) are shown in red amounts. Credit payments (parish issues refunds) are shown in green.

**Verifying a payment:**
1. Click a pending payment to open its detail page.
2. Review the submitted proof of payment (GCash/Maya screenshot, bank receipt, etc.).
3. Click **Verify Payment** to mark it as paid. The parishioner receives a receipt notification.

**Rejecting a payment:**
If the proof is invalid or the amount is wrong, click **Reject** and provide a reason. The parishioner will be notified to resubmit.

**Recording a cash payment:**
1. Click **Record Cash Payment** on the payments list.
2. Select the parishioner and booking.
3. Enter the amount received and date.
4. Click **Record**. This creates a payment record marked as paid.

**Refunding a payment:**
Open a paid payment and click **Refund**. Enter the refund amount and reason. This creates a credit entry in the ledger.

**Voiding a payment:**
Click **Void** to cancel a payment entry that was recorded in error.

**Financial Report (quick view):**
Click **Financial Reports** in the sidebar for a quick report showing total collections for the current month by payment method.

### 7.2 Credit & Debit Ledger

The ledger is a full double-entry record of all financial transactions.

**Adding a ledger entry:**
1. Go to **Credit & Debit Ledger → Add Entry**.
2. Select the transaction type (income/expense), category, amount, date, and description.
3. Click **Save**.

**Ledger Report:**
Click **Ledger Report** for a summary of all ledger entries in a date range, grouped by category.

**Categories:**
Manage ledger categories at **Ledger → Categories** to organize entries (e.g., Service Fees, Donations, Utilities).

### 7.3 Reports

The Reports Module provides three detailed reports, each exportable as PDF or Excel.

**Parishioner Report** (`/admin/reports/parishioners`)
- Total, active, and inactive parishioner counts
- Gender breakdown (Male / Female / Other)
- Total family records
- New registrations in the selected date range
- Top 10 barangays by parishioner count
- Monthly registration trend (last 12 months)

**Payment Report** (`/admin/reports/payments`)
- Total collections in date range or by quarter (Q1–Q4 presets)
- Debit vs. credit breakdown
- Breakdown by payment method (GCash, Maya, card, cash)
- Breakdown by transaction type
- Daily revenue table
- Monthly revenue trend (last 12 months)
- Outstanding bookings count and total unpaid fees

**Booking Report** (`/admin/reports/bookings`)
- Total bookings by status (Pending, Confirmed, Completed, Cancelled)
- Bookings by service type
- Monthly booking trend (last 12 months)
- Total revenue from confirmed/completed bookings in date range

**Exporting:**
On any report page, click **Export PDF** or **Export Excel** to download the report. PDFs include the parish logo, name, and generation timestamp.

### 7.4 Analytics

The Analytics page (`/admin/analytics`) provides visual charts and data analysis:
- Booking trends over time
- Revenue trends
- Service popularity breakdown
- Parishioner growth trend

Click **Export Analytics** to download the analytics data.

---

## 8. Admin Panel — Super Admin

The Super Admin has access to everything the Parish Secretary and Finance Officer can do, plus exclusive system management features.

### 8.1 User Management

Navigate to **System → Users**.

**Creating a new admin user:**
1. Click **Add User**.
2. Enter the name, email, password, and assign a role (parish_secretary, finance_officer, or super_admin).
3. Click **Save**. The new user can immediately log in with the credentials you set.

**Editing a user:**
Click the edit icon on any user to change their name, email, password, or role.

**Activating / Deactivating:**
Toggle the active status switch to enable or disable a user's access without deleting their account.

**Deleting a user:**
Click the delete icon. This is permanent.

### 8.2 System Settings

Navigate to **System → Settings**.

**Parish Information:**

| Field | Description |
|---|---|
| Parish Name | Appears on the public website, certificates, and admin panel header |
| Address | Shown on the public website and printed documents |
| Phone | Shown on contact page and certificates |
| Email | Shown on contact page |
| Parish Priest | Shown on the About page and certificates |
| Parish Secretary | Display name (does not affect login credentials) |
| Finance Officer | Display name (does not affect login credentials) |
| Office Hours | Shown on the About and Contact pages |

Click **Save Parish Information** to apply. Changes take effect immediately across the entire website.

**Social Media Links:**
Enter the full URL for Facebook, Messenger, Instagram, YouTube, and TikTok pages. Leave blank to hide a social link from the footer.

**Media Uploads:**
Upload a new parish logo (max 3MB) or church banner (max 5MB). These are stored on Supabase cloud storage and replace the current images across the entire website.

**Eligibility Settings:**
Toggle **Allow booking with pending requirements** to control whether parishioners can book a date even if their submitted documents have not yet been reviewed. When off, parishioners must wait for full approval before they can select a date.

**Cache Management:**
If changes to settings, routes, or views are not appearing immediately, use the **Clear Cache** buttons to flush the application cache.

### 8.3 Audit Logs

Navigate to **System → Audit Logs**.

Every significant action in the system is logged here: who did what, when, and on which record.

- Filter by action type, user, model type, or date range.
- Each log entry shows the old values vs. the new values for data changes.

Audit logs cannot be modified or deleted — they are a read-only permanent record.

### 8.4 Eligibility Waivers

Navigate to **Eligibility Waivers** in the Services section of the sidebar.

An eligibility waiver allows a specific parishioner to bypass a specific eligibility rule. This is for cases where the standard requirement is not applicable (e.g., a parishioner from another parish who has completed the prerequisite but cannot present a local record).

**Creating a waiver:**
1. Click **Add Waiver**.
2. Select the parishioner and the specific eligibility rule to waive.
3. Enter a reason for the waiver.
4. Click **Save**.

The waiver takes effect immediately — the parishioner will show as eligible for the related service.

---

## 9. Walk-in Kiosk

The Walk-in Kiosk is designed for use at the parish office front desk. It does not require a parishioner login — it is a standalone public form.

**Access:** Click the gold **Walk-in Kiosk** button in the admin sidebar, or go to `/walk-in` on any device.

**How to use (staff-assisted):**

1. Open the kiosk on a tablet or computer at the front desk.
2. The parishioner fills in their name, contact number, and service type.
3. Select a preferred date.
4. Fill in any additional notes.
5. Click **Submit Walk-in Booking**.
6. A confirmation page appears with a booking reference number and QR code.
7. Click **Print Stub** to print a physical QR slip for the parishioner to keep.

Walk-in bookings appear in the admin **Bookings** list alongside online bookings. The staff can then confirm, complete, or cancel them like any other booking.

---

## 10. QR Code Verification

Every issued certificate and confirmed booking includes a QR code.

**Verifying a certificate or booking document:**

1. Open a QR code scanner app on any smartphone, or use the admin panel's built-in scanner.
2. Scan the QR code on the printed document.
3. You will be taken to the public verification page at `/verify/{token}`.
4. The page displays:
   - Document type and certificate number
   - Parishioner name
   - Date issued / booking date
   - Verification status (Valid / Invalid)

**Admin QR Scanner:**

Go to **Bookings → QR Scanner** in the admin panel to use the camera-based scanner directly in the browser. Scan a booking stub to instantly see the booking details, parishioner information, and payment status without searching manually.

---

## 11. Troubleshooting

**I cannot book a service — it says I am not eligible.**
Go to **My Eligibility** to see which requirements are missing. Complete the required seminars or submit the required documents through **My Requirements**. Once the parish secretary approves your submissions, the eligibility gate opens automatically.

**My booking shows "Requirements must be approved" even though I already submitted documents.**
The parish secretary needs to review and approve your document submissions. Check **My Requirements** for the current status. If you already submitted documents through the general requirements flow and they were verified, contact the parish office — staff can manually approve the corresponding booking requirement.

**I uploaded payment proof but the status is still Pending.**
The finance officer needs to verify your payment. This typically happens within the next office working day (Tuesday–Sunday). You will receive an in-app and email notification once it is verified.

**My certificate is Issued but I cannot download it.**
Certificates must be in **Released** status before download is available. An "Issued" certificate means it has been generated but not yet released by the admin. Contact the parish office if it has been more than 3 working days.

**I am not receiving email notifications.**
Check your Spam or Promotions folder. If the problem persists, contact the parish office to verify your registered email address is correct.

**The page shows an error after I submitted a form.**
Do not submit the form again. Take note of the error message and contact the parish office or system administrator.

**I forgot my password.**
Go to the Login page and click **Forgot your password?** to receive a reset link by email.

---

*For technical support, contact the parish office at the phone number or email listed on the Contact page of the parish website.*

*Last updated: October 2026*
