<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Tukang;
use App\Models\Layanan;
use App\Models\Order;
use App\Models\Review;
use App\Models\Chat;
use App\Models\SupportChat;
use App\Models\Notifikasi;
use App\Models\BroadcastMessage;
use App\Models\BadgeAward;
use App\Models\Pembayaran;
use App\Models\AdminActivity;
use App\Models\FcmToken;
use App\Services\FcmService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    // ─── Auth ────────────────────────────────────────────────────────────────

    private function isAdminAuthed(Request $request): bool
    {
        $token = $request->cookie('admin_token');
        if (!$token) return false;
        $expected = hash_hmac('sha256', 'admin_authenticated', config('app.key'));
        return hash_equals($expected, $token);
    }

    private function adminUsername(): string
    {
        return trim((string) session('admin_username', config('app.admin_user', 'admin')));
    }

    private function logActivity(string $action, ?string $subjectType = null, $subjectId = null, ?string $subjectName = null, array $meta = []): void
    {
        try {
            AdminActivity::create([
                'admin_username' => $this->adminUsername(),
                'action' => $action,
                'subject_type' => $subjectType,
                'subject_id' => $subjectId ? (int) $subjectId : null,
                'subject_name' => $subjectName,
                'meta' => $meta ?: null,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to save admin activity', ['error' => $e->getMessage(), 'action' => $action]);
        }
    }

    public function showLogin(Request $request)
    {
        if ($this->isAdminAuthed($request)) return redirect()->route('admin.dashboard');
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $adminUser = trim((string) config('app.admin_user', 'admin'));
        $adminPass = trim((string) config('app.admin_pass', 'admin123'));
        $givenUser = trim((string) $request->username);
        $givenPass = trim((string) $request->password);

        $ok = (hash_equals($adminUser, $givenUser) && hash_equals($adminPass, $givenPass));

        Log::info('Admin login attempt', [
            'username' => $givenUser,
            'success'  => $ok,
            'ip'       => $request->ip(),
        ]);

        if ($ok) {
            $token = hash_hmac('sha256', 'admin_authenticated', config('app.key'));
            $isSecure = $request->secure() || $request->header('X-Forwarded-Proto') === 'https';
            $cookie = cookie('admin_token', $token, 60 * 24 * 365 * 5, '/', null, $isSecure, true, false, 'lax');
            session(['admin_username' => $givenUser]);
            $this->logActivity('login', 'admin', null, $givenUser, ['ip' => $request->ip()]);
            return redirect()->route('admin.dashboard')
                ->withCookie($cookie);
        }

        return back()->with('error', 'Username atau password salah.')->withInput();
    }

    public function logout(Request $request)
    {
        $this->logActivity('logout', 'admin', null, $this->adminUsername());
        return redirect()->route('admin.login')
            ->withCookie(\Cookie::forget('admin_token'))
            ->with('success', 'Berhasil logout.');
    }

    // ─── Dashboard ───────────────────────────────────────────────────────────

    public function dashboard(Request $request)
    {
        $period   = in_array($request->query('period'), ['today','week','month','all'])
                        ? $request->query('period') : 'month';
        $tukangId = $request->query('tukang_id', '');
        $view     = in_array($request->query('view'), ['summary','workers','customers','orders','performance'])
                        ? $request->query('view') : 'summary';

        $from = match($period) {
            'today' => now()->startOfDay(),
            'week'  => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            default => null,
        };

        $periodLabel = match($period) {
            'today' => 'Hari Ini (' . now()->format('d M Y') . ')',
            'week'  => 'Minggu Ini (' . now()->startOfWeek()->format('d M') . ' – ' . now()->endOfWeek()->format('d M Y') . ')',
            'month' => 'Bulan Ini (' . now()->format('F Y') . ')',
            default => 'Semua Waktu',
        };

        $dashboardError = null;
        $stats = [
            'users' => 0, 'tukang' => 0, 'tukang_aktif' => 0,
            'orders' => 0, 'reviews' => 0, 'orders_total' => 0, 'avg_rating' => 0,
            'pending_verification' => 0, 'badges' => 0,
        ];
        $tukangList = collect();
        $tukangPerformance = collect();
        $topWorkers = collect();
        $topRated = collect();
        $topCustomers = collect();
        $recentOrders = collect();
        $recentActivities = collect();
        $chartLabels = [];
        $chartData = [];

        $safe = function (callable $fn, $default = null) use (&$dashboardError) {
            try {
                return $fn();
            } catch (QueryException $ex) {
                \Log::error('Dashboard DB error', ['message' => $ex->getMessage()]);
                $dashboardError = 'Database belum siap atau migrasi belum berhasil.';
                return $default;
            }
        };

        $ordersQ = Order::query()
            ->when($from,     fn($q) => $q->where('created_at', '>=', $from))
            ->when($tukangId, fn($q) => $q->where('id_tukang',  $tukangId));

        $reviewsQ = Review::query()
            ->when($from || $tukangId, fn($q) =>
                $q->whereHas('order', fn($oq) =>
                    $oq->when($from,     fn($x) => $x->where('created_at', '>=', $from))
                       ->when($tukangId, fn($x) => $x->where('id_tukang',  $tukangId))
                )
            );

        $stats = [
            'users'        => $safe(fn() => User::count(), 0),
            'tukang'       => $safe(fn() => Tukang::count(), 0),
            'tukang_aktif' => $safe(fn() => Tukang::where('status_aktif', 1)->count(), 0),
            'orders'       => $safe(fn() => $ordersQ->count(), 0),
            'reviews'      => $safe(fn() => $reviewsQ->count(), 0),
            'orders_total' => $safe(fn() => Order::count(), 0),
            'avg_rating'   => $safe(fn() => round((float) $reviewsQ->avg('rating'), 1), 0),
            'pending_verification' => $safe(fn() => Tukang::where('status_verifikasi', 'pending')->count(), 0),
            'badges'      => $safe(fn() => BadgeAward::count(), 0),
        ];

        $finance = [
            'gross_revenue' => $safe(fn() => (float) Pembayaran::where('status', 'paid')->sum('jumlah'), 0),
            'pending_revenue' => $safe(fn() => (float) Pembayaran::where('status', 'pending')->sum('jumlah'), 0),
            'failed_revenue' => $safe(fn() => (float) Pembayaran::whereIn('status', ['failed', 'expired'])->sum('jumlah'), 0),
            'deposit_potential' => $safe(fn() => (float) Order::whereNotNull('deposit_fee')->sum('deposit_fee'), 0),
            'deposit_deducted' => $safe(fn() => (float) Order::whereNotNull('deposit_deducted_at')->sum('deposit_fee'), 0),
        ];
        $finance['net_revenue'] = max(0, $finance['gross_revenue'] - $finance['deposit_deducted']);
        $finance['active_revenue'] = max(0, $finance['gross_revenue'] + $finance['pending_revenue']);

        $orderStatusBreakdown = [
            'pending' => $safe(fn() => Order::where('status', 'pending')->count(), 0),
            'confirmed' => $safe(fn() => Order::where('status', 'confirmed')->count(), 0),
            'in_progress' => $safe(fn() => Order::where('status', 'in_progress')->count(), 0),
            'done' => $safe(fn() => Order::where('status', 'done')->count(), 0),
            'rejected' => $safe(fn() => Order::where('status', 'rejected')->count(), 0),
        ];

        $difficultyBreakdown = [
            'easy' => $safe(fn() => Order::where('difficulty_level', 'easy')->count(), 0),
            'medium' => $safe(fn() => Order::where('difficulty_level', 'medium')->count(), 0),
            'hard' => $safe(fn() => Order::where('difficulty_level', 'hard')->count(), 0),
        ];

        $tukangList = $safe(fn() => Tukang::orderBy('nama')->get(['id_tukang', 'nama']), collect());

        $tukangPerformance = $safe(fn() => Tukang::with(['orders' => function ($q) use ($from, $tukangId) {
                    if ($from)     $q->where('created_at', '>=', $from);
                    if ($tukangId) $q->where('id_tukang',  $tukangId);
                    $q->with('review');
                }])
                ->when($tukangId, fn($q) => $q->where('id_tukang', $tukangId))
                ->get()
                ->map(function ($t) {
                    $orders  = $t->orders;
                    $ratings = $orders->map(fn($o) => optional($o->review)->rating)->filter();
                    return [
                        'id'            => $t->id_tukang,
                        'nama'          => $t->nama,
                        'kategori'      => $t->kategori,
                        'status_aktif'  => $t->status_aktif,
                        'tarif'         => $t->tarif ?? 0,
                        'orders_count'  => $orders->count(),
                        'reviews_count' => $ratings->count(),
                        'avg_rating'    => $ratings->count() ? round($ratings->avg(), 1) : 0,
                        'revenue'       => $orders->count() * ($t->tarif ?? 0),
                    ];
                })
                ->sortByDesc('orders_count')
                ->values(), collect());

        $topWorkers = $safe(fn() => $tukangPerformance->take(10), collect());
        $topRated   = $safe(fn() => $tukangPerformance
            ->filter(fn($t) => $t['reviews_count'] > 0)
            ->sortByDesc('avg_rating')->take(5)->values(), collect());

        $topCustomers = $safe(fn() => User::withCount(['orders as orders_count' => function ($q) use ($from, $tukangId) {
                if ($from)     $q->where('created_at', '>=', $from);
                if ($tukangId) $q->where('id_tukang',  $tukangId);
            }])->orderByDesc('orders_count')->limit(10)->get(), collect());

        if ($period === 'today') {
            for ($h = 0; $h < 24; $h += 2) {
                $chartLabels[] = str_pad($h, 2, '0', STR_PAD_LEFT) . ':00';
                $start = now()->startOfDay()->addHours($h);
                $end   = $start->copy()->addHours(2);
                $chartData[] = $safe(fn() => Order::whereBetween('created_at', [$start, $end])
                    ->when($tukangId, fn($q) => $q->where('id_tukang', $tukangId))->count(), 0);
            }
        } else {
            $chartDays = $period === 'week' ? 7 : ($period === 'month' ? 30 : 14);
            for ($i = $chartDays - 1; $i >= 0; $i--) {
                $day           = now()->subDays($i);
                $chartLabels[] = $day->format('d/m');
                $chartData[] = $safe(fn() => Order::whereDate('created_at', $day->toDateString())
                    ->when($tukangId, fn($q) => $q->where('id_tukang', $tukangId))->count(), 0);
            }
        }

        $recentOrders = $safe(fn() => Order::with(['user', 'tukang', 'layanan', 'review'])
            ->when($from,     fn($q) => $q->where('created_at', '>=', $from))
            ->when($tukangId, fn($q) => $q->where('id_tukang',  $tukangId))
            ->orderByDesc('id_order')->limit(10)->get(), collect());

        $recentActivities = $safe(fn() => AdminActivity::orderByDesc('id_admin_activity')->limit(8)->get(), collect());

        return view('admin.dashboard', compact(
            'stats', 'recentOrders', 'tukangPerformance',
            'topRated', 'topWorkers', 'topCustomers',
            'tukangList', 'period', 'tukangId', 'periodLabel',
            'chartLabels', 'chartData', 'view', 'dashboardError', 'recentActivities',
            'finance', 'orderStatusBreakdown', 'difficultyBreakdown'
        ));
    }

    // ─── Users ───────────────────────────────────────────────────────────────

    public function users(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $status = in_array($request->query('status'), ['active', 'banned'], true)
            ? $request->query('status')
            : '';

        $users = $this->usersQuery($q, $status)
            ->withCount('orders')
            ->orderByDesc('id_user')
            ->paginate(15);

        return view('admin.users.index', compact('users', 'q', 'status'));
    }

    private function usersQuery(string $q = '', string $status = '')
    {
        return User::query()
            ->when($q !== '', fn($query) => $query->where(function ($sub) use ($q) {
                $sub->where('nama', 'like', "%{$q}%")
                    ->orWhere('no_hp', 'like', "%{$q}%");
            }))
            ->when($status === 'active', fn($query) => $query->where('is_banned', 0))
            ->when($status === 'banned', fn($query) => $query->where('is_banned', 1));
    }

    public function exportUsers(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $status = in_array($request->query('status'), ['active', 'banned'], true)
            ? $request->query('status')
            : '';
        $filename = 'tomas-users-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($q, $status) {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['ID', 'Nama', 'No HP', 'Status', 'Total Order']);

            $this->usersQuery($q, $status)
                ->withCount('orders')
                ->orderByDesc('id_user')
                ->chunk(200, function ($users) use ($output) {
                    foreach ($users as $user) {
                        fputcsv($output, [
                            $user->id_user,
                            $user->nama,
                            $user->no_hp,
                            $user->is_banned ? 'Banned' : 'Active',
                            $user->orders_count,
                        ]);
                    }
                });

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function search(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        if ($q === '') {
            return redirect()->route('admin.dashboard');
        }

        $users = User::where(function ($query) use ($q) {
                $query->where('nama', 'like', "%{$q}%")
                    ->orWhere('no_hp', 'like', "%{$q}%");
            })
            ->withCount('orders')
            ->orderByDesc('id_user')
            ->limit(8)
            ->get();

        $tukang = Tukang::where(function ($query) use ($q) {
                $query->where('nama', 'like', "%{$q}%")
                    ->orWhere('kategori', 'like', "%{$q}%")
                    ->orWhere('no_hp', 'like', "%{$q}%")
                    ->orWhere('lokasi', 'like', "%{$q}%");
            })
            ->orderByDesc('id_tukang')
            ->limit(8)
            ->get();

        $layanan = Layanan::where('nama_layanan', 'like', "%{$q}%")
            ->orderBy('nama_layanan')
            ->limit(8)
            ->get();

        $orders = Order::with(['user', 'tukang', 'layanan'])
            ->where(function ($query) use ($q) {
                $query->where('id_order', $q)
                    ->orWhereHas('user', fn($user) => $user->where('nama', 'like', "%{$q}%"))
                    ->orWhereHas('tukang', fn($tukang) => $tukang->where('nama', 'like', "%{$q}%"))
                    ->orWhereHas('layanan', fn($layanan) => $layanan->where('nama_layanan', 'like', "%{$q}%"));
            })
            ->orderByDesc('id_order')
            ->limit(8)
            ->get();

        return view('admin.search', compact('q', 'users', 'tukang', 'layanan', 'orders'));
    }

    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        $this->logActivity('delete_user', 'user', $user->id_user, $user->nama);
        $user->delete();
        return back()->with('success', 'User berhasil dihapus.');
    }

    // ─── Tukang ──────────────────────────────────────────────────────────────

    public function tukang(Request $request)
    {
        $q = $request->query('q');
        $status = $request->query('status');

        $list = Tukang::where('status_verifikasi', '!=', 'rejected')
                      ->when($q, fn($query) => $query->where(function ($sub) use ($q) {
                            $sub->where('nama', 'like', "%$q%")
                                ->orWhere('kategori', 'like', "%$q%");
                        }))
                      ->when(in_array($status, ['0', '1'], true), fn($query) => $query->where('status_aktif', (int) $status))
                      ->orderByDesc('id_tukang')->paginate(15);
        $layananList = Layanan::all();
        return view('admin.tukang.index', compact('list', 'q', 'layananList'));
    }

    public function createTukang()
    {
        $layanan = Layanan::all();
        return view('admin.tukang.form', compact('layanan'));
    }

    public function storeTukang(Request $request)
    {
        $request->validate([
            'nama'        => 'required|string|max:100',
            'kategori'    => 'required|string|max:100',
            'lokasi'      => 'nullable|string|max:100',
            'bio'         => 'nullable|string|max:500',
            'tarif'       => 'nullable|numeric|min:0',
            'status_aktif'=> 'required|in:0,1',
            'foto'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('tukang', 'public');
        }

        Tukang::create([
            'nama'         => $request->nama,
            'kategori'     => $request->kategori,
            'lokasi'       => $request->lokasi,
            'bio'          => $request->bio,
            'tarif'        => $request->tarif,
            'status_aktif' => $request->status_aktif,
            'status_verifikasi' => 'verified',
            'foto'         => $fotoPath,
        ]);

        $this->logActivity('create_tukang', 'tukang', null, $request->nama, [
            'status_aktif' => $request->status_aktif,
            'kategori' => $request->kategori,
        ]);

        return redirect()->route('admin.tukang')->with('success', 'Tukang berhasil ditambahkan.');
    }

    public function editTukang($id)
    {
        $tukang  = Tukang::findOrFail($id);
        $layanan = Layanan::all();
        return view('admin.tukang.form', compact('tukang', 'layanan'));
    }

    public function updateTukang(Request $request, $id)
    {
        $tukang = Tukang::findOrFail($id);
        $request->validate([
            'nama'        => 'required|string|max:100',
            'kategori'    => 'required|string|max:100',
            'lokasi'      => 'nullable|string|max:100',
            'bio'         => 'nullable|string|max:500',
            'tarif'       => 'nullable|numeric|min:0',
            'status_aktif'=> 'required|in:0,1',
            'foto'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $data = [
            'nama'         => $request->nama,
            'kategori'     => $request->kategori,
            'lokasi'       => $request->lokasi,
            'bio'          => $request->bio,
            'tarif'        => $request->tarif,
            'status_aktif' => $request->status_aktif,
        ];

        if (blank($tukang->username)) {
            $data['status_verifikasi'] = 'verified';
        }

        if ($request->hasFile('foto')) {
            if ($tukang->foto) \Storage::disk('public')->delete($tukang->foto);
            $data['foto'] = $request->file('foto')->store('tukang', 'public');
        }

        if ($request->boolean('hapus_foto') && $tukang->foto) {
            \Storage::disk('public')->delete($tukang->foto);
            $data['foto'] = null;
        }

        $tukang->update($data);
        $this->logActivity('update_tukang', 'tukang', $tukang->id_tukang, $tukang->nama);

        return redirect()->route('admin.tukang')->with('success', 'Tukang berhasil diperbarui.');
    }

    public function deleteTukang($id)
    {
        $tukang = Tukang::findOrFail($id);
        $this->logActivity('delete_tukang', 'tukang', $tukang->id_tukang, $tukang->nama);
        $tukang->delete();
        return back()->with('success', 'Tukang berhasil dihapus.');
    }

    // ─── Layanan ─────────────────────────────────────────────────────────────

    public function layanan()
    {
        $layanan = Layanan::orderBy('id_layanan')->get();
        return view('admin.layanan.index', compact('layanan'));
    }

    public function storeLayanan(Request $request)
    {
        $request->validate(['nama_layanan' => 'required|string|max:100|unique:layanan,nama_layanan']);
        Layanan::create(['nama_layanan' => $request->nama_layanan]);
        $this->logActivity('create_layanan', 'layanan', null, $request->nama_layanan);
        return back()->with('success', 'Layanan berhasil ditambahkan.');
    }

    public function updateLayanan(Request $request, $id)
    {
        $layanan = Layanan::findOrFail($id);
        $request->validate(['nama_layanan' => 'required|string|max:100|unique:layanan,nama_layanan,' . $id . ',id_layanan']);
        $layanan->update(['nama_layanan' => $request->nama_layanan]);
        $this->logActivity('update_layanan', 'layanan', $layanan->id_layanan, $request->nama_layanan);
        return back()->with('success', 'Layanan berhasil diperbarui.');
    }

    public function deleteLayanan($id)
    {
        $layanan = Layanan::findOrFail($id);
        $this->logActivity('delete_layanan', 'layanan', $layanan->id_layanan, $layanan->nama_layanan);
        $layanan->delete();
        return back()->with('success', 'Layanan berhasil dihapus.');
    }

    // ─── Orders ──────────────────────────────────────────────────────────────

    public function orders(Request $request)
    {
        $q = $request->query('q');
        $orders = Order::with(['user', 'tukang', 'layanan', 'review'])
                       ->when($q, fn($query) => $query->whereHas('user', fn($u) => $u->where('nama', 'like', "%$q%"))
                                                       ->orWhereHas('tukang', fn($t) => $t->where('nama', 'like', "%$q%")))
                       ->orderByDesc('id_order')->paginate(15);
        return view('admin.orders.index', compact('orders', 'q'));
    }

    public function deleteOrder($id)
    {
        $order = Order::findOrFail($id);
        $this->logActivity('delete_order', 'order', $order->id_order, (string) $order->id_order);
        $order->delete();
        return back()->with('success', 'Order berhasil dihapus.');
    }

    // ─── Tukang Verifikasi ────────────────────────────────────────────────────

    public function verifikasiTukang(Request $request)
    {
        $tukang = Tukang::where('status_verifikasi', 'pending')->orderByDesc('id_tukang')->paginate(20);
        return view('admin.tukang.verifikasi', compact('tukang'));
    }

    public function approveTukang($id)
    {
        $tukang = Tukang::findOrFail($id);
        $tukang->update(['status_verifikasi' => 'verified', 'status_aktif' => 1]);
        $this->logActivity('approve_tukang', 'tukang', $tukang->id_tukang, $tukang->nama);

        // ── Notifikasi FCM ke tukang ──
        $tokens = FcmToken::getTokens('tukang', $tukang->id_tukang);
        FcmService::sendToMany($tokens,
            'Akun Diverifikasi ✅',
            'Selamat! Akun Anda telah diverifikasi oleh admin. Anda sekarang bisa menerima pesanan.',
            ['type' => 'verifikasi', 'status' => 'verified']
        );

        return back()->with('success', "Tukang {$tukang->nama} berhasil diverifikasi.");
    }

    public function rejectTukang($id)
    {
        $tukang = Tukang::findOrFail($id);
        $reason = trim((string) request()->input('reason', ''));
        $tukang->update([
            'status_verifikasi' => 'rejected',
            'rejection_reason' => $reason !== '' ? $reason : 'Data verifikasi belum memenuhi syarat.',
            'status_aktif' => 0,
        ]);
        $this->logActivity('reject_tukang', 'tukang', $tukang->id_tukang, $tukang->nama);

        // ── Notifikasi FCM ke tukang ──
        $tokens = FcmToken::getTokens('tukang', $tukang->id_tukang);
        FcmService::sendToMany($tokens,
            'Verifikasi Ditolak',
            'Maaf, verifikasi akun Anda ditolak oleh admin. ' . ($tukang->rejection_reason ?? 'Silakan hubungi admin untuk informasi lebih lanjut.'),
            ['type' => 'verifikasi', 'status' => 'rejected', 'reason' => $tukang->rejection_reason ?? '']
        );

        return back()->with('success', "Tukang {$tukang->nama} ditolak.");
    }

    public function banTukang($id)
    {
        $tukang = Tukang::findOrFail($id);
        $tukang->update(['status_aktif' => 0]);
        $this->logActivity('ban_tukang', 'tukang', $tukang->id_tukang, $tukang->nama);

        // ── Notifikasi FCM ke tukang ──
        $tokens = FcmToken::getTokens('tukang', $tukang->id_tukang);
        FcmService::sendToMany($tokens,
            'Akun Dinonaktifkan',
            'Akun Anda telah dinonaktifkan oleh admin. Silakan hubungi admin untuk informasi lebih lanjut.',
            ['type' => 'ban', 'status' => 'banned']
        );

        return back()->with('success', "Tukang {$tukang->nama} dinonaktifkan.");
    }

    public function unbanTukang($id)
    {
        $tukang = Tukang::findOrFail($id);
        $tukang->update(['status_aktif' => 1]);
        $this->logActivity('unban_tukang', 'tukang', $tukang->id_tukang, $tukang->nama);

        // ── Notifikasi FCM ke tukang ──
        $tokens = FcmToken::getTokens('tukang', $tukang->id_tukang);
        FcmService::sendToMany($tokens,
            'Akun Diaktifkan Kembali ✅',
            'Akun Anda telah diaktifkan kembali oleh admin. Anda sekarang bisa menerima pesanan.',
            ['type' => 'ban', 'status' => 'active']
        );

        return back()->with('success', "Tukang {$tukang->nama} diaktifkan kembali.");
    }

    // ─── User Ban ─────────────────────────────────────────────────────────────

    public function banUser($id)
    {
        $user = User::findOrFail($id);
        $user->update(['is_banned' => 1]);
        $this->logActivity('ban_user', 'user', $user->id_user, $user->nama);

        // ── Notifikasi in-app ke user ──
        Notifikasi::kirim(
            $user->id_user,
            'Akun Dinonaktifkan',
            'Akun Anda telah dinonaktifkan oleh admin.',
            'warning'
        );
        // ── FCM push ke user ──
        $tokens = FcmToken::getTokens('user', $user->id_user);
        FcmService::sendToMany($tokens,
            'Akun Dinonaktifkan',
            'Akun Anda telah dinonaktifkan oleh admin.',
            ['type' => 'ban', 'status' => 'banned']
        );

        return back()->with('success', "User {$user->nama} dinonaktifkan.");
    }

    public function unbanUser($id)
    {
        $user = User::findOrFail($id);
        $user->update(['is_banned' => 0]);
        $this->logActivity('unban_user', 'user', $user->id_user, $user->nama);

        // ── Notifikasi in-app ke user ──
        Notifikasi::kirim(
            $user->id_user,
            'Akun Diaktifkan Kembali',
            'Akun Anda telah diaktifkan kembali oleh admin.',
            'info'
        );
        // ── FCM push ke user ──
        $tokens = FcmToken::getTokens('user', $user->id_user);
        FcmService::sendToMany($tokens,
            'Akun Diaktifkan Kembali ✅',
            'Akun Anda telah diaktifkan kembali oleh admin.',
            ['type' => 'ban', 'status' => 'active']
        );

        return back()->with('success', "User {$user->nama} diaktifkan kembali.");
    }

    // ─── Monitoring Pembayaran ────────────────────────────────────────────────

    public function pembayaran(Request $request)
    {
        $status = $request->query('status', 'all');
        $orders = Order::with(['user', 'tukang'])
            ->where('metode_bayar', '!=', 'Tunai')
            ->when($status !== 'all', fn($q) => $q->where('status_payment', $status))
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.pembayaran', compact('orders', 'status'));
    }

    public function konfirmasiPembayaran($id)
    {
        $order = Order::with(['user', 'tukang'])->findOrFail($id);
        $order->update(['status_payment' => 'confirmed', 'status' => 'done']);
        $this->logActivity('confirm_payment', 'order', $order->id_order, (string) $order->id_order);

        // ── Notifikasi in-app ke user ──
        if ($order->id_user) {
            Notifikasi::kirim(
                $order->id_user,
                'Pembayaran Dikonfirmasi ✅',
                'Pembayaran pesanan Anda telah dikonfirmasi oleh admin. Pekerjaan selesai.',
                'order'
            );
            // ── FCM push ke user ──
            $userTokens = FcmToken::getTokens('user', $order->id_user);
            FcmService::sendToMany($userTokens,
                'Pembayaran Dikonfirmasi ✅',
                'Pembayaran pesanan Anda telah dikonfirmasi. Pekerjaan selesai.',
                ['type' => 'payment_confirmed', 'id_order' => (string) $order->id_order]
            );
        }

        // ── Notifikasi FCM ke tukang ──
        if ($order->id_tukang) {
            $tukangTokens = FcmToken::getTokens('tukang', $order->id_tukang);
            FcmService::sendToMany($tukangTokens,
                'Pembayaran Dikonfirmasi ✅',
                'Admin telah mengkonfirmasi pembayaran untuk pesanan #' . $order->id_order . '.',
                ['type' => 'payment_confirmed', 'id_order' => (string) $order->id_order]
            );
        }

        return back()->with('success', 'Pembayaran dikonfirmasi.');
    }

    // ─── Reviews ─────────────────────────────────────────────────────────────

    public function reviews(Request $request)
    {
        $q = $request->query('q');
        $reviews = Review::with(['order.user', 'order.tukang'])
                         ->when($q, fn($query) => $query->whereHas('order.tukang', fn($t) => $t->where('nama', 'like', "%$q%")))
                         ->orderByDesc('id_review')->paginate(15);
        return view('admin.reviews.index', compact('reviews', 'q'));
    }

    public function deleteReview($id)
    {
        $review = Review::findOrFail($id);
        $this->logActivity('delete_review', 'review', $review->id_review, (string) $review->id_review);
        $review->delete();
        return back()->with('success', 'Review berhasil dihapus.');
    }

    // ─── Broadcast (Pesan ke Tukang) ─────────────────────────────────────────

    public function broadcast(Request $request)
    {
        $broadcasts = BroadcastMessage::orderByDesc('created_at')->paginate(20);
        return view('admin.broadcast.index', compact('broadcasts'));
    }

    public function storeBroadcast(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:200',
            'isi'   => 'required|string',
            'tipe'  => 'required|in:info,warning,promo',
        ]);

        BroadcastMessage::create($request->only('judul', 'isi', 'tipe'));
        $this->logActivity('create_broadcast', 'broadcast', null, $request->judul, ['tipe' => $request->tipe]);

        // ── FCM push ke semua tukang ──
        $allTukangTokens = FcmToken::where('user_type', 'tukang')->pluck('fcm_token')->toArray();
        FcmService::sendToMany(
            $allTukangTokens,
            $request->judul,
            \Str::limit($request->isi, 100),
            ['type' => 'broadcast', 'tipe' => $request->tipe]
        );

        return back()->with('success', 'Pesan berhasil dikirim ke semua tukang.');
    }

    public function deleteBroadcast($id)
    {
        $broadcast = BroadcastMessage::findOrFail($id);
        $this->logActivity('delete_broadcast', 'broadcast', $broadcast->id_broadcast, $broadcast->judul);
        $broadcast->delete();
        return back()->with('success', 'Pesan dihapus.');
    }

    // ─── Support Center (Pesan dari Tukang) ─────────────────────────────────

    public function support(Request $request)
    {
        $selectedTukangId = $request->query('tukang_id');

        $threads = SupportChat::with('tukang')
            ->orderByDesc('id_support_chat')
            ->get()
            ->groupBy('id_tukang')
            ->map(function ($messages) {
                $last = $messages->first();
                return [
                    'id_tukang'      => $last->id_tukang,
                    'tukang'         => $last->tukang,
                    'kategori'       => $last->kategori,
                    'last_message'   => $last->pesan,
                    'last_time'      => $last->created_at,
                    'total_messages' => $messages->count(),
                    'from_tukang'    => $messages->where('dari_tukang', true)->count(),
                    'from_admin'     => $messages->where('dari_tukang', false)->count(),
                ];
            })
            ->sortByDesc(fn ($thread) => optional($thread['last_time'])->getTimestamp() ?? 0)
            ->values();

        if (!$selectedTukangId && $threads->isNotEmpty()) {
            $selectedTukangId = (string) $threads->first()['id_tukang'];
        }

        $selectedTukang = $selectedTukangId ? Tukang::find($selectedTukangId) : null;
        $messages = $selectedTukangId
            ? SupportChat::where('id_tukang', $selectedTukangId)
                ->orderBy('id_support_chat')
                ->get()
            : collect();

        return view('admin.support.index', compact(
            'threads',
            'selectedTukang',
            'selectedTukangId',
            'messages'
        ));
    }

    public function replySupport(Request $request, $id_tukang)
    {
        $request->validate([
            'pesan' => 'required|string|max:1000',
        ]);

        $tukang = Tukang::findOrFail($id_tukang);
        $kategori = SupportChat::where('id_tukang', $id_tukang)
            ->orderByDesc('id_support_chat')
            ->value('kategori') ?? 'bantuan';

        SupportChat::create([
            'id_tukang'   => $tukang->id_tukang,
            'kategori'    => $kategori,
            'pesan'       => $request->pesan,
            'dari_tukang' => false,
        ]);

        $tokens = FcmToken::getTokens('tukang', $tukang->id_tukang);
        FcmService::sendToMany(
            $tokens,
            'Balasan dari Pusat',
            \Str::limit($request->pesan, 80),
            ['type' => 'support_reply', 'id_tukang' => (string) $tukang->id_tukang]
        );

        return back()->with('success', 'Balasan berhasil dikirim ke tukang.');
    }
}
