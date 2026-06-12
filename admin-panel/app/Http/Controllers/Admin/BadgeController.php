<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BadgeAward;
use App\Models\AdminActivity;
use App\Models\FcmToken;
use App\Models\Notifikasi;
use App\Models\Tukang;
use App\Models\User;
use App\Services\FcmService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BadgeController extends Controller
{
    private function logActivity(string $action, ?string $subjectType = null, $subjectId = null, ?string $subjectName = null, array $meta = []): void
    {
        try {
            AdminActivity::create([
                'admin_username' => trim((string) session('admin_username', config('app.admin_user', 'admin'))),
                'action' => $action,
                'subject_type' => $subjectType,
                'subject_id' => $subjectId ? (int) $subjectId : null,
                'subject_name' => $subjectName,
                'meta' => $meta ?: null,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // audit trail should never block badge operations
        }
    }

    public function index(Request $request)
    {
        $badges = BadgeAward::orderByDesc('id_badge_award')->get();
        $users = User::orderBy('nama')->get(['id_user', 'nama', 'no_hp']);
        $tukangs = Tukang::orderBy('nama')->get(['id_tukang', 'nama', 'username']);

        return view('admin.badges.index', compact('badges', 'users', 'tukangs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'target_type' => 'required|in:user,tukang',
            'target_id'   => 'required|integer',
            'nama'        => 'required|string|max:120',
            'deskripsi'   => 'nullable|string|max:500',
            'warna'       => 'nullable|string|max:20',
            'gambar'      => 'required|image|mimes:jpg,jpeg,png,webp|max:3072',
        ]);

        $targetType = $request->target_type;
        $targetId = (int) $request->target_id;

        if ($targetType === 'user' && !User::where('id_user', $targetId)->exists()) {
            return back()->with('error', 'User target tidak ditemukan.');
        }
        if ($targetType === 'tukang' && !Tukang::where('id_tukang', $targetId)->exists()) {
            return back()->with('error', 'Tukang target tidak ditemukan.');
        }

        $path = $request->file('gambar')->store('badges', 'public');

        $badge = BadgeAward::create([
            'target_type'       => $targetType,
            'target_id'         => $targetId,
            'nama'              => $request->nama,
            'deskripsi'         => $request->deskripsi,
            'warna'             => $request->warna,
            'gambar'            => $path,
            'created_by_admin'  => session('admin_username', 'admin'),
        ]);
        $this->logActivity('create_badge', $targetType, $targetId, $badge->nama, [
            'target_type' => $targetType,
        ]);

        if ($targetType === 'user') {
            Notifikasi::kirim(
                $targetId,
                'Badge baru didapat!',
                'Kamu mendapatkan badge "' . $badge->nama . '"',
                'badge'
            );

            $tokens = FcmToken::getTokens('user', $targetId);
            FcmService::sendToMany(
                $tokens,
                'Badge baru didapat!',
                Str::limit('Kamu mendapatkan badge "' . $badge->nama . '"', 80),
                ['type' => 'badge', 'id_user' => (string) $targetId]
            );
        } else {
            $tokens = FcmToken::getTokens('tukang', $targetId);
            FcmService::sendToMany(
                $tokens,
                'Badge baru didapat!',
                Str::limit('Kamu mendapatkan badge "' . $badge->nama . '"', 80),
                ['type' => 'badge', 'id_tukang' => (string) $targetId]
            );
        }

        return back()->with('success', 'Badge berhasil ditambahkan.');
    }

    public function destroy($id)
    {
        $badge = BadgeAward::findOrFail($id);
        $this->logActivity('delete_badge', $badge->target_type, $badge->target_id, $badge->nama);
        if ($badge->gambar) {
            Storage::disk('public')->delete($badge->gambar);
        }
        $badge->delete();

        return back()->with('success', 'Badge dihapus.');
    }
}
