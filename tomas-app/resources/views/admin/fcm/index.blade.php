@extends('admin.layouts.app')
@section('title', 'Firebase Push')

@section('content')
<div style="padding:24px">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:20px;flex-wrap:wrap">
        <div>
            <h1 style="font-size:20px;font-weight:700;color:#1e293b">Firebase Push</h1>
            <p style="color:#64748b;font-size:13px;margin-top:2px">Kelola service account Firebase untuk push notification HP user dan tukang</p>
        </div>
        <div style="background:#eff6ff;border:1px solid #bfdbfe;color:#1d4ed8;border-radius:10px;padding:10px 14px;font-size:13px;font-weight:600">
            <i class="fas fa-bell" style="margin-right:6px"></i>Push akan aktif setelah file service account valid
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

    <div style="display:grid;grid-template-columns:360px 1fr;gap:18px;align-items:start">
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;box-shadow:0 1px 6px rgba(0,0,0,.05);overflow:hidden">
            <div style="padding:16px 18px;border-bottom:1px solid #f1f5f9">
                <h2 style="font-size:15px;font-weight:600;color:#1e293b">
                    <i class="fas fa-upload" style="color:#2563eb;margin-right:6px"></i>Upload Service Account
                </h2>
            </div>
            <form action="{{ route('admin.fcm.store') }}" method="POST" enctype="multipart/form-data" style="padding:18px;display:flex;flex-direction:column;gap:14px">
                @csrf
                <div>
                    <label style="font-size:12px;font-weight:600;color:#374151">Firebase service-account JSON</label>
                    <input name="service_account" type="file" accept=".json,application/json" required
                        style="width:100%;margin-top:6px;border:1px solid #d1d5db;border-radius:10px;padding:10px 12px;font-size:12px;outline:none;background:#fff">
                </div>

                <button type="submit"
                    style="margin-top:2px;background:linear-gradient(135deg,#2563eb,#4f46e5);color:#fff;border:none;border-radius:10px;padding:12px 16px;font-size:13px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;gap:8px">
                    <i class="fas fa-save"></i> Simpan Credential
                </button>
            </form>
        </div>

        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;box-shadow:0 1px 6px rgba(0,0,0,.05);overflow:hidden">
            <div style="padding:16px 18px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap">
                <h2 style="font-size:15px;font-weight:600;color:#1e293b">
                    <i class="fas fa-circle-info" style="color:#2563eb;margin-right:6px"></i>Status Firebase Push
                </h2>
                <form action="{{ route('admin.fcm.delete') }}" method="POST" onsubmit="return confirm('Hapus service account Firebase?')" style="margin:0">
                    @csrf
                    @method('DELETE')
                    <button type="submit" style="border:none;background:#fee2e2;color:#b91c1c;border-radius:8px;padding:6px 10px;font-size:12px;font-weight:600;cursor:pointer">
                        Hapus File
                    </button>
                </form>
            </div>
            <div style="padding:18px">
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:14px">
                    <div style="border:1px solid #e2e8f0;border-radius:14px;padding:14px;background:#f8fafc">
                        <div style="font-size:11px;color:#64748b;font-weight:600;text-transform:uppercase">Project ID</div>
                        <div style="margin-top:8px;font-size:14px;font-weight:700;color:#111827">{{ $status['project_id'] ?: 'Belum ada' }}</div>
                    </div>
                    <div style="border:1px solid #e2e8f0;border-radius:14px;padding:14px;background:#f8fafc">
                        <div style="font-size:11px;color:#64748b;font-weight:600;text-transform:uppercase">Service Account</div>
                        <div style="margin-top:8px;font-size:14px;font-weight:700;color:{{ $status['service_account_active'] ? '#15803d' : '#dc2626' }}">
                            {{ $status['service_account_active'] ? 'Aktif' : 'Belum ada credential' }}
                        </div>
                        <div style="margin-top:6px;font-size:11px;color:#64748b">
                            Sumber: {{ $status['service_account_source'] ? strtoupper($status['service_account_source']) : 'Belum ada' }}
                        </div>
                        @if($status['file_path'])
                            <div style="margin-top:6px;font-size:11px;color:#64748b;word-break:break-all">{{ $status['file_path'] }}</div>
                        @endif
                    </div>
                    <div style="border:1px solid #e2e8f0;border-radius:14px;padding:14px;background:#f8fafc">
                        <div style="font-size:11px;color:#64748b;font-weight:600;text-transform:uppercase">Token User</div>
                        <div style="margin-top:8px;font-size:14px;font-weight:700;color:#111827">{{ $status['token_users'] }}</div>
                    </div>
                    <div style="border:1px solid #e2e8f0;border-radius:14px;padding:14px;background:#f8fafc">
                        <div style="font-size:11px;color:#64748b;font-weight:600;text-transform:uppercase">Token Tukang</div>
                        <div style="margin-top:8px;font-size:14px;font-weight:700;color:#111827">{{ $status['token_tukang'] }}</div>
                    </div>
                </div>

                <div style="margin-top:18px;border:1px solid #dbeafe;background:#eff6ff;border-radius:14px;padding:16px">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap">
                        <div>
                            <div style="font-size:14px;font-weight:700;color:#1e3a8a">Tes push notification</div>
                            <div style="font-size:12px;color:#1d4ed8;margin-top:4px">Kirim notifikasi sistem ke HP user atau tukang untuk memastikan FCM benar-benar aktif di luar aplikasi.</div>
                        </div>
                    </div>

                    <form action="{{ route('admin.fcm.test') }}" method="POST" style="margin-top:14px;display:grid;grid-template-columns:1fr 1fr;gap:12px;align-items:end">
                        @csrf
                        <div>
                            <label style="font-size:12px;font-weight:600;color:#374151">Judul tes</label>
                            <input name="title" type="text" value="Tes Push Tomas"
                                style="width:100%;margin-top:6px;border:1px solid #bfdbfe;border-radius:10px;padding:10px 12px;font-size:12px;outline:none;background:#fff">
                        </div>
                        <div>
                            <label style="font-size:12px;font-weight:600;color:#374151">Isi pesan</label>
                            <input name="body" type="text" value="Notifikasi percobaan dari admin panel."
                                style="width:100%;margin-top:6px;border:1px solid #bfdbfe;border-radius:10px;padding:10px 12px;font-size:12px;outline:none;background:#fff">
                        </div>
                        <div style="grid-column:1 / -1;display:flex;gap:10px;flex-wrap:wrap;margin-top:2px">
                            <button type="submit" name="target" value="user"
                                style="background:#2563eb;color:#fff;border:none;border-radius:10px;padding:11px 14px;font-size:12px;font-weight:700;cursor:pointer">
                                Kirim Tes ke User
                            </button>
                            <button type="submit" name="target" value="tukang"
                                style="background:#0f766e;color:#fff;border:none;border-radius:10px;padding:11px 14px;font-size:12px;font-weight:700;cursor:pointer">
                                Kirim Tes ke Tukang
                            </button>
                            <button type="submit" name="target" value="all"
                                style="background:#7c3aed;color:#fff;border:none;border-radius:10px;padding:11px 14px;font-size:12px;font-weight:700;cursor:pointer">
                                Kirim Tes ke Semua
                            </button>
                        </div>
                    </form>
                </div>

                <div style="margin-top:18px;background:#fff7ed;border:1px solid #fdba74;border-radius:12px;padding:14px 16px;color:#9a3412;font-size:13px;line-height:1.55">
                    Upload file JSON dari <strong>Firebase Console → Project Settings → Service Accounts → Generate new private key</strong>.
                    Setelah file tersimpan, backend akan otomatis membaca project ID dan private key dari file atau database untuk mengirim push notification ke HP user dan tukang.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
