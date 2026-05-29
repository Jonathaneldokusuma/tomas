<?php $__env->startSection('title', 'Orders'); ?>

<?php $__env->startSection('content'); ?>


<div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:20px;gap:12px;flex-wrap:wrap">
    <div>
        <h1 style="font-size:22px;font-weight:800;color:#0d1b2e;line-height:1.2">Order Management</h1>
        <p style="font-size:12px;color:#6b7280;margin-top:4px">Track and manage all service orders across the platform.</p>
    </div>
</div>

<div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden">
    
    <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid #f1f5f9;gap:12px;flex-wrap:wrap">
        <h3 style="font-size:14px;font-weight:700;color:#0d1b2e">All Orders</h3>
        <form method="GET" action="<?php echo e(route('admin.orders')); ?>" style="display:flex;align-items:center;gap:8px">
            <div style="position:relative">
                <i class="fas fa-search" style="position:absolute;left:9px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:10px"></i>
                <input type="text" name="q" value="<?php echo e($q); ?>" placeholder="Cari user / tukang..."
                    style="padding:7px 12px 7px 26px;font-size:12px;border:1px solid #e2e8f0;border-radius:8px;outline:none;width:210px;background:#f8fafc;color:#374151"
                    onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
            </div>
            <button style="background:#0d1b2e;color:#fff;border:none;border-radius:8px;padding:7px 14px;font-size:12px;font-weight:600;cursor:pointer">Cari</button>
            <?php if($q): ?><a href="<?php echo e(route('admin.orders')); ?>" style="font-size:12px;color:#9ca3af;text-decoration:none">Reset</a><?php endif; ?>
        </form>
    </div>

    
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="background:#f8fafc">
                    <th style="padding:10px 18px;text-align:left;font-size:10px;font-weight:700;color:#6b7280;letter-spacing:.06em;white-space:nowrap">ORDER ID</th>
                    <th style="padding:10px 18px;text-align:left;font-size:10px;font-weight:700;color:#6b7280;letter-spacing:.06em">CLIENT</th>
                    <th style="padding:10px 18px;text-align:left;font-size:10px;font-weight:700;color:#6b7280;letter-spacing:.06em">SERVICE TYPE</th>
                    <th style="padding:10px 18px;text-align:left;font-size:10px;font-weight:700;color:#6b7280;letter-spacing:.06em">TUKANG</th>
                    <th style="padding:10px 18px;text-align:left;font-size:10px;font-weight:700;color:#6b7280;letter-spacing:.06em">STATUS</th>
                    <th style="padding:10px 18px;text-align:left;font-size:10px;font-weight:700;color:#6b7280;letter-spacing:.06em">ACTION</th>
                </tr>
            </thead>
            <tbody>
                <?php $colors=[['#eff6ff','#2563eb'],['#f0fdf4','#16a34a'],['#fefce8','#ca8a04'],['#fdf4ff','#9333ea'],['#fff7ed','#ea580c']]; ?>
                <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php [$bg,$tx] = $colors[$i % 5]; ?>
                <tr style="border-top:1px solid #f1f5f9" onmouseover="this.style.background='#fafbfc'" onmouseout="this.style.background=''">
                    <td style="padding:13px 18px;font-family:monospace;font-size:12px;color:#374151;white-space:nowrap">#ORD-<?php echo e(str_pad($order->id_order,5,'0',STR_PAD_LEFT)); ?></td>
                    <td style="padding:13px 18px">
                        <div style="display:flex;align-items:center;gap:9px">
                            <div style="width:30px;height:30px;border-radius:50%;background:<?php echo e($bg); ?>;color:<?php echo e($tx); ?>;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;flex-shrink:0"><?php echo e(strtoupper(substr($order->user->nama??'U',0,1))); ?></div>
                            <div>
                                <div style="font-size:13px;font-weight:600;color:#111827"><?php echo e($order->user->nama ?? '-'); ?></div>
                                <div style="font-size:11px;color:#9ca3af"><?php echo e($order->user->no_hp ?? ''); ?></div>
                            </div>
                        </div>
                    </td>
                    <td style="padding:13px 18px">
                        <span style="background:#eff6ff;color:#2563eb;font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px;white-space:nowrap"><?php echo e($order->layanan->nama_layanan ?? '-'); ?></span>
                    </td>
                    <td style="padding:13px 18px">
                        <div style="font-size:13px;font-weight:500;color:#374151"><?php echo e($order->tukang->nama ?? '-'); ?></div>
                        <div style="font-size:11px;color:#9ca3af"><?php echo e($order->tukang->kategori ?? ''); ?></div>
                    </td>
                    <td style="padding:13px 18px">
                        <?php if($order->review): ?>
                        <span style="background:#dcfce7;color:#15803d;font-size:11px;font-weight:600;padding:4px 10px;border-radius:20px;white-space:nowrap">
                            <i class="fas fa-star" style="font-size:9px;margin-right:2px"></i>Selesai
                        </span>
                        <?php else: ?>
                        <span style="background:#dbeafe;color:#1d4ed8;font-size:11px;font-weight:600;padding:4px 10px;border-radius:20px;white-space:nowrap">Aktif</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding:13px 18px">
                        <form action="<?php echo e(route('admin.orders.delete', $order->id_order)); ?>" method="POST" style="display:inline"
                              onsubmit="return confirm('Hapus order #<?php echo e($order->id_order); ?>?')">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button type="submit"
                                style="display:flex;align-items:center;gap:4px;font-size:11px;font-weight:600;color:#ef4444;border:1px solid #fecaca;background:#fff5f5;border-radius:7px;padding:5px 10px;cursor:pointer">
                                <i class="fas fa-trash" style="font-size:9px"></i> Delete
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="6" style="padding:48px;text-align:center;color:#9ca3af;font-size:13px">Tidak ada data order</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    
    <?php if($orders->hasPages()): ?>
    <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 18px;border-top:1px solid #f1f5f9">
        <span style="font-size:12px;color:#6b7280">Showing <?php echo e($orders->firstItem()); ?>–<?php echo e($orders->lastItem()); ?> of <?php echo e($orders->total()); ?> results</span>
        <div><?php echo e($orders->withQueryString()->links()); ?></div>
    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\laravel web app\tomas-app\resources\views\admin\orders\index.blade.php ENDPATH**/ ?>