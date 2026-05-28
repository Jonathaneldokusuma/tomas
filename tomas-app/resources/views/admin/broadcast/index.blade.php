@extends('admin.layouts.app')
@section('title', 'Broadcast Pesan ke Tukang')

@section('content')
<div style="padding:24px">

    {{-- Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
        <div>
            <h1 style="font-size:20px;font-weight:700;color:#1e293b">Broadcast Pesan</h1>
            <p style="color:#64748b;font-size:13px;margin-top:2px">Kirim pesan ke semua tukang (Pesan dari Pusat)</p>
        </div>
    </div>

    @if(session('success'))
    <div style="background:#dcfce7;border:1px solid #86efac;border-radius:8px;padding:12px 16px;color:#166534;margin-bottom:16px;font-size:13px">
        <i class="fas fa-check-circle" style="margin-right:6px"></i>{{ session('success') }}
    </div>
    @endif

    {{-- Form kirim pesan --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;margin-bottom:24px;box-shadow:0 1px 6px rgba(0,0,0,.05)">
        <h2 style="font-size:15px;font-weight:600;color:#1e293b;margin-bottom:14px">
            <i class="fas fa-paper-plane" style="color:#2563eb;margin-right:6px"></i>Kirim Pesan Baru
        </h2>
        <form action="{{ route('admin.broadcast.store') }}" method="POST">
            @csrf
            <div style="display:grid;grid-template-columns:1fr auto;gap:12px;margin-bottom:12px">
                <div>
                    <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:4px">Judul Pesan</label>
                    <input type="text" name="judul" required maxlength="200" placeholder="Judul singkat yang jelas..."
                        style="width:100%;border:1px solid #d1d5db;border-radius:8px;padding:9px 12px;font-size:13px;outline:none"
                        onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'">
                </div>
                <div>
                    <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:4px">Tipe</label>
                    <select name="tipe" style="border:1px solid #d1d5db;border-radius:8px;padding:9px 12px;font-size:13px;outline:none;background:#fff;height:38px">
                        <option value="info">ℹ️ Info</option>
                        <option value="warning">⚠️ Peringatan</option>
                        <option value="promo">🎁 Promo</option>
                    </select>
                </div>
            </div>
            <div style="margin-bottom:14px">
                <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:4px">Isi Pesan</label>
                <textarea name="isi" required rows="4" placeholder="Tulis isi pesan lengkap di sini..."
                    style="width:100%;border:1px solid #d1d5db;border-radius:8px;padding:9px 12px;font-size:13px;outline:none;resize:vertical"
                    onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'"></textarea>
            </div>
            <button type="submit"
                style="background:linear-gradient(135deg,#2563eb,#4f46e5);color:#fff;border:none;border-radius:8px;padding:10px 20px;font-size:13px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:8px">
                <i class="fas fa-bullhorn"></i> Kirim ke Semua Tukang
            </button>
        </form>
    </div>

    {{-- Daftar pesan terkirim --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;box-shadow:0 1px 6px rgba(0,0,0,.05)">
        <div style="padding:16px 20px;border-bottom:1px solid #f1f5f9">
            <h2 style="font-size:15px;font-weight:600;color:#1e293b">
                <i class="fas fa-history" style="color:#6366f1;margin-right:6px"></i>Riwayat Pesan ({{ $broadcasts->total() }})
            </h2>
        </div>

        @forelse($broadcasts as $msg)
        @php
            $tipeColor = match($msg->tipe) { 'warning' => '#f97316', 'promo' => '#22c55e', default => '#3b82f6' };
            $tipeBg    = match($msg->tipe) { 'warning' => '#fff7ed', 'promo' => '#f0fdf4', default => '#eff6ff' };
            $tipeLabel = match($msg->tipe) { 'warning' => '⚠️ Peringatan', 'promo' => '🎁 Promo', default => 'ℹ️ Info' };
        @endphp
        <div style="padding:16px 20px;border-bottom:1px solid #f1f5f9;display:flex;gap:14px;align-items:flex-start">
            <div style="background:{{ $tipeBg }};border-radius:8px;padding:8px;flex-shrink:0">
                <span style="font-size:18px">{{ str_contains($tipeLabel,'⚠') ? '⚠️' : (str_contains($tipeLabel,'🎁') ? '🎁' : 'ℹ️') }}</span>
            </div>
            <div style="flex:1;min-width:0">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
                    <span style="font-weight:600;color:#1e293b;font-size:14px">{{ $msg->judul }}</span>
                    <span style="background:{{ $tipeBg }};color:{{ $tipeColor }};border-radius:12px;padding:2px 8px;font-size:11px;font-weight:600">{{ $tipeLabel }}</span>
                </div>
                <p style="color:#64748b;font-size:13px;line-height:1.5;margin-bottom:6px">{{ Str::limit($msg->isi, 200) }}</p>
                <span style="color:#94a3b8;font-size:11px">{{ $msg->created_at->diffForHumans() }} · {{ $msg->created_at->format('d M Y H:i') }}</span>
            </div>
            <form action="{{ route('admin.broadcast.delete', $msg->id_broadcast) }}" method="POST" onsubmit="return confirm('Hapus pesan ini?')">
                @csrf @method('DELETE')
                <button type="submit" style="background:#fee2e2;color:#dc2626;border:none;border-radius:6px;padding:6px 10px;font-size:12px;cursor:pointer">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
        </div>
        @empty
        <div style="padding:40px;text-align:center;color:#94a3b8">
            <i class="fas fa-bullhorn" style="font-size:32px;margin-bottom:10px;display:block;opacity:.4"></i>
            Belum ada pesan broadcast.
        </div>
        @endforelse

        <div style="padding:14px 20px">
            {{ $broadcasts->links() }}
        </div>
    </div>
</div>
@endsection
