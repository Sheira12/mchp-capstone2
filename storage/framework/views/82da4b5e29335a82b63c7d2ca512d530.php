

<?php $__env->startSection('title', 'Bookings'); ?>
<?php $__env->startSection('page-title', 'Bookings'); ?>

<?php $__env->startSection('content'); ?>
<div class="py-6 space-y-5">

    
    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-gray-500"><?php echo e($bookings->total()); ?> bookings found</p>
        <div class="flex gap-2">
            <a href="<?php echo e(route('admin.bookings.calendar')); ?>" class="btn-secondary text-sm">📅 Calendar View</a>
            <a href="<?php echo e(route('admin.bookings.create')); ?>" class="btn-primary text-sm">+ New Booking</a>
        </div>
    </div>

    
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <form method="GET" class="flex flex-wrap gap-3">
            <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Search parishioner or reference..." class="form-input text-sm flex-1 min-w-48">
            <select name="status" class="form-select text-sm">
                <option value="">All Statuses</option>
                <?php $__currentLoopData = \App\Models\Booking::STATUSES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($val); ?>" <?php echo e(request('status') === $val ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <select name="type" class="form-select text-sm">
                <option value="">All Types</option>
                <?php $__currentLoopData = \App\Models\Booking::TYPES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($val); ?>" <?php echo e(request('type') === $val ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <input type="date" name="date_from" value="<?php echo e(request('date_from')); ?>" class="form-input text-sm">
            <input type="date" name="date_to" value="<?php echo e(request('date_to')); ?>" class="form-input text-sm">
            <button type="submit" class="btn-primary text-sm">Filter</button>
            <?php if(request()->hasAny(['search', 'status', 'type', 'date_from', 'date_to'])): ?>
            <a href="<?php echo e(route('admin.bookings.index')); ?>" class="btn-secondary text-sm">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Reference</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Parishioner</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Service</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Scheduled</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Fee</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php $__empty_1 = true; $__currentLoopData = $bookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $statusColors = ['pending' => 'amber', 'confirmed' => 'green', 'completed' => 'blue', 'cancelled' => 'red'];
                        $color = $statusColors[$booking->status] ?? 'gray';
                    ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-xs text-gray-500"><?php echo e($booking->reference_number); ?></td>
                        <td class="px-4 py-3">
                            <a href="<?php echo e(route('admin.parishioners.show', $booking->parishioner)); ?>" class="font-medium text-gray-900 hover:text-blue-700">
                                <?php echo e($booking->parishioner->full_name); ?>

                            </a>
                        </td>
                        <td class="px-4 py-3 text-gray-600"><?php echo e($booking->getTypeLabel()); ?></td>
                        <td class="px-4 py-3 text-gray-600">
                            <?php echo e($booking->scheduled_date->format('M d, Y')); ?>

                            <?php if($booking->scheduled_time): ?>
                            <span class="text-gray-400"><?php echo e(\Carbon\Carbon::parse($booking->scheduled_time)->format('g:i A')); ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-gray-600">₱<?php echo e(number_format($booking->service_fee, 2)); ?></td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-<?php echo e($color); ?>-100 text-<?php echo e($color); ?>-800">
                                <?php echo e($booking->getStatusLabel()); ?>

                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="<?php echo e(route('admin.bookings.show', $booking)); ?>" class="text-blue-600 hover:text-blue-800 text-xs">View</a>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-gray-400">No bookings found.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($bookings->hasPages()): ?>
        <div class="px-4 py-3 border-t border-gray-100">
            <?php echo e($bookings->links()); ?>

        </div>
        <?php endif; ?>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Admin\Capstone 2\resources\views/admin/bookings/index.blade.php ENDPATH**/ ?>