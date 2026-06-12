<!-- Bottom Navigation Partial -->
<div class="bottom-nav bg-white border-t border-gray-200 shadow-lg">
    <div class="flex items-center justify-around py-2 px-2">
        <a href="{{ route('home') }}"
            class="nav-icon {{ request()->routeIs('home') ? 'text-primary' : 'text-gray-400' }} flex-1">
            <i class="fas fa-house text-xl"></i>
            <span class="text-[10px] font-medium">Home</span>
        </a>
        <a href="{{ route('chat') }}"
            class="nav-icon {{ request()->routeIs('chat*') ? 'text-primary' : 'text-gray-400' }} flex-1">
            <i class="far fa-comment-dots text-xl"></i>
            <span class="text-[10px] font-medium">Chat</span>
        </a>
        <a href="{{ route('tambah') }}" class="nav-icon flex-1">
            <div class="bg-primary text-white w-14 h-14 rounded-full flex items-center justify-center shadow-lg -mt-5 border-4 border-white">
                <i class="fas fa-plus text-2xl"></i>
            </div>
            <span class="text-[10px] font-semibold text-primary">Tambah</span>
        </a>
        <a href="{{ route('riwayat') }}"
            class="nav-icon {{ request()->routeIs('riwayat') ? 'text-primary' : 'text-gray-400' }} flex-1">
            <i class="fas fa-rectangle-list text-xl"></i>
            <span class="text-[10px] font-medium">Riwayat</span>
        </a>
        <a href="{{ route('profile') }}"
            class="nav-icon {{ request()->routeIs('profile') ? 'text-primary' : 'text-gray-400' }} flex-1">
            <i class="far fa-user text-xl"></i>
            <span class="text-[10px] font-medium">Profile</span>
        </a>
    </div>
</div>
