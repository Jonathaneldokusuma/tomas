@extends('layouts.app')
@section('title', 'Chat - ' . $tukang->nama)

@push('styles')
<style>
.bubble-user   { background: #1565C0; color:#fff; border-radius:18px 18px 4px 18px; }
.bubble-tukang { background: #F0F4FF; color:#1A2B47; border-radius:18px 18px 18px 4px; }
#chat-body     { padding-bottom: 80px; }
.chat-input-bar { position:fixed; bottom:0; left:50%; transform:translateX(-50%);
                  width:100%; max-width:430px; background:#fff;
                  border-top:1px solid #eee; padding:10px 12px; z-index:50; }
</style>
@endpush

@section('content')
<div class="page-content bg-gray-50" id="chat-page">

    <!-- Header -->
    <div class="flex items-center gap-3 px-4 pt-5 pb-3 bg-white border-b border-gray-100 sticky top-0 z-10">
        <a href="{{ route('chat') }}" class="w-9 h-9 rounded-full flex items-center justify-center bg-gray-100">
            <i class="fas fa-arrow-left text-navy text-sm"></i>
        </a>
        <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center flex-none overflow-hidden">
            @if($tukang->foto)
            <img src="{{ Storage::url($tukang->foto) }}" alt="" class="w-full h-full object-cover">
            @else
            <i class="fas fa-user text-gray-400 text-lg"></i>
            @endif
        </div>
        <div class="flex-1 min-w-0">
            <div class="font-bold text-navy text-sm leading-tight">{{ $tukang->nama }}</div>
            <div class="text-xs {{ $tukang->status_aktif ? 'text-green-500' : 'text-gray-400' }} font-medium">
                {{ $tukang->status_aktif ? '● Online' : '● Offline' }}
            </div>
        </div>
        <a href="{{ route('tukang.show', $tukang->id_tukang) }}"
           class="text-xs text-primary font-semibold px-3 py-1.5 rounded-full border border-primary">
            Profil
        </a>
    </div>

    <!-- Messages -->
    <div id="chat-body" class="px-4 pt-4 space-y-3">
        @if($messages->isEmpty())
        <div class="flex flex-col items-center py-10 text-gray-400">
            <i class="fas fa-comments text-5xl mb-3 opacity-20"></i>
            <p class="text-sm">Belum ada pesan. Mulai obrolan!</p>
        </div>
        @else
        @foreach($messages as $msg)
            @if($msg->dari_user)
            {{-- Bubble kanan (user) --}}
            <div class="flex justify-end">
                <div class="max-w-[75%]">
                    <div class="bubble-user text-sm px-4 py-2.5 leading-snug">{{ $msg->pesan }}</div>
                    <div class="text-right text-xs text-gray-400 mt-1 pr-1">
                        {{ $msg->created_at->format('H:i') }}
                    </div>
                </div>
            </div>
            @else
            {{-- Bubble kiri (tukang) --}}
            <div class="flex gap-2 items-end">
                <div class="w-7 h-7 rounded-full bg-blue-100 flex items-center justify-center flex-none">
                    <i class="fas fa-user text-blue-400 text-xs"></i>
                </div>
                <div class="max-w-[75%]">
                    <div class="bubble-tukang text-sm px-4 py-2.5 leading-snug">{{ $msg->pesan }}</div>
                    <div class="text-xs text-gray-400 mt-1 pl-1">
                        {{ $msg->created_at->format('H:i') }}
                    </div>
                </div>
            </div>
            @endif
        @endforeach
        @endif
    </div>

    <!-- Input Bar -->
    <div class="chat-input-bar">
        <form action="{{ route('chat.send', $tukang->id_tukang) }}" method="POST"
              class="flex items-center gap-2">
            @csrf
            <input type="text" name="pesan"
                   placeholder="Ketik pesan..."
                   autocomplete="off"
                   class="flex-1 bg-gray-100 rounded-full px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-blue-300"
                   required>
            <button type="submit"
                    class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center shadow">
                <i class="fas fa-paper-plane text-white text-sm"></i>
            </button>
        </form>
    </div>

</div>
@endsection

@push('scripts')
<script>
    // Scroll ke bawah otomatis
    const body = document.getElementById('chat-body');
    if (body) body.scrollIntoView({ block: 'end' });
    window.scrollTo(0, document.body.scrollHeight);
</script>
@endpush
