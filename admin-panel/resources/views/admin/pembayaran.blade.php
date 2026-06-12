@extends('admin.layouts.app')
@section('title', 'Monitoring Pembayaran')

@section('content')

<div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:20px;gap:12px;flex-wrap:wrap">
    <div>
        <h1 style="font-size:22px;font-weight:800;color:#0d1b2e;line-height:1.2">Monitoring Pembayaran</h1>
        <p style="font-size:12px;color:#6b7280;margin-top:4px">Pantau bukti transfer dan konfirmasi pembayaran.</p>
    </div>
</div>

@if(session('success'))
<div style="background:#d1fae5;border:1px solid #6ee7b7;border-radius:10px;padding:12px 16px;margin-bottom:16px;color:#065f46;font-size:13px">
    <i class="fas fa-check-circle" style="margin-right:6px"></i>{{ session('success') }}
</div>
@endif

{{-- Filter Tabs --}}
<div style="display:flex;gap:0;border:1px solid #e2e8f0;border-radius:9px;overflow:hidden;margin-bottom:16px;width:fit-content">
    @foreach(['all' => 'Semua', 'pending' => 'Belum Bayar', 'uploaded' => 'Bukti Terkirim', 'confirmed' => 'Dikonfirmasi'] as $key => $label)
    <a href="{{ route('admin.pembayaran') }}?status={{ $key }}"
       style="padding:8px 16px;font-size:12px;font-weight:{{ $status === $key ? '700' : '500' }};background:{{ $status === $key ? '#2563eb' : '#fff' }};color:{{ $status === $key ? '#fff' : '#6b7280' }};text-decoration:none;border-right:1px solid #e2e8f0;white-space:nowrap">
        {{ $label }}
    </a>
    @endforeach
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
        @forelse($orders as $order)
        <tr style="border-bottom:1px solid #f1f5f9">
            <td style="padding:12px 16px">
                <div style="font-size:13px;font-weight:700;color:#0d1b2e">#{{ $order->id_order }}</div>
                <div style="font-size:11px;color:#9ca3af">{{ $order->created_at?->format('d M Y') }}</div>
            </td>
            <td style="padding:12px 16px;font-size:13px;color:#374151">{{ $order->user?->nama ?? '-' }}</td>
            <td style="padding:12px 16px;font-size:13px;color:#374151">{{ $order->tukang?->nama ?? '-' }}</td>
            <td style="padding:12px 16px;font-size:13px;color:#374151">{{ $order->metode_bayar }}</td>
            <td style="padding:12px 16px">
                @if($order->bukti_bayar)
                <a href="{{ url('storage/'.$order->bukti_bayar) }}" target="_blank">
                    <img src="{{ url('storage/'.$order->bukti_bayar) }}" style="height:48px;border-radius:6px;border:1px solid #e2e8f0;object-fit:cover">
                </a>
                @else
                <span style="font-size:11px;color:#9ca3af">Belum ada</span>
                @endif
            </td>
            <td style="padding:12px 16px">
                @php
                    $payStatus = $order->status_payment ?? 'pending';
                    $colors = ['pending' => ['bg' => '#fef3c7', 'text' => '#92400e'], 'uploaded' => ['bg' => '#dbeafe', 'text' => '#1e40af'], 'confirmed' => ['bg' => '#d1fae5', 'text' => '#065f46']];
                    $c = $colors[$payStatus] ?? $colors['pending'];
                    $labels = ['pending' => 'Belum Bayar', 'uploaded' => 'Bukti Terkirim', 'confirmed' => 'Dikonfirmasi'];
                @endphp
                <span style="background:{{ $c['bg'] }};color:{{ $c['text'] }};padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600">
                    {{ $labels[$payStatus] ?? $payStatus }}
                </span>
            </td>
            <td style="padding:12px 16px;text-align:center">
                @if($payStatus === 'uploaded')
                <form method="POST" action="{{ route('admin.pembayaran.konfirmasi', $order->id_order) }}">
                    @csrf
                    <button type="submit" onclick="return confirm('Konfirmasi pembayaran ini?')"
                        style="background:#2563eb;color:#fff;border:none;border-radius:7px;padding:6px 14px;font-size:12px;font-weight:600;cursor:pointer">
                        <i class="fas fa-check" style="margin-right:4px"></i>Konfirmasi
                    </button>
                </form>
                @else
                <span style="font-size:12px;color:#9ca3af">-</span>
                @endif
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="7" style="text-align:center;padding:40px;color:#9ca3af;font-size:13px">Tidak ada data pembayaran</td>
        </tr>
        @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top:16px">{{ $orders->links() }}</div>

@endsection
