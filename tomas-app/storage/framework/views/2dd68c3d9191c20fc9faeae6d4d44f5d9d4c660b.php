<?php $__env->startSection('title', 'Dashboard'); ?>

<?php $__env->startSection('content'); ?>


<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:10px">
    <div>
        <h1 style="font-size:22px;font-weight:800;color:#0d1b2e;line-height:1.2">Dashboard</h1>
        <p style="font-size:12px;color:#6b7280;margin-top:3px">
            <i class="fas fa-clock" style="font-size:10px;margin-right:3px;color:#9ca3af"></i>
            <?php echo e(now()->format('l, d F Y')); ?>

            &nbsp;·&nbsp; <?php echo e($periodLabel); ?>

            <?php if($tukangId): ?>
            &nbsp;·&nbsp;
            <span style="color:#2563eb;font-weight:600"><?php echo e($tukangList->firstWhere('id_tukang', $tukangId)?->nama ?? 'Tukang'); ?></span>
            <?php endif; ?>
        </p>
    </div>
    <span style="background:#dcfce7;color:#15803d;font-size:11px;font-weight:600;padding:6px 14px;border-radius:20px;display:flex;align-items:center;gap:6px">
        <span style="width:7px;height:7px;background:#16a34a;border-radius:50%;display:inline-block;animation:pulse 2s infinite"></span>
        Live
    </span>
</div>


<div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:16px 20px;margin-bottom:20px;box-shadow:0 1px 4px rgba(0,0,0,.04)">

    
    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">

        
        <div style="display:flex;gap:3px;background:#f1f5f9;border-radius:10px;padding:3px">
            <?php $__currentLoopData = ['today'=>'Hari Ini','week'=>'Minggu Ini','month'=>'Bulan Ini','all'=>'Semua']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pv=>$pl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a href="<?php echo e(route('admin.dashboard', array_merge(request()->except('period'), ['period'=>$pv, 'view'=>$view]))); ?>"
               style="padding:7px 16px;border-radius:8px;font-size:12px;font-weight:600;text-decoration:none;white-space:nowrap;transition:all .15s;<?php echo e($period==$pv ? 'background:#fff;color:#1d4ed8;box-shadow:0 1px 4px rgba(0,0,0,.1)' : 'color:#6b7280'); ?>">
                <?php echo e($pl); ?>

            </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        
        <form method="GET" style="display:flex;align-items:center;gap:8px">
            <input type="hidden" name="period"   value="<?php echo e($period); ?>">
            <input type="hidden" name="view"     value="<?php echo e($view); ?>">
            <label style="font-size:12px;font-weight:600;color:#374151;white-space:nowrap">
                <i class="fas fa-filter" style="font-size:10px;color:#9ca3af;margin-right:3px"></i> Pekerja:
            </label>
            <select name="tukang_id" onchange="this.form.submit()"
                    style="border:1px solid #e2e8f0;border-radius:8px;padding:7px 32px 7px 10px;font-size:12px;color:#374151;background:#f8fafc;cursor:pointer;min-width:160px">
                <option value="">— Semua —</option>
                <?php $__currentLoopData = $tukangList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($t->id_tukang); ?>" <?php echo e($tukangId==$t->id_tukang ? 'selected' : ''); ?>><?php echo e($t->nama); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <?php if($tukangId): ?>
            <a href="<?php echo e(route('admin.dashboard', ['period'=>$period,'view'=>$view])); ?>"
               style="font-size:11px;color:#ef4444;font-weight:600;text-decoration:none;white-space:nowrap;padding:7px 10px;border-radius:8px;border:1px solid #fecaca;background:#fef2f2">
                <i class="fas fa-xmark"></i> Reset
            </a>
            <?php endif; ?>
        </form>
    </div>

    
    <div style="height:1px;background:#f1f5f9;margin:12px 0"></div>

    
    <div style="display:flex;gap:6px;flex-wrap:wrap">
        <?php
        $viewTabs = [
            'summary'     => ['icon'=>'fa-gauge',    'label'=>'Ringkasan'],
            'workers'     => ['icon'=>'fa-hammer',   'label'=>'Pekerja'],
            'customers'   => ['icon'=>'fa-users',    'label'=>'Pelanggan'],
            'orders'      => ['icon'=>'fa-receipt',  'label'=>'Pesanan'],
            'performance' => ['icon'=>'fa-chart-bar','label'=>'Performa'],
        ];
        ?>
        <?php $__currentLoopData = $viewTabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vv=>$vd): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a href="<?php echo e(route('admin.dashboard', array_merge(request()->query(), ['view'=>$vv]))); ?>"
           style="display:inline-flex;align-items:center;gap:6px;padding:7px 16px;border-radius:8px;font-size:12px;font-weight:600;text-decoration:none;transition:all .15s;<?php echo e($view==$vv ? 'background:#2563eb;color:#fff;box-shadow:0 2px 8px rgba(37,99,235,.3)' : 'background:#f8fafc;color:#6b7280;border:1px solid #e2e8f0'); ?>">
            <i class="fas <?php echo e($vd['icon']); ?>" style="font-size:11px"></i> <?php echo e($vd['label']); ?>

        </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>


<?php
$statCards = [
    ['label'=>'Total Pengguna',   'val'=>number_format($stats['users']),          'icon'=>'fa-users',             'ibg'=>'#eff6ff','ic'=>'#2563eb','sub'=>'Terdaftar'],
    ['label'=>'Pekerja Aktif',    'val'=>number_format($stats['tukang_aktif']),   'icon'=>'fa-screwdriver-wrench','ibg'=>'#fff7ed','ic'=>'#ea580c','sub'=>'dari '.$stats['tukang'].' total'],
    ['label'=>'Pesanan',          'val'=>number_format($stats['orders']),         'icon'=>'fa-receipt',           'ibg'=>'#f0fdf4','ic'=>'#16a34a','sub'=>$periodLabel],
    ['label'=>'Ulasan',           'val'=>number_format($stats['reviews']),        'icon'=>'fa-star',              'ibg'=>'#fefce8','ic'=>'#ca8a04','sub'=>$periodLabel],
    ['label'=>'Rating Rata-rata', 'val'=>($stats['avg_rating'] ?: '—'),           'icon'=>'fa-chart-line',        'ibg'=>'#fdf4ff','ic'=>'#9333ea','sub'=>'Dari ulasan'],
    ['label'=>'Total Pesanan',    'val'=>number_format($stats['orders_total']),   'icon'=>'fa-database',          'ibg'=>'#f0f9ff','ic'=>'#0ea5e9','sub'=>'Sepanjang waktu'],
];
?>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(155px,1fr));gap:12px;margin-bottom:20px">
    <?php $__currentLoopData = $statCards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:16px 18px;position:relative;overflow:hidden;transition:transform .18s,box-shadow .18s;cursor:default"
         onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 10px 28px rgba(37,99,235,.1)'"
         onmouseout="this.style.transform='';this.style.boxShadow=''">
        <div style="position:absolute;top:-16px;right:-16px;width:70px;height:70px;background:<?php echo e($c['ibg']); ?>;border-radius:50%;opacity:.6"></div>
        <div style="width:36px;height:36px;background:<?php echo e($c['ibg']); ?>;border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:12px;position:relative">
            <i class="fas <?php echo e($c['icon']); ?>" style="color:<?php echo e($c['ic']); ?>;font-size:14px"></i>
        </div>
        <div style="font-size:26px;font-weight:800;color:#0d1b2e;line-height:1;position:relative"><?php echo e($c['val']); ?></div>
        <div style="font-size:12px;font-weight:600;color:#374151;margin-top:5px"><?php echo e($c['label']); ?></div>
        <div style="font-size:10px;color:#9ca3af;margin-top:2px"><?php echo e($c['sub']); ?></div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>



<?php if(in_array($view, ['summary','workers'])): ?>
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;margin-bottom:20px;box-shadow:0 1px 4px rgba(0,0,0,.04)">

    
    <div style="padding:16px 20px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;background:linear-gradient(135deg,#fffbeb 0%,#fff7ed 100%)">
        <div style="display:flex;align-items:center;gap:10px">
            <div style="width:32px;height:32px;background:linear-gradient(135deg,#f97316,#ea580c);border-radius:10px;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(234,88,12,.3)">
                <i class="fas fa-trophy" style="color:#fff;font-size:13px"></i>
            </div>
            <div>
                <h3 style="font-size:14px;font-weight:700;color:#0d1b2e">Top Pekerja — Orderan Terbanyak</h3>
                <p style="font-size:11px;color:#9ca3af;margin-top:1px"><?php echo e($periodLabel); ?></p>
            </div>
        </div>
        <?php if($view === 'summary'): ?>
        <a href="<?php echo e(route('admin.dashboard', array_merge(request()->query(), ['view'=>'workers']))); ?>"
           style="font-size:12px;color:#ea580c;font-weight:600;text-decoration:none;padding:6px 14px;background:#fff7ed;border-radius:8px;border:1px solid #fed7aa">
            Lihat Detail →
        </a>
        <?php endif; ?>
    </div>

    <?php if($topWorkers->count() > 0): ?>

    
    <div style="padding:24px 20px 20px;background:linear-gradient(135deg,#fafbff 0%,#f0f4ff 100%);border-bottom:1px solid #f1f5f9">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:14px;max-width:580px;margin:0 auto">
            <?php $__currentLoopData = $topWorkers->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
            $medals    = ['🥇','🥈','🥉'];
            $podBgs    = ['linear-gradient(145deg,#fffbeb,#fef3c7)','linear-gradient(145deg,#f8fafc,#e2e8f0)','linear-gradient(145deg,#fff7ed,#fed7aa)'];
            $podBorder = ['#fbbf24','#9ca3af','#f97316'];
            $podColor  = ['#b45309','#4b5563','#c2410c'];
            $podShadow = ['rgba(251,191,36,.25)','rgba(0,0,0,.08)','rgba(249,115,22,.2)'];
            ?>
            <div style="background:<?php echo e($podBgs[$i]); ?>;border:2px solid <?php echo e($podBorder[$i]); ?>;border-radius:16px;padding:18px 12px;text-align:center;box-shadow:0 4px 16px <?php echo e($podShadow[$i]); ?>;position:relative">
                <?php if($i === 0): ?>
                <div style="position:absolute;top:-10px;left:50%;transform:translateX(-50%);background:linear-gradient(135deg,#fbbf24,#f59e0b);color:#fff;font-size:9px;font-weight:700;padding:2px 10px;border-radius:20px;white-space:nowrap;letter-spacing:.04em">
                    # 1 TERBAIK
                </div>
                <?php endif; ?>
                <div style="font-size:26px;margin-bottom:8px"><?php echo e($medals[$i]); ?></div>
                <div style="width:48px;height:48px;background:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:800;color:<?php echo e($podColor[$i]); ?>;margin:0 auto 10px;box-shadow:0 3px 10px rgba(0,0,0,.12)">
                    <?php echo e(strtoupper(substr($t['nama'],0,1))); ?>

                </div>
                <div style="font-size:13px;font-weight:700;color:#111827;margin-bottom:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis" title="<?php echo e($t['nama']); ?>">
                    <?php echo e($t['nama']); ?>

                </div>
                <div style="font-size:10px;color:#6b7280;margin-bottom:10px"><?php echo e($t['kategori'] ?: 'Umum'); ?></div>
                <div style="font-size:28px;font-weight:800;color:<?php echo e($podColor[$i]); ?>;line-height:1"><?php echo e($t['orders_count']); ?></div>
                <div style="font-size:10px;color:#9ca3af;margin-bottom:6px">pesanan</div>
                <?php if($t['avg_rating'] > 0): ?>
                <div style="display:inline-block;background:rgba(0,0,0,.07);border-radius:20px;padding:2px 10px;font-size:11px;font-weight:700;color:<?php echo e($podColor[$i]); ?>">
                    <?php echo e($t['avg_rating']); ?> ★
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    
    <?php if($topWorkers->count() > 3): ?>
    <div style="padding:8px 20px 12px">
        <div style="font-size:10px;font-weight:700;color:#9ca3af;letter-spacing:.07em;padding:10px 4px 6px">PERINGKAT SELANJUTNYA</div>
        <?php $maxO = max(1, $topWorkers->first()['orders_count']); ?>
        <?php $__currentLoopData = $topWorkers->skip(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php $rank = $i + 4; ?>
        <div style="display:flex;align-items:center;gap:10px;padding:9px 10px;border-radius:10px;transition:background .12s"
             onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
            <div style="width:26px;text-align:center;font-size:12px;font-weight:700;color:#9ca3af;flex-shrink:0"><?php echo e($rank); ?></div>
            <div style="width:34px;height:34px;border-radius:50%;background:#eff6ff;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#2563eb;flex-shrink:0">
                <?php echo e(strtoupper(substr($t['nama'],0,1))); ?>

            </div>
            <div style="flex:1;min-width:0">
                <div style="font-size:13px;font-weight:600;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?php echo e($t['nama']); ?></div>
                <div style="font-size:10px;color:#9ca3af"><?php echo e($t['kategori'] ?: 'Umum'); ?></div>
            </div>
            <div style="width:90px;flex-shrink:0">
                <div style="background:#f1f5f9;border-radius:4px;height:5px">
                    <div style="background:linear-gradient(90deg,#3b82f6,#6366f1);height:100%;border-radius:4px;width:<?php echo e($maxO > 0 ? min(100, round(($t['orders_count']/$maxO)*100)) : 0); ?>%"></div>
                </div>
            </div>
            <div style="text-align:right;flex-shrink:0;min-width:36px">
                <div style="font-size:15px;font-weight:800;color:#0d1b2e"><?php echo e($t['orders_count']); ?></div>
                <div style="font-size:9px;color:#9ca3af">pesanan</div>
            </div>
            <?php if($t['avg_rating'] > 0): ?>
            <div style="background:#fefce8;color:#ca8a04;font-size:11px;font-weight:700;padding:3px 9px;border-radius:20px;flex-shrink:0"><?php echo e($t['avg_rating']); ?>★</div>
            <?php else: ?>
            <div style="width:52px"></div>
            <?php endif; ?>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <div style="padding:48px;text-align:center;color:#9ca3af;font-size:13px">
        <i class="fas fa-inbox" style="font-size:28px;display:block;margin-bottom:8px;opacity:.4"></i>
        Belum ada data pekerja
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>



<?php if(in_array($view, ['summary','orders','performance'])): ?>
<div style="display:grid;grid-template-columns:1fr 290px;gap:14px;margin-bottom:20px">

    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.04)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
            <div>
                <h3 style="font-size:14px;font-weight:700;color:#0d1b2e">Tren Pesanan</h3>
                <p style="font-size:11px;color:#9ca3af;margin-top:2px"><?php echo e($periodLabel); ?></p>
            </div>
            <span style="background:#eff6ff;color:#2563eb;font-size:11px;font-weight:700;padding:5px 14px;border-radius:20px">
                <?php echo e(array_sum($chartData)); ?> total
            </span>
        </div>
        <canvas id="trendChart" style="max-height:200px"></canvas>
    </div>

    <div style="background:linear-gradient(160deg,#1d4ed8 0%,#4338ca 60%,#6d28d9 100%);border-radius:14px;padding:20px;color:#fff;display:flex;flex-direction:column;box-shadow:0 4px 20px rgba(37,99,235,.25)">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:3px">
            <i class="fas fa-star" style="font-size:12px;color:#fde68a"></i>
            <h3 style="font-size:13px;font-weight:700">Rating Tertinggi</h3>
        </div>
        <p style="font-size:11px;color:rgba(255,255,255,.6);margin-bottom:14px">Tukang terbaik</p>
        <?php $__empty_1 = true; $__currentLoopData = $topRated; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div style="display:flex;align-items:center;gap:9px;margin-bottom:8px;background:rgba(255,255,255,.12);border-radius:10px;padding:8px 10px"
             onmouseover="this.style.background='rgba(255,255,255,.2)'" onmouseout="this.style.background='rgba(255,255,255,.12)'">
            <div style="width:22px;height:22px;border-radius:50%;background:<?php echo e(['#fbbf24','#9ca3af','#cd7c2e','#60a5fa','#34d399'][$i] ?? '#ffffff44'); ?>;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:800;color:#1a2b47;flex-shrink:0">
                <?php echo e($i+1); ?>

            </div>
            <div style="flex:1;min-width:0">
                <div style="font-size:12px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?php echo e($t['nama']); ?></div>
                <div style="font-size:10px;color:rgba(255,255,255,.55)"><?php echo e($t['reviews_count']); ?> ulasan</div>
            </div>
            <div style="font-size:13px;font-weight:800;color:#fde68a;flex-shrink:0"><?php echo e($t['avg_rating']); ?>★</div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div style="text-align:center;color:rgba(255,255,255,.45);font-size:12px;margin:auto 0;padding:20px 0">
            <i class="fas fa-star" style="display:block;font-size:22px;margin-bottom:6px;opacity:.3"></i>
            Belum ada data
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>



<?php if(in_array($view, ['workers','performance'])): ?>
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;margin-bottom:20px;box-shadow:0 1px 4px rgba(0,0,0,.04)">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:15px 20px;border-bottom:1px solid #f1f5f9">
        <div>
            <h3 style="font-size:14px;font-weight:700;color:#0d1b2e">Performa Lengkap Pekerja</h3>
            <p style="font-size:11px;color:#9ca3af;margin-top:2px"><?php echo e($periodLabel); ?> &middot; diurutkan berdasarkan pesanan terbanyak</p>
        </div>
        <a href="<?php echo e(route('admin.tukang')); ?>" style="font-size:12px;color:#2563eb;font-weight:600;text-decoration:none;padding:6px 14px;background:#eff6ff;border-radius:8px;border:1px solid #bfdbfe">
            Kelola Pekerja →
        </a>
    </div>
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="background:#f8fafc">
                    <th style="padding:10px 16px;text-align:left;font-size:10px;font-weight:700;color:#6b7280;letter-spacing:.06em">#</th>
                    <th style="padding:10px 16px;text-align:left;font-size:10px;font-weight:700;color:#6b7280;letter-spacing:.06em">PEKERJA</th>
                    <th style="padding:10px 16px;text-align:left;font-size:10px;font-weight:700;color:#6b7280;letter-spacing:.06em">KATEGORI</th>
                    <th style="padding:10px 16px;text-align:center;font-size:10px;font-weight:700;color:#6b7280;letter-spacing:.06em">PESANAN</th>
                    <th style="padding:10px 16px;text-align:center;font-size:10px;font-weight:700;color:#6b7280;letter-spacing:.06em">ULASAN</th>
                    <th style="padding:10px 16px;text-align:center;font-size:10px;font-weight:700;color:#6b7280;letter-spacing:.06em">RATING</th>
                    <th style="padding:10px 16px;text-align:left;font-size:10px;font-weight:700;color:#6b7280;letter-spacing:.06em">PROGRESS</th>
                    <th style="padding:10px 16px;text-align:center;font-size:10px;font-weight:700;color:#6b7280;letter-spacing:.06em">STATUS</th>
                </tr>
            </thead>
            <tbody>
                <?php $maxOrders = max(1, $tukangPerformance->max('orders_count')); ?>
                <?php $__empty_1 = true; $__currentLoopData = $tukangPerformance; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $rc  = $t['avg_rating'] >= 4 ? '#16a34a' : ($t['avg_rating'] >= 2.5 ? '#ca8a04' : ($t['avg_rating'] > 0 ? '#dc2626' : '#9ca3af'));
                    $rb  = $t['avg_rating'] >= 4 ? '#dcfce7' : ($t['avg_rating'] >= 2.5 ? '#fefce8' : ($t['avg_rating'] > 0 ? '#fef2f2' : '#f1f5f9'));
                    $barW = $maxOrders > 0 ? min(100, round(($t['orders_count']/$maxOrders)*100)) : 0;
                ?>
                <tr style="border-top:1px solid #f1f5f9;transition:background .12s"
                    onmouseover="this.style.background='#fafbfc'" onmouseout="this.style.background=''">
                    <td style="padding:12px 16px;font-size:13px;font-weight:700;color:#9ca3af">
                        <?php if($i < 3): ?> <?php echo e(['🥇','🥈','🥉'][$i]); ?> <?php else: ?> <?php echo e($i+1); ?> <?php endif; ?>
                    </td>
                    <td style="padding:12px 16px">
                        <div style="display:flex;align-items:center;gap:9px">
                            <div style="width:34px;height:34px;border-radius:50%;background:#eff6ff;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#2563eb;flex-shrink:0;border:2px solid #bfdbfe">
                                <?php echo e(strtoupper(substr($t['nama'],0,1))); ?>

                            </div>
                            <div style="font-size:13px;font-weight:600;color:#111827"><?php echo e($t['nama']); ?></div>
                        </div>
                    </td>
                    <td style="padding:12px 16px">
                        <?php if($t['kategori']): ?>
                        <span style="background:#f1f5f9;color:#374151;font-size:11px;font-weight:500;padding:3px 10px;border-radius:20px"><?php echo e($t['kategori']); ?></span>
                        <?php else: ?>
                        <span style="color:#d1d5db;font-size:12px">—</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding:12px 16px;text-align:center;font-size:18px;font-weight:800;color:#0d1b2e"><?php echo e($t['orders_count']); ?></td>
                    <td style="padding:12px 16px;text-align:center;font-size:13px;color:#374151"><?php echo e($t['reviews_count']); ?></td>
                    <td style="padding:12px 16px;text-align:center">
                        <?php if($t['avg_rating'] > 0): ?>
                        <span style="background:<?php echo e($rb); ?>;color:<?php echo e($rc); ?>;font-size:12px;font-weight:700;padding:3px 10px;border-radius:20px"><?php echo e($t['avg_rating']); ?>★</span>
                        <?php else: ?>
                        <span style="color:#d1d5db;font-size:12px">—</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding:12px 20px;min-width:110px">
                        <div style="background:#f1f5f9;border-radius:4px;height:6px">
                            <div style="background:linear-gradient(90deg,#3b82f6,#6366f1);height:100%;border-radius:4px;width:<?php echo e($barW); ?>%"></div>
                        </div>
                        <div style="font-size:10px;color:#9ca3af;margin-top:3px"><?php echo e($barW); ?>%</div>
                    </td>
                    <td style="padding:12px 16px;text-align:center">
                        <span style="background:<?php echo e($t['status_aktif'] ? '#dcfce7':'#f1f5f9'); ?>;color:<?php echo e($t['status_aktif'] ? '#15803d':'#9ca3af'); ?>;font-size:11px;font-weight:600;padding:3px 12px;border-radius:20px">
                            <?php echo e($t['status_aktif'] ? 'Aktif' : 'Nonaktif'); ?>

                        </span>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="8" style="padding:48px;text-align:center;color:#9ca3af;font-size:13px">
                    <i class="fas fa-inbox" style="font-size:24px;display:block;margin-bottom:8px;opacity:.3"></i>
                    Belum ada data pekerja
                </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>



<?php if(in_array($view, ['summary','customers'])): ?>
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;margin-bottom:20px;box-shadow:0 1px 4px rgba(0,0,0,.04)">
    <div style="padding:16px 20px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between">
        <div style="display:flex;align-items:center;gap:10px">
            <div style="width:32px;height:32px;background:linear-gradient(135deg,#2563eb,#4f46e5);border-radius:10px;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(37,99,235,.3)">
                <i class="fas fa-users" style="color:#fff;font-size:13px"></i>
            </div>
            <div>
                <h3 style="font-size:14px;font-weight:700;color:#0d1b2e">Top Pelanggan Aktif</h3>
                <p style="font-size:11px;color:#9ca3af;margin-top:1px"><?php echo e($periodLabel); ?></p>
            </div>
        </div>
        <a href="<?php echo e(route('admin.users')); ?>" style="font-size:12px;color:#2563eb;font-weight:600;text-decoration:none;padding:6px 14px;background:#eff6ff;border-radius:8px;border:1px solid #bfdbfe">
            Lihat Semua →
        </a>
    </div>
    <div style="padding:16px 20px">
        <?php if($topCustomers->isEmpty()): ?>
        <div style="text-align:center;color:#9ca3af;font-size:13px;padding:28px 0">
            <i class="fas fa-users" style="font-size:28px;display:block;margin-bottom:8px;opacity:.3"></i>
            Belum ada data pelanggan
        </div>
        <?php else: ?>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(190px,1fr));gap:10px">
            <?php $__currentLoopData = $topCustomers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
            $abgs = ['#eff6ff','#f0fdf4','#fefce8','#fdf4ff','#fff7ed','#f0f9ff','#fef2f2','#f8fafc','#fafffe','#f5f3ff'];
            $atxs = ['#2563eb','#16a34a','#ca8a04','#9333ea','#ea580c','#0ea5e9','#dc2626','#64748b','#0d9488','#7c3aed'];
            [$abg, $atx] = [$abgs[$i % 10], $atxs[$i % 10]];
            ?>
            <div style="display:flex;align-items:center;gap:10px;padding:10px 12px;background:#f8fafc;border-radius:10px;border:1px solid #f1f5f9;transition:border-color .15s,box-shadow .15s"
                 onmouseover="this.style.borderColor='#bfdbfe';this.style.boxShadow='0 2px 8px rgba(37,99,235,.08)'"
                 onmouseout="this.style.borderColor='#f1f5f9';this.style.boxShadow=''">
                <div style="width:38px;height:38px;border-radius:50%;background:<?php echo e($abg); ?>;color:<?php echo e($atx); ?>;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;flex-shrink:0">
                    <?php echo e(strtoupper(substr($c->nama??'U',0,1))); ?>

                </div>
                <div style="flex:1;min-width:0">
                    <div style="font-size:13px;font-weight:600;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?php echo e($c->nama); ?></div>
                    <div style="font-size:11px;color:#9ca3af"><?php echo e($c->orders_count); ?> pesanan</div>
                </div>
                <?php if($i < 3): ?>
                <div style="font-size:16px;flex-shrink:0"><?php echo e(['🥇','🥈','🥉'][$i]); ?></div>
                <?php else: ?>
                <div style="font-size:13px;font-weight:700;color:#374151;flex-shrink:0;min-width:20px;text-align:right"><?php echo e($c->orders_count); ?></div>
                <?php endif; ?>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>



<?php if(in_array($view, ['summary','orders','customers','workers'])): ?>
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.04)">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:15px 20px;border-bottom:1px solid #f1f5f9">
        <div>
            <h3 style="font-size:14px;font-weight:700;color:#0d1b2e">Aktivitas Terbaru</h3>
            <p style="font-size:11px;color:#9ca3af;margin-top:2px"><?php echo e($periodLabel); ?></p>
        </div>
        <a href="<?php echo e(route('admin.orders')); ?>" style="font-size:12px;color:#2563eb;font-weight:600;text-decoration:none;padding:6px 14px;background:#eff6ff;border-radius:8px;border:1px solid #bfdbfe">
            Lihat Semua →
        </a>
    </div>
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="background:#f8fafc">
                    <th style="padding:10px 16px;text-align:left;font-size:10px;font-weight:700;color:#6b7280;letter-spacing:.06em">ORDER</th>
                    <th style="padding:10px 16px;text-align:left;font-size:10px;font-weight:700;color:#6b7280;letter-spacing:.06em">PELANGGAN</th>
                    <th style="padding:10px 16px;text-align:left;font-size:10px;font-weight:700;color:#6b7280;letter-spacing:.06em">LAYANAN</th>
                    <th style="padding:10px 16px;text-align:left;font-size:10px;font-weight:700;color:#6b7280;letter-spacing:.06em">PEKERJA</th>
                    <th style="padding:10px 16px;text-align:center;font-size:10px;font-weight:700;color:#6b7280;letter-spacing:.06em">RATING</th>
                    <th style="padding:10px 16px;text-align:left;font-size:10px;font-weight:700;color:#6b7280;letter-spacing:.06em">STATUS</th>
                </tr>
            </thead>
            <tbody>
                <?php $ac = [['#eff6ff','#2563eb'],['#f0fdf4','#16a34a'],['#fefce8','#ca8a04'],['#fdf4ff','#9333ea'],['#fff7ed','#ea580c']]; ?>
                <?php $__empty_1 = true; $__currentLoopData = $recentOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php [$bg,$tx] = $ac[$i % 5]; ?>
                <tr style="border-top:1px solid #f1f5f9;transition:background .12s"
                    onmouseover="this.style.background='#fafbfc'" onmouseout="this.style.background=''">
                    <td style="padding:12px 16px;color:#374151;font-family:monospace;font-size:12px;font-weight:600">
                        #<?php echo e(str_pad($order->id_order,5,'0',STR_PAD_LEFT)); ?>

                    </td>
                    <td style="padding:12px 16px">
                        <div style="display:flex;align-items:center;gap:8px">
                            <div style="width:28px;height:28px;border-radius:50%;background:<?php echo e($bg); ?>;color:<?php echo e($tx); ?>;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;flex-shrink:0">
                                <?php echo e(strtoupper(substr($order->user->nama??'U',0,1))); ?>

                            </div>
                            <span style="font-size:13px;font-weight:500;color:#111827"><?php echo e($order->user->nama ?? '-'); ?></span>
                        </div>
                    </td>
                    <td style="padding:12px 16px;font-size:13px;color:#374151"><?php echo e($order->layanan->nama_layanan ?? '-'); ?></td>
                    <td style="padding:12px 16px;font-size:13px;color:#374151"><?php echo e($order->tukang->nama ?? '-'); ?></td>
                    <td style="padding:12px 16px;text-align:center">
                        <?php if($order->review): ?>
                        <span style="background:#fefce8;color:#ca8a04;font-size:12px;font-weight:700;padding:2px 9px;border-radius:20px"><?php echo e($order->review->rating); ?>★</span>
                        <?php else: ?>
                        <span style="color:#d1d5db;font-size:12px">—</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding:12px 16px">
                        <?php if($order->review): ?>
                        <span style="background:#dcfce7;color:#15803d;font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px">Selesai</span>
                        <?php else: ?>
                        <span style="background:#dbeafe;color:#1d4ed8;font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px">Aktif</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="6" style="padding:48px;text-align:center;color:#9ca3af;font-size:13px">
                    <i class="fas fa-inbox" style="font-size:24px;display:block;margin-bottom:8px;opacity:.3"></i>
                    Belum ada aktivitas pada periode ini
                </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if($recentOrders->count() > 0): ?>
    <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 20px;border-top:1px solid #f1f5f9;background:#fafbfc">
        <span style="font-size:12px;color:#6b7280">Menampilkan <?php echo e($recentOrders->count()); ?> dari <?php echo e($stats['orders']); ?> pesanan</span>
        <a href="<?php echo e(route('admin.orders')); ?>" style="font-size:12px;font-weight:600;color:#2563eb;text-decoration:none">Lihat selengkapnya →</a>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<?php if(in_array($view, ['summary','orders','performance'])): ?>
<script>
new Chart(document.getElementById('trendChart'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($chartLabels); ?>,
        datasets: [{
            label: 'Pesanan',
            data: <?php echo json_encode($chartData); ?>,
            backgroundColor: (ctx) => {
                const g = ctx.chart.ctx.createLinearGradient(0, 0, 0, 200);
                g.addColorStop(0, '#3b82f6');
                g.addColorStop(1, '#6366f1');
                return g;
            },
            borderRadius: 7,
            borderSkipped: false,
            maxBarThickness: 32,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: { label: ctx => '  ' + ctx.parsed.y + ' pesanan' },
                backgroundColor: '#0d1b2e',
                padding: 10,
                cornerRadius: 8,
            }
        },
        scales: {
            x: { grid: { display: false }, border: { display: false }, ticks: { font: { size: 10 }, color: '#9ca3af' } },
            y: { grid: { color: '#f1f5f9' }, border: { display: false }, ticks: { font: { size: 10 }, color: '#9ca3af', stepSize: 1 }, beginAtZero: true }
        }
    }
});
</script>
<?php endif; ?>
<style>
@keyframes  pulse { 0%, 100% { opacity: 1; } 50% { opacity: .4; } }
</style>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('admin.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\laravel web app\tomas-app\resources\views\admin\dashboard.blade.php ENDPATH**/ ?>