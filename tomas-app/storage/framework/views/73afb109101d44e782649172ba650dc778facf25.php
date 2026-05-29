
<?php $__env->startSection('title', 'Broadcast Pesan ke Tukang'); ?>

<?php $__env->startSection('content'); ?>
<div style="padding:24px">

    
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
        <div>
            <h1 style="font-size:20px;font-weight:700;color:#1e293b">Broadcast Pesan</h1>
            <p style="color:#64748b;font-size:13px;margin-top:2px">Kirim pesan ke semua tukang (Pesan dari Pusat)</p>
        </div>
    </div>

    <?php if(session('success')): ?>
    <div style="background:#dcfce7;border:1px solid #86efac;border-radius:8px;padding:12px 16px;color:#166534;margin-bottom:16px;font-size:13px">
        <i class="fas fa-check-circle" style="margin-right:6px"></i><?php echo e(session('success')); ?>

    </div>
    <?php endif; ?>

    
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;margin-bottom:24px;box-shadow:0 1px 6px rgba(0,0,0,.05)">
        <h2 style="font-size:15px;font-weight:600;color:#1e293b;margin-bottom:14px">
            <i class="fas fa-paper-plane" style="color:#2563eb;margin-right:6px"></i>Kirim Pesan Baru
        </h2>
        <form action="<?php echo e(route('admin.broadcast.store')); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <div style="display:grid;grid-template-columns:1fr auto;gap:12px;margin-bottom:12px">
                <div>
                    <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:4px">Judul Pesan</label>
                    <input type="text" name="judul" required maxlength="200" placeholder="Judul singkat yang jelas..."
                        style="width:100%;border:1px solid #d1d5db;border-radius:8px;padding:9px 12px;font-size:13px;outline:none"
                        onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'">
                </div>
                <div>
                    <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:4px">Tipe</label>
                    <select name="tipe" style="border:1px solid #d1d5db;border-radius:8px;padding:9px 12px;font-size:13px;outline:none;background:#fff;height:38px">
                        <option value="info">ℹ️ Info</option>
                        <option value="warning">⚠️ Peringatan</option>
                        <option value="promo">🎁 Promo</option>
                    </select>
                </div>
            </div>
            <div style="margin-bottom:14px">
                <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:4px">Isi Pesan</label>
                <textarea name="isi" required rows="4" placeholder="Tulis isi pesan lengkap di sini..."
                    style="width:100%;border:1px solid #d1d5db;border-radius:8px;padding:9px 12px;font-size:13px;outline:none;resize:vertical"
                    onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'"></textarea>
            </div>
            <button type="submit"
                style="background:linear-gradient(135deg,#2563eb,#4f46e5);color:#fff;border:none;border-radius:8px;padding:10px 20px;font-size:13px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:8px">
                <i class="fas fa-bullhorn"></i> Kirim ke Semua Tukang
            </button>
        </form>
    </div>

    
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;box-shadow:0 1px 6px rgba(0,0,0,.05)">
        <div style="padding:16px 20px;border-bottom:1px solid #f1f5f9">
            <h2 style="font-size:15px;font-weight:600;color:#1e293b">
                <i class="fas fa-history" style="color:#6366f1;margin-right:6px"></i>Riwayat Pesan (<?php echo e($broadcasts->total()); ?>)
            </h2>
        </div>

        <?php $__empty_1 = true; $__currentLoopData = $broadcasts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $msg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <?php
            $tipeColor = match($msg->tipe) { 'warning' => '#f97316', 'promo' => '#22c55e', default => '#3b82f6' };
            $tipeBg    = match($msg->tipe) { 'warning' => '#fff7ed', 'promo' => '#f0fdf4', default => '#eff6ff' };
            $tipeLabel = match($msg->tipe) { 'warning' => '⚠️ Peringatan', 'promo' => '🎁 Promo', default => 'ℹ️ Info' };
        ?>
        <div style="padding:16px 20px;border-bottom:1px solid #f1f5f9;display:flex;gap:14px;align-items:flex-start">
            <div style="background:<?php echo e($tipeBg); ?>;border-radius:8px;padding:8px;flex-shrink:0">
                <span style="font-size:18px"><?php echo e(str_contains($tipeLabel,'⚠') ? '⚠️' : (str_contains($tipeLabel,'🎁') ? '🎁' : 'ℹ️')); ?></span>
            </div>
            <div style="flex:1;min-width:0">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
                    <span style="font-weight:600;color:#1e293b;font-size:14px"><?php echo e($msg->judul); ?></span>
                    <span style="background:<?php echo e($tipeBg); ?>;color:<?php echo e($tipeColor); ?>;border-radius:12px;padding:2px 8px;font-size:11px;font-weight:600"><?php echo e($tipeLabel); ?></span>
                </div>
                <p style="color:#64748b;font-size:13px;line-height:1.5;margin-bottom:6px"><?php echo e(Str::limit($msg->isi, 200)); ?></p>
                <span style="color:#94a3b8;font-size:11px"><?php echo e($msg->created_at->diffForHumans()); ?> · <?php echo e($msg->created_at->format('d M Y H:i')); ?></span>
            </div>
            <form action="<?php echo e(route('admin.broadcast.delete', $msg->id_broadcast)); ?>" method="POST" onsubmit="return confirm('Hapus pesan ini?')">
                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                <button type="submit" style="background:#fee2e2;color:#dc2626;border:none;border-radius:6px;padding:6px 10px;font-size:12px;cursor:pointer">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div style="padding:40px;text-align:center;color:#94a3b8">
            <i class="fas fa-bullhorn" style="font-size:32px;margin-bottom:10px;display:block;opacity:.4"></i>
            Belum ada pesan broadcast.
        </div>
        <?php endif; ?>

        <div style="padding:14px 20px">
            <?php echo e($broadcasts->links()); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\laravel web app\tomas-app\resources\views\admin\broadcast\index.blade.php ENDPATH**/ ?>