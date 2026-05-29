
<?php $__env->startSection('title', 'Reviews'); ?>

<?php $__env->startSection('content'); ?>


<div style="margin-bottom:20px">
    <h1 style="font-size:22px;font-weight:800;color:#0d1b2e;line-height:1.2">Review Management</h1>
    <p style="font-size:12px;color:#6b7280;margin-top:4px">Monitor and manage all user reviews across the platform.</p>
</div>

<div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden">
    
    <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid #f1f5f9;gap:12px;flex-wrap:wrap">
        <h3 style="font-size:14px;font-weight:700;color:#0d1b2e">All Reviews</h3>
        <form method="GET" action="<?php echo e(route('admin.reviews')); ?>" style="display:flex;align-items:center;gap:8px">
            <div style="position:relative">
                <i class="fas fa-search" style="position:absolute;left:9px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:10px"></i>
                <input type="text" name="q" value="<?php echo e($q); ?>" placeholder="Cari komentar / tukang..."
                    style="padding:7px 12px 7px 26px;font-size:12px;border:1px solid #e2e8f0;border-radius:8px;outline:none;width:210px;background:#f8fafc;color:#374151"
                    onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
            </div>
            <button style="background:#0d1b2e;color:#fff;border:none;border-radius:8px;padding:7px 14px;font-size:12px;font-weight:600;cursor:pointer">Cari</button>
            <?php if($q): ?><a href="<?php echo e(route('admin.reviews')); ?>" style="font-size:12px;color:#9ca3af;text-decoration:none">Reset</a><?php endif; ?>
        </form>
    </div>

    
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="background:#f8fafc">
                    <th style="padding:10px 18px;text-align:left;font-size:10px;font-weight:700;color:#6b7280;letter-spacing:.06em;white-space:nowrap">ORDER</th>
                    <th style="padding:10px 18px;text-align:left;font-size:10px;font-weight:700;color:#6b7280;letter-spacing:.06em">CLIENT</th>
                    <th style="padding:10px 18px;text-align:left;font-size:10px;font-weight:700;color:#6b7280;letter-spacing:.06em">PROVIDER</th>
                    <th style="padding:10px 18px;text-align:left;font-size:10px;font-weight:700;color:#6b7280;letter-spacing:.06em">RATING</th>
                    <th style="padding:10px 18px;text-align:left;font-size:10px;font-weight:700;color:#6b7280;letter-spacing:.06em">KOMENTAR</th>
                    <th style="padding:10px 18px;text-align:left;font-size:10px;font-weight:700;color:#6b7280;letter-spacing:.06em">ACTION</th>
                </tr>
            </thead>
            <tbody>
                <?php $colors=[['#eff6ff','#2563eb'],['#f0fdf4','#16a34a'],['#fefce8','#ca8a04'],['#fdf4ff','#9333ea'],['#fff7ed','#ea580c']]; ?>
                <?php $__empty_1 = true; $__currentLoopData = $reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php [$bg,$tx] = $colors[$i % 5]; ?>
                <tr style="border-top:1px solid #f1f5f9" onmouseover="this.style.background='#fafbfc'" onmouseout="this.style.background=''">
                    <td style="padding:13px 18px;font-family:monospace;font-size:12px;color:#374151;white-space:nowrap">#ORD-<?php echo e(str_pad($review->id_order,5,'0',STR_PAD_LEFT)); ?></td>
                    <td style="padding:13px 18px">
                        <div style="display:flex;align-items:center;gap:9px">
                            <div style="width:30px;height:30px;border-radius:50%;background:<?php echo e($bg); ?>;color:<?php echo e($tx); ?>;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;flex-shrink:0">
                                <?php echo e(strtoupper(substr($review->order->user->nama??'U',0,1))); ?>

                            </div>
                            <span style="font-size:13px;font-weight:500;color:#111827;white-space:nowrap"><?php echo e($review->order->user->nama ?? '-'); ?></span>
                        </div>
                    </td>
                    <td style="padding:13px 18px">
                        <div style="font-size:13px;font-weight:500;color:#374151"><?php echo e($review->order->tukang->nama ?? '-'); ?></div>
                        <div style="font-size:11px;color:#9ca3af"><?php echo e($review->order->tukang->kategori ?? ''); ?></div>
                    </td>
                    <td style="padding:13px 18px">
                        <div style="display:flex;align-items:center;gap:2px">
                            <?php for($s = 1; $s <= 5; $s++): ?>
                            <i class="fas fa-star" style="font-size:11px;color:<?php echo e($s <= $review->rating ? '#f59e0b' : '#e5e7eb'); ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <div style="font-size:10px;color:#9ca3af;margin-top:2px"><?php echo e($review->rating); ?>/5</div>
                    </td>
                    <td style="padding:13px 18px;font-size:12px;color:#6b7280;max-width:200px">
                        <span style="display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?php echo e(Str::limit($review->komentar,70) ?? '-'); ?></span>
                    </td>
                    <td style="padding:13px 18px">
                        <form action="<?php echo e(route('admin.reviews.delete', $review->id_review)); ?>" method="POST" style="display:inline"
                              onsubmit="return confirm('Hapus review #<?php echo e($review->id_review); ?>?')">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button type="submit"
                                style="display:flex;align-items:center;gap:4px;font-size:11px;font-weight:600;color:#ef4444;border:1px solid #fecaca;background:#fff5f5;border-radius:7px;padding:5px 10px;cursor:pointer">
                                <i class="fas fa-trash" style="font-size:9px"></i> Delete
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="6" style="padding:48px;text-align:center;color:#9ca3af;font-size:13px">Tidak ada data review</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if($reviews->hasPages()): ?>
    <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 18px;border-top:1px solid #f1f5f9">
        <span style="font-size:12px;color:#6b7280">Showing <?php echo e($reviews->firstItem()); ?>–<?php echo e($reviews->lastItem()); ?> of <?php echo e($reviews->total()); ?> results</span>
        <div><?php echo e($reviews->withQueryString()->links()); ?></div>
    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\laravel web app\tomas-app\resources\views\admin\reviews\index.blade.php ENDPATH**/ ?>