@extends('admin.layouts.app')
@section('title', 'Verifikasi Tukang')

@section('content')

<div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:20px;gap:12px;flex-wrap:wrap">
    <div>
        <h1 style="font-size:22px;font-weight:800;color:#0d1b2e;line-height:1.2">Verifikasi Tukang</h1>
        <p style="font-size:12px;color:#6b7280;margin-top:4px">Tinjau KTP dan selfie tukang yang mendaftar.</p>
    </div>
    <div>
        <span style="background:#fef3c7;color:#92400e;padding:6px 14px;border-radius:20px;font-size:12px;font-weight:600">
            {{ $tukang->total() }} menunggu verifikasi
        </span>
    </div>
</div>

@if(session('success'))
<div style="background:#d1fae5;border:1px solid #6ee7b7;border-radius:10px;padding:12px 16px;margin-bottom:16px;color:#065f46;font-size:13px">
    <i class="fas fa-check-circle" style="margin-right:6px"></i>{{ session('success') }}
</div>
@endif

@php
    $tukangList = collect($tukang->items());
@endphp

<div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-bottom:16px">
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:14px 16px">
        <div style="font-size:12px;color:#6b7280">Menunggu</div>
        <div style="font-size:22px;font-weight:800;color:#92400e">{{ $tukang->total() }}</div>
    </div>
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:14px 16px">
        <div style="font-size:12px;color:#6b7280">Foto KTP / Selfie</div>
        <div style="font-size:22px;font-weight:800;color:#2563eb">{{ $tukangList->filter(fn ($item) => $item->foto_ktp || $item->foto_selfie)->count() }}</div>
    </div>
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:14px 16px">
        <div style="font-size:12px;color:#6b7280">Siap Diproses</div>
        <div style="font-size:22px;font-weight:800;color:#10b981">{{ $tukangList->filter(fn ($item) => $item->foto_ktp && $item->foto_selfie)->count() }}</div>
    </div>
</div>

@if($tukang->isEmpty())
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:48px;text-align:center">
    <i class="fas fa-check-double" style="font-size:40px;color:#10b981;margin-bottom:16px;display:block"></i>
    <p style="color:#374151;font-weight:600;font-size:15px">Semua tukang sudah diverifikasi</p>
    <p style="color:#9ca3af;font-size:13px;margin-top:4px">Tidak ada pendaftar yang menunggu.</p>
</div>
@else

<div style="display:grid;gap:16px">
@foreach($tukang as $t)
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden">
    <div style="display:flex;gap:16px;padding:20px;flex-wrap:wrap">
        {{-- Avatar --}}
        <div style="flex-shrink:0">
            @if($t->foto)
                <img src="{{ url('storage/'.$t->foto) }}" style="width:60px;height:60px;border-radius:50%;object-fit:cover">
            @else
                <div style="width:60px;height:60px;border-radius:50%;background:#dbeafe;display:flex;align-items:center;justify-content:center;font-size:22px;color:#2563eb;font-weight:bold">
                    {{ strtoupper(substr($t->nama ?? 'T', 0, 1)) }}
                </div>
            @endif
        </div>
        {{-- Info --}}
        <div style="flex:1;min-width:200px">
            <div style="font-size:16px;font-weight:700;color:#0d1b2e">{{ $t->nama ?? '-' }}</div>
            <div style="font-size:12px;color:#6b7280;margin-top:2px">
                @if($t->username)<span>@{{ $t->username }}</span>@endif
                @if($t->no_hp)<span style="margin-left:12px"><i class="fas fa-phone" style="margin-right:3px"></i>{{ $t->no_hp }}</span>@endif
                @if($t->no_ktp)<span style="margin-left:12px"><i class="fas fa-id-card" style="margin-right:3px"></i>{{ $t->no_ktp }}</span>@endif
            </div>
            <div style="margin-top:6px;font-size:12px;color:#374151">
                Daftar: {{ $t->created_at?->format('d M Y H:i') }}
            </div>
            <div style="margin-top:8px">
                <span style="background:#fef3c7;color:#92400e;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600">
                    Menunggu Verifikasi
                </span>
            </div>
        </div>
        {{-- Actions --}}
        <div style="display:flex;gap:8px;align-items:flex-start;flex-shrink:0">
            <form method="POST" action="{{ route('admin.tukang.approve', $t->id_tukang) }}">
                @csrf
                <button type="submit" onclick="return confirm('Verifikasi tukang ini?')"
                    style="background:#10b981;color:#fff;border:none;border-radius:8px;padding:8px 16px;font-size:12px;font-weight:600;cursor:pointer">
                    <i class="fas fa-check" style="margin-right:4px"></i>Verifikasi
                </button>
            </form>
            <form method="POST" action="{{ route('admin.tukang.reject', $t->id_tukang) }}">
                @csrf
                <input type="hidden" name="reason" value="" class="reject-reason-input">
                <button type="submit" onclick="return window.askRejectReason(this.form)"
                    style="background:#ef4444;color:#fff;border:none;border-radius:8px;padding:8px 16px;font-size:12px;font-weight:600;cursor:pointer">
                    <i class="fas fa-times" style="margin-right:4px"></i>Tolak
                </button>
            </form>
        </div>
    </div>
    {{-- KTP Photos --}}
    @if($t->foto_ktp || $t->foto_selfie)
    <div style="border-top:1px solid #f1f5f9;padding:16px 20px">
        <div style="font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:10px">Dokumen Verifikasi</div>
            <div style="display:flex;gap:12px;flex-wrap:wrap">
            @if($t->foto_ktp)
            <div>
                <div style="font-size:11px;color:#6b7280;margin-bottom:4px">Foto KTP</div>
                <a href="{{ url('storage/'.$t->foto_ktp) }}" target="_blank" data-preview-image="{{ url('storage/'.$t->foto_ktp) }}" data-preview-title="Foto KTP - {{ $t->nama }}">
                    <img src="{{ url('storage/'.$t->foto_ktp) }}" style="height:100px;border-radius:8px;border:1px solid #e2e8f0;object-fit:cover;cursor:pointer">
                </a>
            </div>
            @endif
            @if($t->foto_selfie)
            <div>
                <div style="font-size:11px;color:#6b7280;margin-bottom:4px">Selfie dengan KTP</div>
                <a href="{{ url('storage/'.$t->foto_selfie) }}" target="_blank" data-preview-image="{{ url('storage/'.$t->foto_selfie) }}" data-preview-title="Selfie - {{ $t->nama }}">
                    <img src="{{ url('storage/'.$t->foto_selfie) }}" style="height:100px;border-radius:8px;border:1px solid #e2e8f0;object-fit:cover;cursor:pointer">
                </a>
            </div>
            @endif
        </div>
    </div>
    @else
    <div style="border-top:1px solid #f1f5f9;padding:12px 20px;background:#fffbeb">
        <p style="font-size:12px;color:#92400e"><i class="fas fa-exclamation-triangle" style="margin-right:6px"></i>Tukang belum upload foto KTP & selfie.</p>
    </div>
    @endif
    @if($t->status_verifikasi === 'rejected' && !empty($t->rejection_reason))
    <div style="border-top:1px solid #f1f5f9;padding:12px 20px;background:#fef2f2">
        <p style="font-size:12px;color:#991b1b">
            <i class="fas fa-circle-exclamation" style="margin-right:6px"></i>
            Alasan penolakan: {{ $t->rejection_reason }}
        </p>
    </div>
    @endif
</div>
@endforeach
</div>

<div style="margin-top:16px">{{ $tukang->links() }}</div>
@endif

<div id="image-preview-modal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.72);z-index:9999;align-items:center;justify-content:center;padding:20px">
    <div style="background:#fff;border-radius:18px;max-width:min(92vw,960px);width:100%;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.35)">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid #e2e8f0">
            <div>
                <div id="image-preview-title" style="font-size:14px;font-weight:700;color:#0f172a">Preview Foto</div>
                <div style="font-size:12px;color:#64748b">Klik area gelap atau tombol tutup untuk kembali.</div>
            </div>
            <button type="button" id="image-preview-close" style="border:none;background:#eff6ff;color:#1d4ed8;border-radius:10px;padding:8px 12px;font-size:12px;font-weight:700;cursor:pointer">
                Tutup
            </button>
        </div>
        <div style="background:#0f172a">
            <img id="image-preview-img" src="" alt="Preview foto verifikasi" style="display:block;max-height:80vh;width:100%;object-fit:contain;margin:0 auto">
        </div>
    </div>
</div>

<script>
(function () {
    const modal = document.getElementById('image-preview-modal');
    const image = document.getElementById('image-preview-img');
    const title = document.getElementById('image-preview-title');
    const close = document.getElementById('image-preview-close');

    function openPreview(src, text) {
        if (!src) return;
        image.src = src;
        title.textContent = text || 'Preview Foto';
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closePreview() {
        modal.style.display = 'none';
        image.src = '';
        document.body.style.overflow = '';
    }

    document.querySelectorAll('[data-preview-image]').forEach((el) => {
        el.addEventListener('click', (event) => {
            event.preventDefault();
            openPreview(el.getAttribute('data-preview-image'), el.getAttribute('data-preview-title'));
        });
    });

    close.addEventListener('click', closePreview);
    modal.addEventListener('click', (event) => {
        if (event.target === modal) closePreview();
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') closePreview();
    });

    window.askRejectReason = function (form) {
        const reason = window.prompt('Masukkan alasan penolakan (opsional):', '');
        if (reason === null) return false;
        const input = form.querySelector('.reject-reason-input');
        if (input) input.value = reason;
        return confirm('Yakin ingin menolak tukang ini?');
    };
})();
</script>

@endsection
