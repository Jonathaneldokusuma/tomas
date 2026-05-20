@extends('admin.layouts.app')
@section('title', isset($tukang) ? 'Edit Tukang' : 'Tambah Tukang')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="font-bold text-navy">{{ isset($tukang) ? 'Edit Tukang: '.$tukang->nama : 'Tambah Tukang Baru' }}</h2>
        </div>
        <form action="{{ isset($tukang) ? route('admin.tukang.update', $tukang->id_tukang) : route('admin.tukang.store') }}"
              method="POST" enctype="multipart/form-data" class="px-6 py-6 space-y-5">
            @csrf
            @if(isset($tukang)) @method('PUT') @endif

            {{-- Nama --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">Nama</label>
                <input type="text" name="nama" value="{{ old('nama', $tukang->nama ?? '') }}"
                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-blue-400 @error('nama') border-red-400 @enderror"
                    placeholder="Nama tukang" required>
                @error('nama')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Kategori --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">Kategori</label>
                <select name="kategori"
                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-blue-400 bg-white @error('kategori') border-red-400 @enderror">
                    <option value="">-- Pilih Kategori --</option>
                    @foreach($layanan as $l)
                    <option value="{{ $l->nama_layanan }}"
                        {{ old('kategori', $tukang->kategori ?? '') === $l->nama_layanan ? 'selected' : '' }}>
                        {{ $l->nama_layanan }}
                    </option>
                    @endforeach
                </select>
                @error('kategori')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Lokasi --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">Lokasi</label>
                <input type="text" name="lokasi" value="{{ old('lokasi', $tukang->lokasi ?? '') }}"
                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-blue-400"
                    placeholder="Contoh: Jakarta Selatan">
            </div>

            {{-- Bio --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">Bio</label>
                <textarea name="bio" rows="3"
                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-blue-400 resize-none"
                    placeholder="Deskripsi singkat tukang...">{{ old('bio', $tukang->bio ?? '') }}</textarea>
            </div>

            {{-- Foto --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">Foto Profil</label>
                @if(isset($tukang) && $tukang->foto)
                <div class="flex items-center gap-4 mb-3">
                    <img src="{{ Storage::url($tukang->foto) }}" alt="Foto" class="w-16 h-16 rounded-xl object-cover border border-gray-200">
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Foto saat ini</p>
                        <label class="flex items-center gap-1.5 cursor-pointer text-xs text-red-500">
                            <input type="checkbox" name="hapus_foto" value="1" class="accent-red-500"> Hapus foto ini
                        </label>
                    </div>
                </div>
                @endif
                <div style="border:2px dashed #e2e8f0;border-radius:12px;padding:20px;text-align:center;cursor:pointer;transition:border-color .2s"
                     onclick="document.getElementById('fotoInput').click()"
                     ondragover="event.preventDefault();this.style.borderColor='#3b82f6'"
                     ondragleave="this.style.borderColor='#e2e8f0'"
                     ondrop="event.preventDefault();this.style.borderColor='#e2e8f0';handleDrop(event)">
                    <div id="fotoPreview" class="hidden mb-2">
                        <img id="fotoPreviewImg" src="" alt="" class="w-20 h-20 rounded-xl object-cover mx-auto border border-gray-200">
                    </div>
                    <i class="fas fa-cloud-arrow-up text-2xl text-blue-400 mb-2"></i>
                    <p class="text-xs text-gray-500">Klik atau drag foto ke sini</p>
                    <p class="text-xs text-gray-400 mt-0.5">JPG, PNG, WEBP · Maks 2MB</p>
                    <input type="file" id="fotoInput" name="foto" accept="image/*" class="hidden"
                           onchange="previewFoto(this)">
                </div>
                @error('foto')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Tarif --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">Tarif (Rp)</label>
                <input type="number" name="tarif" value="{{ old('tarif', $tukang->tarif ?? '') }}"
                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-blue-400"
                    placeholder="Contoh: 150000" min="0">
                @error('tarif')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Status --}}
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-2 uppercase tracking-wider">Status</label>
                <div class="flex items-center gap-5">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="status_aktif" value="1"
                            {{ old('status_aktif', $tukang->status_aktif ?? 1) == 1 ? 'checked' : '' }} class="accent-blue-600">
                        <span class="text-sm font-medium text-green-600">Aktif</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="status_aktif" value="0"
                            {{ old('status_aktif', $tukang->status_aktif ?? 1) == 0 ? 'checked' : '' }} class="accent-gray-500">
                        <span class="text-sm font-medium text-gray-500">Nonaktif</span>
                    </label>
                </div>
            </div>

            {{-- Buttons --}}
            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                    class="bg-blue-600 text-white text-sm px-6 py-2.5 rounded-xl font-semibold hover:bg-blue-700">
                    <i class="fas fa-save mr-2"></i>{{ isset($tukang) ? 'Simpan Perubahan' : 'Tambah Tukang' }}
                </button>
                <a href="{{ route('admin.tukang') }}"
                   class="text-sm px-6 py-2.5 rounded-xl font-semibold border border-gray-200 text-gray-600 hover:bg-gray-50">
                    Batal
                </a>
            </div>

            {{-- Timestamps --}}
            @if(isset($tukang) && $tukang->updated_at)
            <div class="pt-2 border-t border-gray-100 flex items-center gap-6">
                @if($tukang->created_at)
                <div class="text-xs text-gray-400"><i class="fas fa-calendar-plus mr-1"></i>Dibuat: <span class="font-medium text-gray-500">{{ $tukang->created_at->format('d M Y, H:i') }}</span></div>
                @endif
                <div class="text-xs text-gray-400"><i class="fas fa-clock mr-1"></i>Diperbarui: <span class="font-medium text-gray-500">{{ $tukang->updated_at->format('d M Y, H:i') }}</span></div>
            </div>
            @endif
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function previewFoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('fotoPreviewImg').src = e.target.result;
            document.getElementById('fotoPreview').classList.remove('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}
function handleDrop(event) {
    const dt = event.dataTransfer;
    if (dt.files.length) {
        document.getElementById('fotoInput').files = dt.files;
        previewFoto(document.getElementById('fotoInput'));
    }
}
</script>
@endpush
