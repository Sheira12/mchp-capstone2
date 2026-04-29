<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password — <?php echo e(config('parish.name')); ?></title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css']); ?>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-900 to-indigo-900 flex items-center justify-center p-4">
<div class="w-full max-w-md">

    
    <div class="text-center mb-8">
        <img src="<?php echo e(asset('images/parish-logo.png')); ?>" alt="Parish Logo"
             class="w-20 h-20 rounded-full mx-auto mb-4 object-cover border-4 border-white shadow-lg">
        <h1 class="text-white text-xl font-bold">Mary Help of Christians Parish</h1>
        <p class="text-blue-200 text-sm">Southville 1, Niugan, Cabuyao, Laguna</p>
    </div>

    
    <div class="bg-white rounded-2xl shadow-2xl p-8">

        <div class="text-center mb-6">
            <div class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <h2 class="text-xl font-bold text-gray-900">Set New Password</h2>
            <p class="text-sm text-gray-500 mt-1">Choose a strong password for your account.</p>
        </div>

        <?php if($errors->any()): ?>
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><p><?php echo e($error); ?></p><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('password.update')); ?>" class="space-y-4">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="token" value="<?php echo e($token); ?>">

            <div>
                <label class="form-label">Email Address</label>
                <input type="email" name="email" value="<?php echo e(old('email', $email ?? '')); ?>" required
                       class="form-input w-full <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                       placeholder="your@email.com">
            </div>
            <div>
                <label class="form-label">New Password</label>
                <input type="password" name="password" required minlength="8"
                       class="form-input w-full <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                       placeholder="Minimum 8 characters">
            </div>
            <div>
                <label class="form-label">Confirm New Password</label>
                <input type="password" name="password_confirmation" required
                       class="form-input w-full"
                       placeholder="Repeat new password">
            </div>
            <button type="submit" class="w-full btn-primary py-2.5">
                Reset Password
            </button>
        </form>

        <p class="text-center text-sm text-gray-500 mt-6">
            <a href="<?php echo e(route('login')); ?>" class="text-blue-600 hover:underline">← Back to Sign In</a>
        </p>
    </div>
</div>
</body>
</html>
<?php /**PATH C:\Users\Admin\Capstone 2\resources\views/auth/reset-password.blade.php ENDPATH**/ ?>