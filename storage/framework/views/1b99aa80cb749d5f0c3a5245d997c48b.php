

<?php $__env->startSection('title', 'Certificates'); ?>
<?php $__env->startSection('page-title', 'Certificates'); ?>

<?php $__env->startSection('content'); ?>
<div class="py-6 space-y-4">

    
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Search</label>
                <input type="text" name="search" value="<?php echo e(request('search')); ?>"
                       class="form-input text-sm w-48" placeholder="Name or cert #…">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Type</label>
                <select name="type" class="form-select text-sm">
                    <option value="">All Types</option>
                    <option value="baptism" <?php if(request('type') === 'baptism'): echo 'selected'; endif; ?>>Baptism</option>
                    <option value="confirmation" <?php if(request('type') === 'confirmation'): echo 'selected'; endif; ?>>Confirmation</option>
                    <option value="marriage" <?php if(request('type') === 'marriage'): echo 'selected'; endif; ?>>Marriage</option>
                    <option value="first_communion" <?php if(request('type') === 'first_communion'): echo 'selected'; endif; ?>>First Communion</option>
                    <option value="other" <?php if(request('type') === 'other'): echo 'selected'; endif; ?>>Other</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Status</label>
                <select name="status" class="form-select text-sm">
                    <option value="">All Status</option>
                    <option value="draft" <?php if(request('status') === 'draft'): echo 'selected'; endif; ?>>Draft</option>
                    <option value="issued" <?php if(request('status') === 'issued'): echo 'selected'; endif; ?>>Issued</option>
                    <option value="released" <?php if(request('status') === 'released'): echo 'selected'; endif; ?>>Released</option>
                </select>
            </div>
            <button type="submit" class="btn-secondary text-sm">Filter</button>
            <?php if(request()->hasAny(['search','type','status'])): ?>
                <a href="<?php echo e(route('admin.certificates.index')); ?>" class="btn-secondary text-sm">Clear</a>
            <?php endif; ?>
            <div class="ml-auto">
                <a href="<?php echo e(route('admin.certificates.create')); ?>" class="btn-primary text-sm">+ New Certificate</a>
            </div>
        </form>
    </div>

    
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500">
                    <th class="px-4 py-3 font-medium">Certificate #</th>
                    <th class="px-4 py-3 font-medium">Parishioner</th>
                    <th class="px-4 py-3 font-medium">Type</th>
                    <th class="px-4 py-3 font-medium">Issued Date</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php $__empty_1 = true; $__currentLoopData = $certificates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $statusColors = ['draft' => 'gray', 'issued' => 'blue', 'released' => 'green'];
                    $sc = $statusColors[$cert->status] ?? 'gray';
                ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-mono text-xs text-gray-700"><?php echo e($cert->certificate_number); ?></td>
                    <td class="px-4 py-3 font-medium text-gray-900">
                        <a href="<?php echo e(route('admin.certificates.show', $cert)); ?>" class="hover:text-blue-700">
                            <?php echo e($cert->parishioner->full_name); ?>

                        </a>
                    </td>
                    <td class="px-4 py-3 text-gray-600 capitalize"><?php echo e(str_replace('_', ' ', $cert->type)); ?></td>
                    <td class="px-4 py-3 text-gray-600"><?php echo e($cert->issued_date->format('M d, Y')); ?></td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-<?php echo e($sc); ?>-100 text-<?php echo e($sc); ?>-800">
                            <?php echo e(ucfirst($cert->status)); ?>

                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <a href="<?php echo e(route('admin.certificates.show', $cert)); ?>" class="text-blue-600 hover:underline text-xs">View</a>
                            <a href="<?php echo e(route('admin.certificates.download', $cert)); ?>" class="text-green-600 hover:underline text-xs">PDF</a>
                            <?php if($cert->status === 'issued'): ?>
                            <form method="POST" action="<?php echo e(route('admin.certificates.release', $cert)); ?>">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="text-purple-600 hover:underline text-xs">Release</button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-400">No certificates found.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">
            <?php echo e($certificates->links()); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Admin\Capstone 2\resources\views/admin/certificates/index.blade.php ENDPATH**/ ?>