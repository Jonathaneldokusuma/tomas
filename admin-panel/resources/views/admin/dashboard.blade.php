@extends('admin.layouts.app')
@section('title', 'Dashboard')

@section('content')

{{-- ═══ Page Header ═══════════════════════════════════════════════════════════ --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:10px">
    <div>
        <h1 style="font-size:22px;font-weight:800;color:#0d1b2e;line-height:1.2">Dashboard</h1>
        <p style="font-size:12px;color:#6b7280;margin-top:3px">
            <i class="fas fa-clock" style="font-size:10px;margin-right:3px;color:#9ca3af"></i>
            {{ now()->format('l, d F Y') }}
            &nbsp;·&nbsp; {{ $periodLabel }}
            @if($tukangId)
            &nbsp;·&nbsp;
            <span style="color:#2563eb;font-weight:600">{{ $tukangList->firstWhere('id_tukang', $tukangId)?->nama ?? 'Tukang' }}</span>
            @endif
        </p>
    </div>
    <span style="background:#dcfce7;color:#15803d;font-size:11px;font-weight:600;padding:6px 14px;border-radius:20px;display:flex;align-items:center;gap:6px">
        <span style="width:7px;height:7px;background:#16a34a;border-radius:50%;display:inline-block;animation:pulse 2s infinite"></span>
        Live
    </span>
</div>

@if(!empty($dashboardError))
<div style="background:#fff7ed;border:1px solid #fdba74;border-radius:14px;padding:14px 16px;margin-bottom:20px;color:#9a3412;font-size:13px;font-weight:500;display:flex;align-items:flex-start;gap:10px">
    <i class="fas fa-triangle-exclamation" style="font-size:16px;margin-top:1px"></i>
    <div>
        <div style="font-weight:700;margin-bottom:3px">Database belum siap</div>
        <div>{{ $dashboardError }}</div>
    </div>
</div>
@endif

{{-- ═══ Filter Bar ════════════════════════════════════════════════════════════ --}}
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:16px 20px;margin-bottom:20px;box-shadow:0 1px 4px rgba(0,0,0,.04)">

    {{-- Row 1: Period + Tukang --}}
    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">

        {{-- Period Buttons --}}
        <div style="display:flex;gap:3px;background:#f1f5f9;border-radius:10px;padding:3px">
            @foreach(['today'=>'Hari Ini','week'=>'Minggu Ini','month'=>'Bulan Ini','all'=>'Semua'] as $pv=>$pl)
            <a href="{{ route('admin.dashboard', array_merge(request()->except('period'), ['period'=>$pv, 'view'=>$view])) }}"
               style="padding:7px 16px;border-radius:8px;font-size:12px;font-weight:600;text-decoration:none;white-space:nowrap;transition:all .15s;{{ $period==$pv ? 'background:#fff;color:#1d4ed8;box-shadow:0 1px 4px rgba(0,0,0,.1)' : 'color:#6b7280' }}">
                {{ $pl }}
            </a>
            @endforeach
        </div>

        {{-- Tukang Filter --}}
        <form method="GET" style="display:flex;align-items:center;gap:8px">
            <input type="hidden" name="period"   value="{{ $period }}">
            <input type="hidden" name="view"     value="{{ $view }}">
            <label style="font-size:12px;font-weight:600;color:#374151;white-space:nowrap">
                <i class="fas fa-filter" style="font-size:10px;color:#9ca3af;margin-right:3px"></i> Pekerja:
            </label>
            <select name="tukang_id" onchange="this.form.submit()"
                    style="border:1px solid #e2e8f0;border-radius:8px;padding:7px 32px 7px 10px;font-size:12px;color:#374151;background:#f8fafc;cursor:pointer;min-width:160px">
                <option value="">— Semua —</option>
                @foreach($tukangList as $t)
                <option value="{{ $t->id_tukang }}" {{ $tukangId==$t->id_tukang ? 'selected' : '' }}>{{ $t->nama }}</option>
                @endforeach
            </select>
            @if($tukangId)
            <a href="{{ route('admin.dashboard', ['period'=>$period,'view'=>$view]) }}"
               style="font-size:11px;color:#ef4444;font-weight:600;text-decoration:none;white-space:nowrap;padding:7px 10px;border-radius:8px;border:1px solid #fecaca;background:#fef2f2">
                <i class="fas fa-xmark"></i> Reset
            </a>
            @endif
        </form>
    </div>

    {{-- Divider --}}
    <div style="height:1px;background:#f1f5f9;margin:12px 0"></div>

    {{-- Row 2: View Tabs --}}
    <div style="display:flex;gap:6px;flex-wrap:wrap">
        @php
        $viewTabs = [
            'summary'     => ['icon'=>'fa-gauge',    'label'=>'Ringkasan'],
            'workers'     => ['icon'=>'fa-hammer',   'label'=>'Pekerja'],
            'customers'   => ['icon'=>'fa-users',    'label'=>'Pelanggan'],
            'orders'      => ['icon'=>'fa-receipt',  'label'=>'Pesanan'],
            'performance' => ['icon'=>'fa-chart-bar','label'=>'Performa'],
        ];
        @endphp
        @foreach($viewTabs as $vv=>$vd)
        <a href="{{ route('admin.dashboard', array_merge(request()->query(), ['view'=>$vv])) }}"
           style="display:inline-flex;align-items:center;gap:6px;padding:7px 16px;border-radius:8px;font-size:12px;font-weight:600;text-decoration:none;transition:all .15s;{{ $view==$vv ? 'background:#2563eb;color:#fff;box-shadow:0 2px 8px rgba(37,99,235,.3)' : 'background:#f8fafc;color:#6b7280;border:1px solid #e2e8f0' }}">
            <i class="fas {{ $vd['icon'] }}" style="font-size:11px"></i> {{ $vd['label'] }}
        </a>
        @endforeach
    </div>
</div>

{{-- ═══ Stats Cards ════════════════════════════════════════════════════════════ --}}
@php
$statCards = [
    ['label'=>'Total Pengguna',   'val'=>number_format($stats['users']),          'icon'=>'fa-users',             'ibg'=>'#eff6ff','ic'=>'#2563eb','sub'=>'Terdaftar'],
    ['label'=>'Pekerja Aktif',    'val'=>number_format($stats['tukang_aktif']),   'icon'=>'fa-screwdriver-wrench','ibg'=>'#fff7ed','ic'=>'#ea580c','sub'=>'dari '.$stats['tukang'].' total'],
    ['label'=>'Verifikasi Pending','val'=>number_format($stats['pending_verification']),'icon'=>'fa-id-card-clip','ibg'=>'#fef3c7','ic'=>'#b45309','sub'=>'Menunggu review'],
    ['label'=>'Badge Aktif',      'val'=>number_format($stats['badges']),        'icon'=>'fa-award',            'ibg'=>'#fdf4ff','ic'=>'#9333ea','sub'=>'User + tukang'],
    ['label'=>'Pesanan',          'val'=>number_format($stats['orders']),         'icon'=>'fa-receipt',           'ibg'=>'#f0fdf4','ic'=>'#16a34a','sub'=>$periodLabel],
    ['label'=>'Ulasan',           'val'=>number_format($stats['reviews']),        'icon'=>'fa-star',              'ibg'=>'#fefce8','ic'=>'#ca8a04','sub'=>$periodLabel],
    ['label'=>'Rating Rata-rata', 'val'=>($stats['avg_rating'] ?: '—'),           'icon'=>'fa-chart-line',        'ibg'=>'#fdf4ff','ic'=>'#9333ea','sub'=>'Dari ulasan'],
    ['label'=>'Total Pesanan',    'val'=>number_format($stats['orders_total']),   'icon'=>'fa-database',          'ibg'=>'#f0f9ff','ic'=>'#0ea5e9','sub'=>'Sepanjang waktu'],
    ['label'=>'Gross Revenue',    'val'=>'Rp'.number_format($finance['gross_revenue'],0,',','.'), 'icon'=>'fa-wallet', 'ibg'=>'#ecfeff','ic'=>'#0891b2','sub'=>'Pembayaran paid'],
    ['label'=>'Net Revenue',      'val'=>'Rp'.number_format($finance['net_revenue'],0,',','.'),   'icon'=>'fa-coins',  'ibg'=>'#f0fdf4','ic'=>'#16a34a','sub'=>'Setelah potongan deposit'],
];
@endphp
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(155px,1fr));gap:12px;margin-bottom:20px">
    @foreach($statCards as $c)
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:16px 18px;position:relative;overflow:hidden;transition:transform .18s,box-shadow .18s;cursor:default"
         onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 10px 28px rgba(37,99,235,.1)'"
         onmouseout="this.style.transform='';this.style.boxShadow=''">
        <div style="position:absolute;top:-16px;right:-16px;width:70px;height:70px;background:{{ $c['ibg'] }};border-radius:50%;opacity:.6"></div>
        <div style="width:36px;height:36px;background:{{ $c['ibg'] }};border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:12px;position:relative">
            <i class="fas {{ $c['icon'] }}" style="color:{{ $c['ic'] }};font-size:14px"></i>
        </div>
        <div style="font-size:26px;font-weight:800;color:#0d1b2e;line-height:1;position:relative">{{ $c['val'] }}</div>
        <div style="font-size:12px;font-weight:600;color:#374151;margin-top:5px">{{ $c['label'] }}</div>
        <div style="font-size:10px;color:#9ca3af;margin-top:2px">{{ $c['sub'] }}</div>
    </div>
    @endforeach
</div>

<div style="display:grid;grid-template-columns:1.2fr .8fr;gap:14px;margin-bottom:20px">
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:18px">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:12px">
            <div>
                <h3 style="font-size:14px;font-weight:700;color:#0d1b2e">Aktivitas Admin Terbaru</h3>
                <p style="font-size:11px;color:#9ca3af;margin-top:2px">Audit trail untuk aksi backend penting.</p>
            </div>
            <i class="fas fa-clipboard-list" style="color:#2563eb"></i>
        </div>
        <div style="display:flex;flex-direction:column;gap:10px">
            @forelse($recentActivities as $activity)
                <div style="display:flex;align-items:flex-start;gap:10px;padding:10px 12px;border:1px solid #f1f5f9;border-radius:12px;background:#fafbff">
                    <div style="width:30px;height:30px;border-radius:10px;background:#eff6ff;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <i class="fas fa-shield-halved" style="color:#2563eb;font-size:12px"></i>
                    </div>
                    <div style="min-width:0;flex:1">
                        <div style="font-size:13px;font-weight:700;color:#111827">{{ $activity->action }}</div>
                        <div style="font-size:11px;color:#6b7280;margin-top:2px">
                            {{ $activity->admin_username ?: 'admin' }}
                            @if($activity->subject_name)
                                · {{ $activity->subject_name }}
                            @endif
                        </div>
                    </div>
                    <div style="font-size:11px;color:#9ca3af;white-space:nowrap">{{ $activity->created_at?->diffForHumans() }}</div>
                </div>
            @empty
                <div style="padding:22px;text-align:center;color:#9ca3af;font-size:13px">
                    Belum ada aktivitas admin yang tercatat.
                </div>
            @endforelse
        </div>
    </div>
    <div style="display:flex;flex-direction:column;gap:14px">
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:18px">
            <div style="font-size:14px;font-weight:700;color:#0d1b2e;margin-bottom:6px">Backend Health</div>
            <div style="font-size:12px;color:#6b7280;line-height:1.55">
                Admin panel sudah terhubung ke backend live, verifikasi bisa melihat foto dokumen, badge bisa diproses untuk user/tukang, dan aktivitas penting terekam.
            </div>
        </div>
        <div style="background:linear-gradient(135deg,#2563eb,#7c3aed);border-radius:14px;padding:18px;color:#fff">
            <div style="font-size:14px;font-weight:700;margin-bottom:6px">Quick Actions</div>
            <div style="display:flex;flex-direction:column;gap:8px">
                <a href="{{ route('admin.tukang.verifikasi') }}" style="color:#fff;text-decoration:none;font-size:12px;font-weight:600">• Buka verifikasi tukang</a>
                <a href="{{ route('admin.badges') }}" style="color:#fff;text-decoration:none;font-size:12px;font-weight:600">• Kelola badge live</a>
                <a href="{{ route('admin.broadcast') }}" style="color:#fff;text-decoration:none;font-size:12px;font-weight:600">• Kirim broadcast</a>
            </div>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:20px">
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:18px">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:12px">
            <div>
                <h3 style="font-size:14px;font-weight:700;color:#0d1b2e">Status Order</h3>
                <p style="font-size:11px;color:#9ca3af;margin-top:2px">Komposisi pesanan saat ini.</p>
            </div>
            <i class="fas fa-chart-pie" style="color:#2563eb"></i>
        </div>
        <canvas id="statusChart" style="max-height:220px"></canvas>
    </div>
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:18px">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:12px">
            <div>
                <h3 style="font-size:14px;font-weight:700;color:#0d1b2e">Level Kesulitan</h3>
                <p style="font-size:11px;color:#9ca3af;margin-top:2px">Distribusi kompleksitas order.</p>
            </div>
            <i class="fas fa-chart-donut" style="color:#9333ea"></i>
        </div>
        <canvas id="difficultyChart" style="max-height:220px"></canvas>
    </div>
</div>

<div style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:20px">
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:16px 18px">
        <div style="font-size:11px;color:#6b7280;font-weight:600">Pending Revenue</div>
        <div style="font-size:22px;font-weight:800;color:#ea580c">Rp{{ number_format($finance['pending_revenue'],0,',','.') }}</div>
        <div style="font-size:11px;color:#9ca3af">Menunggu settlement</div>
    </div>
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:16px 18px">
        <div style="font-size:11px;color:#6b7280;font-weight:600">Deposit Potential</div>
        <div style="font-size:22px;font-weight:800;color:#7c3aed">Rp{{ number_format($finance['deposit_potential'],0,',','.') }}</div>
        <div style="font-size:11px;color:#9ca3af">Estimasi potongan deposit</div>
    </div>
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:16px 18px">
        <div style="font-size:11px;color:#6b7280;font-weight:600">Deposit Deducted</div>
        <div style="font-size:22px;font-weight:800;color:#16a34a">Rp{{ number_format($finance['deposit_deducted'],0,',','.') }}</div>
        <div style="font-size:11px;color:#9ca3af">Sudah dipotong</div>
    </div>
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:16px 18px">
        <div style="font-size:11px;color:#6b7280;font-weight:600">Net to Admin</div>
        <div style="font-size:22px;font-weight:800;color:#2563eb">Rp{{ number_format($finance['net_revenue'],0,',','.') }}</div>
        <div style="font-size:11px;color:#9ca3af">Gross minus deposit</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin-bottom:20px">
    <a href="{{ route('admin.tukang.verifikasi') }}" style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:16px 18px;text-decoration:none;color:inherit;display:block">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px">
            <div>
                <div style="font-size:13px;font-weight:700;color:#0d1b2e">Buka Verifikasi</div>
                <div style="font-size:12px;color:#6b7280;margin-top:3px">Lihat foto KTP dan selfie tukang.</div>
            </div>
            <i class="fas fa-arrow-right" style="color:#2563eb"></i>
        </div>
    </a>
    <a href="{{ route('admin.badges') }}" style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:16px 18px;text-decoration:none;color:inherit;display:block">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px">
            <div>
                <div style="font-size:13px;font-weight:700;color:#0d1b2e">Kelola Badge</div>
                <div style="font-size:12px;color:#6b7280;margin-top:3px">Tambah badge user atau tukang secara live.</div>
            </div>
            <i class="fas fa-award" style="color:#9333ea"></i>
        </div>
    </a>
</div>


{{-- ═══════════════════════════════════════════════════════════════════════════
     SECTION: TOP WORKERS (Ringkasan + Pekerja)
═══════════════════════════════════════════════════════════════════════════ --}}
@if(in_array($view, ['summary','workers']))
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;margin-bottom:20px;box-shadow:0 1px 4px rgba(0,0,0,.04)">

    {{-- Header --}}
    <div style="padding:16px 20px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;background:linear-gradient(135deg,#fffbeb 0%,#fff7ed 100%)">
        <div style="display:flex;align-items:center;gap:10px">
            <div style="width:32px;height:32px;background:linear-gradient(135deg,#f97316,#ea580c);border-radius:10px;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(234,88,12,.3)">
                <i class="fas fa-trophy" style="color:#fff;font-size:13px"></i>
            </div>
            <div>
                <h3 style="font-size:14px;font-weight:700;color:#0d1b2e">Top Pekerja — Orderan Terbanyak</h3>
                <p style="font-size:11px;color:#9ca3af;margin-top:1px">{{ $periodLabel }}</p>
            </div>
        </div>
        @if($view === 'summary')
        <a href="{{ route('admin.dashboard', array_merge(request()->query(), ['view'=>'workers'])) }}"
           style="font-size:12px;color:#ea580c;font-weight:600;text-decoration:none;padding:6px 14px;background:#fff7ed;border-radius:8px;border:1px solid #fed7aa">
            Lihat Detail →
        </a>
        @endif
    </div>

    @if($topWorkers->count() > 0)

    {{-- Podium Top 3 --}}
    <div style="padding:24px 20px 20px;background:linear-gradient(135deg,#fafbff 0%,#f0f4ff 100%);border-bottom:1px solid #f1f5f9">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:14px;max-width:580px;margin:0 auto">
            @foreach($topWorkers->take(3) as $i => $t)
            @php
            $medals    = ['🥇','🥈','🥉'];
            $podBgs    = ['linear-gradient(145deg,#fffbeb,#fef3c7)','linear-gradient(145deg,#f8fafc,#e2e8f0)','linear-gradient(145deg,#fff7ed,#fed7aa)'];
            $podBorder = ['#fbbf24','#9ca3af','#f97316'];
            $podColor  = ['#b45309','#4b5563','#c2410c'];
            $podShadow = ['rgba(251,191,36,.25)','rgba(0,0,0,.08)','rgba(249,115,22,.2)'];
            @endphp
            <div style="background:{{ $podBgs[$i] }};border:2px solid {{ $podBorder[$i] }};border-radius:16px;padding:18px 12px;text-align:center;box-shadow:0 4px 16px {{ $podShadow[$i] }};position:relative">
                @if($i === 0)
                <div style="position:absolute;top:-10px;left:50%;transform:translateX(-50%);background:linear-gradient(135deg,#fbbf24,#f59e0b);color:#fff;font-size:9px;font-weight:700;padding:2px 10px;border-radius:20px;white-space:nowrap;letter-spacing:.04em">
                    # 1 TERBAIK
                </div>
                @endif
                <div style="font-size:26px;margin-bottom:8px">{{ $medals[$i] }}</div>
                <div style="width:48px;height:48px;background:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:800;color:{{ $podColor[$i] }};margin:0 auto 10px;box-shadow:0 3px 10px rgba(0,0,0,.12)">
                    {{ strtoupper(substr($t['nama'],0,1)) }}
                </div>
                <div style="font-size:13px;font-weight:700;color:#111827;margin-bottom:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis" title="{{ $t['nama'] }}">
                    {{ $t['nama'] }}
                </div>
                <div style="font-size:10px;color:#6b7280;margin-bottom:10px">{{ $t['kategori'] ?: 'Umum' }}</div>
                <div style="font-size:28px;font-weight:800;color:{{ $podColor[$i] }};line-height:1">{{ $t['orders_count'] }}</div>
                <div style="font-size:10px;color:#9ca3af;margin-bottom:6px">pesanan</div>
                @if($t['avg_rating'] > 0)
                <div style="display:inline-block;background:rgba(0,0,0,.07);border-radius:20px;padding:2px 10px;font-size:11px;font-weight:700;color:{{ $podColor[$i] }}">
                    {{ $t['avg_rating'] }} ★
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    {{-- Rank 4-10 --}}
    @if($topWorkers->count() > 3)
    <div style="padding:8px 20px 12px">
        <div style="font-size:10px;font-weight:700;color:#9ca3af;letter-spacing:.07em;padding:10px 4px 6px">PERINGKAT SELANJUTNYA</div>
        @php $maxO = max(1, $topWorkers->first()['orders_count']); @endphp
        @foreach($topWorkers->skip(3) as $i => $t)
        @php $rank = $i + 4; @endphp
        <div style="display:flex;align-items:center;gap:10px;padding:9px 10px;border-radius:10px;transition:background .12s"
             onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
            <div style="width:26px;text-align:center;font-size:12px;font-weight:700;color:#9ca3af;flex-shrink:0">{{ $rank }}</div>
            <div style="width:34px;height:34px;border-radius:50%;background:#eff6ff;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#2563eb;flex-shrink:0">
                {{ strtoupper(substr($t['nama'],0,1)) }}
            </div>
            <div style="flex:1;min-width:0">
                <div style="font-size:13px;font-weight:600;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $t['nama'] }}</div>
                <div style="font-size:10px;color:#9ca3af">{{ $t['kategori'] ?: 'Umum' }}</div>
            </div>
            <div style="width:90px;flex-shrink:0">
                <div style="background:#f1f5f9;border-radius:4px;height:5px">
                    <div style="background:linear-gradient(90deg,#3b82f6,#6366f1);height:100%;border-radius:4px;width:{{ $maxO > 0 ? min(100, round(($t['orders_count']/$maxO)*100)) : 0 }}%"></div>
                </div>
            </div>
            <div style="text-align:right;flex-shrink:0;min-width:36px">
                <div style="font-size:15px;font-weight:800;color:#0d1b2e">{{ $t['orders_count'] }}</div>
                <div style="font-size:9px;color:#9ca3af">pesanan</div>
            </div>
            @if($t['avg_rating'] > 0)
            <div style="background:#fefce8;color:#ca8a04;font-size:11px;font-weight:700;padding:3px 9px;border-radius:20px;flex-shrink:0">{{ $t['avg_rating'] }}★</div>
            @else
            <div style="width:52px"></div>
            @endif
        </div>
        @endforeach
    </div>
    @endif

    @else
    <div style="padding:48px;text-align:center;color:#9ca3af;font-size:13px">
        <i class="fas fa-inbox" style="font-size:28px;display:block;margin-bottom:8px;opacity:.4"></i>
        Belum ada data pekerja
    </div>
    @endif
</div>
@endif


{{-- ═══════════════════════════════════════════════════════════════════════════
     SECTION: CHART + TOP RATED (Ringkasan + Pesanan + Performa)
═══════════════════════════════════════════════════════════════════════════ --}}
@if(in_array($view, ['summary','orders','performance']))
<div style="display:grid;grid-template-columns:1fr 290px;gap:14px;margin-bottom:20px">

    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:20px;box-shadow:0 1px 4px rgba(0,0,0,.04)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
            <div>
                <h3 style="font-size:14px;font-weight:700;color:#0d1b2e">Tren Pesanan</h3>
                <p style="font-size:11px;color:#9ca3af;margin-top:2px">{{ $periodLabel }}</p>
            </div>
            <span style="background:#eff6ff;color:#2563eb;font-size:11px;font-weight:700;padding:5px 14px;border-radius:20px">
                {{ array_sum($chartData) }} total
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
        @forelse($topRated as $i => $t)
        <div style="display:flex;align-items:center;gap:9px;margin-bottom:8px;background:rgba(255,255,255,.12);border-radius:10px;padding:8px 10px"
             onmouseover="this.style.background='rgba(255,255,255,.2)'" onmouseout="this.style.background='rgba(255,255,255,.12)'">
            <div style="width:22px;height:22px;border-radius:50%;background:{{ ['#fbbf24','#9ca3af','#cd7c2e','#60a5fa','#34d399'][$i] ?? '#ffffff44' }};display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:800;color:#1a2b47;flex-shrink:0">
                {{ $i+1 }}
            </div>
            <div style="flex:1;min-width:0">
                <div style="font-size:12px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $t['nama'] }}</div>
                <div style="font-size:10px;color:rgba(255,255,255,.55)">{{ $t['reviews_count'] }} ulasan</div>
            </div>
            <div style="font-size:13px;font-weight:800;color:#fde68a;flex-shrink:0">{{ $t['avg_rating'] }}★</div>
        </div>
        @empty
        <div style="text-align:center;color:rgba(255,255,255,.45);font-size:12px;margin:auto 0;padding:20px 0">
            <i class="fas fa-star" style="display:block;font-size:22px;margin-bottom:6px;opacity:.3"></i>
            Belum ada data
        </div>
        @endforelse
    </div>
</div>
@endif


{{-- ═══════════════════════════════════════════════════════════════════════════
     SECTION: PERFORMANCE TABLE (Pekerja + Performa)
═══════════════════════════════════════════════════════════════════════════ --}}
@if(in_array($view, ['workers','performance']))
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;margin-bottom:20px;box-shadow:0 1px 4px rgba(0,0,0,.04)">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:15px 20px;border-bottom:1px solid #f1f5f9">
        <div>
            <h3 style="font-size:14px;font-weight:700;color:#0d1b2e">Performa Lengkap Pekerja</h3>
            <p style="font-size:11px;color:#9ca3af;margin-top:2px">{{ $periodLabel }} &middot; diurutkan berdasarkan pesanan terbanyak</p>
        </div>
        <a href="{{ route('admin.tukang') }}" style="font-size:12px;color:#2563eb;font-weight:600;text-decoration:none;padding:6px 14px;background:#eff6ff;border-radius:8px;border:1px solid #bfdbfe">
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
                @php $maxOrders = max(1, $tukangPerformance->max('orders_count')); @endphp
                @forelse($tukangPerformance as $i => $t)
                @php
                    $rc  = $t['avg_rating'] >= 4 ? '#16a34a' : ($t['avg_rating'] >= 2.5 ? '#ca8a04' : ($t['avg_rating'] > 0 ? '#dc2626' : '#9ca3af'));
                    $rb  = $t['avg_rating'] >= 4 ? '#dcfce7' : ($t['avg_rating'] >= 2.5 ? '#fefce8' : ($t['avg_rating'] > 0 ? '#fef2f2' : '#f1f5f9'));
                    $barW = $maxOrders > 0 ? min(100, round(($t['orders_count']/$maxOrders)*100)) : 0;
                @endphp
                <tr style="border-top:1px solid #f1f5f9;transition:background .12s"
                    onmouseover="this.style.background='#fafbfc'" onmouseout="this.style.background=''">
                    <td style="padding:12px 16px;font-size:13px;font-weight:700;color:#9ca3af">
                        @if($i < 3) {{ ['🥇','🥈','🥉'][$i] }} @else {{ $i+1 }} @endif
                    </td>
                    <td style="padding:12px 16px">
                        <div style="display:flex;align-items:center;gap:9px">
                            <div style="width:34px;height:34px;border-radius:50%;background:#eff6ff;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:#2563eb;flex-shrink:0;border:2px solid #bfdbfe">
                                {{ strtoupper(substr($t['nama'],0,1)) }}
                            </div>
                            <div style="font-size:13px;font-weight:600;color:#111827">{{ $t['nama'] }}</div>
                        </div>
                    </td>
                    <td style="padding:12px 16px">
                        @if($t['kategori'])
                        <span style="background:#f1f5f9;color:#374151;font-size:11px;font-weight:500;padding:3px 10px;border-radius:20px">{{ $t['kategori'] }}</span>
                        @else
                        <span style="color:#d1d5db;font-size:12px">—</span>
                        @endif
                    </td>
                    <td style="padding:12px 16px;text-align:center;font-size:18px;font-weight:800;color:#0d1b2e">{{ $t['orders_count'] }}</td>
                    <td style="padding:12px 16px;text-align:center;font-size:13px;color:#374151">{{ $t['reviews_count'] }}</td>
                    <td style="padding:12px 16px;text-align:center">
                        @if($t['avg_rating'] > 0)
                        <span style="background:{{ $rb }};color:{{ $rc }};font-size:12px;font-weight:700;padding:3px 10px;border-radius:20px">{{ $t['avg_rating'] }}★</span>
                        @else
                        <span style="color:#d1d5db;font-size:12px">—</span>
                        @endif
                    </td>
                    <td style="padding:12px 20px;min-width:110px">
                        <div style="background:#f1f5f9;border-radius:4px;height:6px">
                            <div style="background:linear-gradient(90deg,#3b82f6,#6366f1);height:100%;border-radius:4px;width:{{ $barW }}%"></div>
                        </div>
                        <div style="font-size:10px;color:#9ca3af;margin-top:3px">{{ $barW }}%</div>
                    </td>
                    <td style="padding:12px 16px;text-align:center">
                        <span style="background:{{ $t['status_aktif'] ? '#dcfce7':'#f1f5f9' }};color:{{ $t['status_aktif'] ? '#15803d':'#9ca3af' }};font-size:11px;font-weight:600;padding:3px 12px;border-radius:20px">
                            {{ $t['status_aktif'] ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" style="padding:48px;text-align:center;color:#9ca3af;font-size:13px">
                    <i class="fas fa-inbox" style="font-size:24px;display:block;margin-bottom:8px;opacity:.3"></i>
                    Belum ada data pekerja
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif


{{-- ═══════════════════════════════════════════════════════════════════════════
     SECTION: TOP CUSTOMERS (Ringkasan + Pelanggan)
═══════════════════════════════════════════════════════════════════════════ --}}
@if(in_array($view, ['summary','customers']))
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;margin-bottom:20px;box-shadow:0 1px 4px rgba(0,0,0,.04)">
    <div style="padding:16px 20px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between">
        <div style="display:flex;align-items:center;gap:10px">
            <div style="width:32px;height:32px;background:linear-gradient(135deg,#2563eb,#4f46e5);border-radius:10px;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(37,99,235,.3)">
                <i class="fas fa-users" style="color:#fff;font-size:13px"></i>
            </div>
            <div>
                <h3 style="font-size:14px;font-weight:700;color:#0d1b2e">Top Pelanggan Aktif</h3>
                <p style="font-size:11px;color:#9ca3af;margin-top:1px">{{ $periodLabel }}</p>
            </div>
        </div>
        <a href="{{ route('admin.users') }}" style="font-size:12px;color:#2563eb;font-weight:600;text-decoration:none;padding:6px 14px;background:#eff6ff;border-radius:8px;border:1px solid #bfdbfe">
            Lihat Semua →
        </a>
    </div>
    <div style="padding:16px 20px">
        @if($topCustomers->isEmpty())
        <div style="text-align:center;color:#9ca3af;font-size:13px;padding:28px 0">
            <i class="fas fa-users" style="font-size:28px;display:block;margin-bottom:8px;opacity:.3"></i>
            Belum ada data pelanggan
        </div>
        @else
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(190px,1fr));gap:10px">
            @foreach($topCustomers as $i => $c)
            @php
            $abgs = ['#eff6ff','#f0fdf4','#fefce8','#fdf4ff','#fff7ed','#f0f9ff','#fef2f2','#f8fafc','#fafffe','#f5f3ff'];
            $atxs = ['#2563eb','#16a34a','#ca8a04','#9333ea','#ea580c','#0ea5e9','#dc2626','#64748b','#0d9488','#7c3aed'];
            [$abg, $atx] = [$abgs[$i % 10], $atxs[$i % 10]];
            @endphp
            <div style="display:flex;align-items:center;gap:10px;padding:10px 12px;background:#f8fafc;border-radius:10px;border:1px solid #f1f5f9;transition:border-color .15s,box-shadow .15s"
                 onmouseover="this.style.borderColor='#bfdbfe';this.style.boxShadow='0 2px 8px rgba(37,99,235,.08)'"
                 onmouseout="this.style.borderColor='#f1f5f9';this.style.boxShadow=''">
                <div style="width:38px;height:38px;border-radius:50%;background:{{ $abg }};color:{{ $atx }};display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;flex-shrink:0">
                    {{ strtoupper(substr($c->nama??'U',0,1)) }}
                </div>
                <div style="flex:1;min-width:0">
                    <div style="font-size:13px;font-weight:600;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $c->nama }}</div>
                    <div style="font-size:11px;color:#9ca3af">{{ $c->orders_count }} pesanan</div>
                </div>
                @if($i < 3)
                <div style="font-size:16px;flex-shrink:0">{{ ['🥇','🥈','🥉'][$i] }}</div>
                @else
                <div style="font-size:13px;font-weight:700;color:#374151;flex-shrink:0;min-width:20px;text-align:right">{{ $c->orders_count }}</div>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endif


{{-- ═══════════════════════════════════════════════════════════════════════════
     SECTION: RECENT ACTIVITY (Ringkasan + Pesanan + Pelanggan + Pekerja)
═══════════════════════════════════════════════════════════════════════════ --}}
@if(in_array($view, ['summary','orders','customers','workers']))
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.04)">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:15px 20px;border-bottom:1px solid #f1f5f9">
        <div>
            <h3 style="font-size:14px;font-weight:700;color:#0d1b2e">Aktivitas Terbaru</h3>
            <p style="font-size:11px;color:#9ca3af;margin-top:2px">{{ $periodLabel }}</p>
        </div>
        <a href="{{ route('admin.orders') }}" style="font-size:12px;color:#2563eb;font-weight:600;text-decoration:none;padding:6px 14px;background:#eff6ff;border-radius:8px;border:1px solid #bfdbfe">
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
                @php $ac = [['#eff6ff','#2563eb'],['#f0fdf4','#16a34a'],['#fefce8','#ca8a04'],['#fdf4ff','#9333ea'],['#fff7ed','#ea580c']]; @endphp
                @forelse($recentOrders as $i => $order)
                @php [$bg,$tx] = $ac[$i % 5]; @endphp
                <tr style="border-top:1px solid #f1f5f9;transition:background .12s"
                    onmouseover="this.style.background='#fafbfc'" onmouseout="this.style.background=''">
                    <td style="padding:12px 16px;color:#374151;font-family:monospace;font-size:12px;font-weight:600">
                        #{{ str_pad($order->id_order,5,'0',STR_PAD_LEFT) }}
                    </td>
                    <td style="padding:12px 16px">
                        <div style="display:flex;align-items:center;gap:8px">
                            <div style="width:28px;height:28px;border-radius:50%;background:{{ $bg }};color:{{ $tx }};display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;flex-shrink:0">
                                {{ strtoupper(substr($order->user->nama??'U',0,1)) }}
                            </div>
                            <span style="font-size:13px;font-weight:500;color:#111827">{{ $order->user->nama ?? '-' }}</span>
                        </div>
                    </td>
                    <td style="padding:12px 16px;font-size:13px;color:#374151">{{ $order->layanan->nama_layanan ?? '-' }}</td>
                    <td style="padding:12px 16px;font-size:13px;color:#374151">{{ $order->tukang->nama ?? '-' }}</td>
                    <td style="padding:12px 16px;text-align:center">
                        @if($order->review)
                        <span style="background:#fefce8;color:#ca8a04;font-size:12px;font-weight:700;padding:2px 9px;border-radius:20px">{{ $order->review->rating }}★</span>
                        @else
                        <span style="color:#d1d5db;font-size:12px">—</span>
                        @endif
                    </td>
                    <td style="padding:12px 16px">
                        @if($order->review)
                        <span style="background:#dcfce7;color:#15803d;font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px">Selesai</span>
                        @else
                        <span style="background:#dbeafe;color:#1d4ed8;font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px">Aktif</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="padding:48px;text-align:center;color:#9ca3af;font-size:13px">
                    <i class="fas fa-inbox" style="font-size:24px;display:block;margin-bottom:8px;opacity:.3"></i>
                    Belum ada aktivitas pada periode ini
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($recentOrders->count() > 0)
    <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 20px;border-top:1px solid #f1f5f9;background:#fafbfc">
        <span style="font-size:12px;color:#6b7280">Menampilkan {{ $recentOrders->count() }} dari {{ $stats['orders'] }} pesanan</span>
        <a href="{{ route('admin.orders') }}" style="font-size:12px;font-weight:600;color:#2563eb;text-decoration:none">Lihat selengkapnya →</a>
    </div>
    @endif
</div>
@endif

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@if(in_array($view, ['summary','orders','performance']))
<script>
new Chart(document.getElementById('trendChart'), {
    type: 'bar',
    data: {
        labels: {!! json_encode($chartLabels) !!},
        datasets: [{
            label: 'Pesanan',
            data: {!! json_encode($chartData) !!},
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

const statusCtx = document.getElementById('statusChart');
if (statusCtx) {
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: ['Pending', 'Confirmed', 'In Progress', 'Done', 'Rejected'],
        datasets: [{
            data: {!! json_encode(array_values($orderStatusBreakdown)) !!},
            backgroundColor: ['#f59e0b', '#3b82f6', '#8b5cf6', '#16a34a', '#ef4444'],
            borderWidth: 0,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom', labels: { boxWidth: 10, usePointStyle: true } },
        },
        cutout: '64%',
    }
});
}

const diffCtx = document.getElementById('difficultyChart');
if (diffCtx) {
new Chart(diffCtx, {
    type: 'pie',
    data: {
        labels: ['Easy', 'Medium', 'Hard'],
        datasets: [{
            data: {!! json_encode(array_values($difficultyBreakdown)) !!},
            backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
            borderWidth: 0,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom', labels: { boxWidth: 10, usePointStyle: true } },
        }
    }
});
}
</script>
@endif
<style>
@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .4; } }
</style>
@endpush
@endsection

