<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login – Tomas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="min-h-screen flex items-center justify-center" style="background: linear-gradient(135deg,#0f1c35 0%,#1a2b4a 30%,#1e3a8a 65%,#4f46e5 100%)">
<div class="w-full max-w-sm mx-4">
    {{-- Card --}}
    <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
        {{-- Header --}}
        <div class="px-8 pt-10 pb-6 text-center" style="background:linear-gradient(160deg,#0f1c35 0%,#1a2b4a 60%,#1e3a8a 100%)">
            <img src="{{ asset('images/tomas-logo.png') }}" alt="Tomas" class="h-20 object-contain mx-auto mb-2">
            <p class="text-blue-300 text-sm mt-1">Admin Panel</p>
        </div>

        {{-- Form --}}
        <div class="px-8 py-8">
            @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-600 rounded-xl px-4 py-2.5 text-sm mb-5 flex items-center gap-2">
                <i class="fas fa-circle-exclamation"></i> {{ session('error') }}
            </div>
            @endif
            @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-600 rounded-xl px-4 py-2.5 text-sm mb-5 flex items-center gap-2">
                <i class="fas fa-circle-check"></i> {{ session('success') }}
            </div>
            @endif

            <form action="{{ route('admin.login.post') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">Username</label>
                    <div class="relative">
                        <i class="fas fa-user absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                        <input type="text" name="username" value="{{ old('username') }}"
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-10 pr-4 py-3 text-sm text-gray-700 outline-none focus:border-blue-400 focus:bg-white transition"
                            placeholder="admin" required autocomplete="off">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">Password</label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                        <input type="password" name="password" id="adminPass"
                            class="w-full bg-gray-50 border border-gray-200 rounded-xl pl-10 pr-10 py-3 text-sm text-gray-700 outline-none focus:border-blue-400 focus:bg-white transition"
                            placeholder="••••••••" required>
                        <button type="button" onclick="togglePass()" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i class="fas fa-eye text-sm" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit"
                    class="w-full py-3 rounded-xl text-white font-bold text-sm shadow-lg hover:opacity-90 transition mt-2"
                    style="background:#1A2B47">
                    <i class="fas fa-right-to-bracket mr-2"></i>Masuk ke Admin
                </button>
            </form>

            <div class="text-center mt-6">
                <a href="{{ route('home') }}" class="text-xs text-gray-400 hover:text-gray-600">
                    <i class="fas fa-arrow-left mr-1"></i>Kembali ke App
                </a>
            </div>
        </div>
    </div>

    <p class="text-center text-blue-200 text-xs mt-6 opacity-60">© 2025 Tomas. All rights reserved.</p>
</div>
<script>
function togglePass() {
    const inp = document.getElementById('adminPass');
    const icon = document.getElementById('eyeIcon');
    if (inp.type === 'password') { inp.type = 'text'; icon.className = 'fas fa-eye-slash text-sm'; }
    else { inp.type = 'password'; icon.className = 'fas fa-eye text-sm'; }
}
</script>
</body>
</html>
