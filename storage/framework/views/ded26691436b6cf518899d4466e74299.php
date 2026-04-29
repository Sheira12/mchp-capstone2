

<?php $__env->startSection('title', 'Users'); ?>
<?php $__env->startSection('page-title', 'Users'); ?>

<?php $__env->startSection('content'); ?>
<div class="py-6 space-y-4">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <form method="GET" class="flex gap-2">
            <input type="text" name="search" value="<?php echo e(request('search')); ?>"
                   class="form-input text-sm w-64" placeholder="Search name or email…">
            <button type="submit" class="btn-secondary text-sm">Search</button>
            <?php if(request('search')): ?>
                <a href="<?php echo e(route('admin.users.index')); ?>" class="btn-secondary text-sm">Clear</a>
            <?php endif; ?>
        </form>
        <a href="<?php echo e(route('admin.users.create')); ?>" class="btn-primary text-sm">+ New User</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500">
                    <th class="px-4 py-3 font-medium">Name</th>
                    <th class="px-4 py-3 font-medium">Email</th>
                    <th class="px-4 py-3 font-medium">Role</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3 font-medium">Joined</th>
                    <th class="px-4 py-3 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-sm font-bold text-blue-700">
                                <?php echo e(substr($user->name, 0, 1)); ?>

                            </div>
                            <span class="font-medium text-gray-900"><?php echo e($user->name); ?></span>
                            <?php if($user->id === auth()->id()): ?>
                                <span class="text-xs text-blue-500">(you)</span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-600"><?php echo e($user->email); ?></td>
                    <td class="px-4 py-3">
                        <?php $__currentLoopData = $user->roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                            <?php echo e(str_replace('_', ' ', $role->name)); ?>

                        </span>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </td>
                    <td class="px-4 py-3">
                        <?php if($user->is_active ?? true): ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                        <?php else: ?>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs"><?php echo e($user->created_at->format('M d, Y')); ?></td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <a href="<?php echo e(route('admin.users.edit', $user)); ?>" class="text-blue-600 hover:underline text-xs">Edit</a>
                            <?php if($user->id !== auth()->id()): ?>
                            <form method="POST" action="<?php echo e(route('admin.users.toggle-active', $user)); ?>">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="text-amber-600 hover:underline text-xs">
                                    <?php echo e(($user->is_active ?? true) ? 'Deactivate' : 'Activate'); ?>

                                </button>
                            </form>
                            <form method="POST" action="<?php echo e(route('admin.users.destroy', $user)); ?>"
                                  onsubmit="return confirm('Delete this user?')">
                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="text-red-600 hover:underline text-xs">Delete</button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-400">No users found.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">
            <?php echo e($users->links()); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Admin\Capstone 2\resources\views/admin/users/index.blade.php ENDPATH**/ ?>