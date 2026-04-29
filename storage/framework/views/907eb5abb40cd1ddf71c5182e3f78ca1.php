<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — <?php echo e(config('parish.name')); ?></title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css']); ?>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-900 to-indigo-900 flex items-center justify-center p-4">
<div class="w-full max-w-md">

    
    <div class="text-center mb-8">
        <img src="<?php echo e(asset('images/parish-logo.png')); ?>" alt="Parish Logo" class="w-20 h-20 rounded-full mx-auto mb-4 object-cover border-4 border-white shadow-lg">
        <h1 class="text-white text-xl font-bold">Mary Help of Christians Parish</h1>
        <p class="text-blue-200 text-sm">Southville 1, Niugan, Cabuyao, Laguna</p>
    </div>

    
    <div class="bg-white rounded-2xl shadow-2xl p-8">
        <h2 class="text-xl font-bold text-gray-900 mb-6">Sign In</h2>

        <?php if($errors->any()): ?>
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><p><?php echo e($error); ?></p><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('login')); ?>" class="space-y-4">
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
            <div>
                <label class="form-label">Password</label>
                <input type="password" name="password" required
                       class="form-input w-full"
                       placeholder="••••••••">
            </div>
            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" name="remember" class="rounded border-gray-300 text-blue-600">
                    Remember me
                </label>
                <a href="<?php echo e(route('password.request')); ?>" class="text-sm text-blue-600 hover:underline">Forgot password?</a>
            </div>
            <button type="submit" class="w-full btn-primary py-2.5">Sign In</button>
        </form>

        <p class="text-center text-sm text-gray-500 mt-6">
            Don't have an account?
            <a href="<?php echo e(route('register')); ?>" class="text-blue-600 hover:underline font-medium">Register</a>
        </p>
        <p class="text-center text-sm text-gray-400 mt-2">
            <a href="<?php echo e(route('home')); ?>" class="hover:underline">← Back to Parish Website</a>
        </p>
    </div>
</div>
</body>
</html>
<?php /**PATH C:\Users\Admin\Capstone 2\resources\views/auth/login.blade.php ENDPATH**/ ?>