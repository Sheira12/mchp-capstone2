

<?php $__env->startSection('title', 'Families'); ?>
<?php $__env->startSection('page-title', 'Families'); ?>

<?php $__env->startSection('content'); ?>
<div class="py-6 space-y-4">

    
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <form method="GET" class="flex gap-2">
            <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Search family name or barangay…"
                   class="form-input text-sm w-64">
            <button type="submit" class="btn-secondary text-sm">Search</button>
            <?php if(request('search')): ?>
                <a href="<?php echo e(route('admin.families.index')); ?>" class="btn-secondary text-sm">Clear</a>
            <?php endif; ?>
        </form>
        <a href="<?php echo e(route('admin.families.create')); ?>" class="btn-primary text-sm">+ New Family</a>
    </div>

    
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500">
                    <th class="px-4 py-3 font-medium">Family Name</th>
                    <th class="px-4 py-3 font-medium">Address</th>
                    <th class="px-4 py-3 font-medium">Barangay</th>
                    <th class="px-4 py-3 font-medium">Members</th>
                    <th class="px-4 py-3 font-medium">Contact</th>
                    <th class="px-4 py-3 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php $__empty_1 = true; $__currentLoopData = $families; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $family): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900">
                        <a href="<?php echo e(route('admin.families.show', $family)); ?>" class="hover:text-blue-700">
                            <?php echo e($family->family_name); ?>

                        </a>
                    </td>
                    <td class="px-4 py-3 text-gray-600"><?php echo e($family->address ?? '—'); ?></td>
                    <td class="px-4 py-3 text-gray-600"><?php echo e($family->barangay ?? '—'); ?></td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            <?php echo e($family->members_count); ?>

                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-600"><?php echo e($family->contact_number ?? '—'); ?></td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <a href="<?php echo e(route('admin.families.show', $family)); ?>" class="text-blue-600 hover:underline text-xs">View</a>
                            <a href="<?php echo e(route('admin.families.edit', $family)); ?>" class="text-gray-600 hover:underline text-xs">Edit</a>
                            <form method="POST" action="<?php echo e(route('admin.families.destroy', $family)); ?>"
                                  onsubmit="return confirm('Delete this family? Members will be unlinked.')">
                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="text-red-600 hover:underline text-xs">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-400">No families found.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">
            <?php echo e($families->links()); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Admin\Capstone 2\resources\views/admin/families/index.blade.php ENDPATH**/ ?>