<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Tukang;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Database\QueryException;

function runCheck($label, $fn) {
    echo "-- $label --\n";
    try {
        $res = $fn();
        if (is_object($res) && method_exists($res, 'count')) {
            echo "Result (count): " . $res->count() . "\n";
        } elseif (is_array($res)) {
            echo "Result (array): " . json_encode($res) . "\n";
        } else {
            echo "Result: " . var_export($res, true) . "\n";
        }
    } catch (QueryException $qe) {
        echo "QueryException: " . $qe->getMessage() . "\n";
    } catch (Throwable $t) {
        echo "Exception: " . get_class($t) . " - " . $t->getMessage() . "\n";
    }
}

$from = null; // default
$tukangId = null;

runCheck('User::count', fn() => User::count());
runCheck('Tukang::count', fn() => Tukang::count());
runCheck('Tukang::active', fn() => Tukang::where('status_aktif', 1)->count());
runCheck('Orders base count', function() use ($from, $tukangId) {
    $q = Order::query()->when($from, fn($q) => $q->where('created_at', '>=', $from))->when($tukangId, fn($q) => $q->where('id_tukang', $tukangId));
    return $q->count();
});
runCheck('Reviews base count', function() use ($from, $tukangId) {
    $q = Review::query()->when($from || $tukangId, fn($q) =>
        $q->whereHas('order', fn($oq) =>
            $oq->when($from, fn($x) => $x->where('created_at', '>=', $from))->when($tukangId, fn($x) => $x->where('id_tukang', $tukangId))
        )
    );
    return $q->count();
});
runCheck('Tukang list (id,nama)', fn() => Tukang::orderBy('nama')->get(['id_tukang','nama']));
runCheck('Tukang performance (with orders+reviews)', function() use ($from, $tukangId) {
    return Tukang::with(['orders' => function($q) use ($from, $tukangId) {
        if ($from) $q->where('created_at', '>=', $from);
        if ($tukangId) $q->where('id_tukang', $tukangId);
        $q->with('review');
    }])->when($tukangId, fn($q) => $q->where('id_tukang', $tukangId))->get();
});
runCheck('Top customers', function() use ($from, $tukangId) {
    return User::withCount(['orders as orders_count' => function($q) use ($from, $tukangId) {
        if ($from) $q->where('created_at', '>=', $from);
        if ($tukangId) $q->where('id_tukang', $tukangId);
    }])->orderByDesc('orders_count')->limit(10)->get();
});
runCheck('Recent orders', function() use ($from, $tukangId) {
    return Order::with(['user','tukang','layanan','review'])->when($from, fn($q) => $q->where('created_at', '>=', $from))->when($tukangId, fn($q) => $q->where('id_tukang', $tukangId))->orderByDesc('id_order')->limit(10)->get();
});

echo "Done checks.\n";
