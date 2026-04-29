

<?php $__env->startSection('title', 'Dashboard'); ?>
<?php $__env->startSection('page-title', 'Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<div class="py-6 space-y-6">

    
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Total Parishioners</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1"><?php echo e(number_format($stats['totalParishioners'])); ?></p>
                    <p class="text-xs text-green-600 mt-1">+<?php echo e($stats['newParishioners']); ?> this month</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Pending Bookings</p>
                    <p class="text-3xl font-bold text-amber-600 mt-1"><?php echo e($stats['pendingBookings']); ?></p>
                    <p class="text-xs text-gray-400 mt-1"><?php echo e($stats['confirmedBookings']); ?> confirmed</p>
                </div>
                <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Revenue (This Month)</p>
                    <p class="text-3xl font-bold text-green-600 mt-1">₱<?php echo e(number_format($stats['totalRevenue'], 2)); ?></p>
                    <p class="text-xs text-gray-400 mt-1">Via e-wallet & cash</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">Pending Certificates</p>
                    <p class="text-3xl font-bold text-purple-600 mt-1"><?php echo e($stats['pendingCertificates']); ?></p>
                    <p class="text-xs text-gray-400 mt-1">Awaiting release</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </div>
            </div>
        </div>
    </div>

    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-800 mb-4">Sacraments This Month</h3>
            <canvas id="sacramentsChart" height="200"></canvas>
        </div>

        
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-800 mb-4">Revenue Trend (12 Months)</h3>
            <canvas id="revenueChart" height="200"></canvas>
        </div>
    </div>

    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="font-semibold text-gray-800 mb-4">Sacrament Breakdown</h3>
            <?php
                $sacramentLabels = ['baptism' => 'Baptism', 'first_communion' => 'First Communion', 'confirmation' => 'Confirmation', 'marriage' => 'Marriage', 'death_burial' => 'Death/Burial'];
                $colors = ['baptism' => 'blue', 'first_communion' => 'green', 'confirmation' => 'purple', 'marriage' => 'pink', 'death_burial' => 'gray'];
            ?>
            <div class="space-y-3">
                <?php $__currentLoopData = $sacramentLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $count = $stats['sacramentCounts'][$key] ?? 0; ?>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600"><?php echo e($label); ?></span>
                    <div class="flex items-center gap-2">
                        <div class="w-24 bg-gray-100 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: <?php echo e($count > 0 ? min(100, ($count / max(array_values($stats['sacramentCounts']) ?: [1])) * 100) : 0); ?>%"></div>
                        </div>
                        <span class="text-sm font-semibold text-gray-800 w-6 text-right"><?php echo e($count); ?></span>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-800">Recent Bookings</h3>
                <a href="<?php echo e(route('admin.bookings.index')); ?>" class="text-sm text-blue-600 hover:underline">View all</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 border-b">
                            <th class="pb-2 font-medium">Parishioner</th>
                            <th class="pb-2 font-medium">Service</th>
                            <th class="pb-2 font-medium">Date</th>
                            <th class="pb-2 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php $__currentLoopData = $stats['recentBookings']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="hover:bg-gray-50">
                            <td class="py-2">
                                <a href="<?php echo e(route('admin.bookings.show', $booking)); ?>" class="font-medium text-gray-900 hover:text-blue-700">
                                    <?php echo e($booking->parishioner->full_name); ?>

                                </a>
                            </td>
                            <td class="py-2 text-gray-600"><?php echo e($booking->getTypeLabel()); ?></td>
                            <td class="py-2 text-gray-500"><?php echo e($booking->scheduled_date->format('M d, Y')); ?></td>
                            <td class="py-2">
                                <?php
                                    $statusColors = ['pending' => 'amber', 'confirmed' => 'green', 'completed' => 'blue', 'cancelled' => 'red'];
                                    $color = $statusColors[$booking->status] ?? 'gray';
                                ?>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-<?php echo e($color); ?>-100 text-<?php echo e($color); ?>-800">
                                    <?php echo e($booking->getStatusLabel()); ?>

                                </span>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="font-semibold text-gray-800 mb-4">Export Report</h3>
        <form action="<?php echo e(route('admin.dashboard.export')); ?>" method="POST" class="flex flex-wrap gap-3 items-end">
            <?php echo csrf_field(); ?>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Period</label>
                <select name="period" class="form-select text-sm" onchange="toggleCustomDates(this.value)">
                    <option value="month">This Month</option>
                    <option value="week">This Week</option>
                    <option value="year">This Year</option>
                    <option value="custom">Custom Range</option>
                </select>
            </div>
            <div id="custom-dates" class="hidden flex gap-2">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">From</label>
                    <input type="date" name="date_from" class="form-input text-sm">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">To</label>
                    <input type="date" name="date_to" class="form-input text-sm">
                </div>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Format</label>
                <select name="type" class="form-select text-sm">
                    <option value="pdf">PDF</option>
                    <option value="excel">Excel</option>
                </select>
            </div>
            <button type="submit" class="btn-primary text-sm">Export Report</button>
        </form>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
// Sacraments Pie Chart
const sacramentsCtx = document.getElementById('sacramentsChart').getContext('2d');
new Chart(sacramentsCtx, {
    type: 'doughnut',
    data: {
        labels: ['Baptism', 'First Communion', 'Confirmation', 'Marriage', 'Death/Burial'],
        datasets: [{
            data: [
                <?php echo e($stats['sacramentCounts']['baptism'] ?? 0); ?>,
                <?php echo e($stats['sacramentCounts']['first_communion'] ?? 0); ?>,
                <?php echo e($stats['sacramentCounts']['confirmation'] ?? 0); ?>,
                <?php echo e($stats['sacramentCounts']['marriage'] ?? 0); ?>,
                <?php echo e($stats['sacramentCounts']['death_burial'] ?? 0); ?>,
            ],
            backgroundColor: ['#3b82f6', '#10b981', '#8b5cf6', '#ec4899', '#6b7280'],
            borderWidth: 0,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } }
    }
});

// Revenue Line Chart
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
const revenueTrend = <?php echo json_encode($stats['revenueTrend'], 15, 512) ?>;
const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: revenueTrend.map(r => months[r.month - 1] + ' ' + r.year),
        datasets: [{
            label: 'Revenue (₱)',
            data: revenueTrend.map(r => r.total),
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59,130,246,0.1)',
            fill: true,
            tension: 0.4,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { callback: v => '₱' + v.toLocaleString() } }
        }
    }
});

function toggleCustomDates(val) {
    document.getElementById('custom-dates').classList.toggle('hidden', val !== 'custom');
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Admin\Capstone 2\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>