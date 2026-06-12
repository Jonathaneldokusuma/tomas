@extends('admin.layouts.app')
@section('title', 'Users')

@section('content')

{{-- Header --}}
<div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:20px;gap:12px;flex-wrap:wrap">
    <div>
        <h1 style="font-size:22px;font-weight:800;color:#0d1b2e;line-height:1.2">Marketplace Admin</h1>
        <p style="font-size:12px;color:#6b7280;margin-top:4px">Kelola akun user, status ban, dan akses order dari satu panel backend.</p>
    </div>
</div>

{{-- Stats Row --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px">
    @php
    $uCards = [
        ['label'=>'Total Users',       'val'=>\App\Models\User::count(), 'sub'=>'Semua akun pengguna',      'ibg'=>'#eff6ff','ic'=>'#2563eb','icon'=>'fa-users'],
        ['label'=>'Active Users',      'val'=>\App\Models\User::where('is_banned',0)->count(), 'sub'=>'Bisa memakai app',    'ibg'=>'#f0fdf4','ic'=>'#16a34a','icon'=>'fa-user-check'],
        ['label'=>'Banned Users',      'val'=>\App\Models\User::where('is_banned',1)->count(), 'sub'=>'Akun dinonaktifkan',   'ibg'=>'#fff7ed','ic'=>'#ea580c','icon'=>'fa-user-slash'],
        ['label'=>'Total Orders',      'val'=>\App\Models\Order::count(),                          'sub'=>'Semua order',          'ibg'=>'#fdf4ff','ic'=>'#9333ea','icon'=>'fa-receipt'],
        ['label'=>'Badge User',        'val'=>\App\Models\BadgeAward::where('target_type','user')->count(), 'sub'=>'Badge aktif user', 'ibg'=>'#eef2ff','ic'=>'#4f46e5','icon'=>'fa-award'],
    ];
    @endphp
    @foreach($uCards as $c)
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:16px 18px">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
            <div style="width:36px;height:36px;background:{{ $c['ibg'] }};border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <i class="fas {{ $c['icon'] }}" style="color:{{ $c['ic'] }};font-size:14px"></i>
            </div>
        </div>
        <div style="font-size:24px;font-weight:800;color:#0d1b2e;line-height:1">{{ number_format($c['val']) }}</div>
        <div style="font-size:12px;font-weight:600;color:#374151;margin-top:3px">{{ $c['label'] }}</div>
        <div style="font-size:11px;color:#9ca3af;margin-top:1px">{{ $c['sub'] }}</div>
    </div>
    @endforeach
</div>

{{-- Table Card --}}
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden">
    {{-- Toolbar --}}
    <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid #f1f5f9;gap:12px;flex-wrap:wrap">
        <div style="display:flex;align-items:center;gap:0;border:1px solid #e2e8f0;border-radius:9px;overflow:hidden;flex-shrink:0">
            <a href="{{ route('admin.users') }}" style="padding:7px 16px;font-size:12px;font-weight:600;background:{{ !$status ? '#2563eb':'#fff' }};color:{{ !$status ? '#fff':'#6b7280' }};text-decoration:none;white-space:nowrap">All Users</a>
            <a href="{{ route('admin.users', ['status'=>'active', 'q'=>$q]) }}" style="padding:7px 16px;font-size:12px;font-weight:500;background:{{ $status==='active' ? '#2563eb':'#fff' }};color:{{ $status==='active' ? '#fff':'#6b7280' }};text-decoration:none;border-left:1px solid #e2e8f0;white-space:nowrap">Active</a>
            <a href="{{ route('admin.users', ['status'=>'banned', 'q'=>$q]) }}" style="padding:7px 16px;font-size:12px;font-weight:500;background:{{ $status==='banned' ? '#2563eb':'#fff' }};color:{{ $status==='banned' ? '#fff':'#6b7280' }};text-decoration:none;border-left:1px solid #e2e8f0;white-space:nowrap">Banned</a>
        </div>
        <div style="display:flex;align-items:center;gap:8px;flex:1;justify-content:flex-end">
            <form method="GET" action="{{ route('admin.users') }}" style="display:flex;align-items:center;gap:8px">
                @if($status)<input type="hidden" name="status" value="{{ $status }}">@endif
                <div style="position:relative">
                    <i class="fas fa-search" style="position:absolute;left:9px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:10px"></i>
                    <input type="text" name="q" value="{{ $q }}" placeholder="Cari nama / no HP..."
                        style="padding:7px 12px 7px 26px;font-size:12px;border:1px solid #e2e8f0;border-radius:8px;outline:none;width:210px;color:#374151;background:#f8fafc"
                        onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                </div>
                <button style="background:#0d1b2e;color:#fff;border:none;border-radius:8px;padding:7px 14px;font-size:12px;font-weight:600;cursor:pointer">Filter</button>
                @if($q || $status)<a href="{{ route('admin.users') }}" style="font-size:12px;color:#9ca3af;text-decoration:none">Reset</a>@endif
            </form>
            <a href="{{ route('admin.users.export', request()->only(['q', 'status'])) }}" style="display:flex;align-items:center;gap:6px;border:1px solid #e2e8f0;background:#fff;border-radius:8px;padding:7px 12px;font-size:12px;font-weight:500;color:#374151;cursor:pointer;text-decoration:none">
                <i class="fas fa-file-export" style="font-size:11px;color:#9ca3af"></i> Export
            </a>
        </div>
    </div>

    {{-- Table --}}
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
                @php $colors=[['#eff6ff','#2563eb'],['#f0fdf4','#16a34a'],['#fefce8','#ca8a04'],['#fdf4ff','#9333ea'],['#fff7ed','#ea580c']]; @endphp
                @forelse($users as $i => $user)
                @php [$bg,$tx] = $colors[$i % 5]; @endphp
                <tr style="border-top:1px solid #f1f5f9" onmouseover="this.style.background='#fafbfc'" onmouseout="this.style.background=''">
                    <td style="padding:13px 18px">
                        <div style="display:flex;align-items:center;gap:10px">
                            <div style="width:32px;height:32px;border-radius:50%;background:{{ $bg }};color:{{ $tx }};display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;flex-shrink:0">
                                {{ strtoupper(substr($user->nama,0,1)) }}
                            </div>
                            <div>
                                <div style="font-size:13px;font-weight:600;color:#111827">{{ $user->nama }}</div>
                                <div style="font-size:11px;color:#9ca3af">ID: {{ str_pad($user->id_user,5,'0',STR_PAD_LEFT) }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="padding:13px 18px;font-size:12px;color:#6b7280;font-family:monospace">{{ $user->no_hp }}</td>
                    <td style="padding:13px 18px;font-size:12px;color:#6b7280;white-space:nowrap">–</td>
                    <td style="padding:13px 18px">
                        @if($user->is_banned)
                            <span style="background:#fee2e2;color:#b91c1c;font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px">● Banned</span>
                        @else
                            <span style="background:#dcfce7;color:#15803d;font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px">● Active</span>
                        @endif
                    </td>
                    <td style="padding:13px 18px">
                        <div style="display:flex;align-items:center;gap:10px">
                            <a href="{{ route('admin.orders', ['q' => $user->nama]) }}" style="font-size:12px;color:#2563eb;font-weight:600;text-decoration:none">View Orders</a>
                            <span style="color:#e2e8f0">|</span>
                            @if($user->is_banned)
                            <form action="{{ route('admin.users.unban', $user->id_user) }}" method="POST" style="display:inline">
                                @csrf
                                <button type="submit" style="font-size:12px;color:#16a34a;font-weight:600;background:none;border:none;cursor:pointer;padding:0">Unban</button>
                            </form>
                            @else
                            <form action="{{ route('admin.users.ban', $user->id_user) }}" method="POST" style="display:inline"
                                  onsubmit="return confirm('Nonaktifkan user {{ addslashes($user->nama) }}?')">
                                @csrf
                                <button type="submit" style="font-size:12px;color:#ea580c;font-weight:600;background:none;border:none;cursor:pointer;padding:0">Ban</button>
                            </form>
                            @endif
                            <span style="color:#e2e8f0">|</span>
                            <form action="{{ route('admin.users.delete', $user->id_user) }}" method="POST" style="display:inline"
                                  onsubmit="return confirm('Hapus user {{ addslashes($user->nama) }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" style="font-size:12px;color:#ef4444;font-weight:600;background:none;border:none;cursor:pointer;padding:0">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" style="padding:48px;text-align:center;color:#9ca3af;font-size:13px">Tidak ada data pengguna</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 18px;border-top:1px solid #f1f5f9">
        <span style="font-size:12px;color:#6b7280">Showing 1 to {{ $users->count() }} of {{ $users->total() }} entries</span>
        <div>{{ $users->withQueryString()->links() }}</div>
    </div>
</div>

{{-- Info Card --}}
<div style="margin-top:16px;background:linear-gradient(135deg,#2563eb,#1d4ed8);border-radius:14px;padding:20px 24px;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap">
    <div>
        <div style="font-size:14px;font-weight:700;color:#fff;margin-bottom:4px">User Verification Updates</div>
        <div style="font-size:12px;color:rgba(255,255,255,.75);max-width:520px">
            Data pengguna diperbarui secara real-time. Gunakan fitur delete dengan hati-hati — data yang dihapus tidak dapat dipulihkan.
        </div>
    </div>
    <a href="{{ route('admin.dashboard') }}" style="flex-shrink:0;background:#fff;color:#2563eb;font-size:12px;font-weight:700;padding:9px 20px;border-radius:8px;text-decoration:none;white-space:nowrap">View Dashboard</a>
</div>

@endsection
