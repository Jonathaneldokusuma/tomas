@extends('admin.layouts.app')
@section('title', 'Support Center User')

@section('content')
<div style="padding:24px">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:20px;flex-wrap:wrap">
        <div>
            <h1 style="font-size:20px;font-weight:700;color:#1e293b">Support Center User</h1>
            <p style="color:#64748b;font-size:13px;margin-top:2px">Chat dua arah antara app user dan customer service pusat</p>
        </div>
        <div style="background:#eff6ff;border:1px solid #bfdbfe;color:#1d4ed8;border-radius:10px;padding:10px 14px;font-size:13px;font-weight:600">
            <i class="fas fa-circle-info" style="margin-right:6px"></i>Pesan dari user juga masuk ke notifikasi dan FCM
        </div>
    </div>

    @if(session('success'))
        <div style="background:#dcfce7;border:1px solid #86efac;border-radius:8px;padding:12px 16px;color:#166534;margin-bottom:16px;font-size:13px">
            <i class="fas fa-check-circle" style="margin-right:6px"></i>{{ session('success') }}
        </div>
    @endif

    <div style="display:grid;grid-template-columns:320px 1fr;gap:18px;align-items:start">
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;box-shadow:0 1px 6px rgba(0,0,0,.05);overflow:hidden">
            <div style="padding:16px 18px;border-bottom:1px solid #f1f5f9">
                <h2 style="font-size:15px;font-weight:600;color:#1e293b">
                    <i class="fas fa-comments" style="color:#2563eb;margin-right:6px"></i>Thread Masuk
                </h2>
            </div>
            <div style="max-height:70vh;overflow:auto">
                @forelse($threads as $thread)
                    @php
                        $isActive = (string) $thread['id_user'] === (string) $selectedUserId;
                        $name = $thread['user']->nama ?? 'User';
                        $snippet = \Illuminate\Support\Str::limit($thread['last_message'] ?? '', 70);
                    @endphp
                    <a href="{{ route('admin.support.user', ['user_id' => $thread['id_user']]) }}"
                       style="display:block;padding:14px 16px;border-bottom:1px solid #f8fafc;text-decoration:none;background:{{ $isActive ? '#eff6ff' : '#fff' }};border-left:3px solid {{ $isActive ? '#2563eb' : 'transparent' }}">
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:10px">
                            <div style="min-width:0">
                                <div style="font-weight:700;color:#111827;font-size:14px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $name }}</div>
                                <div style="font-size:12px;color:#64748b;margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $snippet ?: 'Belum ada pesan' }}</div>
                            </div>
                            <div style="flex-shrink:0;text-align:right">
                                <div style="font-size:11px;color:#94a3b8">{{ optional($thread['last_time'])->diffForHumans() }}</div>
                                <div style="margin-top:4px;display:inline-flex;align-items:center;gap:5px;background:#dbeafe;color:#1d4ed8;border-radius:999px;padding:2px 8px;font-size:10px;font-weight:700">
                                    <i class="fas fa-circle" style="font-size:7px"></i>{{ $thread['kategori'] }}
                                </div>
                            </div>
                        </div>
                    </a>
                @empty
                    <div style="padding:40px;text-align:center;color:#94a3b8">
                        <i class="fas fa-headset" style="font-size:32px;margin-bottom:10px;display:block;opacity:.4"></i>
                        Belum ada pesan dari user.
                    </div>
                @endforelse
            </div>
        </div>

        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;box-shadow:0 1px 6px rgba(0,0,0,.05);overflow:hidden;display:flex;flex-direction:column;min-height:70vh">
            <div style="padding:16px 18px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap">
                <div>
                    <h2 style="font-size:15px;font-weight:700;color:#1e293b">
                        {{ $selectedUser ? $selectedUser->nama : 'Pilih thread di kiri' }}
                    </h2>
                    <p style="font-size:12px;color:#64748b;margin-top:2px">
                        @if($selectedUser)
                            {{ $selectedUser->no_hp ?? 'No HP belum diisi' }} · ID {{ $selectedUser->id_user }}
                        @else
                            Tidak ada percakapan yang dipilih
                        @endif
                    </p>
                </div>
                @if($selectedUserId)
                    <span style="background:#eff6ff;color:#1d4ed8;border-radius:999px;padding:6px 10px;font-size:11px;font-weight:700">
                        {{ $messages->count() }} pesan
                    </span>
                @endif
            </div>

            <div style="flex:1;padding:18px;overflow:auto;background:linear-gradient(180deg,#f8fafc 0%,#ffffff 100%)">
                @if(!$selectedUserId)
                    <div style="height:100%;min-height:300px;display:flex;align-items:center;justify-content:center;color:#94a3b8;text-align:center">
                        <div>
                            <i class="fas fa-comments" style="font-size:36px;display:block;margin-bottom:10px;opacity:.35"></i>
                            Pilih salah satu user untuk melihat percakapan support.
                        </div>
                    </div>
                @else
                    <div style="display:flex;flex-direction:column;gap:12px">
                        @forelse($messages as $message)
                            @php
                                $isUser = (bool) $message->dari_user;
                            @endphp
                            <div style="display:flex;justify-content:{{ $isUser ? 'flex-start' : 'flex-end' }}">
                                <div style="max-width:min(680px, 82%);background:{{ $isUser ? '#fff' : '#dbeafe' }};border:1px solid {{ $isUser ? '#e2e8f0' : '#bfdbfe' }};border-radius:16px;padding:12px 14px;box-shadow:0 1px 5px rgba(0,0,0,.04)">
                                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px">
                                        <span style="font-size:11px;font-weight:700;color:{{ $isUser ? '#2563eb' : '#1d4ed8' }}">
                                            {{ $isUser ? 'User' : 'Pusat' }}
                                        </span>
                                        <span style="font-size:11px;color:#94a3b8">{{ $message->created_at?->format('d M Y H:i') }}</span>
                                    </div>
                                    <div style="font-size:14px;line-height:1.6;color:#1e293b;white-space:pre-wrap">{{ $message->pesan }}</div>
                                </div>
                            </div>
                        @empty
                            <div style="height:100%;min-height:220px;display:flex;align-items:center;justify-content:center;color:#94a3b8">
                                Belum ada percakapan di thread ini.
                            </div>
                        @endforelse
                    </div>
                @endif
            </div>

            @if($selectedUserId)
                <div style="padding:16px 18px;border-top:1px solid #f1f5f9;background:#fff">
                    <form action="{{ route('admin.support.user.reply', $selectedUserId) }}" method="POST" style="display:flex;flex-direction:column;gap:10px">
                        @csrf
                        <label style="font-size:12px;font-weight:600;color:#374151">Balas ke user</label>
                        <textarea name="pesan" rows="4" required placeholder="Tulis balasan customer service di sini..."
                            style="width:100%;border:1px solid #d1d5db;border-radius:10px;padding:12px 14px;font-size:13px;outline:none;resize:vertical"
                            onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#d1d5db'"></textarea>
                        <div style="display:flex;justify-content:flex-end">
                            <button type="submit"
                                style="background:linear-gradient(135deg,#2563eb,#4f46e5);color:#fff;border:none;border-radius:10px;padding:10px 16px;font-size:13px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:8px">
                                <i class="fas fa-paper-plane"></i> Kirim Balasan
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
