

<?php $__env->startSection('title', 'Financial Report'); ?>
<?php $__env->startSection('page-title', 'Financial Report'); ?>

<?php $__env->startSection('content'); ?>
<div class="py-6 space-y-5">

    
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs text-gray-500 mb-1">From</label>
                <input type="date" name="date_from" value="<?php echo e($from); ?>" class="form-input text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">To</label>
                <input type="date" name="date_to" value="<?php echo e($to); ?>" class="form-input text-sm">
            </div>
            <button type="submit" class="btn-secondary text-sm">Apply</button>
        </form>
    </div>

    
    <?php
        $totalRevenue = $data->sum('total');
        $totalCount   = $data->sum('count');
        $byMethod     = $data->groupBy('payment_method');
    ?>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <p class="text-sm text-gray-500">Total Revenue</p>
            <p class="text-3xl font-bold text-green-600 mt-1">₱<?php echo e(number_format($totalRevenue, 2)); ?></p>
            <p class="text-xs text-gray-400 mt-1"><?php echo e($totalCount); ?> transactions</p>
        </div>
        <?php $__currentLoopData = $byMethod; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $method => $rows): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <p class="text-sm text-gray-500 capitalize"><?php echo e($method); ?></p>
            <p class="text-3xl font-bold text-gray-800 mt-1">₱<?php echo e(number_format($rows->sum('total'), 2)); ?></p>
            <p class="text-xs text-gray-400 mt-1"><?php echo e($rows->sum('count')); ?> transactions</p>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h3 class="font-semibold text-gray-800 mb-4">Daily Revenue</h3>
        <canvas id="revenueChart" height="100"></canvas>
    </div>

    
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500">
                    <th class="px-4 py-3 font-medium">Date</th>
                    <th class="px-4 py-3 font-medium">Method</th>
                    <th class="px-4 py-3 font-medium">Transactions</th>
                    <th class="px-4 py-3 font-medium">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php $__empty_1 = true; $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-700"><?php echo e(\Carbon\Carbon::parse($row->date)->format('M d, Y')); ?></td>
                    <td class="px-4 py-3 text-gray-600 capitalize"><?php echo e($row->payment_method); ?></td>
                    <td class="px-4 py-3 text-gray-600"><?php echo e($row->count); ?></td>
                    <td class="px-4 py-3 font-semibold text-gray-900">₱<?php echo e(number_format($row->total, 2)); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="4" class="px-4 py-8 text-center text-gray-400">No data for selected period.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const data = <?php echo json_encode($data, 15, 512) ?>;
const dates = [...new Set(data.map(r => r.date))].sort();
const methods = [...new Set(data.map(r => r.payment_method))];
const colors = { cash: '#10b981', gcash: '#3b82f6', paymaya: '#8b5cf6' };

const datasets = methods.map(method => ({
    label: method.charAt(0).toUpperCase() + method.slice(1),
    data: dates.map(date => {
        const row = data.find(r => r.date === date && r.payment_method === method);
        return row ? row.total : 0;
    }),
    backgroundColor: colors[method] || '#6b7280',
    borderRadius: 4,
}));

new Chart(document.getElementById('revenueChart').getContext('2d'), {
    type: 'bar',
    data: { labels: dates, datasets },
    options: {
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: {
            x: { stacked: true },
            y: { stacked: true, beginAtZero: true, ticks: { callback: v => '₱' + v.toLocaleString() } }
        }
    }
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Admin\Capstone 2\resources\views/admin/payments/report.blade.php ENDPATH**/ ?>