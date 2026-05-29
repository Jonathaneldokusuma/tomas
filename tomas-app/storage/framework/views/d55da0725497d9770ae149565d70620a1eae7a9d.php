<?php $__env->startSection('title', 'Riwayat'); ?>
<?php $__env->startSection('content'); ?>
<div class="page-content">
    <!-- Banner kecil -->
    <div class="px-4 pt-4 mb-4">
        <div class="rounded-2xl overflow-hidden bg-gradient-to-r from-blue-600 to-blue-400 p-3 flex items-center gap-3 min-h-[60px]">
            <div class="text-white">
                <div class="font-black text-base leading-tight">SOLUSI JASA<br>TERLENGKAP!</div>
                <div class="text-blue-100 text-xs">Semua Layanan dalam Satu Aplikasi!</div>
            </div>
        </div>
    </div>

    <?php if(session('success')): ?>
    <div class="mx-4 mb-3 bg-green-50 text-green-700 px-4 py-2 rounded-xl text-sm font-medium">
        <?php echo e(session('success')); ?>

    </div>
    <?php endif; ?>

    <!-- Terbaru -->
    <div class="px-4">
        <div class="flex items-center justify-between mb-3">
            <h2 class="font-bold text-navy text-base">Terbaru</h2>
        </div>

        <div class="space-y-3">
            <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-full bg-gray-200 flex-none flex items-center justify-center overflow-hidden">
                        <?php if(isset($order->tukang) && $order->tukang->foto): ?>
                        <img src="<?php echo e(Storage::url($order->tukang->foto)); ?>" alt="" class="w-full h-full object-cover">
                        <?php else: ?>
                        <i class="fas fa-user text-gray-400"></i>
                        <?php endif; ?>
                    </div>
                    <div class="flex-1">
                        <div class="font-semibold text-navy text-sm">
                            <?php echo e($order->tukang->nama ?? 'Tukang'); ?>

                        </div>
                        <div class="text-xs text-gray-400">
                            <?php echo e($order->layanan->nama_layanan ?? '-'); ?>

                        </div>
                    </div>
                    <div class="text-right">
                        <?php if($order->review): ?>
                        <span class="text-xs text-yellow-500 font-semibold">
                            <?php for($s=1;$s<=5;$s++): ?><i class="fas fa-star<?php echo e($s <= $order->review->rating ? '' : '-half-alt'); ?> text-xs"></i><?php endfor; ?>
                        </span>
                        <?php else: ?>
                        <span class="text-xs text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">Belum direview</span>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if($order->review && $order->review->komentar): ?>
                <div class="text-xs text-gray-500 italic bg-gray-50 rounded-xl px-3 py-2 mb-3 border border-gray-100">
                    "<?php echo e($order->review->komentar); ?>"
                </div>
                <?php endif; ?>

                <div class="flex gap-2 border-t border-gray-100 pt-3">
                    <?php if(!$order->review): ?>
                    <a href="<?php echo e(route('review.create', $order->id_order)); ?>"
                       class="flex items-center gap-1 text-primary text-xs font-semibold">
                        <i class="fas fa-star text-xs"></i> Rate &amp; tip
                    </a>
                    <span class="text-gray-300 mx-1">|</span>
                    <?php endif; ?>
                    <a href="<?php echo e(route('chat.show', $order->tukang->id_tukang ?? 0)); ?>"
                       class="flex items-center gap-1 text-blue-500 text-xs font-semibold">
                        <i class="fas fa-comment text-xs"></i> Chat
                    </a>
                    <span class="text-gray-300 mx-1">|</span>
                    <form action="<?php echo e(route('orders.reorder', $order->id_order)); ?>" method="POST" class="inline">
                        <?php echo csrf_field(); ?>
                        <button type="submit"
                                class="flex items-center gap-1 text-primary text-xs font-semibold bg-transparent border-0 p-0 cursor-pointer">
                            <i class="fas fa-redo text-xs"></i> Sewa lagi
                        </button>
                    </form>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="flex flex-col items-center py-10 text-gray-400">
                <i class="fas fa-clipboard-list text-5xl mb-3 opacity-20"></i>
                <p class="text-sm font-medium">Belum ada riwayat pesanan</p>
                <a href="<?php echo e(route('tukang.index')); ?>"
                   class="mt-4 bg-blue-600 text-white text-sm font-bold px-6 py-2.5 rounded-full">
                    Pesan Sekarang
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php echo $__env->make('partials.bottom-nav', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\laravel web app\tomas-app\resources\views\riwayat.blade.php ENDPATH**/ ?>