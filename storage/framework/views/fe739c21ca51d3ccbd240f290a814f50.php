

<?php $__env->startSection('title', 'Announcements'); ?>
<?php $__env->startSection('page-title', 'Announcements'); ?>

<?php $__env->startSection('content'); ?>
<div class="py-6 space-y-4">

    <div class="flex justify-end">
        <a href="<?php echo e(route('admin.announcements.create')); ?>" class="btn-primary text-sm">+ New Announcement</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500">
                    <th class="px-4 py-3 font-medium">Title</th>
                    <th class="px-4 py-3 font-medium">Category</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3 font-medium">Published</th>
                    <th class="px-4 py-3 font-medium">Expires</th>
                    <th class="px-4 py-3 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php $__empty_1 = true; $__currentLoopData = $announcements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ann): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <?php if($ann->image_path): ?>
                                <img src="<?php echo e(Storage::url($ann->image_path)); ?>" class="w-10 h-10 rounded object-cover flex-shrink-0">
                            <?php endif; ?>
                            <div>
                                <p class="font-medium text-gray-900"><?php echo e($ann->title); ?></p>
                                <p class="text-xs text-gray-400 mt-0.5">by <?php echo e($ann->createdBy?->name); ?></p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            <?php echo e($ann->category); ?>

                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <?php if($ann->published_at): ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Published</span>
                        <?php else: ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Draft</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">
                        <?php echo e($ann->published_at ? $ann->published_at->format('M d, Y') : '—'); ?>

                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">
                        <?php if($ann->expires_at): ?>
                            <span class="<?php echo e($ann->expires_at->isPast() ? 'text-red-500' : ''); ?>">
                                <?php echo e($ann->expires_at->format('M d, Y')); ?>

                            </span>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <a href="<?php echo e(route('admin.announcements.edit', $ann)); ?>" class="text-blue-600 hover:underline text-xs">Edit</a>
                            <form method="POST" action="<?php echo e(route('admin.announcements.destroy', $ann)); ?>"
                                  onsubmit="return confirm('Delete this announcement?')">
                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="text-red-600 hover:underline text-xs">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-400">No announcements yet.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">
            <?php echo e($announcements->links()); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Admin\Capstone 2\resources\views/admin/announcements/index.blade.php ENDPATH**/ ?>