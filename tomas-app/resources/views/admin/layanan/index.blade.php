@extends('admin.layouts.app')
@section('title', 'Layanan')

@section('content')

{{-- Header --}}
<div style="margin-bottom:20px">
    <h1 style="font-size:22px;font-weight:800;color:#0d1b2e;line-height:1.2">Service Categories</h1>
    <p style="font-size:12px;color:#6b7280;margin-top:4px">Manage all service categories available in the marketplace.</p>
</div>

<div style="display:grid;grid-template-columns:280px 1fr;gap:16px;align-items:start">

    {{-- Add Form --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden">
        <div style="padding:14px 18px;border-bottom:1px solid #f1f5f9">
            <h3 style="font-size:13px;font-weight:700;color:#0d1b2e">Add New Category</h3>
        </div>
        <form action="{{ route('admin.layanan.store') }}" method="POST" style="padding:18px">
            @csrf
            <div style="margin-bottom:14px">
                <label style="display:block;font-size:10px;font-weight:700;color:#6b7280;letter-spacing:.06em;margin-bottom:6px">NAMA LAYANAN</label>
                <input type="text" name="nama_layanan" value="{{ old('nama_layanan') }}"
                    style="width:100%;border:1px solid #e2e8f0;border-radius:9px;padding:9px 12px;font-size:13px;outline:none;color:#374151;background:#f8fafc"
                    placeholder="Contoh: Tukang Listrik" required
                    onfocus="this.style.borderColor='#3b82f6';this.style.background='#fff'" onblur="this.style.borderColor='#e2e8f0';this.style.background='#f8fafc'">
                @error('nama_layanan')<p style="color:#ef4444;font-size:11px;margin-top:4px">{{ $message }}</p>@enderror
            </div>
            <button type="submit"
                style="width:100%;background:#2563eb;color:#fff;border:none;border-radius:9px;padding:10px;font-size:13px;font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px">
                <i class="fas fa-plus" style="font-size:10px"></i> Tambah Kategori
            </button>
        </form>
    </div>

    {{-- List --}}
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;overflow:hidden">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid #f1f5f9">
            <h3 style="font-size:13px;font-weight:700;color:#0d1b2e">Service Categories</h3>
            <span style="background:#eff6ff;color:#2563eb;font-size:11px;font-weight:600;padding:3px 10px;border-radius:20px">{{ $layanan->count() }} total</span>
        </div>
        <div>
            @forelse($layanan as $l)
            @php $cnt = \App\Models\Tukang::where('kategori',$l->nama_layanan)->count(); @endphp
            <div style="padding:13px 18px;border-bottom:1px solid #f8fafc" onmouseover="this.style.background='#fafbfc'" onmouseout="this.style.background=''">
                <div style="display:flex;align-items:center;justify-content:space-between;gap:12px" id="row-{{ $l->id_layanan }}">
                    <div style="display:flex;align-items:center;gap:10px;flex:1;min-width:0">
                        <div style="width:34px;height:34px;background:#eff6ff;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                            <i class="fas fa-list-check" style="color:#2563eb;font-size:13px"></i>
                        </div>
                        <div>
                            <span style="font-size:13px;font-weight:600;color:#111827">{{ $l->nama_layanan }}</span>
                            <span style="font-size:11px;color:#9ca3af;margin-left:6px">#{{ $l->id_layanan }}</span>
                            <div style="font-size:11px;color:#6b7280;margin-top:1px">{{ $cnt }} provider terdaftar</div>
                        </div>
                    </div>
                    <div style="display:flex;align-items:center;gap:6px;flex-shrink:0">
                        <button onclick="startEdit({{ $l->id_layanan }}, '{{ addslashes($l->nama_layanan) }}')"
                            style="display:flex;align-items:center;gap:4px;font-size:11px;font-weight:600;color:#2563eb;border:1px solid #bfdbfe;background:#eff6ff;border-radius:7px;padding:5px 10px;cursor:pointer">
                            <i class="fas fa-pen" style="font-size:9px"></i> Edit
                        </button>
                        <form action="{{ route('admin.layanan.delete', $l->id_layanan) }}" method="POST" style="display:inline"
                              onsubmit="return confirm('Hapus layanan {{ addslashes($l->nama_layanan) }}?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                style="display:flex;align-items:center;gap:4px;font-size:11px;font-weight:600;color:#ef4444;border:1px solid #fecaca;background:#fff5f5;border-radius:7px;padding:5px 10px;cursor:pointer">
                                <i class="fas fa-trash" style="font-size:9px"></i> Delete
                            </button>
                        </form>
                    </div>
                </div>
                {{-- Inline edit form --}}
                <div id="edit-{{ $l->id_layanan }}" style="display:none;margin-top:10px">
                    <form action="{{ route('admin.layanan.update', $l->id_layanan) }}" method="POST" style="display:flex;gap:8px">
                        @csrf @method('PUT')
                        <input type="text" name="nama_layanan" id="inp-{{ $l->id_layanan }}" value="{{ $l->nama_layanan }}" required
                            style="flex:1;border:1px solid #3b82f6;border-radius:8px;padding:8px 12px;font-size:12px;outline:none;color:#374151">
                        <button style="background:#2563eb;color:#fff;border:none;border-radius:8px;padding:8px 14px;font-size:12px;font-weight:600;cursor:pointer">Save</button>
                        <button type="button" onclick="cancelEdit({{ $l->id_layanan }})"
                            style="border:1px solid #e2e8f0;background:#f8fafc;border-radius:8px;padding:8px 12px;font-size:12px;color:#6b7280;cursor:pointer">Cancel</button>
                    </form>
                </div>
            </div>
            @empty
            <div style="padding:48px;text-align:center;color:#9ca3af;font-size:13px">Belum ada layanan</div>
            @endforelse
        </div>
    </div>
</div>

@push('scripts')
<script>
function startEdit(id, val) {
    document.getElementById('edit-'+id).style.display = 'block';
    document.getElementById('inp-'+id).value = val;
    document.getElementById('inp-'+id).focus();
}
function cancelEdit(id) {
    document.getElementById('edit-'+id).style.display = 'none';
}
</script>
@endpush
@endsection
