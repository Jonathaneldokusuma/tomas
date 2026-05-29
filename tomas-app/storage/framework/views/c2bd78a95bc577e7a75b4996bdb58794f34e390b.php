<?php $__env->startSection('title', isset($tukang) ? 'Edit Tukang' : 'Tambah Tukang'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-2xl">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="font-bold text-navy"><?php echo e(isset($tukang) ? 'Edit Tukang: '.$tukang->nama : 'Tambah Tukang Baru'); ?></h2>
        </div>
        <form action="<?php echo e(isset($tukang) ? route('admin.tukang.update', $tukang->id_tukang) : route('admin.tukang.store')); ?>"
              method="POST" enctype="multipart/form-data" class="px-6 py-6 space-y-5">
            <?php echo csrf_field(); ?>
            <?php if(isset($tukang)): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>

            
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">Nama</label>
                <input type="text" name="nama" value="<?php echo e(old('nama', $tukang->nama ?? '')); ?>"
                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-blue-400 <?php $__errorArgs = ['nama'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                    placeholder="Nama tukang" required>
                <?php $__errorArgs = ['nama'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">Kategori</label>
                <select name="kategori"
                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-blue-400 bg-white <?php $__errorArgs = ['kategori'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-400 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                    <option value="">-- Pilih Kategori --</option>
                    <?php $__currentLoopData = $layanan; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($l->nama_layanan); ?>"
                        <?php echo e(old('kategori', $tukang->kategori ?? '') === $l->nama_layanan ? 'selected' : ''); ?>>
                        <?php echo e($l->nama_layanan); ?>

                    </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <?php $__errorArgs = ['kategori'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">Lokasi</label>
                <input type="text" name="lokasi" value="<?php echo e(old('lokasi', $tukang->lokasi ?? '')); ?>"
                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-blue-400"
                    placeholder="Contoh: Jakarta Selatan">
            </div>

            
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">Bio</label>
                <textarea name="bio" rows="3"
                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-blue-400 resize-none"
                    placeholder="Deskripsi singkat tukang..."><?php echo e(old('bio', $tukang->bio ?? '')); ?></textarea>
            </div>

            
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">Foto Profil</label>
                <?php if(isset($tukang) && $tukang->foto): ?>
                <div class="flex items-center gap-4 mb-3">
                    <img src="<?php echo e(Storage::url($tukang->foto)); ?>" alt="Foto" class="w-16 h-16 rounded-xl object-cover border border-gray-200">
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Foto saat ini</p>
                        <label class="flex items-center gap-1.5 cursor-pointer text-xs text-red-500">
                            <input type="checkbox" name="hapus_foto" value="1" class="accent-red-500"> Hapus foto ini
                        </label>
                    </div>
                </div>
                <?php endif; ?>
                <div style="border:2px dashed #e2e8f0;border-radius:12px;padding:20px;text-align:center;cursor:pointer;transition:border-color .2s"
                     onclick="document.getElementById('fotoInput').click()"
                     ondragover="event.preventDefault();this.style.borderColor='#3b82f6'"
                     ondragleave="this.style.borderColor='#e2e8f0'"
                     ondrop="event.preventDefault();this.style.borderColor='#e2e8f0';handleDrop(event)">
                    <div id="fotoPreview" class="hidden mb-2">
                        <img id="fotoPreviewImg" src="" alt="" class="w-20 h-20 rounded-xl object-cover mx-auto border border-gray-200">
                    </div>
                    <i class="fas fa-cloud-arrow-up text-2xl text-blue-400 mb-2"></i>
                    <p class="text-xs text-gray-500">Klik atau drag foto ke sini</p>
                    <p class="text-xs text-gray-400 mt-0.5">JPG, PNG, WEBP · Maks 2MB</p>
                    <input type="file" id="fotoInput" name="foto" accept="image/*" class="hidden"
                           onchange="previewFoto(this)">
                </div>
                <?php $__errorArgs = ['foto'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">Tarif (Rp)</label>
                <input type="number" name="tarif" value="<?php echo e(old('tarif', $tukang->tarif ?? '')); ?>"
                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-blue-400"
                    placeholder="Contoh: 150000" min="0">
                <?php $__errorArgs = ['tarif'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-2 uppercase tracking-wider">Status</label>
                <div class="flex items-center gap-5">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="status_aktif" value="1"
                            <?php echo e(old('status_aktif', $tukang->status_aktif ?? 1) == 1 ? 'checked' : ''); ?> class="accent-blue-600">
                        <span class="text-sm font-medium text-green-600">Aktif</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="status_aktif" value="0"
                            <?php echo e(old('status_aktif', $tukang->status_aktif ?? 1) == 0 ? 'checked' : ''); ?> class="accent-gray-500">
                        <span class="text-sm font-medium text-gray-500">Nonaktif</span>
                    </label>
                </div>
            </div>

            
            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                    class="bg-blue-600 text-white text-sm px-6 py-2.5 rounded-xl font-semibold hover:bg-blue-700">
                    <i class="fas fa-save mr-2"></i><?php echo e(isset($tukang) ? 'Simpan Perubahan' : 'Tambah Tukang'); ?>

                </button>
                <a href="<?php echo e(route('admin.tukang')); ?>"
                   class="text-sm px-6 py-2.5 rounded-xl font-semibold border border-gray-200 text-gray-600 hover:bg-gray-50">
                    Batal
                </a>
            </div>

            
            <?php if(isset($tukang) && $tukang->updated_at): ?>
            <div class="pt-2 border-t border-gray-100 flex items-center gap-6">
                <?php if($tukang->created_at): ?>
                <div class="text-xs text-gray-400"><i class="fas fa-calendar-plus mr-1"></i>Dibuat: <span class="font-medium text-gray-500"><?php echo e($tukang->created_at->format('d M Y, H:i')); ?></span></div>
                <?php endif; ?>
                <div class="text-xs text-gray-400"><i class="fas fa-clock mr-1"></i>Diperbarui: <span class="font-medium text-gray-500"><?php echo e($tukang->updated_at->format('d M Y, H:i')); ?></span></div>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function previewFoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('fotoPreviewImg').src = e.target.result;
            document.getElementById('fotoPreview').classList.remove('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}
function handleDrop(event) {
    const dt = event.dataTransfer;
    if (dt.files.length) {
        document.getElementById('fotoInput').files = dt.files;
        previewFoto(document.getElementById('fotoInput'));
    }
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\laravel web app\tomas-app\resources\views\admin\tukang\form.blade.php ENDPATH**/ ?>