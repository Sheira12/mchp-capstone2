

<?php $__env->startSection('title', 'Audit Logs'); ?>
<?php $__env->startSection('page-title', 'Audit Logs'); ?>

<?php $__env->startSection('content'); ?>
<div class="py-6 space-y-4">

    
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Action</label>
                <select name="action" class="form-select text-sm">
                    <option value="">All Actions</option>
                    <?php $__currentLoopData = ['create','update','delete','verify','download','release','refund','void','login','logout']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($action); ?>" <?php if(request('action') === $action): echo 'selected'; endif; ?>><?php echo e(ucfirst($action)); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">From</label>
                <input type="date" name="date_from" value="<?php echo e(request('date_from')); ?>" class="form-input text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">To</label>
                <input type="date" name="date_to" value="<?php echo e(request('date_to')); ?>" class="form-input text-sm">
            </div>
            <button type="submit" class="btn-secondary text-sm">Filter</button>
            <?php if(request()->hasAny(['action','date_from','date_to','user_id'])): ?>
                <a href="<?php echo e(route('admin.audit-logs.index')); ?>" class="btn-secondary text-sm">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500">
                    <th class="px-4 py-3 font-medium">Date/Time</th>
                    <th class="px-4 py-3 font-medium">User</th>
                    <th class="px-4 py-3 font-medium">Action</th>
                    <th class="px-4 py-3 font-medium">Model</th>
                    <th class="px-4 py-3 font-medium">Description</th>
                    <th class="px-4 py-3 font-medium">IP</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $actionColors = [
                        'create' => 'green', 'update' => 'blue', 'delete' => 'red',
                        'verify' => 'purple', 'download' => 'gray', 'release' => 'teal',
                        'refund' => 'orange', 'void' => 'red', 'login' => 'green', 'logout' => 'gray'
                    ];
                    $ac = $actionColors[$log->action] ?? 'gray';
                ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-500 text-xs whitespace-nowrap">
                        <?php echo e($log->created_at->format('M d, Y')); ?><br>
                        <span class="text-gray-400"><?php echo e($log->created_at->format('g:i A')); ?></span>
                    </td>
                    <td class="px-4 py-3 text-gray-700"><?php echo e($log->user?->name ?? 'System'); ?></td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-<?php echo e($ac); ?>-100 text-<?php echo e($ac); ?>-800">
                            <?php echo e(ucfirst($log->action)); ?>

                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-600 text-xs">
                        <?php echo e(class_basename($log->auditable_type ?? '')); ?>

                        <?php if($log->auditable_id): ?>
                            <span class="text-gray-400">#<?php echo e($log->auditable_id); ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-gray-600 text-xs max-w-xs truncate"><?php echo e($log->description ?? '—'); ?></td>
                    <td class="px-4 py-3 text-gray-400 text-xs"><?php echo e($log->ip_address ?? '—'); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-400">No audit logs found.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">
            <?php echo e($logs->links()); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Admin\Capstone 2\resources\views/admin/audit-logs/index.blade.php ENDPATH**/ ?>