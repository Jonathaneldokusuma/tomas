@extends('layouts.app')
@section('title', 'Favorit Saya')
@section('content')
<div class="page-content bg-gray-50">

    <!-- Header -->
    <div class="flex items-center gap-3 px-4 pt-5 pb-3 bg-white border-b border-gray-100 sticky top-0 z-10">
        <a href="{{ route('profile') }}" class="w-9 h-9 rounded-full flex items-center justify-center bg-gray-100">
            <i class="fas fa-arrow-left text-navy text-sm"></i>
        </a>
        <h1 class="font-bold text-navy text-lg flex-1">Favorit Saya</h1>
        <span class="text-sm text-gray-400">{{ $favorits->count() }} tukang</span>
    </div>

    <div class="px-4 pt-4 pb-24 space-y-3">
        @forelse($favorits as $fav)
        @if($fav->tukang)
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 flex items-center gap-3">
            <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center flex-none overflow-hidden">
                @if($fav->tukang->foto)
                <img src="{{ Storage::url($fav->tukang->foto) }}" alt="" class="w-full h-full object-cover">
                @else
                <i class="fas fa-user text-gray-400 text-lg"></i>
                @endif
            </div>
            <div class="flex-1 min-w-0">
                <div class="font-bold text-navy text-sm">{{ $fav->tukang->nama }}</div>
                <div class="text-xs text-gray-500 mt-0.5">
                    <i class="fas fa-tag text-xs mr-1 text-blue-400"></i>{{ $fav->tukang->kategori ?? 'Umum' }}
                    &nbsp;·&nbsp;
                    <i class="fas fa-location-dot text-xs mr-1 text-red-400"></i>{{ $fav->tukang->lokasi ?? 'Surakarta' }}
                </div>
                <div class="flex items-center gap-1 mt-1">
                    <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold
                        {{ $fav->tukang->status_aktif ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-400' }}">
                        {{ $fav->tukang->status_aktif ? '● Aktif' : '● Offline' }}
                    </span>
                </div>
            </div>
            <div class="flex flex-col gap-2 items-end">
                <a href="{{ route('tukang.show', $fav->tukang->id_tukang) }}"
                   class="bg-blue-600 text-white text-xs font-bold px-4 py-2 rounded-full">
                    Pesan
                </a>
                <button class="fav-btn text-red-500 text-xl"
                        data-id="{{ $fav->tukang->id_tukang }}"
                        title="Hapus dari favorit">
                    <i class="fas fa-heart"></i>
                </button>
            </div>
        </div>
        @endif
        @empty
        <div class="flex flex-col items-center py-16 text-gray-400">
            <i class="fas fa-heart text-6xl mb-4 opacity-20"></i>
            <p class="text-sm font-medium">Belum ada tukang favorit</p>
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

@push('scripts')
<script>
document.querySelectorAll('.fav-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const id = this.dataset.id;
        fetch(`/favorit/${id}`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (!data.favorited) {
                this.closest('.bg-white').remove();
            }
        });
    });
});
</script>
@endpush
