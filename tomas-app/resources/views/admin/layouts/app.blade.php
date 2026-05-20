<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tomas Admin – @yield('title', 'Dashboard')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: linear-gradient(135deg, #eef2ff 0%, #f0f4f8 50%, #e8f0fe 100%); min-height: 100vh; }
        .nav-item { display: flex; align-items: center; gap: 10px; padding: 8px 12px; border-radius: 9px; font-size: 13px; font-weight: 500; color: #7a9cbf; transition: all .2s; text-decoration: none; white-space: nowrap; position: relative; }
        .nav-item:hover { background: rgba(255,255,255,.1); color: #e2e8f0; transform: translateX(2px); }
        .nav-item.active { background: linear-gradient(90deg, rgba(59,130,246,.35) 0%, rgba(59,130,246,.1) 100%); color: #fff; border-left: 3px solid #3b82f6; padding-left: 9px; box-shadow: 0 0 12px rgba(59,130,246,.2); }
        .nav-item i { width: 16px; text-align: center; font-size: 13px; flex-shrink: 0; }
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-thumb { background: rgba(255,255,255,.15); border-radius: 4px; }
        .icon-btn { width: 34px; height: 34px; border: 1px solid #e2e8f0; border-radius: 9px; background: #fff; display: flex; align-items: center; justify-content: center; color: #6b7280; cursor: pointer; transition: all .15s; flex-shrink: 0; }
        .icon-btn:hover { background: linear-gradient(135deg, #eff6ff, #dbeafe); border-color: #bfdbfe; color: #2563eb; }
        .topbar-gradient { background: linear-gradient(90deg, #fff 0%, #fafbff 100%); border-bottom: 1px solid #e2e8f0; box-shadow: 0 1px 12px rgba(37,99,235,.06); }
    </style>
    @stack('styles')
</head>
<body style="display:flex;min-height:100vh">

{{-- ── SIDEBAR ──────────────────────────────── --}}
<aside style="width:210px;background:linear-gradient(180deg,#0f2248 0%,#0d1b38 45%,#080f20 100%);min-height:100vh;display:flex;flex-direction:column;flex-shrink:0;position:sticky;top:0;box-shadow:4px 0 24px rgba(0,0,0,.35)">
    {{-- Brand --}}
    <div style="padding:18px 14px 14px;border-bottom:1px solid rgba(255,255,255,.08);background:linear-gradient(135deg,rgba(37,99,235,.15),rgba(124,58,237,.08))">
        <div style="display:flex;align-items:center;gap:10px">
            <img src="{{ asset('images/tomas-logo.png') }}" alt="Tomas" style="height:42px;object-fit:contain">
            <div>
                <div style="color:#fff;font-weight:700;font-size:13px;line-height:1.2">TomasAdmin</div>
                <div style="color:#5b8fc9;font-size:10px;margin-top:1px;background:linear-gradient(90deg,#60a5fa,#a78bfa);-webkit-background-clip:text;-webkit-text-fill-color:transparent">Management Portal</div>
            </div>
        </div>
    </div>

    {{-- Nav --}}
    <nav style="flex:1;padding:12px 8px;display:flex;flex-direction:column;gap:2px;overflow-y:auto">
        <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="fas fa-table-columns"></i><span>Dashboard</span>
        </a>
        <a href="{{ route('admin.tukang') }}" class="nav-item {{ request()->routeIs('admin.tukang*') ? 'active' : '' }}">
            <i class="fas fa-screwdriver-wrench"></i><span>Services</span>
        </a>
        <a href="{{ route('admin.users') }}" class="nav-item {{ request()->routeIs('admin.users') ? 'active' : '' }}">
            <i class="fas fa-users"></i><span>Users</span>
        </a>
        <a href="{{ route('admin.layanan') }}" class="nav-item {{ request()->routeIs('admin.layanan') ? 'active' : '' }}">
            <i class="fas fa-list-check"></i><span>Layanan</span>
        </a>
        <a href="{{ route('admin.orders') }}" class="nav-item {{ request()->routeIs('admin.orders') ? 'active' : '' }}">
            <i class="fas fa-receipt"></i><span>Orders</span>
        </a>
        <a href="{{ route('admin.reviews') }}" class="nav-item {{ request()->routeIs('admin.reviews') ? 'active' : '' }}">
            <i class="fas fa-star"></i><span>Reviews</span>
        </a>
    </nav>

    {{-- Bottom --}}
    <div style="padding:10px 8px 14px;border-top:1px solid rgba(255,255,255,.08);display:flex;flex-direction:column;gap:2px">
        <a href="{{ route('admin.tukang.create') }}"
           style="display:flex;align-items:center;justify-content:center;gap:8px;background:linear-gradient(135deg,#2563eb 0%,#4f46e5 100%);color:#fff;border-radius:10px;padding:9px 12px;font-size:12px;font-weight:600;text-decoration:none;margin-bottom:4px;transition:all .2s;box-shadow:0 4px 14px rgba(37,99,235,.4)"
           onmouseover="this.style.opacity='.88';this.style.transform='translateY(-1px)'" onmouseout="this.style.opacity='1';this.style.transform='translateY(0)'">
            <i class="fas fa-plus" style="font-size:10px"></i> New Service
        </a>
        <a href="{{ route('home') }}" target="_blank" class="nav-item" style="font-size:12px">
            <i class="fas fa-up-right-from-square"></i><span>View App</span>
        </a>
        <form action="{{ route('admin.logout') }}" method="POST">
            @csrf
            <button type="submit" class="nav-item w-full" style="color:#f87171;font-size:12px;background:none;border:none;cursor:pointer;text-align:left">
                <i class="fas fa-right-from-bracket"></i><span>Logout</span>
            </button>
        </form>
    </div>
</aside>

{{-- ── MAIN ────────────────────────────────── --}}
<div style="flex:1;display:flex;flex-direction:column;overflow-x:hidden;min-width:0">

    {{-- Top Bar --}}
    <header class="topbar-gradient" style="height:56px;display:flex;align-items:center;padding:0 22px;gap:12px;position:sticky;top:0;z-index:20">
        <div style="position:relative;flex:1;max-width:340px">
            <i class="fas fa-magnifying-glass" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:11px"></i>
            <input type="text" placeholder="Search data, users, or services..."
                style="width:100%;background:#f8fafc;border:1px solid #e2e8f0;border-radius:9px;padding:7px 12px 7px 28px;font-size:12px;outline:none;transition:all .2s;color:#374151"
                onfocus="this.style.borderColor='#3b82f6';this.style.background='#fff';this.style.boxShadow='0 0 0 3px rgba(59,130,246,.1)'"
                onblur="this.style.borderColor='#e2e8f0';this.style.background='#f8fafc';this.style.boxShadow='none'">
        </div>
        <div style="flex:1"></div>
        <button class="icon-btn"><i class="fas fa-bell" style="font-size:13px"></i></button>
        <button class="icon-btn"><i class="fas fa-gear" style="font-size:13px"></i></button>
        <div style="width:1px;height:26px;background:#e2e8f0;margin:0 4px"></div>
        <div style="display:flex;align-items:center;gap:8px">
            <div style="width:30px;height:30px;background:linear-gradient(135deg,#2563eb 0%,#7c3aed 100%);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:11px;font-weight:700;flex-shrink:0;box-shadow:0 2px 8px rgba(37,99,235,.4)">
                {{ strtoupper(substr(session('admin_username','A'),0,1)) }}
            </div>
            <div>
                <div style="font-size:12px;font-weight:600;color:#111827;line-height:1.2">{{ session('admin_username','Admin') }}</div>
                <div style="font-size:10px;color:#9ca3af;line-height:1.2">Super Admin</div>
            </div>
        </div>
    </header>

    {{-- Flash --}}
    @if(session('success'))
    <div id="flash-msg" style="margin:16px 20px 0;background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;border-radius:10px;padding:11px 16px;font-size:13px;display:flex;align-items:center;gap:8px">
        <i class="fas fa-circle-check"></i> {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div id="flash-msg" style="margin:16px 20px 0;background:#fef2f2;border:1px solid #fecaca;color:#dc2626;border-radius:10px;padding:11px 16px;font-size:13px;display:flex;align-items:center;gap:8px">
        <i class="fas fa-circle-exclamation"></i> {{ session('error') }}
    </div>
    @endif

    {{-- Content --}}
    <main style="flex:1;padding:22px 20px">
        @yield('content')
    </main>
</div>

<script>
setTimeout(() => { const el = document.getElementById('flash-msg'); if(el) el.style.display='none'; }, 3000);
</script>
@stack('scripts')
</body>
</html>
