<?php $__env->startSection('title', 'Profile'); ?>
<?php $__env->startSection('content'); ?>
<div class="page-content">
    <!-- Blue Header -->
    <div class="bg-primary px-4 pt-10 pb-6">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-full bg-white/30 flex items-center justify-center flex-none">
                <i class="fas fa-user text-white text-3xl"></i>
            </div>
            <div class="flex-1">
                <div class="text-white font-bold text-xl">
                    <?php echo e(session('user_nama') ?? 'Username'); ?>

                </div>
                <div class="text-blue-100 text-xs"><?php echo e(session('user_hp') ?? '+62 xxx'); ?></div>
            </div>
            <button class="bg-white text-primary text-xs font-bold px-4 py-1.5 rounded-full">
                Profile
            </button>
        </div>
    </div>

    <!-- Cards -->
    <div class="px-4 -mt-4">
        <div class="bg-white rounded-2xl shadow-sm p-4 mb-4">
            <div class="grid grid-cols-3 gap-3">
                <!-- QRIS -->
                <div class="flex flex-col items-center gap-1">
                    <div class="w-12 h-12 bg-green-50 rounded-xl flex items-center justify-center">
                        <i class="fas fa-qrcode text-green-600 text-2xl"></i>
                    </div>
                    <span class="text-xs font-semibold text-navy">Qris</span>
                    <span class="text-xs text-gray-400">Scan &amp; Pay</span>
                </div>
                <!-- Placeholder -->
                <div class="flex flex-col items-center gap-1">
                    <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center">
                        <i class="fas fa-wallet text-blue-500 text-2xl"></i>
                    </div>
                    <span class="text-xs font-semibold text-navy">Dompet</span>
                    <span class="text-xs text-gray-400">Saldo</span>
                </div>
                <!-- Placeholder -->
                <div class="flex flex-col items-center gap-1">
                    <div class="w-12 h-12 bg-orange-50 rounded-xl flex items-center justify-center">
                        <i class="fas fa-gift text-orange-500 text-2xl"></i>
                    </div>
                    <span class="text-xs font-semibold text-navy">Promo</span>
                    <span class="text-xs text-gray-400">Voucher</span>
                </div>
            </div>
        </div>

        <!-- General Menu -->
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden mb-4">
            <div class="px-4 pt-3 pb-1">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">General</span>
            </div>
            <?php $__currentLoopData = [
                ['icon' => 'fas fa-heart', 'color' => 'text-red-500', 'label' => 'Favorit'],
                ['icon' => 'fas fa-credit-card', 'color' => 'text-blue-500', 'label' => 'Metode Pembayaran'],
                ['icon' => 'fas fa-cog', 'color' => 'text-gray-500', 'label' => 'Pengaturan'],
                ['icon' => 'fas fa-shield-alt', 'color' => 'text-green-500', 'label' => 'Privasi & Keamanan'],
            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $menu): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a href="#" class="flex items-center justify-between px-4 py-3.5 border-t border-gray-50 hover:bg-gray-50">
                <div class="flex items-center gap-3">
                    <i class="<?php echo e($menu['icon']); ?> <?php echo e($menu['color']); ?> w-4 text-center"></i>
                    <span class="text-sm text-navy"><?php echo e($menu['label']); ?></span>
                </div>
                <i class="fas fa-chevron-right text-gray-300 text-xs"></i>
            </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <!-- Logout -->
        <a href="<?php echo e(route('logout')); ?>"
            class="block w-full text-center bg-red-50 text-red-500 font-semibold py-3.5 rounded-2xl text-sm hover:bg-red-100 transition mb-4"
            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            Keluar
        </a>
        <form id="logout-form" action="<?php echo e(route('logout')); ?>" method="POST" class="hidden"><?php echo csrf_field(); ?></form>
    </div>
</div>

<?php echo $__env->make('partials.bottom-nav', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\laravel web app\tomas-app\resources\views\profile.blade.php ENDPATH**/ ?>