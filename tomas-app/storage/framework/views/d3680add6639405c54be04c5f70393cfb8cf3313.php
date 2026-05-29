<?php $__env->startSection('title', $tukang->nama); ?>
<?php $__env->startSection('content'); ?>
<?php $isFav = session('user_id') ? $tukang->isFavoritedBy(session('user_id')) : false; ?>
<div class="page-content">
    <!-- Header foto -->
    <div class="relative">
        <div class="w-full h-56 bg-gray-200 flex items-center justify-center overflow-hidden">
            <?php if($tukang->foto): ?>
            <img src="<?php echo e(Storage::url($tukang->foto)); ?>" alt="<?php echo e($tukang->nama); ?>" class="w-full h-full object-cover">
            <?php else: ?>
            <i class="fas fa-user text-gray-300 text-7xl"></i>
            <?php endif; ?>
        </div>
        <a href="<?php echo e(route('tukang.index')); ?>"
            class="absolute top-4 left-4 bg-white/80 backdrop-blur w-9 h-9 rounded-full flex items-center justify-center shadow">
            <i class="fas fa-chevron-left text-navy"></i>
        </a>
        <button id="fav-btn" data-id="<?php echo e($tukang->id_tukang); ?>"
                class="absolute top-4 right-4 bg-white/80 backdrop-blur w-9 h-9 rounded-full flex items-center justify-center shadow">
            <i id="fav-icon" class="<?php echo e($isFav ? 'fas' : 'far'); ?> fa-heart text-red-500"></i>
        </button>
    </div>

    <!-- Info -->
    <div class="px-4 pt-4">
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-xl font-bold text-navy"><?php echo e($tukang->nama); ?></h1>
                <div class="flex items-center gap-2 mt-1">
                    <div class="flex items-center gap-1">
                        <i class="fas fa-star text-yellow-400 text-sm"></i>
                        <span class="text-sm font-semibold text-gray-700">4.7</span>
                    </div>
                    <span class="text-gray-300">|</span>
                    <span class="text-sm text-gray-500">24 ulasan</span>
                </div>
                <div class="flex items-center gap-1 mt-1">
                    <i class="fas fa-location-dot text-red-400 text-sm"></i>
                    <span class="text-sm text-gray-500"><?php echo e($tukang->lokasi ?? 'Surakarta'); ?></span>
                </div>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold <?php echo e($tukang->status_aktif ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-500'); ?>">
                <?php echo e($tukang->status_aktif ? '● Aktif' : '● Offline'); ?>

            </span>
        </div>

        <!-- Kategori -->
        <div class="mt-4 bg-gray-50 rounded-2xl p-3">
            <h3 class="text-sm font-bold text-navy mb-2">Layanan / Kategori</h3>
            <div class="flex flex-wrap gap-2">
                <span class="bg-blue-50 text-primary text-xs px-3 py-1 rounded-full font-medium">
                    <?php echo e($tukang->kategori ?? 'Umum'); ?>

                </span>
            </div>
        </div>

        <!-- Deskripsi -->
        <div class="mt-4">
            <h3 class="text-sm font-bold text-navy mb-1">Tentang</h3>
            <p class="text-sm text-gray-500 leading-relaxed">
                <?php echo e($tukang->bio ?? 'Tukang berpengalaman dengan keahlian di bidang perbaikan rumah, instalasi, dan berbagai layanan teknis lainnya.'); ?>

            </p>
        </div>
    </div>

    <!-- Tombol Pesan -->
    <div class="px-4 mt-6 pb-28">
        <!-- Pilih layanan -->
        <div class="mb-4">
            <label class="text-sm font-semibold text-navy block mb-2">Pilih Layanan</label>
            <div id="layananPicker" class="flex flex-wrap gap-2">
                <?php
                    $layanans = \App\Models\Layanan::all();
                    $selectedLayanan = old('id_layanan', $layanans->first()?->id_layanan);
                ?>
                <?php $__currentLoopData = $layanans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <label class="cursor-pointer layanan-chip">
                    <input type="radio" name="id_layanan_sel" value="<?php echo e($l->id_layanan); ?>" class="hidden"
                           <?php echo e($selectedLayanan == $l->id_layanan ? 'checked' : ''); ?>>
                    <span class="inline-block px-3 py-1.5 rounded-full text-xs font-semibold border-2 transition-all
                        <?php echo e($selectedLayanan == $l->id_layanan ? 'bg-blue-600 border-blue-600 text-white' : 'border-gray-200 text-gray-500 bg-white'); ?>">
                        <?php echo e($l->nama_layanan); ?>

                    </span>
                </label>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        <form action="<?php echo e(route('orders.store')); ?>" method="POST" id="orderForm">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="id_tukang" value="<?php echo e($tukang->id_tukang); ?>">
            <input type="hidden" name="id_layanan" id="selectedLayanan" value="<?php echo e($selectedLayanan); ?>">
            <button type="submit"
                class="w-full bg-blue-600 text-white font-bold py-4 rounded-2xl text-base shadow-lg">
                Pesan Sekarang
            </button>
        </form>
        <a href="<?php echo e(route('chat.show', $tukang->id_tukang)); ?>"
            class="mt-2 w-full border-2 border-blue-600 text-blue-600 font-bold py-3.5 rounded-2xl text-base flex items-center justify-center gap-2">
            <i class="far fa-comment-dots"></i> Chat
        </a>
    </div>
</div>

<?php echo $__env->make('partials.bottom-nav', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
// Layanan chip picker
document.querySelectorAll('.layanan-chip').forEach(chip => {
    chip.addEventListener('click', function () {
        const val = this.querySelector('input').value;
        document.getElementById('selectedLayanan').value = val;
        document.querySelectorAll('.layanan-chip span').forEach(s => {
            s.classList.remove('bg-blue-600', 'border-blue-600', 'text-white');
            s.classList.add('border-gray-200', 'text-gray-500', 'bg-white');
        });
        this.querySelector('span').classList.add('bg-blue-600', 'border-blue-600', 'text-white');
        this.querySelector('span').classList.remove('border-gray-200', 'text-gray-500', 'bg-white');
    });
});

// Favorit toggle
const favBtn  = document.getElementById('fav-btn');
const favIcon = document.getElementById('fav-icon');
if (favBtn) {
    favBtn.addEventListener('click', function () {
        fetch(`/favorit/${this.dataset.id}`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            favIcon.classList.toggle('fas', data.favorited);
            favIcon.classList.toggle('far', !data.favorited);
            favIcon.style.transform = 'scale(1.5)';
            setTimeout(() => favIcon.style.transform = 'scale(1)', 200);

            // Toast kecil
            const toast = document.createElement('div');
            toast.className = 'fixed top-16 left-1/2 -translate-x-1/2 bg-gray-800 text-white text-xs px-4 py-2 rounded-full z-50 shadow-lg';
            toast.textContent = data.favorited ? '❤️ Ditambahkan ke favorit' : '🤍 Dihapus dari favorit';
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 2000);
        });
    });
}

// Notif badge (jika di halaman ini ada bell)
function loadNotifBadge() {
    fetch('/notifikasi/unread', { headers: { 'Accept': 'application/json' } })
    .then(r => r.json())
    .then(data => {
        const badge = document.getElementById('notif-badge');
        if (!badge) return;
        if (data.count > 0) { badge.textContent = data.count; badge.classList.remove('hidden'); }
        else badge.classList.add('hidden');
    }).catch(() => {});
}
loadNotifBadge();
</script>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\laravel web app\tomas-app\resources\views\tukang\show.blade.php ENDPATH**/ ?>