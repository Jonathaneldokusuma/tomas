<?php $__env->startSection('title', 'Home'); ?>

<?php $__env->startPush('styles'); ?>
<style>
.category-card { transition: transform .15s; }
.category-card:active { transform: scale(.93); }
.tukang-card { transition: box-shadow .15s; }
.tukang-card:hover { box-shadow: 0 4px 18px rgba(0,122,255,.15); }
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="page-content bg-white">
    <!-- Header -->
    <div class="flex items-center justify-between px-4 pt-5 pb-3 bg-white">
        <!-- Logo kiri: icon + text -->
        <div class="flex items-center gap-1.5">
            <img src="<?php echo e(asset('images/tomas-logo.png')); ?>" alt="Tomas" class="h-10 w-10 object-contain">
            <span class="text-navy font-black text-base tracking-wide" style="color:#1A2B47">tomas</span>
        </div>
        <!-- Lokasi tengah -->
        <div class="flex items-center gap-1 text-sm font-semibold" style="color:#1A2B47">
            <i class="fas fa-location-dot text-red-500 text-sm"></i>
            <span>Surakarta</span>
            <i class="fas fa-chevron-down text-xs text-gray-400 ml-0.5"></i>
        </div>
        <!-- Ikon kanan -->
        <div class="flex items-center gap-3 text-xl">
            <a href="<?php echo e(route('favorit.index')); ?>" class="relative text-gray-500">
                <i class="far fa-heart"></i>
            </a>
            <a href="<?php echo e(route('notifikasi.index')); ?>" class="relative text-gray-500" id="bell-btn">
                <i class="far fa-bell"></i>
                <span id="notif-badge"
                      class="absolute -top-1.5 -right-1.5 bg-red-500 text-white text-[9px] font-bold rounded-full min-w-[16px] h-4 flex items-center justify-center px-0.5 hidden">
                    0
                </span>
            </a>
        </div>
    </div>

    <!-- Search -->
    <div class="px-4 pb-3 bg-white">
        <div class="flex items-center gap-2 bg-gray-100 rounded-full px-4 py-2.5">
            <i class="fas fa-search text-gray-400 text-sm"></i>
            <input type="text" placeholder="Cari jasa yang anda inginkan"
                class="bg-transparent text-sm text-gray-500 outline-none flex-1 placeholder-gray-400">
        </div>
    </div>

    <!-- Banner -->
    <div class="px-4 mb-5">
        <div class="rounded-2xl overflow-hidden relative min-h-[130px]"
            style="background: linear-gradient(135deg, #1565C0 0%, #42A5F5 100%);">
            <!-- Konten kiri -->
            <div class="relative z-10 p-4 pr-28">
                <div class="flex items-center gap-1.5 mb-2">
                    <img src="<?php echo e(asset('images/tomas-logo.png')); ?>" alt="Tomas" class="h-7 object-contain brightness-0 invert">
                    <span class="text-white font-bold text-sm">tomas</span>
                </div>
                <div class="text-white font-black text-xl leading-tight">SOLUSI JASA<br>TERLENGKAP!</div>
                <div class="text-blue-100 text-xs mt-1 leading-snug">Semua Layanan dalam<br>Satu Aplikasi!</div>
                <button class="mt-2.5 bg-orange-500 text-white text-xs font-black px-4 py-2 rounded-full uppercase tracking-wide shadow">
                    PESAN SEKARANG
                </button>
            </div>
            <!-- Dekorasi kanan: ponsel + spider -->
            <div class="absolute right-3 top-0 bottom-0 flex flex-col items-center justify-center gap-1 opacity-90">
                <div class="text-5xl leading-none" style="filter: drop-shadow(0 2px 4px rgba(0,0,0,.3))">🕷️</div>
                <div class="bg-white/20 rounded-2xl p-2 backdrop-blur-sm">
                    <i class="fas fa-mobile-alt text-white text-4xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Kategori Layanan -->
    <div class="px-4 mb-5">
        <?php
        $iconMap = [
            'tukang'       => ['icon' => 'fas fa-screwdriver-wrench', 'color' => 'text-orange-500', 'bg' => 'bg-orange-50'],
            'antar'        => ['icon' => 'fas fa-motorcycle',         'color' => 'text-blue-500',   'bg' => 'bg-blue-50'],
            'foto'         => ['icon' => 'fas fa-camera',             'color' => 'text-purple-500', 'bg' => 'bg-purple-50'],
            'baby'         => ['icon' => 'fas fa-baby',               'color' => 'text-pink-500',   'bg' => 'bg-pink-50'],
            'latih'        => ['icon' => 'fas fa-dumbbell',           'color' => 'text-green-500',  'bg' => 'bg-green-50'],
            'servis'       => ['icon' => 'fas fa-snowflake',          'color' => 'text-cyan-500',   'bg' => 'bg-cyan-50'],
            'default'      => ['icon' => 'fas fa-bolt',               'color' => 'text-yellow-500', 'bg' => 'bg-yellow-50'],
        ];
        function getIcon($name, $map) {
            $name = strtolower($name);
            foreach ($map as $key => $val) {
                if ($key !== 'default' && str_contains($name, $key)) return $val;
            }
            return $map['default'];
        }
        ?>

        <div class="flex items-start justify-between gap-1 overflow-x-auto scrollbar-hide pb-1">
            <?php $__currentLoopData = $layanan->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php $ic = getIcon($item->nama_layanan, $iconMap); ?>
            <a href="<?php echo e(route('web.layanan.show', $item->id_layanan)); ?>"
                class="category-card flex flex-col items-center gap-1.5 flex-none w-16">
                <div class="w-14 h-14 <?php echo e($ic['bg']); ?> rounded-2xl flex items-center justify-center shadow-sm">
                    <i class="<?php echo e($ic['icon']); ?> <?php echo e($ic['color']); ?> text-2xl"></i>
                </div>
                <span class="text-xs text-navy font-medium text-center leading-tight" style="color:#1A2B47">
                    <?php echo e($item->nama_layanan); ?>

                </span>
            </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    <!-- Section: Tukang per Layanan -->
    <?php $__currentLoopData = $layanan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $layananItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php
        $tukangPerLayanan = $tukangList->filter(
            fn($t) => strtolower(trim($t->kategori ?? '')) === strtolower(trim($layananItem->nama_layanan))
        );
    ?>
    <?php if($tukangPerLayanan->isNotEmpty()): ?>
    <div class="mb-5">
        <div class="flex items-center justify-between px-4 mb-3">
            <h2 class="font-bold text-lg" style="color:#1A2B47"><?php echo e($layananItem->nama_layanan); ?></h2>
            <a href="<?php echo e(route('web.layanan.show', $layananItem->id_layanan)); ?>"
                class="text-primary text-xs font-semibold">Lihat Semua</a>
        </div>
        <div class="flex gap-3 overflow-x-auto px-4 pb-1 scrollbar-hide">
            <?php $__currentLoopData = $tukangPerLayanan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tukang): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php $isFav = session('user_id') ? $tukang->isFavoritedBy(session('user_id')) : false; ?>
            <div class="tukang-card flex-none w-36 rounded-2xl overflow-hidden border border-gray-100 bg-white shadow-sm relative">
                <!-- Heart button -->
                <button class="fav-btn absolute top-2 right-2 z-10 w-7 h-7 rounded-full bg-white/80 flex items-center justify-center shadow-sm"
                        data-id="<?php echo e($tukang->id_tukang); ?>" title="Favorit">
                    <i class="<?php echo e($isFav ? 'fas' : 'far'); ?> fa-heart text-red-500 text-sm"></i>
                </button>
                <a href="<?php echo e(route('tukang.show', $tukang->id_tukang)); ?>">
                <!-- Foto placeholder -->
                <div class="w-full h-28 bg-gray-200 flex items-center justify-center overflow-hidden">
                    <?php if($tukang->foto): ?>
                    <img src="<?php echo e(Storage::url($tukang->foto)); ?>" alt="<?php echo e($tukang->nama); ?>" class="w-full h-full object-cover">
                    <?php else: ?>
                    <i class="fas fa-user text-4xl text-gray-400"></i>
                    <?php endif; ?>
                </div>
                <div class="p-2.5">
                    <div class="font-bold text-sm truncate" style="color:#1A2B47"><?php echo e($tukang->nama); ?></div>
                    <div class="text-xs text-gray-400 truncate"><?php echo e($tukang->kategori ?? ''); ?></div>
                    <div class="flex items-center gap-1 mt-1">
                        <i class="fas fa-star text-yellow-400 text-xs"></i>
                        <span class="text-xs font-semibold text-gray-600">4.7</span>
                    </div>
                    <div class="flex items-center gap-1 mt-0.5">
                        <i class="fas fa-location-dot text-red-400 text-xs"></i>
                        <span class="text-xs text-gray-400 truncate"><?php echo e($tukang->lokasi ?? 'Surakarta'); ?></span>
                    </div>
                </div>
                </a>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <?php if($tukangList->isEmpty()): ?>
    <div class="px-4 py-8 flex flex-col items-center text-gray-400">
        <i class="fas fa-hard-hat text-5xl mb-3 opacity-30"></i>
        <p class="text-sm">Belum ada tukang tersedia</p>
    </div>
    <?php endif; ?>
</div>

<?php echo $__env->make('partials.bottom-nav', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
// --- Favorit toggle ---
document.querySelectorAll('.fav-btn').forEach(btn => {
    btn.addEventListener('click', function (e) {
        e.preventDefault(); e.stopPropagation();
        const id = this.dataset.id;
        const icon = this.querySelector('i');
        fetch(`/favorit/${id}`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            icon.classList.toggle('fas', data.favorited);
            icon.classList.toggle('far', !data.favorited);
            // Animasi kecil
            icon.style.transform = 'scale(1.4)';
            setTimeout(() => icon.style.transform = 'scale(1)', 200);
        });
    });
});

// --- Notif badge ---
function loadNotifBadge() {
    fetch('/notifikasi/unread', { headers: { 'Accept': 'application/json' } })
    .then(r => r.json())
    .then(data => {
        const badge = document.getElementById('notif-badge');
        if (!badge) return;
        if (data.count > 0) {
            badge.textContent = data.count > 99 ? '99+' : data.count;
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    }).catch(() => {});
}
loadNotifBadge();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\laravel web app\tomas-app\resources\views\home.blade.php ENDPATH**/ ?>