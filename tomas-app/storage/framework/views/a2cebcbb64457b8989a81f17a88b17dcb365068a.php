
<?php $__env->startSection('title', 'Monitoring Pembayaran'); ?>

<?php $__env->startSection('content'); ?>

<div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:20px;gap:12px;flex-wrap:wrap">
    <div>
        <h1 style="font-size:22px;font-weight:800;color:#0d1b2e;line-height:1.2">Monitoring Pembayaran</h1>
        <p style="font-size:12px;color:#6b7280;margin-top:4px">Pantau bukti transfer dan konfirmasi pembayaran.</p>
    </div>
</div>

<?php if(session('success')): ?>
<div style="background:#d1fae5;border:1px solid #6ee7b7;border-radius:10px;padding:12px 16px;margin-bottom:16px;color:#065f46;font-size:13px">
    <i class="fas fa-check-circle" style="margin-right:6px"></i><?php echo e(session('success')); ?>

</div>
<?php endif; ?>


<div style="display:flex;gap:0;border:1px solid #e2e8f0;border-radius:9px;overflow:hidden;margin-bottom:16px;width:fit-content">
    <?php $__currentLoopData = ['all' => 'Semua', 'pending' => 'Belum Bayar', 'uploaded' => 'Bukti Terkirim', 'confirmed' => 'Dikonfirmasi']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <a href="<?php echo e(route('admin.pembayaran')); ?>?status=<?php echo e($key); ?>"
       style="padding:8px 16px;font-size:12px;font-weight:<?php echo e($status === $key ? '700' : '500'); ?>;background:<?php echo e($status === $key ? '#2563eb' : '#fff'); ?>;color:<?php echo e($status === $key ? '#fff' : '#6b7280'); ?>;text-decoration:none;border-right:1px solid #e2e8f0;white-space:nowrap">
        <?php echo e($label); ?>

    </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden">
    <table style="width:100%;border-collapse:collapse">
        <thead>
            <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0">
                <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase">Order</th>
                <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase">User</th>
                <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase">Tukang</th>
                <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase">Metode</th>
                <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase">Bukti</th>
                <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase">Status</th>
                <th style="padding:12px 16px;text-align:center;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase">Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr style="border-bottom:1px solid #f1f5f9">
            <td style="padding:12px 16px">
                <div style="font-size:13px;font-weight:700;color:#0d1b2e">#<?php echo e($order->id_order); ?></div>
                <div style="font-size:11px;color:#9ca3af"><?php echo e($order->created_at?->format('d M Y')); ?></div>
            </td>
            <td style="padding:12px 16px;font-size:13px;color:#374151"><?php echo e($order->user?->nama ?? '-'); ?></td>
            <td style="padding:12px 16px;font-size:13px;color:#374151"><?php echo e($order->tukang?->nama ?? '-'); ?></td>
            <td style="padding:12px 16px;font-size:13px;color:#374151"><?php echo e($order->metode_bayar); ?></td>
            <td style="padding:12px 16px">
                <?php if($order->bukti_bayar): ?>
                <a href="<?php echo e(url('storage/'.$order->bukti_bayar)); ?>" target="_blank">
                    <img src="<?php echo e(url('storage/'.$order->bukti_bayar)); ?>" style="height:48px;border-radius:6px;border:1px solid #e2e8f0;object-fit:cover">
                </a>
                <?php else: ?>
                <span style="font-size:11px;color:#9ca3af">Belum ada</span>
                <?php endif; ?>
            </td>
            <td style="padding:12px 16px">
                <?php
                    $payStatus = $order->status_payment ?? 'pending';
                    $colors = ['pending' => ['bg' => '#fef3c7', 'text' => '#92400e'], 'uploaded' => ['bg' => '#dbeafe', 'text' => '#1e40af'], 'confirmed' => ['bg' => '#d1fae5', 'text' => '#065f46']];
                    $c = $colors[$payStatus] ?? $colors['pending'];
                    $labels = ['pending' => 'Belum Bayar', 'uploaded' => 'Bukti Terkirim', 'confirmed' => 'Dikonfirmasi'];
                ?>
                <span style="background:<?php echo e($c['bg']); ?>;color:<?php echo e($c['text']); ?>;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600">
                    <?php echo e($labels[$payStatus] ?? $payStatus); ?>

                </span>
            </td>
            <td style="padding:12px 16px;text-align:center">
                <?php if($payStatus === 'uploaded'): ?>
                <form method="POST" action="<?php echo e(route('admin.pembayaran.konfirmasi', $order->id_order)); ?>">
                    <?php echo csrf_field(); ?>
                    <button type="submit" onclick="return confirm('Konfirmasi pembayaran ini?')"
                        style="background:#2563eb;color:#fff;border:none;border-radius:7px;padding:6px 14px;font-size:12px;font-weight:600;cursor:pointer">
                        <i class="fas fa-check" style="margin-right:4px"></i>Konfirmasi
                    </button>
                </form>
                <?php else: ?>
                <span style="font-size:12px;color:#9ca3af">-</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr>
            <td colspan="7" style="text-align:center;padding:40px;color:#9ca3af;font-size:13px">Tidak ada data pembayaran</td>
        </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<div style="margin-top:16px"><?php echo e($orders->links()); ?></div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\laravel web app\tomas-app\resources\views\admin\pembayaran.blade.php ENDPATH**/ ?>