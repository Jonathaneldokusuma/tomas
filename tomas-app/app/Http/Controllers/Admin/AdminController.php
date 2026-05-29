<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Tukang;
use App\Models\Layanan;
use App\Models\Order;
use App\Models\Review;
use App\Models\Chat;
use App\Models\Notifikasi;
use App\Models\BroadcastMessage;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    // ─── Auth ────────────────────────────────────────────────────────────────

    public function showLogin()
    {
        if (session('is_admin')) return redirect()->route('admin.dashboard');
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // normalize/trim inputs and configured credentials to avoid accidental whitespace mismatches
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
            // regenerate session id to prevent fixation and mark user as admin
            $request->session()->regenerate();
            session(['is_admin' => true, 'admin_username' => $adminUser]);
            return redirect()->route('admin.dashboard');
        }

        return back()->with('error', 'Username atau password salah.')->withInput();
    }

    public function logout()
    {
        session()->forget(['is_admin', 'admin_username']);
        return redirect()->route('admin.login')->with('success', 'Berhasil logout.');
    }

    // ─── Dashboard ───────────────────────────────────────────────────────────

    public function dashboard(Request $request)
    {
        // ── Filters ──────────────────────────────────────────────────────────
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
            'users' => 0,
            'tukang' => 0,
            'tukang_aktif' => 0,
            'orders' => 0,
            'reviews' => 0,
            'orders_total' => 0,
            'avg_rating' => 0,
        ];
        $tukangList = collect();
        $tukangPerformance = collect();
        $topWorkers = collect();
        $topRated = collect();
        $topCustomers = collect();
        $recentOrders = collect();
        $chartLabels = [];
        $chartData = [];

        $safe = function (callable $fn, $default = null) use (&$dashboardError) {
            try {
                return $fn();
            } catch (QueryException $ex) {
                \Log::error('Dashboard DB error', ['message' => $ex->getMessage()]);
                $dashboardError = 'Database belum siap atau migrasi belum berhasil. Login admin sudah aktif, tetapi data dashboard belum bisa dimuat.';
                return $default;
            }
        };

        // build base queries (these are lightweight and only create query builders)
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

        // Stats
        $stats = [
            'users'        => $safe(fn() => User::count(), 0),
            'tukang'       => $safe(fn() => Tukang::count(), 0),
            'tukang_aktif' => $safe(fn() => Tukang::where('status_aktif', 1)->count(), 0),
            'orders'       => $safe(fn() => $ordersQ->count(), 0),
            'reviews'      => $safe(fn() => $reviewsQ->count(), 0),
            'orders_total' => $safe(fn() => Order::count(), 0),
            'avg_rating'   => $safe(fn() => round((float) $reviewsQ->avg('rating'), 1), 0),
        ];

        $tukangList = $safe(fn() => Tukang::orderBy('nama')->get(['id_tukang', 'nama']), collect());

        // Tukang Performance (safe-wrapped)
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

        $topRated = $safe(fn() => $tukangPerformance
            ->filter(fn($t) => $t['reviews_count'] > 0)
            ->sortByDesc('avg_rating')
            ->take(5)
            ->values(), collect());

        // Top Customers
        $topCustomers = $safe(fn() => User::withCount(['orders as orders_count' => function ($q) use ($from, $tukangId) {
                if ($from)     $q->where('created_at', '>=', $from);
                if ($tukangId) $q->where('id_tukang',  $tukangId);
            }])
            ->orderByDesc('orders_count')
            ->limit(10)
            ->get(), collect());

        // Chart
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

        // Recent Orders
        $recentOrders = $safe(fn() => Order::with(['user', 'tukang', 'layanan', 'review'])
            ->when($from,     fn($q) => $q->where('created_at', '>=', $from))
            ->when($tukangId, fn($q) => $q->where('id_tukang',  $tukangId))
            ->orderByDesc('id_order')
            ->limit(10)
            ->get(), collect());

        return view('admin.dashboard', compact(
            'stats', 'recentOrders', 'tukangPerformance',
            'topRated', 'topWorkers', 'topCustomers',
            'tukangList', 'period', 'tukangId', 'periodLabel',
            'chartLabels', 'chartData', 'view', 'dashboardError'
        ));
    }

    // ─── Users ───────────────────────────────────────────────────────────────

    public function users(Request $request)
    {
        $q = $request->query('q');
        $users = User::when($q, fn($query) => $query->where('nama', 'like', "%$q%")
                                                     ->orWhere('no_hp', 'like', "%$q%"))
                     ->orderByDesc('id_user')->paginate(15);
        return view('admin.users.index', compact('users', 'q'));
    }

    public function deleteUser($id)
    {
        User::findOrFail($id)->delete();
        return back()->with('success', 'User berhasil dihapus.');
    }

    // ─── Tukang ──────────────────────────────────────────────────────────────

    public function tukang(Request $request)
    {
        $q = $request->query('q');
        $list = Tukang::when($q, fn($query) => $query->where('nama', 'like', "%$q%")
                                                      ->orWhere('kategori', 'like', "%$q%"))
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
            'foto'         => $fotoPath,
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

        if ($request->hasFile('foto')) {
            if ($tukang->foto) {
                \Storage::disk('public')->delete($tukang->foto);
            }
            $data['foto'] = $request->file('foto')->store('tukang', 'public');
        }

        if ($request->boolean('hapus_foto') && $tukang->foto) {
            \Storage::disk('public')->delete($tukang->foto);
            $data['foto'] = null;
        }

        $tukang->update($data);

        return redirect()->route('admin.tukang')->with('success', 'Tukang berhasil diperbarui.');
    }

    public function deleteTukang($id)
    {
        Tukang::findOrFail($id)->delete();
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
        return back()->with('success', 'Layanan berhasil ditambahkan.');
    }

    public function updateLayanan(Request $request, $id)
    {
        $layanan = Layanan::findOrFail($id);
        $request->validate(['nama_layanan' => 'required|string|max:100|unique:layanan,nama_layanan,' . $id . ',id_layanan']);
        $layanan->update(['nama_layanan' => $request->nama_layanan]);
        return back()->with('success', 'Layanan berhasil diperbarui.');
    }

    public function deleteLayanan($id)
    {
        Layanan::findOrFail($id)->delete();
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
        Order::findOrFail($id)->delete();
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
        return back()->with('success', "Tukang {$tukang->nama} berhasil diverifikasi.");
    }

    public function rejectTukang($id)
    {
        $tukang = Tukang::findOrFail($id);
        $tukang->update(['status_verifikasi' => 'rejected']);
        return back()->with('success', "Tukang {$tukang->nama} ditolak.");
    }

    public function banTukang($id)
    {
        $tukang = Tukang::findOrFail($id);
        $tukang->update(['status_aktif' => 0]);
        return back()->with('success', "Tukang {$tukang->nama} dinonaktifkan.");
    }

    public function unbanTukang($id)
    {
        $tukang = Tukang::findOrFail($id);
        $tukang->update(['status_aktif' => 1]);
        return back()->with('success', "Tukang {$tukang->nama} diaktifkan kembali.");
    }

    // ─── User Ban ─────────────────────────────────────────────────────────────

    public function banUser($id)
    {
        $user = User::findOrFail($id);
        $user->update(['is_banned' => 1]);
        return back()->with('success', "User {$user->nama} dinonaktifkan.");
    }

    public function unbanUser($id)
    {
        $user = User::findOrFail($id);
        $user->update(['is_banned' => 0]);
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
        $order = Order::findOrFail($id);
        $order->update(['status_payment' => 'confirmed', 'status' => 'done']);
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
        Review::findOrFail($id)->delete();
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
        return back()->with('success', 'Pesan berhasil dikirim ke semua tukang.');
    }

    public function deleteBroadcast($id)
    {
        BroadcastMessage::findOrFail($id)->delete();
        return back()->with('success', 'Pesan dihapus.');
    }
}
