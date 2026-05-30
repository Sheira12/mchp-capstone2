<?php

use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\CertificateController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FamilyController;
use App\Http\Controllers\Admin\ParishionerController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\SacramentalRecordController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\Parishioner\BookingController as ParishionerBookingController;
use App\Http\Controllers\Parishioner\DashboardController as ParishionerDashboardController;
use App\Http\Controllers\Parishioner\PaymentController as ParishionerPaymentController;
use App\Http\Controllers\Parishioner\ProfileController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\VerificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/about', [PublicController::class, 'about'])->name('about');
Route::get('/services', [PublicController::class, 'services'])->name('services');
Route::get('/contact', [PublicController::class, 'contact'])->name('contact');
Route::post('/contact', [PublicController::class, 'submitInquiry'])->name('contact.submit');
Route::get('/announcements', [PublicController::class, 'announcements'])->name('announcements');
Route::get('/announcements/{announcement}', [PublicController::class, 'announcement'])->name('announcements.show');

// QR Verification (public)
Route::get('/verify/{token}', [VerificationController::class, 'verify'])->name('verify');
Route::get('/api/verify/{token}', [VerificationController::class, 'apiVerify'])->name('verify.api');

// Chatbot
Route::post('/chatbot', [ChatbotController::class, 'chat'])->name('chatbot.chat');
Route::post('/chatbot/escalate', [ChatbotController::class, 'escalate'])->name('chatbot.escalate');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

// 2FA routes — accessible when NOT fully authenticated (session has 2fa_user_id)
Route::get('/verify-otp', [AuthController::class, 'show2fa'])->name('2fa.show');
Route::post('/verify-otp', [AuthController::class, 'verify2fa'])->name('2fa.verify');
Route::post('/resend-otp', [AuthController::class, 'resend2fa'])->name('2fa.resend');
Route::post('/switch-channel', [AuthController::class, 'switchChannel'])->name('2fa.switch-channel');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Parishioner Self-Service Portal
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:parishioner'])->prefix('portal')->name('parishioner.')->group(function () {
    Route::get('/dashboard', [ParishionerDashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Bookings
    Route::get('/bookings', [ParishionerBookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/create', [ParishionerBookingController::class, 'create'])->name('bookings.create');
    Route::post('/bookings', [ParishionerBookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/{booking}', [ParishionerBookingController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{booking}/cancel', [ParishionerBookingController::class, 'cancel'])->name('bookings.cancel');

    // Payments
    Route::get('/payments', [ParishionerPaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/pay/{booking}', [ParishionerPaymentController::class, 'payBooking'])->name('payments.pay');
    Route::post('/payments/pay/{booking}/cash', [ParishionerPaymentController::class, 'payCash'])->name('payments.pay-cash');
    Route::post('/payments/pay/{booking}/proof', [ParishionerPaymentController::class, 'submitProof'])->name('payments.submit-proof');
    Route::get('/payments/pay/{booking}/demo/{method}', [ParishionerPaymentController::class, 'demoCheckout'])->name('payments.demo-checkout');
    Route::post('/payments/pay/{booking}/demo/{method}/complete', [ParishionerPaymentController::class, 'demoComplete'])->name('payments.demo-complete');
    Route::get('/payments/receipt/{payment}', [ParishionerPaymentController::class, 'receipt'])->name('payments.receipt');
    Route::get('/payments/receipt/{payment}/pdf', [ParishionerPaymentController::class, 'receiptPdf'])->name('payments.receipt-pdf');
    Route::post('/payments/initiate', [ParishionerPaymentController::class, 'initiate'])->name('payments.initiate');
    Route::get('/payments/success', [ParishionerPaymentController::class, 'success'])->name('payments.success');
    Route::get('/payments/failed', [ParishionerPaymentController::class, 'failed'])->name('payments.failed');

    // Certificates
    Route::get('/certificates', [\App\Http\Controllers\Parishioner\CertificateController::class, 'index'])->name('certificates.index');
    Route::get('/certificates/request', [\App\Http\Controllers\Parishioner\CertificateController::class, 'create'])->name('certificates.create');
    Route::post('/certificates/request', [\App\Http\Controllers\Parishioner\CertificateController::class, 'store'])->name('certificates.store');
    Route::get('/certificates/{certificate}/download', [\App\Http\Controllers\Parishioner\CertificateController::class, 'download'])->name('certificates.download');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:super_admin|parish_secretary|finance_officer'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');
    Route::post('/dashboard/export', [DashboardController::class, 'exportReport'])->name('dashboard.export');

    // Parishioners
    Route::get('/parishioners/search', [ParishionerController::class, 'search'])->name('parishioners.search');
    Route::resource('parishioners', ParishionerController::class);

    // Families
    Route::resource('families', FamilyController::class);

    // Sacramental Records
    Route::post('/sacramental-records/{sacramentalRecord}/verify', [SacramentalRecordController::class, 'verify'])
        ->name('sacramental-records.verify');
    Route::get('/sacramental-records/search', [SacramentalRecordController::class, 'search'])
        ->name('sacramental-records.search');
    Route::resource('sacramental-records', SacramentalRecordController::class);

    // Bookings
    Route::get('/bookings/calendar', [BookingController::class, 'calendar'])->name('bookings.calendar');
    Route::get('/bookings/qr-scanner', [BookingController::class, 'qrScanner'])->name('bookings.qr-scanner');
    Route::post('/bookings/qr-verify', [BookingController::class, 'qrVerify'])->name('bookings.qr-verify');
    Route::get('/bookings/{booking}/stub', [BookingController::class, 'printStub'])->name('bookings.stub');
    Route::post('/bookings/{booking}/confirm', [BookingController::class, 'confirm'])->name('bookings.confirm');
    Route::post('/bookings/{booking}/complete', [BookingController::class, 'complete'])->name('bookings.complete');
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
    Route::resource('bookings', BookingController::class);

    // Certificates
    Route::get('/certificates/{certificate}/download', [CertificateController::class, 'download'])->name('certificates.download');
    Route::post('/certificates/{certificate}/regenerate', [CertificateController::class, 'regenerate'])->name('certificates.regenerate');
    Route::post('/certificates/{certificate}/release', [CertificateController::class, 'release'])->name('certificates.release');
    Route::post('/certificates/batch-print', [CertificateController::class, 'batchPrint'])->name('certificates.batch-print');
    Route::resource('certificates', CertificateController::class);

    // Payments (Finance Officer + Admin)
    Route::middleware('role:super_admin|finance_officer')->group(function () {
        Route::post('/payments/record-cash', [PaymentController::class, 'recordCash'])->name('payments.record-cash');
        Route::post('/payments/{payment}/verify', [PaymentController::class, 'verify'])->name('payments.verify');
        Route::post('/payments/{payment}/reject', [PaymentController::class, 'reject'])->name('payments.reject');
        Route::post('/payments/{payment}/refund', [PaymentController::class, 'refund'])->name('payments.refund');
        Route::post('/payments/{payment}/void', [PaymentController::class, 'void'])->name('payments.void');
        Route::get('/payments/report', [PaymentController::class, 'report'])->name('payments.report');
        Route::resource('payments', PaymentController::class)->only(['index', 'show']);
    });

    // User Management (Super Admin only)
    Route::middleware('role:super_admin')->group(function () {
        Route::resource('users', UserController::class);
        Route::post('/users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
        Route::get('/audit-logs', [\App\Http\Controllers\Admin\AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings.index');
        Route::put('/settings', [\App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('settings.update');
        Route::post('/settings/clear-cache', [\App\Http\Controllers\Admin\SettingsController::class, 'clearCache'])->name('settings.clear-cache');
    });

    // Announcements
    Route::resource('announcements', \App\Http\Controllers\Admin\AnnouncementController::class);

    // Mass Schedules
    Route::resource('mass-schedules', \App\Http\Controllers\Admin\MassScheduleController::class);
});

/*
|--------------------------------------------------------------------------
| Payment Webhooks (no CSRF)
|--------------------------------------------------------------------------
*/
Route::post('/webhooks/paymongo', [PaymentWebhookController::class, 'paymongo'])
    ->name('webhooks.paymongo')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

/*
|--------------------------------------------------------------------------
| Payment Redirect Routes
|--------------------------------------------------------------------------
*/
Route::get('/payment/success', function () {
    return view('payment.success');
})->name('payment.success');

Route::get('/payment/failed', function () {
    return view('payment.failed');
})->name('payment.failed');
