
<?php $__env->startSection('title', 'Services'); ?>

<?php $__env->startSection('content'); ?>


<div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:20px;gap:12px;flex-wrap:wrap">
    <div>
        <h1 style="font-size:22px;font-weight:800;color:#0d1b2e;line-height:1.2">Service Directory</h1>
        <p style="font-size:12px;color:#6b7280;margin-top:4px">Manage and audit high-value service listings across the marketplace.</p>
    </div>
    <div style="display:flex;align-items:center;gap:8px">
        <a href="<?php echo e(route('admin.tukang.create')); ?>"
           style="display:flex;align-items:center;gap:6px;background:#2563eb;color:#fff;border-radius:8px;padding:8px 16px;font-size:12px;font-weight:600;text-decoration:none">
            <i class="fas fa-plus" style="font-size:10px"></i> New Service
        </a>
    </div>
</div>


<div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;margin-bottom:16px">
    
    <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid #f1f5f9;gap:12px;flex-wrap:wrap">
        <div style="display:flex;align-items:center;gap:0;border:1px solid #e2e8f0;border-radius:9px;overflow:hidden">
            <a href="<?php echo e(route('admin.tukang')); ?>" style="padding:7px 16px;font-size:12px;font-weight:600;background:<?php echo e(!request('status') ? '#2563eb':'#fff'); ?>;color:<?php echo e(!request('status') ? '#fff':'#6b7280'); ?>;text-decoration:none;white-space:nowrap">All Services</a>
            <a href="<?php echo e(route('admin.tukang')); ?>?status=1" style="padding:7px 16px;font-size:12px;font-weight:500;background:<?php echo e(request('status')==='1' ? '#2563eb':'#fff'); ?>;color:<?php echo e(request('status')==='1' ? '#fff':'#6b7280'); ?>;text-decoration:none;border-left:1px solid #e2e8f0;white-space:nowrap">Active</a>
            <a href="<?php echo e(route('admin.tukang')); ?>?status=0" style="padding:7px 16px;font-size:12px;font-weight:500;background:<?php echo e(request('status')==='0' ? '#2563eb':'#fff'); ?>;color:<?php echo e(request('status')==='0' ? '#fff':'#6b7280'); ?>;text-decoration:none;border-left:1px solid #e2e8f0;white-space:nowrap">Nonaktif</a>
        </div>
        <form method="GET" action="<?php echo e(route('admin.tukang')); ?>" style="display:flex;align-items:center;gap:8px">
            <div style="position:relative">
                <i class="fas fa-search" style="position:absolute;left:9px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:10px"></i>
                <input type="text" name="q" value="<?php echo e($q); ?>" placeholder="Cari nama / kategori..."
                    style="padding:7px 12px 7px 26px;font-size:12px;border:1px solid #e2e8f0;border-radius:8px;outline:none;width:200px;background:#f8fafc;color:#374151"
                    onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
            </div>
            <button style="display:flex;align-items:center;gap:6px;background:#2563eb;color:#fff;border:none;border-radius:8px;padding:7px 14px;font-size:12px;font-weight:600;cursor:pointer">
                <i class="fas fa-filter" style="font-size:10px"></i> Apply Filters
            </button>
            <?php if($q): ?><a href="<?php echo e(route('admin.tukang')); ?>" style="font-size:12px;color:#9ca3af;text-decoration:none">Reset</a><?php endif; ?>
        </form>
    </div>

    
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="background:#f8fafc">
                    <th style="padding:10px 18px;text-align:left;font-size:10px;font-weight:700;color:#6b7280;letter-spacing:.06em">SERVICE NAME</th>
                    <th style="padding:10px 18px;text-align:left;font-size:10px;font-weight:700;color:#6b7280;letter-spacing:.06em">CATEGORY</th>
                    <th style="padding:10px 18px;text-align:left;font-size:10px;font-weight:700;color:#6b7280;letter-spacing:.06em">LOKASI</th>
                    <th style="padding:10px 18px;text-align:center;font-size:10px;font-weight:700;color:#6b7280;letter-spacing:.06em">STATUS</th>
                    <th style="padding:10px 18px;text-align:left;font-size:10px;font-weight:700;color:#6b7280;letter-spacing:.06em">UPDATED</th>
                    <th style="padding:10px 18px;text-align:left;font-size:10px;font-weight:700;color:#6b7280;letter-spacing:.06em">ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php $colors=[['#eff6ff','#2563eb'],['#f0fdf4','#16a34a'],['#fefce8','#ca8a04'],['#fdf4ff','#9333ea'],['#fff7ed','#ea580c']]; ?>
                <?php $__empty_1 = true; $__currentLoopData = $list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $tukang): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php [$bg,$tx] = $colors[$i % 5]; ?>
                <tr style="border-top:1px solid #f1f5f9" onmouseover="this.style.background='#fafbfc'" onmouseout="this.style.background=''">
                    <td style="padding:13px 18px">
                        <div style="display:flex;align-items:center;gap:10px">
                            <?php if($tukang->foto): ?>
                            <img src="<?php echo e(Storage::url($tukang->foto)); ?>" alt="" style="width:34px;height:34px;border-radius:9px;object-fit:cover;flex-shrink:0;border:1px solid #e2e8f0">
                            <?php else: ?>
                            <div style="width:34px;height:34px;border-radius:9px;background:<?php echo e($bg); ?>;color:<?php echo e($tx); ?>;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;flex-shrink:0">
                                <?php echo e(strtoupper(substr($tukang->nama,0,1))); ?>

                            </div>
                            <?php endif; ?>
                            <div>
                                <div style="font-size:13px;font-weight:600;color:#111827"><?php echo e($tukang->nama); ?></div>
                                <?php if($tukang->bio): ?>
                                <div style="font-size:11px;color:#9ca3af;max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?php echo e(Str::limit($tukang->bio,45)); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td style="padding:13px 18px">
                        <span style="background:#eff6ff;color:#2563eb;font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px;white-space:nowrap"><?php echo e($tukang->kategori ?? '-'); ?></span>
                    </td>
                    <td style="padding:13px 18px;font-size:12px;color:#6b7280;white-space:nowrap"><?php echo e($tukang->lokasi ?? '-'); ?></td>
                    <td style="padding:13px 18px;text-align:center">
                        <?php if($tukang->status_aktif): ?>
                        <span style="background:#dcfce7;color:#15803d;font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px">ACTIVE</span>
                        <?php else: ?>
                        <span style="background:#f1f5f9;color:#6b7280;font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px">OFFLINE</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding:13px 18px">
                        <?php if($tukang->updated_at): ?>
                        <div style="font-size:11px;color:#374151;font-weight:500"><?php echo e($tukang->updated_at->format('d M Y')); ?></div>
                        <div style="font-size:10px;color:#9ca3af"><?php echo e($tukang->updated_at->format('H:i')); ?></div>
                        <?php else: ?>
                        <span style="font-size:11px;color:#d1d5db">—</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding:13px 18px">
                        <div style="display:flex;align-items:center;gap:6px">
                            <a href="<?php echo e(route('admin.tukang.edit', $tukang->id_tukang)); ?>"
                               style="width:28px;height:28px;border:1px solid #e2e8f0;border-radius:7px;background:#f8fafc;display:flex;align-items:center;justify-content:center;color:#6b7280;text-decoration:none;transition:background .1s"
                               onmouseover="this.style.background='#eff6ff';this.style.color='#2563eb'" onmouseout="this.style.background='#f8fafc';this.style.color='#6b7280'">
                                <i class="fas fa-pen" style="font-size:10px"></i>
                            </a>
                            <form action="<?php echo e(route('admin.tukang.delete', $tukang->id_tukang)); ?>" method="POST" style="display:inline"
                                  onsubmit="return confirm('Hapus <?php echo e(addslashes($tukang->nama)); ?>?')">
                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                <button type="submit"
                                    style="width:28px;height:28px;border:1px solid #fecaca;border-radius:7px;background:#fff5f5;display:flex;align-items:center;justify-content:center;color:#ef4444;cursor:pointer;transition:background .1s"
                                    onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='#fff5f5'">
                                    <i class="fas fa-trash" style="font-size:10px"></i>
                                </button>
                            </form>
                            
                            <?php if($tukang->status_aktif): ?>
                            <form action="<?php echo e(route('admin.tukang.ban', $tukang->id_tukang)); ?>" method="POST" style="display:inline"
                                  onsubmit="return confirm('Nonaktifkan <?php echo e(addslashes($tukang->nama)); ?>?')">
                                <?php echo csrf_field(); ?>
                                <button type="submit" title="Nonaktifkan"
                                    style="width:28px;height:28px;border:1px solid #fed7aa;border-radius:7px;background:#fff7ed;display:flex;align-items:center;justify-content:center;color:#ea580c;cursor:pointer">
                                    <i class="fas fa-ban" style="font-size:10px"></i>
                                </button>
                            </form>
                            <?php else: ?>
                            <form action="<?php echo e(route('admin.tukang.unban', $tukang->id_tukang)); ?>" method="POST" style="display:inline">
                                <?php echo csrf_field(); ?>
                                <button type="submit" title="Aktifkan kembali"
                                    style="width:28px;height:28px;border:1px solid #6ee7b7;border-radius:7px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;color:#16a34a;cursor:pointer">
                                    <i class="fas fa-check-circle" style="font-size:10px"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="6" style="padding:48px;text-align:center;color:#9ca3af;font-size:13px">Tidak ada data tukang</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    
    <?php if($list->hasPages()): ?>
    <div style="padding:12px 18px;border-top:1px solid #f1f5f9">
        <?php echo e($list->withQueryString()->links()); ?>

    </div>
    <?php endif; ?>
</div>


<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px">
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:16px 18px">
        <div style="font-size:11px;color:#6b7280;font-weight:600;margin-bottom:6px">TOTAL SERVICES</div>
        <div style="font-size:24px;font-weight:800;color:#0d1b2e"><?php echo e($list->total()); ?></div>
        <div style="font-size:11px;color:#16a34a;font-weight:500;margin-top:3px"><i class="fas fa-arrow-up" style="font-size:9px"></i> Terdaftar di platform</div>
    </div>
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:16px 18px">
        <div style="font-size:11px;color:#6b7280;font-weight:600;margin-bottom:6px">ACTIVE PROVIDERS</div>
        <div style="font-size:24px;font-weight:800;color:#0d1b2e"><?php echo e(\App\Models\Tukang::where('status_aktif',1)->count()); ?></div>
        <div style="font-size:11px;color:#16a34a;font-weight:500;margin-top:3px">● Sedang aktif</div>
    </div>
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:16px 18px">
        <div style="font-size:11px;color:#6b7280;font-weight:600;margin-bottom:6px">KATEGORI</div>
        <div style="font-size:24px;font-weight:800;color:#0d1b2e"><?php echo e(\App\Models\Layanan::count()); ?></div>
        <div style="font-size:11px;color:#6b7280;font-weight:500;margin-top:3px">Jenis layanan tersedia</div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\laravel web app\tomas-app\resources\views\admin\tukang\index.blade.php ENDPATH**/ ?>