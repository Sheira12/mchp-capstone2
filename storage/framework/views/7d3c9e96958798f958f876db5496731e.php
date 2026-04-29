

<?php $__env->startSection('title', 'Sacramental Records'); ?>
<?php $__env->startSection('page-title', 'Sacramental Records'); ?>

<?php $__env->startSection('content'); ?>
<div class="py-6 space-y-4">

    
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Search</label>
                <input type="text" name="search" value="<?php echo e(request('search')); ?>"
                       class="form-input text-sm w-48" placeholder="Parishioner name…">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Type</label>
                <select name="type" class="form-select text-sm">
                    <option value="">All Types</option>
                    <option value="baptism" <?php if(request('type') === 'baptism'): echo 'selected'; endif; ?>>Baptism</option>
                    <option value="first_communion" <?php if(request('type') === 'first_communion'): echo 'selected'; endif; ?>>First Communion</option>
                    <option value="confirmation" <?php if(request('type') === 'confirmation'): echo 'selected'; endif; ?>>Confirmation</option>
                    <option value="marriage" <?php if(request('type') === 'marriage'): echo 'selected'; endif; ?>>Marriage</option>
                    <option value="death_burial" <?php if(request('type') === 'death_burial'): echo 'selected'; endif; ?>>Death/Burial</option>
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
            <?php if(request()->hasAny(['search','type','date_from','date_to'])): ?>
                <a href="<?php echo e(route('admin.sacramental-records.index')); ?>" class="btn-secondary text-sm">Clear</a>
            <?php endif; ?>
            <div class="ml-auto">
                <a href="<?php echo e(route('admin.sacramental-records.create')); ?>" class="btn-primary text-sm">+ New Record</a>
            </div>
        </form>
    </div>

    
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500">
                    <th class="px-4 py-3 font-medium">Parishioner</th>
                    <th class="px-4 py-3 font-medium">Type</th>
                    <th class="px-4 py-3 font-medium">Date</th>
                    <th class="px-4 py-3 font-medium">Celebrant</th>
                    <th class="px-4 py-3 font-medium">Register #</th>
                    <th class="px-4 py-3 font-medium">Verified</th>
                    <th class="px-4 py-3 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php $__empty_1 = true; $__currentLoopData = $records; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $typeColors = [
                        'baptism' => 'blue', 'first_communion' => 'green',
                        'confirmation' => 'purple', 'marriage' => 'pink', 'death_burial' => 'gray'
                    ];
                    $typeLabels = [
                        'baptism' => 'Baptism', 'first_communion' => 'First Communion',
                        'confirmation' => 'Confirmation', 'marriage' => 'Marriage', 'death_burial' => 'Death/Burial'
                    ];
                    $color = $typeColors[$record->type] ?? 'gray';
                ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900">
                        <a href="<?php echo e(route('admin.sacramental-records.show', $record)); ?>" class="hover:text-blue-700">
                            <?php echo e($record->parishioner->full_name); ?>

                        </a>
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-<?php echo e($color); ?>-100 text-<?php echo e($color); ?>-800">
                            <?php echo e($typeLabels[$record->type] ?? $record->type); ?>

                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-600"><?php echo e($record->date_administered->format('M d, Y')); ?></td>
                    <td class="px-4 py-3 text-gray-600"><?php echo e($record->celebrant); ?></td>
                    <td class="px-4 py-3 text-gray-500"><?php echo e($record->register_number ?? '—'); ?></td>
                    <td class="px-4 py-3">
                        <?php if($record->verified_at): ?>
                            <span class="text-green-600 text-xs">✓ Verified</span>
                        <?php else: ?>
                            <span class="text-gray-400 text-xs">Pending</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <a href="<?php echo e(route('admin.sacramental-records.show', $record)); ?>" class="text-blue-600 hover:underline text-xs">View</a>
                            <a href="<?php echo e(route('admin.sacramental-records.edit', $record)); ?>" class="text-gray-600 hover:underline text-xs">Edit</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-gray-400">No records found.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">
            <?php echo e($records->links()); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Admin\Capstone 2\resources\views/admin/sacramental-records/index.blade.php ENDPATH**/ ?>