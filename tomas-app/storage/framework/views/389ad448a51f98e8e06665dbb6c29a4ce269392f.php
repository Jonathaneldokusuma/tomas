<?php $__env->startSection('title', isset($aktifFilter) && $aktifFilter ? $aktifFilter : 'Semua Tukang'); ?>
<?php $__env->startSection('content'); ?>
<div class="page-content">
    <!-- Header -->
    <div class="bg-primary px-4 py-4 flex items-center gap-3">
        <a href="<?php echo e(route('home')); ?>" class="text-white">
            <i class="fas fa-chevron-left text-lg"></i>
        </a>
        <h1 class="text-white font-bold text-lg flex-1 text-center pr-5">
            <?php echo e(isset($aktifFilter) && $aktifFilter ? $aktifFilter : 'Semua Tukang'); ?>

        </h1>
    </div>

    <!-- Search -->
    <div class="px-4 pt-3 pb-1">
        <div class="flex items-center gap-2 bg-gray-100 rounded-full px-4 py-2.5">
            <i class="fas fa-search text-gray-400 text-sm"></i>
            <input id="searchInput" type="text" placeholder="Cari tukang..."
                class="bg-transparent text-sm text-gray-500 outline-none flex-1 placeholder-gray-400">
        </div>
    </div>

    <!-- Kategori Chips -->
    <?php $allLayananChips = isset($allLayanan) ? $allLayanan : (isset($layanan) ? \App\Models\Layanan::all() : \App\Models\Layanan::all()); ?>
    <div class="flex gap-2 px-4 py-3 overflow-x-auto scrollbar-hide">
        <a href="<?php echo e(route('tukang.index')); ?>"
           class="flex-none text-xs font-semibold px-4 py-1.5 rounded-full border-2 transition-all
                  <?php echo e(!isset($aktifFilter) || !$aktifFilter ? 'bg-blue-600 border-blue-600 text-white' : 'border-gray-200 text-gray-500 bg-white'); ?>">
            Semua
        </a>
        <?php $__currentLoopData = $allLayananChips; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chip): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a href="<?php echo e(route('tukang.index', ['kategori' => $chip->nama_layanan])); ?>"
           class="flex-none text-xs font-semibold px-4 py-1.5 rounded-full border-2 transition-all
                  <?php echo e((isset($aktifFilter) && $aktifFilter === $chip->nama_layanan) ? 'bg-blue-600 border-blue-600 text-white' : 'border-gray-200 text-gray-500 bg-white'); ?>">
            <?php echo e($chip->nama_layanan); ?>

        </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <!-- Tukang List -->
    <div class="px-4 space-y-3 pb-24" id="tukangList">
        <?php $__empty_1 = true; $__currentLoopData = $tukangList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tukang): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <a href="<?php echo e(route('tukang.show', $tukang->id_tukang)); ?>"
            class="tukang-item flex items-center gap-3 bg-white rounded-2xl border border-gray-100 shadow-sm p-3 hover:shadow-md transition">
            <div class="w-16 h-16 rounded-xl bg-gray-200 flex-none flex items-center justify-center overflow-hidden">
                <?php if($tukang->foto): ?>
                <img src="<?php echo e(Storage::url($tukang->foto)); ?>" alt="<?php echo e($tukang->nama); ?>" class="w-full h-full object-cover">
                <?php else: ?>
                <i class="fas fa-user text-gray-400 text-2xl"></i>
                <?php endif; ?>
            </div>
            <div class="flex-1 min-w-0">
                <div class="font-semibold text-navy text-sm truncate"><?php echo e($tukang->nama); ?></div>
                <div class="text-xs text-primary font-medium mt-0.5 truncate"><?php echo e($tukang->kategori ?? ''); ?></div>
                <div class="flex items-center gap-1 mt-1">
                    <i class="fas fa-star text-yellow-400 text-xs"></i>
                    <span class="text-xs text-gray-500">4.7</span>
                </div>
                <div class="flex items-center gap-1 mt-0.5">
                    <i class="fas fa-location-dot text-red-400 text-xs"></i>
                    <span class="text-xs text-gray-400 truncate"><?php echo e($tukang->lokasi ?? 'Surakarta'); ?></span>
                </div>
            </div>
            <div class="flex flex-col items-end gap-1 flex-none">
                <span class="text-xs <?php echo e($tukang->status_aktif ? 'text-green-500' : 'text-gray-400'); ?> font-semibold">
                    <?php echo e($tukang->status_aktif ? '● Aktif' : '● Offline'); ?>

                </span>
                <span class="bg-primary text-white text-xs px-3 py-1 rounded-full font-medium mt-1">Pesan</span>
            </div>
        </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="flex flex-col items-center justify-center py-16 text-gray-400">
            <i class="fas fa-hard-hat text-5xl mb-3 opacity-30"></i>
            <p class="text-sm">Tidak ada tukang untuk kategori ini</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
// Live search filter
const searchInput = document.getElementById('searchInput');
if (searchInput) {
    searchInput.addEventListener('input', function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll('.tukang-item').forEach(el => {
            const text = el.textContent.toLowerCase();
            el.style.display = text.includes(q) ? '' : 'none';
        });
    });
}
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('partials.bottom-nav', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\laravel web app\tomas-app\resources\views\tukang\index.blade.php ENDPATH**/ ?>