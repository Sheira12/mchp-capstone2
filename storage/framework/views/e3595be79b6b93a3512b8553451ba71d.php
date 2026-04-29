

<?php $__env->startSection('title', 'Parishioners'); ?>
<?php $__env->startSection('page-title', 'Parishioners'); ?>

<?php $__env->startSection('content'); ?>
<div class="py-6 space-y-5">

    
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-sm text-gray-500"><?php echo e($parishioners->total()); ?> parishioners found</p>
        </div>
        <div class="flex gap-2">
            <a href="<?php echo e(route('admin.families.create')); ?>" class="btn-secondary text-sm">+ New Family</a>
            <a href="<?php echo e(route('admin.parishioners.create')); ?>" class="btn-primary text-sm">+ Add Parishioner</a>
        </div>
    </div>

    
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <form method="GET" class="flex flex-wrap gap-3">
            <div class="flex-1 min-w-48">
                <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Search by name, email, phone..." class="form-input w-full text-sm">
            </div>
            <select name="barangay" class="form-select text-sm">
                <option value="">All Barangays</option>
                <?php $__currentLoopData = $barangays; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($b); ?>" <?php echo e(request('barangay') === $b ? 'selected' : ''); ?>><?php echo e($b); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <select name="family_id" class="form-select text-sm">
                <option value="">All Families</option>
                <?php $__currentLoopData = $families; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($f->id); ?>" <?php echo e(request('family_id') == $f->id ? 'selected' : ''); ?>><?php echo e($f->family_name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <select name="sacrament" class="form-select text-sm">
                <option value="">Any Sacrament</option>
                <?php $__currentLoopData = \App\Models\SacramentalRecord::TYPES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($key); ?>" <?php echo e(request('sacrament') === $key ? 'selected' : ''); ?>>Has <?php echo e($label); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <button type="submit" class="btn-primary text-sm">Search</button>
            <?php if(request()->hasAny(['search', 'barangay', 'family_id', 'sacrament'])): ?>
            <a href="<?php echo e(route('admin.parishioners.index')); ?>" class="btn-secondary text-sm">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Name</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Contact</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Barangay</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Family</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Sacraments</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Status</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php $__empty_1 = true; $__currentLoopData = $parishioners; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $parishioner): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <?php if($parishioner->photo_path): ?>
                                <img src="<?php echo e(Storage::url($parishioner->photo_path)); ?>" alt="" class="w-8 h-8 rounded-full object-cover">
                                <?php else: ?>
                                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-semibold text-xs">
                                    <?php echo e(substr($parishioner->first_name, 0, 1)); ?><?php echo e(substr($parishioner->last_name, 0, 1)); ?>

                                </div>
                                <?php endif; ?>
                                <div>
                                    <a href="<?php echo e(route('admin.parishioners.show', $parishioner)); ?>" class="font-medium text-gray-900 hover:text-blue-700">
                                        <?php echo e($parishioner->full_name); ?>

                                    </a>
                                    <?php if($parishioner->is_head_of_family): ?>
                                    <span class="ml-1 text-xs text-blue-600">(Head)</span>
                                    <?php endif; ?>
                                    <?php if($parishioner->birthdate): ?>
                                    <p class="text-xs text-gray-400">Age <?php echo e($parishioner->age); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            <p><?php echo e($parishioner->contact_number ?? '—'); ?></p>
                            <p class="text-xs text-gray-400"><?php echo e($parishioner->email ?? ''); ?></p>
                        </td>
                        <td class="px-4 py-3 text-gray-600"><?php echo e($parishioner->barangay ?? '—'); ?></td>
                        <td class="px-4 py-3 text-gray-600">
                            <?php if($parishioner->family): ?>
                            <a href="<?php echo e(route('admin.families.show', $parishioner->family)); ?>" class="hover:text-blue-700"><?php echo e($parishioner->family->family_name); ?></a>
                            <?php else: ?>
                            <span class="text-gray-400">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-gray-600"><?php echo e($parishioner->sacramental_records_count); ?></span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium <?php echo e($parishioner->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'); ?>">
                                <?php echo e($parishioner->is_active ? 'Active' : 'Inactive'); ?>

                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="<?php echo e(route('admin.parishioners.show', $parishioner)); ?>" class="text-blue-600 hover:text-blue-800 text-xs">View</a>
                                <a href="<?php echo e(route('admin.parishioners.edit', $parishioner)); ?>" class="text-gray-500 hover:text-gray-700 text-xs">Edit</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-gray-400">
                            <svg class="w-12 h-12 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            No parishioners found.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($parishioners->hasPages()): ?>
        <div class="px-4 py-3 border-t border-gray-100">
            <?php echo e($parishioners->links()); ?>

        </div>
        <?php endif; ?>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Admin\Capstone 2\resources\views/admin/parishioners/index.blade.php ENDPATH**/ ?>