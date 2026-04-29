<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password — <?php echo e(config('parish.name')); ?></title>
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
            </div>
            <h2 class="text-xl font-bold text-gray-900">Forgot your password?</h2>
            <p class="text-sm text-gray-500 mt-1">Enter your email and we'll send you a reset link.</p>
        </div>

        <?php if(session('status')): ?>
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 rounded-lg px-4 py-3 text-sm flex items-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <?php echo e(session('status')); ?>

        </div>
        <?php endif; ?>

        <?php if($errors->any()): ?>
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><p><?php echo e($error); ?></p><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('password.email')); ?>" class="space-y-4">
            <?php echo csrf_field(); ?>
            <div>
                <label class="form-label">Email Address</label>
                <input type="email" name="email" value="<?php echo e(old('email')); ?>" required autofocus
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
            <button type="submit" class="w-full btn-primary py-2.5">
                Send Reset Link
            </button>
        </form>

        <p class="text-center text-sm text-gray-500 mt-6">
            Remember your password?
            <a href="<?php echo e(route('login')); ?>" class="text-blue-600 hover:underline font-medium">Sign In</a>
        </p>
        <p class="text-center text-sm text-gray-400 mt-2">
            <a href="<?php echo e(route('home')); ?>" class="hover:underline">← Back to Parish Website</a>
        </p>
    </div>
</div>
</body>
</html>
<?php /**PATH C:\Users\Admin\Capstone 2\resources\views/auth/forgot-password.blade.php ENDPATH**/ ?>