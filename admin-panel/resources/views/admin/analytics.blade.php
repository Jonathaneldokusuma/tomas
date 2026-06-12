@extends('admin.layouts.app')
@section('title', 'Analytics')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@section('content')
<div style="display:flex;flex-wrap:wrap;align-items:end;justify-content:space-between;gap:14px;margin-bottom:18px">
    <div>
        <h1 style="font-size:28px;font-weight:800;color:#0f172a;margin-bottom:4px">Analytics</h1>
        <p style="color:#64748b;font-size:14px">Pantau order, pendapatan, status verifikasi, dan performa layanan dalam satu tab.</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
        @foreach(['today' => 'Hari ini', 'week' => 'Minggu', 'month' => 'Bulan', 'all' => 'Semua'] as $key => $label)
            <a href="{{ route('admin.analytics', ['period' => $key]) }}"
               style="padding:9px 14px;border-radius:999px;border:1px solid {{ $period === $key ? '#2563eb' : '#dbe3f0' }};background:{{ $period === $key ? '#2563eb' : '#fff' }};color:{{ $period === $key ? '#fff' : '#334155' }};font-size:13px;font-weight:700;text-decoration:none">
                {{ $label }}
            </a>
        @endforeach
    </div>
</div>

<div style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:16px">
    @foreach([
        ['label' => 'Gross Revenue', 'value' => 'Rp ' . number_format($grossRevenue, 0, ',', '.'), 'color' => '#2563eb'],
        ['label' => 'Net Revenue', 'value' => 'Rp ' . number_format($netRevenue, 0, ',', '.'), 'color' => '#16a34a'],
        ['label' => 'Orders Selesai', 'value' => number_format($completedOrders, 0, ',', '.'), 'color' => '#7c3aed'],
        ['label' => 'Orders Pending', 'value' => number_format($pendingOrders, 0, ',', '.'), 'color' => '#f59e0b'],
    ] as $card)
    <div style="background:#fff;border:1px solid #e5eaf3;border-radius:16px;padding:18px;box-shadow:0 8px 24px rgba(15,23,42,.04)">
        <div style="font-size:13px;color:#64748b;margin-bottom:8px;font-weight:700">{{ $card['label'] }}</div>
        <div style="font-size:26px;font-weight:800;color:{{ $card['color'] }}">{{ $card['value'] }}</div>
    </div>
    @endforeach
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:14px;margin-bottom:16px">
    <div style="background:#fff;border:1px solid #e5eaf3;border-radius:16px;padding:16px">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
            <div>
                <div style="font-weight:800;color:#0f172a">Tren Order & Revenue</div>
                <div style="font-size:12px;color:#64748b">{{ $periodLabel }}</div>
            </div>
        </div>
        <canvas id="trendChart" height="120"></canvas>
    </div>
    <div style="background:#fff;border:1px solid #e5eaf3;border-radius:16px;padding:16px">
        <div style="font-weight:800;color:#0f172a;margin-bottom:8px">Status Verifikasi Tukang</div>
        <div style="font-size:13px;color:#64748b;margin-bottom:12px">Lihat sebaran akun tukang aktif, terverifikasi, dan yang ditolak.</div>
        <canvas id="statusChart" height="180"></canvas>
        <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;margin-top:14px">
            <div style="padding:12px;border-radius:12px;background:#eff6ff">
                <div style="font-size:12px;color:#64748b">Aktif</div>
                <div style="font-size:22px;font-weight:800;color:#2563eb">{{ number_format($activeWorkers) }}</div>
            </div>
            <div style="padding:12px;border-radius:12px;background:#ecfdf5">
                <div style="font-size:12px;color:#64748b">Terverifikasi</div>
                <div style="font-size:22px;font-weight:800;color:#16a34a">{{ number_format($approvedWorkers) }}</div>
            </div>
            <div style="padding:12px;border-radius:12px;background:#f5f3ff">
                <div style="font-size:12px;color:#64748b">Terdaftar</div>
                <div style="font-size:22px;font-weight:800;color:#7c3aed">{{ number_format($registeredWorkers) }}</div>
            </div>
            <div style="padding:12px;border-radius:12px;background:#fef2f2">
                <div style="font-size:12px;color:#64748b">Ditolak</div>
                <div style="font-size:22px;font-weight:800;color:#dc2626">{{ number_format($rejectedWorkers) }}</div>
            </div>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1.1fr .9fr;gap:14px">
    <div style="background:#fff;border:1px solid #e5eaf3;border-radius:16px;padding:16px">
        <div style="font-weight:800;color:#0f172a;margin-bottom:12px">Level Kerumitan Order</div>
        <canvas id="difficultyChart" height="170"></canvas>
    </div>
    <div style="display:grid;gap:14px">
        <div style="background:#fff;border:1px solid #e5eaf3;border-radius:16px;padding:16px">
            <div style="font-weight:800;color:#0f172a;margin-bottom:12px">Top Layanan</div>
            <div style="display:grid;gap:10px">
                @forelse($topServices as $service)
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 12px;border:1px solid #eef2f7;border-radius:12px">
                        <div style="font-weight:700;color:#0f172a">{{ $service['name'] }}</div>
                        <div style="font-weight:800;color:#2563eb">{{ $service['total'] }}</div>
                    </div>
                @empty
                    <div style="color:#64748b;font-size:13px">Belum ada data layanan.</div>
                @endforelse
            </div>
        </div>

        <div style="background:#fff;border:1px solid #e5eaf3;border-radius:16px;padding:16px">
            <div style="font-weight:800;color:#0f172a;margin-bottom:12px">Top Pekerja</div>
            <div style="display:grid;gap:10px">
                @forelse($topWorkers as $worker)
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 12px;border:1px solid #eef2f7;border-radius:12px">
                        <div>
                            <div style="font-weight:700;color:#0f172a">{{ $worker->nama }}</div>
                            <div style="font-size:12px;color:#64748b">{{ $worker->kategori ?? '-' }}</div>
                        </div>
                        <div style="font-weight:800;color:#16a34a">{{ $worker->orders_count }}</div>
                    </div>
                @empty
                    <div style="color:#64748b;font-size:13px">Belum ada data pekerja.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const labels = @json($chartLabels);
const orderTrend = @json($orderTrend);
const revenueTrend = @json($revenueTrend);
const statusLabels = @json(array_keys($statusBreakdown));
const statusValues = @json(array_values($statusBreakdown));
const difficultyLabels = @json($difficultyBreakdown->pluck('label'));
const difficultyValues = @json($difficultyBreakdown->pluck('total'));

new Chart(document.getElementById('trendChart'), {
    data: {
        labels,
        datasets: [
            {
                type: 'line',
                label: 'Order',
                data: orderTrend,
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37,99,235,.12)',
                tension: .35,
                pointRadius: 2,
            },
            {
                type: 'bar',
                label: 'Revenue',
                data: revenueTrend,
                backgroundColor: 'rgba(16,185,129,.25)',
                borderColor: '#10b981'
            }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } },
        scales: {
            y: { beginAtZero: true }
        }
    }
});

new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
        labels: statusLabels,
        datasets: [{
            data: statusValues,
            backgroundColor: ['#16a34a', '#f59e0b', '#ef4444', '#94a3b8']
        }]
    },
    options: { plugins: { legend: { position: 'bottom' } } }
});

new Chart(document.getElementById('difficultyChart'), {
    type: 'bar',
    data: {
        labels: difficultyLabels,
        datasets: [{
            label: 'Orders',
            data: difficultyValues,
            backgroundColor: '#7c3aed'
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { x: { beginAtZero: true } }
    }
});
</script>
@endpush
