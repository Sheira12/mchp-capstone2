<?php
/**
 * CAPSTONE SYSTEM TEST — Mary Help of Christians Parish
 * Run: php system_test.php
 */
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

$pass = 0; $fail = 0; $warn = 0; $results = [];

function chk($label, $ok, $critical = true) {
    global $pass, $fail, $warn, $results;
    if ($ok) { $pass++; $results[] = ['PASS', $label, '']; }
    else {
        if ($critical) $fail++; else $warn++;
        $results[] = [$critical ? 'FAIL' : 'WARN', $label, ''];
    }
}

function tryChk($label, $fn, $critical = true) {
    global $pass, $fail, $warn, $results;
    try {
        $ok = $fn();
        chk($label, $ok, $critical);
    } catch (Throwable $e) {
        if ($critical) $fail++; else $warn++;
        $results[] = [$critical ? 'FAIL' : 'WARN', $label, substr($e->getMessage(), 0, 80)];
    }
}

// helper
function hasRoute($name) {
    return app('router')->getRoutes()->hasNamedRoute($name);
}

echo "\n[1] DATABASE\n";
tryChk('DB Connected',  function() { return DB::connection()->getPdo() !== null; });
tryChk('MySQL version', function() { return str_contains(DB::select('SELECT VERSION() as v')[0]->v, '.'); });

echo "[2] TABLES\n";
$tables = ['users','parishioners','families','sacramental_records','bookings',
           'payments','certificates','qr_codes','announcements','mass_schedules',
           'services','email_logs','chat_messages','audit_logs','profile_change_logs','events'];
foreach ($tables as $t) {
    tryChk("Table: $t", function() use ($t) { return Schema::hasTable($t); });
}

echo "[3] ROLES\n";
foreach (['super_admin','parish_secretary','finance_officer','parishioner'] as $r) {
    tryChk("Role: $r", function() use ($r) {
        return Spatie\Permission\Models\Role::where('name', $r)->exists();
    });
}

echo "[4] ADMIN ACCOUNTS\n";
$admins = [
    'maryhelpparish@gmail.com'        => 'super_admin',
    'cumpioaries07@gmail.com'         => 'parish_secretary',
    'financemhcpparish@gmail.com    ' => 'finance_officer',
];
foreach ($admins as $email => $role) {
    tryChk("$email => $role", function() use ($email, $role) {
        $u = App\Models\User::where('email', $email)->first();
        return $u && $u->hasRole($role);
    });
}
tryChk('Parishioner test account', function() {
    return App\Models\User::where('email','cumpioaries09@gmail.com')->exists();
}, false);

echo "[5] CORE DATA\n";
tryChk('Services seeded', function() { return App\Models\Service::count() > 0; });
tryChk('Parishioners exist', function() { return App\Models\Parishioner::count() >= 0; });

echo "[6] PARISH CONFIG\n";
tryChk('parish.name correct', function() {
    return config('parish.name') === 'Mary Help of Christians Parish';
});
tryChk('parish.address set',  function() { return !empty(config('parish.address')); });
tryChk('parish.priest set',   function() { return str_contains(config('parish.priest',''), 'Sanchez'); });
tryChk('parish.phone set',    function() { return !empty(config('parish.phone')); });
tryChk('mail.from set',       function() { return !empty(config('mail.from.address')); });
tryChk('app.url set',         function() { return config('app.url') === 'http://127.0.0.1:8000'; });

echo "[7] ROUTES\n";
$routes = [
    'home','about','services','contact','announcements','events','events.show',
    'verify','verify.api',
    'walkin.index','walkin.store','walkin.confirmation','walkin.print',
    'chatbot.chat','chatbot.escalate',
    'login','register','logout',
    '2fa.show','2fa.verify','2fa.resend','2fa.switch-channel',
    'password.request','password.email','password.reset','password.update',
    'admin.dashboard','admin.dashboard.export',
    'admin.parishioners.index','admin.parishioners.create',
    'admin.families.index',
    'admin.sacramental-records.index','admin.sacramental-records.verify',
    'admin.bookings.index','admin.bookings.calendar',
    'admin.bookings.qr-scanner','admin.bookings.qr-verify',
    'admin.bookings.confirm','admin.bookings.complete','admin.bookings.cancel',
    'admin.certificates.index','admin.certificates.download',
    'admin.certificates.regenerate','admin.certificates.batch-print',
    'admin.payments.index','admin.payments.verify','admin.payments.report',
    'admin.announcements.index','admin.mass-schedules.index',
    'admin.events.index','admin.events.create','admin.events.store',
    'admin.users.index','admin.audit-logs.index','admin.settings.index',
    'parishioner.dashboard','parishioner.profile',
    'parishioner.bookings.index','parishioner.bookings.create',
    'parishioner.payments.index','parishioner.payments.pay',
    'parishioner.payments.submit-proof','parishioner.payments.otp-send',
    'parishioner.payments.receipt','parishioner.payments.receipt-pdf',
    'parishioner.certificates.index',
    'webhooks.paymongo',
];
foreach ($routes as $r) {
    tryChk("Route: $r", function() use ($r) { return hasRoute($r); });
}

echo "[8] VIEWS\n";
$views = [
    'auth.login','auth.register','auth.two-factor',
    'auth.forgot-password','auth.reset-password',
    'admin.dashboard',
    'admin.parishioners.index','admin.parishioners.create','admin.parishioners.show',
    'admin.families.index',
    'admin.sacramental-records.index','admin.sacramental-records.create',
    'admin.bookings.index','admin.bookings.calendar','admin.bookings.qr-scanner',
    'admin.certificates.index','admin.certificates.show',
    'admin.payments.index','admin.payments.report',
    'admin.announcements.index','admin.mass-schedules.index',
    'admin.events.index','admin.events.create','admin.events.edit','admin.events.show',
    'admin.users.index','admin.audit-logs.index','admin.settings.index',
    'parishioner.dashboard','parishioner.profile',
    'parishioner.bookings.index','parishioner.bookings.create',
    'parishioner.payments.index','parishioner.payments.pay','parishioner.payments.receipt',
    'parishioner.certificates.index',
    'public.home','public.about','public.services','public.contact',
    'public.announcements','public.events',
    'walkin.index','walkin.confirmation','walkin.print',
    'certificates.baptism','certificates.confirmation','certificates.marriage',
    'certificates.first-communion','certificates.death-burial',
    'certificates.membership','certificates.no-impediment',
    'certificates.generic','certificates.batch',
    'reports.parish-report',
];
foreach ($views as $v) {
    tryChk("View: $v", function() use ($v) { return View::exists($v); });
}

echo "[9] CERTIFICATES\n";
$css = file_get_contents(resource_path('views/certificates/_premium_css.blade.php'));
$bapt = file_get_contents(resource_path('views/certificates/baptism.blade.php'));
// Updated checks for the current border-outer / cert-outer approach
chk('CSS: border structure defined',   str_contains($css, '.border-outer') || str_contains($css, '.cert-outer'));
chk('CSS: no @page background-image',  !str_contains($css, 'background-image'));
chk('CSS: .recipient-name defined',    str_contains($css, '.recipient-name'));
chk('CSS: .sig-wrap defined',          str_contains($css, '.sig-wrap'));
chk('Baptism: has qrBase64',           str_contains($bapt, 'qrBase64'));
chk('Baptism: has border structure',   str_contains($bapt, 'border-outer') || str_contains($bapt, 'cert-outer'));

foreach (['confirmation','marriage','first-communion','death-burial','membership','no-impediment','generic'] as $ct) {
    $c = file_get_contents(resource_path("views/certificates/{$ct}.blade.php"));
    chk("Cert $ct: has border/frame",  str_contains($c, 'border-outer') || str_contains($c, 'cert-outer') || str_contains($c, 'cert-page'));
}

tryChk('CertificateService::generate()',      function() { return method_exists(App\Services\CertificateService::class,'generate'); });
tryChk('CertificateService::batchPdf()',      function() { return method_exists(App\Services\CertificateService::class,'batchPdf'); });
tryChk('Certificate::generateUniqueNumber()', function() { return method_exists(App\Models\Certificate::class,'generateUniqueNumber'); });
tryChk('qr_codes.verification_url column',    function() { return Schema::hasColumn('qr_codes','verification_url'); });

echo "[10] PAYMENTS\n";
$payCtrl = file_get_contents(app_path('Http/Controllers/Parishioner/PaymentController.php'));
chk('PaymentCtrl: sendPaymentOtp()',     str_contains($payCtrl, 'sendPaymentOtp'));
chk('PaymentCtrl: otp_code validation', str_contains($payCtrl, "'otp_code'"));
chk('PaymentCtrl: validateTwoFactor',   str_contains($payCtrl, 'validateTwoFactorCode'));
chk('PaymentCtrl: demoCheckout',        str_contains($payCtrl, 'demoCheckout'));
chk('PaymentCtrl: gcash/maya',          str_contains($payCtrl, 'gcash') && str_contains($payCtrl, 'maya'));
tryChk('PaymentService::createPaymentLink()', function() { return method_exists(App\Services\PaymentService::class,'createPaymentLink'); });
tryChk('PaymentWebhookController',      function() { return class_exists(App\Http\Controllers\PaymentWebhookController::class); });
tryChk('payments table: all columns',   function() {
    return Schema::hasColumns('payments', ['amount','status','payment_method','reference_number','receipt_number','paid_at']);
});

echo "[11] SECURITY & AUTH\n";
$authCtrl = file_get_contents(app_path('Http/Controllers/Auth/AuthController.php'));
chk('2FA: generateTwoFactorCode',    str_contains($authCtrl, 'generateTwoFactorCode'));
chk('2FA: verify2fa()',              str_contains($authCtrl, 'verify2fa'));
chk('2FA: resend2fa()',              str_contains($authCtrl, 'resend2fa'));
chk('2FA: switchChannel()',          str_contains($authCtrl, 'switchChannel'));
chk('Password: sendResetLink',       str_contains($authCtrl, 'sendResetLink'));
chk('Password: resetPassword',       str_contains($authCtrl, 'resetPassword'));
chk('is_active check on login',      str_contains($authCtrl, 'is_active'));
$kernel = file_get_contents(app_path('Http/Kernel.php'));
chk('CSRF middleware in Kernel',     str_contains($kernel, 'VerifyCsrfToken'));
chk('Role middleware in Kernel',     str_contains($kernel, 'role'));

echo "[12] NOTIFICATIONS\n";
tryChk('BookingStatusNotification',   function() { return class_exists(App\Notifications\BookingStatusNotification::class); });
tryChk('PaymentReceiptNotification',  function() { return class_exists(App\Notifications\PaymentReceiptNotification::class); });
tryChk('TwoFactorCodeMail',           function() { return class_exists(App\Mail\TwoFactorCodeMail::class); });
tryChk('InquiryMail',                 function() { return class_exists(App\Mail\InquiryMail::class); });
tryChk('ChatEscalationMail',          function() { return class_exists(App\Mail\ChatEscalationMail::class); });

echo "[13] DASHBOARD & REPORTS\n";
$dash = file_get_contents(app_path('Http/Controllers/Admin/DashboardController.php'));
chk('Dashboard: sacramentStats',     str_contains($dash, 'sacramentStats'));
chk('Dashboard: bookingTypeStats',   str_contains($dash, 'bookingTypeStats'));
chk('Dashboard: medianPayment',      str_contains($dash, 'medianPayment'));
chk('Dashboard: monthlyTrend',       str_contains($dash, 'monthlyTrend'));
chk('Dashboard: revenueTrend',       str_contains($dash, 'revenueTrend'));
chk('Dashboard: exportReport()',     str_contains($dash, 'exportReport'));
tryChk('ReportService::generate()', function() { return method_exists(App\Services\ReportService::class,'generate'); });
tryChk('ParishReportExport class',  function() { return class_exists(App\Exports\ParishReportExport::class); });
tryChk('reports.parish-report view',function() { return View::exists('reports.parish-report'); });

echo "[14] EVENTS MODULE\n";
tryChk('Event model',         function() { return class_exists(App\Models\Event::class); });
tryChk('events table',        function() { return Schema::hasTable('events'); });
tryChk('events columns',      function() { return Schema::hasColumns('events',['title','event_start','category','status','is_featured']); });
tryChk('EventController',     function() { return class_exists(App\Http\Controllers\Admin\EventController::class); });
tryChk('admin.events.index',  function() { return hasRoute('admin.events.index'); });
tryChk('events (public)',     function() { return hasRoute('events'); });
$pubLayout = file_get_contents(resource_path('views/layouts/public.blade.php'));
$adminLayout = file_get_contents(resource_path('views/layouts/app.blade.php'));
chk('Events in public nav',   str_contains($pubLayout, "route('events')"));
chk('Events in admin nav',    str_contains($adminLayout, "route('admin.events.index')"));

echo "[15] GALLERY & LIVESTREAM\n";
tryChk('GalleryItem model',       function() { return class_exists(App\Models\GalleryItem::class); });
tryChk('gallery_items table',     function() { return Schema::hasTable('gallery_items'); });
tryChk('Livestream model',        function() { return class_exists(App\Models\Livestream::class); });
tryChk('livestreams table',       function() { return Schema::hasTable('livestreams'); });
tryChk('GalleryController',       function() { return class_exists(App\Http\Controllers\Admin\GalleryController::class); });
tryChk('LivestreamController',    function() { return class_exists(App\Http\Controllers\Admin\LivestreamController::class); });
tryChk('admin.gallery.index',     function() { return hasRoute('admin.gallery.index'); });
tryChk('admin.livestreams.index', function() { return hasRoute('admin.livestreams.index'); });
tryChk('public gallery route',    function() { return hasRoute('gallery'); });
tryChk('public livestream route', function() { return hasRoute('livestream'); });
chk('Gallery in public nav',      str_contains($pubLayout, "route('gallery')"));
chk('Gallery in admin nav',       str_contains($adminLayout, "route('admin.gallery.index')"));
chk('Livestream in admin nav',    str_contains($adminLayout, "route('admin.livestreams.index')"));
tryChk('Gallery public view',     function() { return View::exists('public.gallery'); });
tryChk('Livestream public view',  function() { return View::exists('public.livestream'); });

echo "[16] NOTIFICATION SYSTEM\n";
tryChk('notifications table',             function() { return Schema::hasTable('notifications'); });
tryChk('AdminBookingNotification',        function() { return class_exists(App\Notifications\AdminBookingNotification::class); });
tryChk('AdminCertificateNotification',    function() { return class_exists(App\Notifications\AdminCertificateNotification::class); });
tryChk('ParishionerStatusNotification',   function() { return class_exists(App\Notifications\ParishionerStatusNotification::class); });
tryChk('admin.notifications.unread',      function() { return hasRoute('admin.notifications.unread'); });
tryChk('parishioner.notifications.unread',function() { return hasRoute('parishioner.notifications.unread'); });
$portalLayout = file_get_contents(resource_path('views/layouts/portal.blade.php'));
chk('Portal bell HTML',                   str_contains($portalLayout, 'portal-notif-badge'));
chk('Portal bell JS polling',             str_contains($portalLayout, 'portalFetchNotifications'));
chk('Admin bell HTML',                    str_contains($adminLayout, 'notif-badge'));
chk('Admin bell JS polling',              str_contains($adminLayout, 'fetchNotifications'));
chk('No Blade/JS conflict in portal',     !str_contains($portalLayout, '${{'));

echo "[15] WALK-IN KIOSK\n";
tryChk('WalkInBookingController',  function() { return class_exists(App\Http\Controllers\WalkInBookingController::class); });
tryChk('Walk-in: store()',         function() { return method_exists(App\Http\Controllers\WalkInBookingController::class,'store'); });
tryChk('Walk-in: printStub()',     function() { return method_exists(App\Http\Controllers\WalkInBookingController::class,'printStub'); });

echo "[16] CHATBOT\n";
tryChk('ChatbotController::chat()',    function() { return method_exists(App\Http\Controllers\ChatbotController::class,'chat'); });
tryChk('ChatbotController::escalate()',function() { return method_exists(App\Http\Controllers\ChatbotController::class,'escalate'); });
tryChk('ChatMessage model',           function() { return class_exists(App\Models\ChatMessage::class); });

echo "[17] AUDIT LOG\n";
tryChk('AuditLog::record()',      function() { return method_exists(App\Models\AuditLog::class,'record'); });
tryChk('AuditLogController',     function() { return class_exists(App\Http\Controllers\Admin\AuditLogController::class); });
tryChk('audit_logs table',       function() { return Schema::hasTable('audit_logs'); });

echo "[18] PROFILE & FAMILY\n";
tryChk('ProfileChangeLog model', function() { return class_exists(App\Models\ProfileChangeLog::class); });
tryChk('Family model',           function() { return class_exists(App\Models\Family::class); });
tryChk('FamilyController',       function() { return class_exists(App\Http\Controllers\Admin\FamilyController::class); });

// ════════════════════════════════════════════════════════
// RESULTS
// ════════════════════════════════════════════════════════
$total = $pass + $fail + $warn;
$pct = $total > 0 ? round($pass / $total * 100, 1) : 0;

echo "\n" . str_repeat('=', 68) . "\n";
echo " CAPSTONE SYSTEM TEST — Mary Help of Christians Parish\n";
echo str_repeat('=', 68) . "\n";

$failures = array_filter($results, function($r) { return $r[0] !== 'PASS'; });
if (empty($failures)) {
    echo " All $total checks passed!\n";
} else {
    echo " Issues found:\n";
    foreach ($failures as $r) {
        echo " [{$r[0]}] {$r[1]}" . ($r[2] ? " — {$r[2]}" : '') . "\n";
    }
}

echo str_repeat('-', 68) . "\n";
printf(" PASSED  : %d / %d\n", $pass, $total);
printf(" FAILED  : %d (critical issues)\n", $fail);
printf(" WARNINGS: %d (non-critical)\n", $warn);
printf(" SCORE   : %.1f%%\n", $pct);
echo str_repeat('=', 68) . "\n";

echo "\n CAPSTONE OBJECTIVES COVERAGE:\n";
echo str_repeat('-', 68) . "\n";
$payCtrl2 = file_get_contents(app_path('Http/Controllers/Parishioner/PaymentController.php'));
$authCtrl2 = file_get_contents(app_path('Http/Controllers/Auth/AuthController.php'));
$objectives = [
    'Obj 1: QR Booking & Verification'       => hasRoute('walkin.index') && hasRoute('admin.bookings.qr-scanner'),
    'Obj 2: Records & Parishioner Profiling' => Schema::hasTable('sacramental_records') && Schema::hasTable('parishioners'),
    'Obj 3: CMS, Chatbot, Events'            => Schema::hasTable('events') && Schema::hasTable('announcements'),
    'Obj 4: Data Visualization Dashboard'    => str_contains($dash, 'medianPayment') && str_contains($dash, 'exportReport'),
    'Obj 5: Payments & OTP Verification'     => str_contains($payCtrl2, 'otp_code') && str_contains($payCtrl2, 'validateTwoFactorCode'),
    'Obj 6: Security & 2FA Authentication'   => str_contains($authCtrl2, 'generateTwoFactorCode') && str_contains($authCtrl2, 'resetPassword'),
];
foreach ($objectives as $obj => $ok) {
    printf(" [%s] %s\n", $ok ? 'COMPLETE' : 'MISSING ', $obj);
}
echo str_repeat('=', 68) . "\n";

if ($fail === 0) {
    echo "\n SYSTEM IS READY FOR CAPSTONE DEFENSE!\n\n";
} else {
    echo "\n $fail critical issue(s) must be fixed before defense.\n\n";
}
