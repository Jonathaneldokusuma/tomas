
<?php $__env->startSection('title', 'Users'); ?>

<?php $__env->startSection('content'); ?>


<div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:20px;gap:12px;flex-wrap:wrap">
    <div>
        <h1 style="font-size:22px;font-weight:800;color:#0d1b2e;line-height:1.2">Marketplace Admin</h1>
        <p style="font-size:12px;color:#6b7280;margin-top:4px">Manage and audit all user accounts across the platform.</p>
    </div>
</div>


<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px">
    <?php
    $uCards = [
        ['label'=>'Total Users',       'val'=>$users->total(), 'sub'=>'+6% from last month',      'ibg'=>'#eff6ff','ic'=>'#2563eb','icon'=>'fa-users'],
        ['label'=>'Active Tukang',      'val'=>\App\Models\Tukang::where('status_aktif',1)->count(), 'sub'=>'Provider aktif',    'ibg'=>'#f0fdf4','ic'=>'#16a34a','icon'=>'fa-hard-hat'],
        ['label'=>'Pending Reviews',   'val'=>\App\Models\Order::doesntHave('review')->count(),   'sub'=>'Order belum review',   'ibg'=>'#fff7ed','ic'=>'#ea580c','icon'=>'fa-clock'],
        ['label'=>'Total Orders',      'val'=>\App\Models\Order::count(),                          'sub'=>'Semua order',          'ibg'=>'#fdf4ff','ic'=>'#9333ea','icon'=>'fa-receipt'],
    ];
    ?>
    <?php $__currentLoopData = $uCards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:16px 18px">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
            <div style="width:36px;height:36px;background:<?php echo e($c['ibg']); ?>;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <i class="fas <?php echo e($c['icon']); ?>" style="color:<?php echo e($c['ic']); ?>;font-size:14px"></i>
            </div>
        </div>
        <div style="font-size:24px;font-weight:800;color:#0d1b2e;line-height:1"><?php echo e(number_format($c['val'])); ?></div>
        <div style="font-size:12px;font-weight:600;color:#374151;margin-top:3px"><?php echo e($c['label']); ?></div>
        <div style="font-size:11px;color:#9ca3af;margin-top:1px"><?php echo e($c['sub']); ?></div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>


<div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden">
    
    <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid #f1f5f9;gap:12px;flex-wrap:wrap">
        <div style="display:flex;align-items:center;gap:0;border:1px solid #e2e8f0;border-radius:9px;overflow:hidden;flex-shrink:0">
            <a href="<?php echo e(route('admin.users')); ?>" style="padding:7px 16px;font-size:12px;font-weight:600;background:<?php echo e(!$q ? '#2563eb':'#fff'); ?>;color:<?php echo e(!$q ? '#fff':'#6b7280'); ?>;text-decoration:none;white-space:nowrap">All Users</a>
            <a href="<?php echo e(route('admin.users')); ?>?q=&status=active" style="padding:7px 16px;font-size:12px;font-weight:500;background:#fff;color:#6b7280;text-decoration:none;border-left:1px solid #e2e8f0;white-space:nowrap">Active</a>
        </div>
        <div style="display:flex;align-items:center;gap:8px;flex:1;justify-content:flex-end">
            <form method="GET" action="<?php echo e(route('admin.users')); ?>" style="display:flex;align-items:center;gap:8px">
                <div style="position:relative">
                    <i class="fas fa-search" style="position:absolute;left:9px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:10px"></i>
                    <input type="text" name="q" value="<?php echo e($q); ?>" placeholder="Cari nama / no HP..."
                        style="padding:7px 12px 7px 26px;font-size:12px;border:1px solid #e2e8f0;border-radius:8px;outline:none;width:210px;color:#374151;background:#f8fafc"
                        onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                </div>
                <button style="background:#0d1b2e;color:#fff;border:none;border-radius:8px;padding:7px 14px;font-size:12px;font-weight:600;cursor:pointer">Filter</button>
                <?php if($q): ?><a href="<?php echo e(route('admin.users')); ?>" style="font-size:12px;color:#9ca3af;text-decoration:none">Reset</a><?php endif; ?>
            </form>
            <button style="display:flex;align-items:center;gap:6px;border:1px solid #e2e8f0;background:#fff;border-radius:8px;padding:7px 12px;font-size:12px;font-weight:500;color:#374151;cursor:pointer">
                <i class="fas fa-file-export" style="font-size:11px;color:#9ca3af"></i> Export
            </button>
        </div>
    </div>

    
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="background:#f8fafc">
                    <th style="padding:10px 18px;text-align:left;font-size:10px;font-weight:700;color:#6b7280;letter-spacing:.06em">NAME</th>
                    <th style="padding:10px 18px;text-align:left;font-size:10px;font-weight:700;color:#6b7280;letter-spacing:.06em">NO. HP</th>
                    <th style="padding:10px 18px;text-align:left;font-size:10px;font-weight:700;color:#6b7280;letter-spacing:.06em">JOIN DATE</th>
                    <th style="padding:10px 18px;text-align:left;font-size:10px;font-weight:700;color:#6b7280;letter-spacing:.06em">STATUS</th>
                    <th style="padding:10px 18px;text-align:left;font-size:10px;font-weight:700;color:#6b7280;letter-spacing:.06em">ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php $colors=[['#eff6ff','#2563eb'],['#f0fdf4','#16a34a'],['#fefce8','#ca8a04'],['#fdf4ff','#9333ea'],['#fff7ed','#ea580c']]; ?>
                <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php [$bg,$tx] = $colors[$i % 5]; ?>
                <tr style="border-top:1px solid #f1f5f9" onmouseover="this.style.background='#fafbfc'" onmouseout="this.style.background=''">
                    <td style="padding:13px 18px">
                        <div style="display:flex;align-items:center;gap:10px">
                            <div style="width:32px;height:32px;border-radius:50%;background:<?php echo e($bg); ?>;color:<?php echo e($tx); ?>;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;flex-shrink:0">
                                <?php echo e(strtoupper(substr($user->nama,0,1))); ?>

                            </div>
                            <div>
                                <div style="font-size:13px;font-weight:600;color:#111827"><?php echo e($user->nama); ?></div>
                                <div style="font-size:11px;color:#9ca3af">ID: <?php echo e(str_pad($user->id_user,5,'0',STR_PAD_LEFT)); ?></div>
                            </div>
                        </div>
                    </td>
                    <td style="padding:13px 18px;font-size:12px;color:#6b7280;font-family:monospace"><?php echo e($user->no_hp); ?></td>
                    <td style="padding:13px 18px;font-size:12px;color:#6b7280;white-space:nowrap">–</td>
                    <td style="padding:13px 18px">
                        <span style="background:#dcfce7;color:#15803d;font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px">● Active</span>
                    </td>
                    <td style="padding:13px 18px">
                        <div style="display:flex;align-items:center;gap:10px">
                            <a href="#" style="font-size:12px;color:#2563eb;font-weight:600;text-decoration:none">View Details</a>
                            <span style="color:#e2e8f0">|</span>
                            <form action="<?php echo e(route('admin.users.delete', $user->id_user)); ?>" method="POST" style="display:inline"
                                  onsubmit="return confirm('Hapus user <?php echo e(addslashes($user->nama)); ?>?')">
                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                <button type="submit" style="font-size:12px;color:#ef4444;font-weight:600;background:none;border:none;cursor:pointer;padding:0">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="5" style="padding:48px;text-align:center;color:#9ca3af;font-size:13px">Tidak ada data pengguna</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    
    <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 18px;border-top:1px solid #f1f5f9">
        <span style="font-size:12px;color:#6b7280">Showing 1 to <?php echo e($users->count()); ?> of <?php echo e($users->total()); ?> entries</span>
        <div><?php echo e($users->withQueryString()->links()); ?></div>
    </div>
</div>


<div style="margin-top:16px;background:linear-gradient(135deg,#2563eb,#1d4ed8);border-radius:14px;padding:20px 24px;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap">
    <div>
        <div style="font-size:14px;font-weight:700;color:#fff;margin-bottom:4px">User Verification Updates</div>
        <div style="font-size:12px;color:rgba(255,255,255,.75);max-width:520px">
            Data pengguna diperbarui secara real-time. Gunakan fitur delete dengan hati-hati — data yang dihapus tidak dapat dipulihkan.
        </div>
    </div>
    <a href="<?php echo e(route('admin.dashboard')); ?>" style="flex-shrink:0;background:#fff;color:#2563eb;font-size:12px;font-weight:700;padding:9px 20px;border-radius:8px;text-decoration:none;white-space:nowrap">View Dashboard</a>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\laravel web app\tomas-app\resources\views\admin\users\index.blade.php ENDPATH**/ ?>