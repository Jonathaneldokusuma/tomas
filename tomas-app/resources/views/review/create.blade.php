@extends('layouts.app')
@section('title', 'Review')
@section('content')
<div class="page-content bg-gray-50">

    <!-- Header -->
    <div class="flex items-center gap-3 px-4 pt-5 pb-3 bg-white border-b border-gray-100">
        <a href="{{ route('riwayat') }}" class="w-9 h-9 rounded-full flex items-center justify-center bg-gray-100">
            <i class="fas fa-arrow-left text-navy text-sm"></i>
        </a>
        <h1 class="font-bold text-navy text-lg">Beri Ulasan</h1>
    </div>

    <div class="px-4 pt-6">
        <!-- Card tukang -->
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 mb-6">
            <div class="flex items-center gap-3">
                <div class="w-14 h-14 rounded-full bg-gray-200 flex items-center justify-center flex-none overflow-hidden">
                    @if(isset($order->tukang) && $order->tukang->foto)
                    <img src="{{ Storage::url($order->tukang->foto) }}" alt="" class="w-full h-full object-cover">
                    @else
                    <i class="fas fa-user text-gray-400 text-2xl"></i>
                    @endif
                </div>
                <div>
                    <div class="font-bold text-navy">{{ $order->tukang->nama ?? '-' }}</div>
                    <div class="text-xs text-gray-500 mt-0.5">{{ $order->layanan->nama_layanan ?? '-' }}</div>
                </div>
            </div>
        </div>

        @if(session('success'))
        <div class="mb-4 bg-green-50 text-green-700 px-4 py-2 rounded-xl text-sm font-medium">
            {{ session('success') }}
        </div>
        @endif

        <!-- Form -->
        <form action="{{ route('review.store', $order->id_order) }}" method="POST">
            @csrf

            <!-- Bintang -->
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 mb-4">
                <p class="text-sm font-semibold text-navy mb-3 text-center">Beri penilaian</p>
                <div class="flex justify-center gap-3" id="star-container">
                    @for($i = 1; $i <= 5; $i++)
                    <label class="cursor-pointer star-label" data-value="{{ $i }}">
                        <input type="radio" name="rating" value="{{ $i }}"
                               class="hidden"
                               {{ old('rating', 5) == $i ? 'checked' : '' }}>
                        <i class="fas fa-star text-4xl {{ old('rating', 5) >= $i ? 'text-yellow-400' : 'text-gray-200' }} transition-colors"
                           id="star-{{ $i }}"></i>
                    </label>
                    @endfor
                </div>
                @error('rating')<p class="text-red-500 text-xs mt-2 text-center">{{ $message }}</p>@enderror
            </div>

            <!-- Komentar -->
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 mb-6">
                <label class="text-sm font-semibold text-navy block mb-2">Komentar (opsional)</label>
                <textarea name="komentar" rows="4"
                          placeholder="Bagikan pengalaman Anda menggunakan jasa ini..."
                          class="w-full bg-gray-50 rounded-xl px-4 py-3 text-sm outline-none focus:ring-2 focus:ring-blue-300 resize-none"
                >{{ old('komentar') }}</textarea>
            </div>

            <button type="submit"
                    class="w-full bg-blue-600 text-white font-bold py-3.5 rounded-2xl text-sm shadow-md">
                Kirim Ulasan
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
const stars = document.querySelectorAll('.star-label');
stars.forEach(label => {
    label.addEventListener('click', function () {
        const val = parseInt(this.dataset.value);
        stars.forEach((s, idx) => {
            const icon = s.querySelector('i');
            icon.classList.toggle('text-yellow-400', idx < val);
            icon.classList.toggle('text-gray-200', idx >= val);
        });
    });
});
</script>
@endpush
