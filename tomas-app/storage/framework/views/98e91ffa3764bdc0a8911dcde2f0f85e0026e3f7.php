
<?php $__env->startSection('title', 'Verifikasi Tukang'); ?>

<?php $__env->startSection('content'); ?>

<div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:20px;gap:12px;flex-wrap:wrap">
    <div>
        <h1 style="font-size:22px;font-weight:800;color:#0d1b2e;line-height:1.2">Verifikasi Tukang</h1>
        <p style="font-size:12px;color:#6b7280;margin-top:4px">Tinjau KTP dan selfie tukang yang mendaftar.</p>
    </div>
    <div>
        <span style="background:#fef3c7;color:#92400e;padding:6px 14px;border-radius:20px;font-size:12px;font-weight:600">
            <?php echo e($tukang->total()); ?> menunggu verifikasi
        </span>
    </div>
</div>

<?php if(session('success')): ?>
<div style="background:#d1fae5;border:1px solid #6ee7b7;border-radius:10px;padding:12px 16px;margin-bottom:16px;color:#065f46;font-size:13px">
    <i class="fas fa-check-circle" style="margin-right:6px"></i><?php echo e(session('success')); ?>

</div>
<?php endif; ?>

<?php if($tukang->isEmpty()): ?>
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:48px;text-align:center">
    <i class="fas fa-check-double" style="font-size:40px;color:#10b981;margin-bottom:16px;display:block"></i>
    <p style="color:#374151;font-weight:600;font-size:15px">Semua tukang sudah diverifikasi</p>
    <p style="color:#9ca3af;font-size:13px;margin-top:4px">Tidak ada pendaftar yang menunggu.</p>
</div>
<?php else: ?>

<div style="display:grid;gap:16px">
<?php $__currentLoopData = $tukang; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden">
    <div style="display:flex;gap:16px;padding:20px;flex-wrap:wrap">
        
        <div style="flex-shrink:0">
            <?php if($t->foto): ?>
                <img src="<?php echo e(url('storage/'.$t->foto)); ?>" style="width:60px;height:60px;border-radius:50%;object-fit:cover">
            <?php else: ?>
                <div style="width:60px;height:60px;border-radius:50%;background:#dbeafe;display:flex;align-items:center;justify-content:center;font-size:22px;color:#2563eb;font-weight:bold">
                    <?php echo e(strtoupper(substr($t->nama ?? 'T', 0, 1))); ?>

                </div>
            <?php endif; ?>
        </div>
        
        <div style="flex:1;min-width:200px">
            <div style="font-size:16px;font-weight:700;color:#0d1b2e"><?php echo e($t->nama ?? '-'); ?></div>
            <div style="font-size:12px;color:#6b7280;margin-top:2px">
                <?php if($t->username): ?><span>{{ $t->username }}</span><?php endif; ?>
                <?php if($t->no_hp): ?><span style="margin-left:12px"><i class="fas fa-phone" style="margin-right:3px"></i><?php echo e($t->no_hp); ?></span><?php endif; ?>
                <?php if($t->no_ktp): ?><span style="margin-left:12px"><i class="fas fa-id-card" style="margin-right:3px"></i><?php echo e($t->no_ktp); ?></span><?php endif; ?>
            </div>
            <div style="margin-top:6px;font-size:12px;color:#374151">
                Daftar: <?php echo e($t->created_at?->format('d M Y H:i')); ?>

            </div>
            <div style="margin-top:8px">
                <span style="background:#fef3c7;color:#92400e;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600">
                    Menunggu Verifikasi
                </span>
            </div>
        </div>
        
        <div style="display:flex;gap:8px;align-items:flex-start;flex-shrink:0">
            <form method="POST" action="<?php echo e(route('admin.tukang.approve', $t->id_tukang)); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit" onclick="return confirm('Verifikasi tukang ini?')"
                    style="background:#10b981;color:#fff;border:none;border-radius:8px;padding:8px 16px;font-size:12px;font-weight:600;cursor:pointer">
                    <i class="fas fa-check" style="margin-right:4px"></i>Verifikasi
                </button>
            </form>
            <form method="POST" action="<?php echo e(route('admin.tukang.reject', $t->id_tukang)); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit" onclick="return confirm('Tolak tukang ini?')"
                    style="background:#ef4444;color:#fff;border:none;border-radius:8px;padding:8px 16px;font-size:12px;font-weight:600;cursor:pointer">
                    <i class="fas fa-times" style="margin-right:4px"></i>Tolak
                </button>
            </form>
        </div>
    </div>
    
    <?php if($t->foto_ktp || $t->foto_selfie): ?>
    <div style="border-top:1px solid #f1f5f9;padding:16px 20px">
        <div style="font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:10px">Dokumen Verifikasi</div>
        <div style="display:flex;gap:12px;flex-wrap:wrap">
            <?php if($t->foto_ktp): ?>
            <div>
                <div style="font-size:11px;color:#6b7280;margin-bottom:4px">Foto KTP</div>
                <a href="<?php echo e(url('storage/'.$t->foto_ktp)); ?>" target="_blank">
                    <img src="<?php echo e(url('storage/'.$t->foto_ktp)); ?>" style="height:100px;border-radius:8px;border:1px solid #e2e8f0;object-fit:cover;cursor:pointer">
                </a>
            </div>
            <?php endif; ?>
            <?php if($t->foto_selfie): ?>
            <div>
                <div style="font-size:11px;color:#6b7280;margin-bottom:4px">Selfie dengan KTP</div>
                <a href="<?php echo e(url('storage/'.$t->foto_selfie)); ?>" target="_blank">
                    <img src="<?php echo e(url('storage/'.$t->foto_selfie)); ?>" style="height:100px;border-radius:8px;border:1px solid #e2e8f0;object-fit:cover;cursor:pointer">
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php else: ?>
    <div style="border-top:1px solid #f1f5f9;padding:12px 20px;background:#fffbeb">
        <p style="font-size:12px;color:#92400e"><i class="fas fa-exclamation-triangle" style="margin-right:6px"></i>Tukang belum upload foto KTP & selfie.</p>
    </div>
    <?php endif; ?>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<div style="margin-top:16px"><?php echo e($tukang->links()); ?></div>
<?php endif; ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\laravel web app\tomas-app\resources\views\admin\tukang\verifikasi.blade.php ENDPATH**/ ?>