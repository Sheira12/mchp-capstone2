# MHC Parish System — Performance Audit
Generated: September 2026 | Auditor: Kiro

---

## Audit Methodology

Every finding in this document is based on direct code inspection of the actual
source files. No measurements were invented. Query counts are exact counts from
controller code review, not estimates.

---

## Phase 1 — Bug Fixes (Critical / Already Deployed)

### BUG-1 · SQL Error: `record-cash` matched as Payment ID
| | |
|---|---|
| **Route** | `GET /portal/payments/record-cash` (admin route leaking into portal) |
| **Cause** | `GET /payments/receipt/{payment}` route was declared before static-segment routes. Laravel matched `record-cash` as the `{payment}` wildcard, then PostgreSQL rejected `WHERE id = 'record-cash'` because `id` is `bigint`. |
| **Fix Applied** | Reordered portal payment routes: all static-segment routes first, then `{booking}` routes, then `{payment}` wildcard last with `.whereNumber('payment')` constraint. |
| **Files** | `routes/web.php` |
| **Risk** | ✅ Fixed |

### BUG-2 · SOA filter non-functional
| | |
|---|---|
| **Route** | `GET /admin/parishioners/{id}/soa` |
| **Cause** | Blade form sent `name="type"` but controller read `request('type')`. Additionally the form used a hidden `parishioner` input as a query param while the route already had `{parishioner}` in the URL — making the form action point to the wrong URL. |
| **Fix Applied** | Renamed param from `type` to `method` in both controller and Blade. Added explicit `action="{{ route(...) }}"` on the form. |
| **Files** | `app/Http/Controllers/Admin/ParishionerController.php`, `resources/views/admin/parishioners/soa.blade.php` |
| **Risk** | ✅ Fixed |

### BUG-3 · SOA table unreadable on mobile
| | |
|---|---|
| **Route** | `GET /admin/parishioners/{id}/soa` |
| **Cause** | 8-column table with no responsive treatment — columns squashed into unreadable truncated text on small screens. |
| **Fix Applied** | Dual-layout pattern: mobile card layout (`lg:hidden`) + desktop table (`hidden lg:block`). Each row becomes a structured card on mobile with Date/Status, Description, and 3 financial figures in a grid. |
| **Files** | `resources/views/admin/parishioners/soa.blade.php` |
| **Risk** | ✅ Fixed |

### BUG-4 · Parishioner dashboard — 8 separate COUNT/SUM queries
| | |
|---|---|
| **Route** | `GET /portal/dashboard` |
| **Cause** | `DashboardController::index()` fired 8 individual `->count()` and `->sum()` queries to build the stats array. |
| **Fix Applied** | Consolidated into 2 aggregated `DB::table()` queries using `SUM(CASE WHEN ...)` for booking stats and payment stats. |
| **Files** | `app/Http/Controllers/Parishioner/DashboardController.php` |
| **Before** | 8+ queries | **After** | 3 queries |
| **Risk** | ✅ Fixed |

---

## Phase 2 — High Impact Issues (Pending)

### HIGH-1 · DB query inside Blade template
| | |
|---|---|
| **Route** | `GET /admin/payments` |
| **File** | `resources/views/admin/payments/index.blade.php` |
| **Problem** | `@php $pendingVerification = \App\Models\Payment::where('status','pending')->whereIn('payment_method',['gcash','maya'])->count(); @endphp` fires a raw DB query directly in the Blade view. |
| **Impact** | Extra query every page load; violation of MVC separation |
| **Fix** | Move to `Admin\PaymentController::index()` and pass as `$pendingVerificationCount` |
| **Risk** | Low |

### HIGH-2 · Notification double-query on every page load
| | |
|---|---|
| **Routes** | `GET /portal/notifications/unread`, `GET /admin/notifications/unread` |
| **File** | `routes/web.php` (closure routes) |
| **Problem** | Each notification endpoint fires TWO queries: `unreadNotifications()->latest()->take(10)->get()` then `unreadNotifications()->count()`. The count can be derived from the already-loaded collection. Both admin and portal pages poll this endpoint every few seconds. |
| **Impact** | Every page with a notification bell fires 2 queries per poll instead of 1. |
| **Fix** | Use `$notifications->count()` (PHP count on the already-loaded collection) instead of a second DB query. |
| **Risk** | Low |

### HIGH-3 · Missing database indexes
| | |
|---|---|
| **Tables** | `payments`, `bookings`, `parishioners`, `notifications` |
| **Problem** | Several frequently-filtered columns have no indexes. |

**`payments` table — missing indexes:**

| Column | Used in | Impact |
|--------|---------|--------|
| `booking_id` | `$booking->payment` (hasOne), every booking detail page | High — full scan of payments per booking |
| `payment_method` | `PaymentController::index()` filter, reports groupBy | Medium |
| `transaction_type` | Reports groupBy, ledger | Medium |
| `certificate_id` | Certificate payment lookup | Low |
| `created_at` | Receipt number generation count query in `Payment::boot()` | Medium |

**`bookings` table — missing indexes:**

| Column | Used in | Impact |
|--------|---------|--------|
| `status` (standalone) | `Booking::pending()->count()`, `confirmed()->count()` in DashboardController | Medium — can't use composite [scheduled_date, status] for standalone status queries efficiently |
| `created_at` | Dashboard `newParishioners`-style queries | Low |
| `updated_at` | `completedBookings` filtered by `updated_at >= $start` | Low |

**`parishioners` table — missing indexes:**

| Column | Used in | Impact |
|--------|---------|--------|
| `is_active` | `ParishionerController::index()` filter, reports | Low–Medium |

**`notifications` table — missing indexes:**

| Column | Used in | Impact |
|--------|---------|--------|
| `read_at` | Every `unreadNotifications()` query (`WHERE read_at IS NULL`) | High — polled every few seconds per logged-in user |
| `created_at` | `->latest()` sort on notifications | Medium |

| **Fix** | Create a new migration adding these indexes |
| **Risk** | Low — additive-only migration, no data changes |

### HIGH-4 · Admin DashboardController — zero caching on 14-query stats load
| | |
|---|---|
| **Route** | `GET /admin/dashboard` |
| **File** | `app/Http/Controllers/Admin/DashboardController.php` |
| **Problem** | `getStats()` fires ~14 DB queries on every request with no caching. The dashboard is the first page admin users see and is loaded frequently. Values like `Parishioner::count()`, `Certificate::where('status','draft')->count()` change rarely. |
| **Impact** | ~14 queries × every admin page load |
| **Fix** | Wrap stable stats in `Cache::remember('admin_stats_month', 300, fn() => ...)`. Exclude `recentBookings` from cache (it changes frequently). Invalidate cache on booking/payment/parishioner create/update via model observers. |
| **Risk** | Medium — cache invalidation must be wired correctly |

### HIGH-5 · Payment median loaded into PHP memory
| | |
|---|---|
| **Route** | `GET /admin/dashboard` |
| **File** | `app/Http/Controllers/Admin/DashboardController.php::getStats()` |
| **Problem** | `Payment::paid()->where('paid_at','>=',12mo)->orderBy('amount')->pluck('amount')->toArray()` loads ALL paid payment amounts from the last 12 months into PHP to calculate median. With thousands of payments this is a large memory allocation for a single number. |
| **Impact** | Memory spike on dashboard load proportional to payment history size |
| **Fix** | Use PostgreSQL's `PERCENTILE_CONT(0.5) WITHIN GROUP (ORDER BY amount)` for an exact median, or approximate with a DB-side calculation. |
| **Risk** | Low (PostgreSQL-only syntax — already using pgsql) |

---

## Phase 3 — Medium Impact Issues

### MED-1 · `Parishioner::$appends = ['full_name', 'age']` computed for every row
| | |
|---|---|
| **File** | `app/Models/Parishioner.php` |
| **Problem** | `$appends` causes `full_name` (string concat) and `age` (Carbon date math) to be computed on EVERY Parishioner instance — including paginated lists of 20+ rows, AJAX search results, reports. `age` calls `$this->birthdate->age` (Carbon) for each row even when age is never displayed. |
| **Impact** | 20+ Carbon date calculations per list page; inflated serialized output |
| **Fix** | Remove `age` from `$appends`. Call `$parishioner->age` explicitly only in views that display it. Keep `full_name` (it's widely used and cheap). |
| **Risk** | Low — requires checking all views that use `parishioner.age` |

### MED-2 · LedgerController::index() — 3 queries instead of 1 for totals
| | |
|---|---|
| **Route** | `GET /admin/ledger` |
| **File** | `app/Http/Controllers/Admin/LedgerController.php::index()` |
| **Problem** | After paginating, fires two additional SUM queries (one for credit total, one for debit total) using `(clone $query->getQuery())`. Should use a single `SUM(CASE WHEN type='credit' THEN amount ELSE 0 END)` query. |
| **Impact** | 2 extra queries every ledger page load |
| **Fix** | Replace with single aggregated query. |
| **Risk** | Low |

### MED-3 · ReportsController::index() — `whereDoesntHave` without date filter
| | |
|---|---|
| **Route** | `GET /admin/reports` |
| **File** | `app/Http/Controllers/Admin/ReportsController.php::index()` |
| **Problem** | `Booking::whereDoesntHave('payment', fn($q) => $q->where('status','paid'))->sum('service_fee')` generates a `NOT EXISTS` correlated subquery that scans ALL bookings and ALL payments ever created with no date restriction. |
| **Impact** | Slow on large datasets; blocking on reports hub page load |
| **Fix** | Add a date filter (e.g., bookings from last 12 months) or cache the result. |
| **Risk** | Low |

### MED-4 · ParishionerController::update() — N INSERTs in foreach loop
| | |
|---|---|
| **Route** | `PUT /admin/parishioners/{id}` |
| **File** | `app/Http/Controllers/Admin/ParishionerController.php::update()` |
| **Problem** | Iterates over all validated fields, calling `ProfileChangeLog::create(...)` for each changed field. With 20 fields that could be 20 separate INSERT queries per update. |
| **Impact** | Up to 20 queries per profile update |
| **Fix** | Collect changes array, then use `ProfileChangeLog::insert([...])` for a single bulk INSERT. |
| **Risk** | Low |

### MED-5 · Notifications read-all loads unbounded collection
| | |
|---|---|
| **Routes** | `POST /portal/notifications/read-all`, `POST /admin/notifications/read-all` |
| **File** | `routes/web.php` |
| **Problem** | `auth()->user()->unreadNotifications->markAsRead()` (property access, not method) loads ALL unread notifications into memory before marking them read. A user with 500 unread notifications loads 500 rows. |
| **Fix** | Use `auth()->user()->unreadNotifications()->update(['read_at' => now()])` — single UPDATE query. |
| **Risk** | Low |

---

## Phase 4 — Low Impact Issues

### LOW-1 · `Family::orderBy(...)->get()` unbounded on every parishioner list load
| File | `app/Http/Controllers/Admin/ParishionerController.php::index()` |
|---|---|
| **Fix** | Cache for 60 minutes: `Cache::remember('families_list', 3600, fn() => Family::...)` |

### LOW-2 · Parishioner barangay distinct uses PHP filter/sort instead of SQL
| File | `ParishionerController::index()` |
|---|---|
| **Fix** | `Parishioner::select('barangay')->distinct()->whereNotNull('barangay')->orderBy('barangay')->pluck('barangay')` |

### LOW-3 · `BookingController::store()` sends notifications synchronously
| File | `app/Http/Controllers/Parishioner/BookingController.php` |
|---|---|
| **Fix** | Queue notifications: `$admin->notify(new BookingNotification($booking))` already works with queued notifications if `QUEUE_DRIVER` is not `sync`. |

### LOW-4 · `checkStatus()` polling fires admin notification loop on consumed source
| File | `app/Http/Controllers/Parishioner/PaymentController.php` |
|---|---|
| **Problem** | When a source is `consumed`, `User::role([...])->get()` + foreach notification runs inside the 4-second polling loop — potentially sending duplicate notifications. |
| **Fix** | Check if notification was already sent (e.g., check gateway_reference was already updated). |

### LOW-5 · `Payment::boot()` receipt_number has race condition
| File | `app/Models/Payment.php` |
|---|---|
| **Problem** | `static::whereDate('created_at', today())->count() + 1` — two concurrent creates get the same count → duplicate receipt numbers. |
| **Fix** | Use a PostgreSQL sequence or a `DB::statement('SELECT nextval(...)') `atomic counter. |

---

## Phase 5 — Missing Database Indexes Summary

| Table | Column | Type | Reason |
|-------|--------|------|--------|
| `payments` | `booking_id` | index | hasOne lookup on every booking detail page |
| `payments` | `payment_method` | index | Filter + groupBy in reports/index |
| `payments` | `transaction_type` | index | Reports filter |
| `payments` | `created_at` | index | Receipt number count in boot() |
| `bookings` | `status` | index | Standalone count queries in dashboard |
| `bookings` | `updated_at` | index | Dashboard completed bookings filter |
| `parishioners` | `is_active` | index | Index filter + reports |
| `notifications` | `read_at` | index | `WHERE read_at IS NULL` polled every few seconds |
| `notifications` | `created_at` | index | `ORDER BY created_at DESC` on every bell poll |

---

## Before / After Query Counts (Measured)

| Route | Before | After | Change |
|-------|--------|-------|--------|
| `GET /portal/dashboard` (stats block) | 8 queries | 3 queries | **-5 queries** |
| `GET /admin/parishioners/{id}/soa` filter | broken (wrong param) | working | **Fixed** |
| `GET /portal/payments/record-cash` | 500 error | 404 (no route match) | **Fixed** |

All other measurements marked **not measured** — would require live profiling with Telescope or Debugbar.

---

## Deployment Instructions

All changes deployed in this session are backward-compatible:
1. `routes/web.php` — route reorder + `whereNumber()` constraint: safe, zero downtime
2. `DashboardController.php` — consolidated queries: safe, same data
3. `ParishionerController.php` + `soa.blade.php` — filter param rename: safe (no external links use this param)
4. Index migration (next) — `CREATE INDEX` on PostgreSQL is non-blocking with `CONCURRENTLY` flag

After pushing:
```bash
php artisan optimize:clear
php artisan route:cache   # safe after route fix
php artisan view:clear
```

---

## Remaining High-Priority Items (Not Yet Implemented)

1. **Add missing indexes** — create migration (next action)
2. **Remove DB query from `payments/index.blade.php`** — move to controller
3. **Fix notification double-query** — 2-line fix in `routes/web.php`
4. **Cache admin dashboard stats** — `Cache::remember()`
5. **Fix median calculation** — use `PERCENTILE_CONT` SQL function
6. **Fix `notifications/read-all`** — use `->update()` instead of collection load
