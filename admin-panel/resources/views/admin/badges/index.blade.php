@extends('admin.layouts.app')
@section('title', 'Badge Custom')

@section('content')
<div style="padding:24px">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:20px;flex-wrap:wrap">
        <div>
            <h1 style="font-size:20px;font-weight:700;color:#1e293b">Badge Custom</h1>
            <p style="color:#64748b;font-size:13px;margin-top:2px">Tambahkan badge dengan gambar spesial untuk user atau tukang</p>
        </div>
        <div style="background:#eff6ff;border:1px solid #bfdbfe;color:#1d4ed8;border-radius:10px;padding:10px 14px;font-size:13px;font-weight:600">
            <i class="fas fa-award" style="margin-right:6px"></i>Badge baru otomatis muncul di profil target
        </div>
    </div>

    @if(session('success'))
        <div style="background:#dcfce7;border:1px solid #86efac;border-radius:8px;padding:12px 16px;color:#166534;margin-bottom:16px;font-size:13px">
            <i class="fas fa-check-circle" style="margin-right:6px"></i>{{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:12px 16px;color:#dc2626;margin-bottom:16px;font-size:13px">
            <i class="fas fa-circle-exclamation" style="margin-right:6px"></i>{{ session('error') }}
        </div>
    @endif

    <div style="display:grid;grid-template-columns:420px 1fr;gap:18px;align-items:start">
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;box-shadow:0 1px 6px rgba(0,0,0,.05);overflow:hidden">
            <div style="padding:16px 18px;border-bottom:1px solid #f1f5f9">
                <h2 style="font-size:15px;font-weight:600;color:#1e293b">
                    <i class="fas fa-plus-circle" style="color:#2563eb;margin-right:6px"></i>Tambah Badge
                </h2>
            </div>
            <form action="{{ route('admin.badges.store') }}" method="POST" enctype="multipart/form-data" style="padding:18px;display:flex;flex-direction:column;gap:14px">
                @csrf
                <div>
                    <label style="font-size:12px;font-weight:600;color:#374151">Jenis Target</label>
                    <select name="target_type" id="badge-target-type" required
                        style="width:100%;margin-top:6px;border:1px solid #d1d5db;border-radius:10px;padding:11px 12px;font-size:13px;outline:none">
                        <option value="user">User</option>
                        <option value="tukang">Tukang</option>
                    </select>
                </div>

                <div>
                    <label style="font-size:12px;font-weight:600;color:#374151">Target</label>
                    <select name="target_id_user" id="badge-target-user" style="width:100%;margin-top:6px;border:1px solid #d1d5db;border-radius:10px;padding:11px 12px;font-size:13px;outline:none">
                        @forelse($users as $user)
                            <option value="{{ $user->id_user }}">{{ $user->nama ?? 'User' }} · ID {{ $user->id_user }}</option>
                        @empty
                            <option value="">Tidak ada user</option>
                        @endforelse
                    </select>
                    <select name="target_id_tukang" id="badge-target-tukang" style="display:none;width:100%;margin-top:6px;border:1px solid #d1d5db;border-radius:10px;padding:11px 12px;font-size:13px;outline:none">
                        @forelse($tukangs as $tukang)
                            <option value="{{ $tukang->id_tukang }}">{{ $tukang->nama ?? $tukang->username ?? 'Tukang' }} · ID {{ $tukang->id_tukang }}</option>
                        @empty
                            <option value="">Tidak ada tukang</option>
                        @endforelse
                    </select>
                </div>

                <div>
                    <label style="font-size:12px;font-weight:600;color:#374151">Nama Badge</label>
                    <input name="nama" type="text" required maxlength="120" placeholder="Contoh: No.1 Tukang"
                        style="width:100%;margin-top:6px;border:1px solid #d1d5db;border-radius:10px;padding:11px 12px;font-size:13px;outline:none">
                </div>

                <div>
                    <label style="font-size:12px;font-weight:600;color:#374151">Deskripsi</label>
                    <textarea name="deskripsi" rows="4" maxlength="500" placeholder="Contoh: Badge spesial untuk performa terbaik."
                        style="width:100%;margin-top:6px;border:1px solid #d1d5db;border-radius:10px;padding:11px 12px;font-size:13px;outline:none;resize:vertical"></textarea>
                </div>

                <div style="display:grid;grid-template-columns:1fr 140px;gap:12px">
                    <div>
                        <label style="font-size:12px;font-weight:600;color:#374151">Warna Aksen</label>
                        <input name="warna" type="text" placeholder="#2563EB"
                            style="width:100%;margin-top:6px;border:1px solid #d1d5db;border-radius:10px;padding:11px 12px;font-size:13px;outline:none">
                    </div>
                    <div>
                        <label style="font-size:12px;font-weight:600;color:#374151">Gambar Badge</label>
                        <input name="gambar" type="file" accept="image/png,image/jpeg,image/webp" required
                            style="width:100%;margin-top:6px;border:1px solid #d1d5db;border-radius:10px;padding:10px 12px;font-size:12px;outline:none;background:#fff">
                    </div>
                </div>

                <button type="submit"
                    style="margin-top:2px;background:linear-gradient(135deg,#2563eb,#4f46e5);color:#fff;border:none;border-radius:10px;padding:12px 16px;font-size:13px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;gap:8px">
                    <i class="fas fa-save"></i> Simpan Badge
                </button>
            </form>
        </div>

        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;box-shadow:0 1px 6px rgba(0,0,0,.05);overflow:hidden">
            <div style="padding:16px 18px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap">
                <h2 style="font-size:15px;font-weight:600;color:#1e293b">
                    <i class="fas fa-layer-group" style="color:#2563eb;margin-right:6px"></i>Daftar Badge
                </h2>
                <span style="font-size:12px;color:#64748b">{{ $badges->count() }} badge tersimpan</span>
            </div>
            <div style="padding:18px">
                @if($badges->isEmpty())
                    <div style="padding:40px;text-align:center;color:#94a3b8">
                        <i class="fas fa-award" style="font-size:32px;margin-bottom:10px;display:block;opacity:.35"></i>
                        Belum ada badge yang dibuat.
                    </div>
                @else
                    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:14px">
                        @foreach($badges as $badge)
                            @php
                                $targetName = $badge->target_type === 'user'
                                    ? optional($users->firstWhere('id_user', $badge->target_id))->nama
                                    : optional($tukangs->firstWhere('id_tukang', $badge->target_id))->nama;
                                $targetFallback = $badge->target_type === 'user'
                                    ? 'User ID ' . $badge->target_id
                                    : 'Tukang ID ' . $badge->target_id;
                                $preview = $badge->image_url ?: '';
                            @endphp
                            <div style="border:1px solid #e2e8f0;border-radius:14px;padding:14px;background:#fff;box-shadow:0 1px 6px rgba(0,0,0,.04)">
                                <div style="display:flex;align-items:center;gap:12px">
                                    <div style="width:58px;height:58px;border-radius:16px;background:{{ $badge->warna ?: '#eff6ff' }};display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0">
                                        @if($preview)
                                            <img src="{{ $preview }}" alt="{{ $badge->nama }}" style="width:100%;height:100%;object-fit:cover">
                                        @else
                                            <i class="fas fa-award" style="color:#fff;font-size:24px"></i>
                                        @endif
                                    </div>
                                    <div style="min-width:0">
                                        <div style="font-size:15px;font-weight:700;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $badge->nama }}</div>
                                        <div style="font-size:12px;color:#64748b;margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $targetName ?: $targetFallback }}</div>
                                        <div style="margin-top:6px;display:inline-flex;align-items:center;background:#dbeafe;color:#1d4ed8;border-radius:999px;padding:2px 8px;font-size:10px;font-weight:700;text-transform:uppercase">
                                            {{ $badge->target_type }}
                                        </div>
                                    </div>
                                </div>
                                @if($badge->deskripsi)
                                    <p style="font-size:12px;color:#4b5563;line-height:1.5;margin-top:10px">{{ $badge->deskripsi }}</p>
                                @endif
                                <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;margin-top:12px">
                                    <div style="font-size:11px;color:#9ca3af">
                                        {{ $badge->created_at?->format('d M Y H:i') }}
                                    </div>
                                    <form action="{{ route('admin.badges.delete', $badge->id_badge_award) }}" method="POST" onsubmit="return confirm('Hapus badge ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" style="border:none;background:#fee2e2;color:#b91c1c;border-radius:8px;padding:6px 10px;font-size:12px;font-weight:600;cursor:pointer">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const type = document.getElementById('badge-target-type');
    const user = document.getElementById('badge-target-user');
    const tukang = document.getElementById('badge-target-tukang');

    function syncTarget() {
        if (type.value === 'user') {
            user.style.display = 'block';
            tukang.style.display = 'none';
            user.name = 'target_id';
            tukang.name = 'target_id_tukang';
        } else {
            user.style.display = 'none';
            tukang.style.display = 'block';
            user.name = 'target_id_user';
            tukang.name = 'target_id';
        }
    }

    type.addEventListener('change', syncTarget);
    syncTarget();
})();
</script>
@endsection
