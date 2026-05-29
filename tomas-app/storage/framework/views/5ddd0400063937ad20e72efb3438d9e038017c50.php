<?php $__env->startSection('title', 'Masuk'); ?>
<?php $__env->startSection('content'); ?>
<div class="min-h-screen flex flex-col items-center justify-center px-8 py-12">
    <!-- Logo -->
    <div class="flex flex-col items-center mb-10">
        <div class="logo-full">
            <img src="<?php echo e(asset('images/tomas-logo.png')); ?>" alt="Tomas" class="h-24 object-contain">
        </div>
    </div>

    <!-- Form -->
    <form action="<?php echo e(route('login.post')); ?>" method="POST" class="w-full space-y-4">
        <?php echo csrf_field(); ?>
        <?php if(session('error')): ?>
            <div class="bg-red-50 border border-red-200 text-red-600 text-sm rounded-xl px-4 py-3">
                <?php echo e(session('error')); ?>

            </div>
        <?php endif; ?>

        <div class="border-b border-gray-300 pb-1">
            <input type="tel" name="no_hp" placeholder="Masukan No. Telp"
                value="<?php echo e(old('no_hp')); ?>"
                class="w-full text-sm text-gray-700 outline-none bg-transparent placeholder-gray-400"
                inputmode="numeric" pattern="[0-9]*" maxlength="15"
                oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                required>
        </div>

        <div class="border-b border-gray-300 pb-1">
            <input type="password" name="password" placeholder="Masukan Password"
                class="w-full text-sm text-gray-700 outline-none bg-transparent placeholder-gray-400"
                required>
        </div>

        <div class="flex items-center justify-between pt-2">
            <a href="#" class="text-xs text-red-500">Forgot?</a>
            <button type="submit"
                class="bg-navy text-white text-sm font-semibold px-8 py-2.5 rounded-full hover:bg-opacity-90 transition">
                Sign Now
            </button>
        </div>
    </form>

    <!-- Daftar -->
    <div class="mt-8 text-center">
        <span class="text-xs text-gray-500">Belum punya akun? </span>
        <a href="<?php echo e(route('register')); ?>" class="text-xs font-semibold text-primary">Daftar</a>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\laravel web app\tomas-app\resources\views\auth\login.blade.php ENDPATH**/ ?>