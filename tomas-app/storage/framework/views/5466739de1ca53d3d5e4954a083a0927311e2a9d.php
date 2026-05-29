<?php $__env->startSection('title', 'Notifikasi'); ?>
<?php $__env->startSection('content'); ?>
<div class="page-content bg-gray-50">

    <!-- Header -->
    <div class="flex items-center gap-3 px-4 pt-5 pb-3 bg-white border-b border-gray-100 sticky top-0 z-10">
        <a href="<?php echo e(url()->previous()); ?>" class="w-9 h-9 rounded-full flex items-center justify-center bg-gray-100">
            <i class="fas fa-arrow-left text-navy text-sm"></i>
        </a>
        <h1 class="font-bold text-navy text-lg flex-1">Notifikasi</h1>
    </div>

    <div class="px-4 pt-4 pb-24 space-y-2">
        <?php $__empty_1 = true; $__currentLoopData = $notifs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notif): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php
            $icons = [
                'favorit' => ['icon' => 'fas fa-heart',         'bg' => 'bg-red-100',    'color' => 'text-red-500'],
                'order'   => ['icon' => 'fas fa-shopping-bag',  'bg' => 'bg-blue-100',   'color' => 'text-blue-500'],
                'chat'    => ['icon' => 'fas fa-comment-dots',  'bg' => 'bg-green-100',  'color' => 'text-green-500'],
                'review'  => ['icon' => 'fas fa-star',          'bg' => 'bg-yellow-100', 'color' => 'text-yellow-500'],
                'info'    => ['icon' => 'fas fa-bell',          'bg' => 'bg-gray-100',   'color' => 'text-gray-500'],
            ];
            $style = $icons[$notif->tipe] ?? $icons['info'];
        ?>
        <div class="bg-white rounded-2xl p-4 shadow-sm border <?php echo e($notif->dibaca ? 'border-gray-100' : 'border-blue-200'); ?> flex items-start gap-3">
            <div class="w-10 h-10 rounded-full <?php echo e($style['bg']); ?> flex items-center justify-center flex-none">
                <i class="<?php echo e($style['icon']); ?> <?php echo e($style['color']); ?> text-base"></i>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-2">
                    <p class="font-semibold text-navy text-sm leading-snug"><?php echo e($notif->judul); ?></p>
                    <?php if(!$notif->dibaca): ?>
                    <span class="w-2 h-2 rounded-full bg-blue-500 flex-none mt-1.5"></span>
                    <?php endif; ?>
                </div>
                <p class="text-xs text-gray-500 mt-0.5 leading-relaxed"><?php echo e($notif->pesan); ?></p>
                <p class="text-xs text-gray-400 mt-1.5">
                    <?php echo e($notif->created_at ? $notif->created_at->diffForHumans() : '-'); ?>

                </p>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="flex flex-col items-center py-16 text-gray-400">
            <i class="fas fa-bell text-6xl mb-4 opacity-20"></i>
            <p class="text-sm font-medium">Belum ada notifikasi</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php echo $__env->make('partials.bottom-nav', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\laravel web app\tomas-app\resources\views\notifikasi\index.blade.php ENDPATH**/ ?>