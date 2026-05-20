@extends('layouts.app')
@section('title', 'Chat')
@section('content')
<div class="page-content">
    <!-- Header -->
    <div class="px-4 pt-6 pb-3">
        <h1 class="text-2xl font-bold text-navy">Chat</h1>
        <div class="flex items-center gap-1 mt-1">
            <span class="text-sm text-gray-500 font-medium">Terbaru</span>
            <i class="fas fa-chevron-down text-xs text-gray-400"></i>
        </div>
    </div>

    @if(session('success'))
    <div class="mx-4 mb-3 bg-green-50 text-green-700 px-4 py-2 rounded-xl text-sm font-medium">
        {{ session('success') }}
    </div>
    @endif

    <!-- Chat List -->
    <div class="divide-y divide-gray-100">
        @forelse($chatList as $item)
        <a href="{{ route('chat.show', $item['tukang']->id_tukang) }}"
           class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 active:bg-gray-100">
            <div class="w-12 h-12 rounded-full bg-gray-200 flex-none flex items-center justify-center overflow-hidden">
                @if($item['tukang']->foto)
                <img src="{{ Storage::url($item['tukang']->foto) }}" alt="" class="w-full h-full object-cover">
                @else
                <i class="fas fa-user text-gray-400 text-xl"></i>
                @endif
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between">
                    <span class="font-semibold text-navy text-sm">{{ $item['tukang']->nama }}</span>
                    @if($item['unread'] > 0)
                    <span class="bg-primary text-white text-xs font-bold rounded-full min-w-[20px] h-5 flex items-center justify-center px-1">
                        {{ $item['unread'] }}
                    </span>
                    @endif
                </div>
                <p class="text-xs text-gray-500 truncate mt-0.5">{{ $item['last']->pesan }}</p>
            </div>
        </a>
        @empty
        <div class="flex flex-col items-center py-16 text-gray-400 px-4">
            <i class="fas fa-comments text-6xl mb-4 opacity-20"></i>
            <p class="text-sm font-medium">Belum ada percakapan</p>
            <p class="text-xs mt-1 text-center">Mulai chat dengan tukang dari halaman profil mereka</p>
            <a href="{{ route('tukang.index') }}"
               class="mt-4 bg-blue-600 text-white text-sm font-bold px-6 py-2.5 rounded-full">
                Cari Tukang
            </a>
        </div>
        @endforelse
    </div>
</div>

@include('partials.bottom-nav')
@endsection
