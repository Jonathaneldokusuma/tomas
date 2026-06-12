@extends('admin.layouts.app')
@section('title', 'Search')

@section('content')
<div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:20px;gap:12px;flex-wrap:wrap">
    <div>
        <h1 style="font-size:22px;font-weight:800;color:#0d1b2e;line-height:1.2">Search Results</h1>
        <p style="font-size:12px;color:#6b7280;margin-top:4px">Hasil untuk “{{ $q }}”.</p>
    </div>
    <form action="{{ route('admin.search') }}" method="GET" style="display:flex;gap:8px;align-items:center">
        <input type="text" name="q" value="{{ $q }}"
            style="width:240px;background:#fff;border:1px solid #e2e8f0;border-radius:9px;padding:8px 12px;font-size:12px;outline:none;color:#374151"
            placeholder="Cari lagi...">
        <button style="background:#0d1b2e;color:#fff;border:none;border-radius:8px;padding:8px 14px;font-size:12px;font-weight:600;cursor:pointer">Search</button>
    </form>
</div>

@php
$sections = [
    ['title' => 'Users', 'icon' => 'fa-users', 'items' => $users, 'empty' => 'Tidak ada user cocok.'],
    ['title' => 'Tukang', 'icon' => 'fa-screwdriver-wrench', 'items' => $tukang, 'empty' => 'Tidak ada tukang cocok.'],
    ['title' => 'Layanan', 'icon' => 'fa-list-check', 'items' => $layanan, 'empty' => 'Tidak ada layanan cocok.'],
    ['title' => 'Orders', 'icon' => 'fa-receipt', 'items' => $orders, 'empty' => 'Tidak ada order cocok.'],
];
@endphp

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px">
    @foreach($sections as $section)
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 16px;border-bottom:1px solid #f1f5f9;background:#f8fafc">
            <div style="display:flex;align-items:center;gap:8px">
                <i class="fas {{ $section['icon'] }}" style="color:#2563eb;font-size:12px"></i>
                <h2 style="font-size:13px;font-weight:800;color:#0d1b2e">{{ $section['title'] }}</h2>
            </div>
            <span style="font-size:11px;color:#64748b;font-weight:700">{{ $section['items']->count() }}</span>
        </div>
        <div>
            @forelse($section['items'] as $item)
                @if($section['title'] === 'Users')
                <a href="{{ route('admin.orders', ['q' => $item->nama]) }}" style="display:block;padding:13px 16px;border-bottom:1px solid #f8fafc;text-decoration:none">
                    <div style="font-size:13px;font-weight:700;color:#111827">{{ $item->nama }}</div>
                    <div style="font-size:11px;color:#64748b;margin-top:2px">{{ $item->no_hp }} · {{ $item->orders_count }} order · {{ $item->is_banned ? 'Banned' : 'Active' }}</div>
                </a>
                @elseif($section['title'] === 'Tukang')
                <a href="{{ route('admin.tukang.edit', $item->id_tukang) }}" style="display:block;padding:13px 16px;border-bottom:1px solid #f8fafc;text-decoration:none">
                    <div style="font-size:13px;font-weight:700;color:#111827">{{ $item->nama }}</div>
                    <div style="font-size:11px;color:#64748b;margin-top:2px">{{ $item->kategori ?: 'Umum' }} · {{ $item->status_aktif ? 'Aktif' : 'Nonaktif' }}</div>
                </a>
                @elseif($section['title'] === 'Layanan')
                <a href="{{ route('admin.layanan') }}" style="display:block;padding:13px 16px;border-bottom:1px solid #f8fafc;text-decoration:none">
                    <div style="font-size:13px;font-weight:700;color:#111827">{{ $item->nama_layanan }}</div>
                    <div style="font-size:11px;color:#64748b;margin-top:2px">Kategori layanan #{{ $item->id_layanan }}</div>
                </a>
                @else
                <a href="{{ route('admin.orders', ['q' => $item->user->nama ?? $item->tukang->nama ?? $item->id_order]) }}" style="display:block;padding:13px 16px;border-bottom:1px solid #f8fafc;text-decoration:none">
                    <div style="font-size:13px;font-weight:700;color:#111827">Order #{{ str_pad($item->id_order, 5, '0', STR_PAD_LEFT) }}</div>
                    <div style="font-size:11px;color:#64748b;margin-top:2px">{{ $item->user->nama ?? '-' }} · {{ $item->tukang->nama ?? '-' }} · {{ $item->layanan->nama_layanan ?? '-' }}</div>
                </a>
                @endif
            @empty
            <div style="padding:26px 16px;text-align:center;color:#9ca3af;font-size:12px">{{ $section['empty'] }}</div>
            @endforelse
        </div>
    </div>
    @endforeach
</div>
@endsection
