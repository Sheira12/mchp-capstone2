

<?php $__env->startSection('title', 'Booking Calendar'); ?>
<?php $__env->startSection('page-title', 'Booking Calendar'); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css">
<style>
#calendar {
    background: #fff;
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    border: 1px solid #f1f5f9;
}
.fc-toolbar-title { font-size: 1.1rem !important; font-weight: 700 !important; color: #0f172a; }
.fc-button-primary {
    background: #2563eb !important;
    border-color: #2563eb !important;
    font-size: 0.8rem !important;
    padding: 0.4rem 0.75rem !important;
    border-radius: 0.5rem !important;
}
.fc-button-primary:hover { background: #1d4ed8 !important; border-color: #1d4ed8 !important; }
.fc-button-primary:disabled { background: #93c5fd !important; border-color: #93c5fd !important; }
.fc-event {
    border-radius: 4px !important;
    font-size: 0.75rem !important;
    padding: 2px 5px !important;
    cursor: pointer;
}
.fc-daygrid-day-number { font-size: 0.8rem; color: #475569; }
.fc-col-header-cell-cushion { font-size: 0.8rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; }
.fc-day-today { background: #eff6ff !important; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="py-6 space-y-5">

    
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm text-gray-500">Showing pending and confirmed bookings</p>
        </div>
        <div class="flex gap-2">
            <a href="<?php echo e(route('admin.bookings.index')); ?>" class="btn-secondary text-sm">☰ List View</a>
            <a href="<?php echo e(route('admin.bookings.create')); ?>" class="btn-primary text-sm">+ New Booking</a>
        </div>
    </div>

    
    <div class="flex items-center gap-5 bg-white rounded-xl border border-gray-100 shadow-sm px-5 py-3">
        <span class="text-sm font-semibold text-gray-600">Legend:</span>
        <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full inline-block" style="background:#d97706;"></span>
            <span class="text-sm text-gray-600">Pending</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full inline-block" style="background:#16a34a;"></span>
            <span class="text-sm text-gray-600">Confirmed</span>
        </div>
    </div>

    
    <div id="calendar"></div>

    
    <div id="booking-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
            <div class="flex items-start justify-between mb-4">
                <h3 class="font-bold text-gray-900 text-lg" id="modal-title">Booking Details</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div id="modal-body" class="space-y-3 text-sm text-gray-700"></div>
            <div class="mt-5 flex gap-3">
                <a id="modal-link" href="#" class="btn-primary text-sm flex-1 text-center">View Full Details</a>
                <button onclick="closeModal()" class="btn-secondary text-sm flex-1">Close</button>
            </div>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script>
const bookings = <?php echo json_encode($bookings, 15, 512) ?>;

document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        initialDate: '<?php echo e($year); ?>-<?php echo e(str_pad($month, 2, "0", STR_PAD_LEFT)); ?>-01',
        headerToolbar: {
            left:   'prev,next today',
            center: 'title',
            right:  'dayGridMonth,timeGridWeek,listWeek'
        },
        events: bookings,
        eventClick: function(info) {
            info.jsEvent.preventDefault();
            showModal(info.event);
        },
        eventDidMount: function(info) {
            info.el.title = info.event.title;
        },
        height: 'auto',
        dayMaxEvents: 4,
        moreLinkClick: 'popover',
    });

    calendar.render();
});

function showModal(event) {
    const parts = event.title.split(' - ');
    const service = parts[0] || event.title;
    const name    = parts[1] || '';
    const start   = event.start;
    const color   = event.backgroundColor;
    const status  = color === '#16a34a' ? 'Confirmed' : 'Pending';

    document.getElementById('modal-title').textContent = service;
    document.getElementById('modal-body').innerHTML = `
        <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background:${color};"></span>
            <span class="font-semibold" style="color:${color};">${status}</span>
        </div>
        <div class="bg-gray-50 rounded-lg p-3 space-y-2">
            <div class="flex justify-between">
                <span class="text-gray-500">Parishioner</span>
                <span class="font-semibold text-gray-900">${name}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Date</span>
                <span class="font-semibold text-gray-900">${start ? start.toLocaleDateString('en-PH', {month:'long',day:'numeric',year:'numeric'}) : '—'}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Time</span>
                <span class="font-semibold text-gray-900">${start && start.getHours() ? start.toLocaleTimeString('en-PH', {hour:'numeric',minute:'2-digit'}) : 'TBD'}</span>
            </div>
        </div>
    `;
    document.getElementById('modal-link').href = event.url || '#';
    document.getElementById('booking-modal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('booking-modal').classList.add('hidden');
}

document.getElementById('booking-modal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Admin\Capstone 2\resources\views/admin/bookings/calendar.blade.php ENDPATH**/ ?>