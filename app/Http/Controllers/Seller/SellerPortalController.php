<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\AnalyticsInsight;
use App\Models\ReportNote;
use App\Models\SellerSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SellerPortalController extends Controller
{
    public function login()
    {
        if (Auth::check()) {
            return redirect()->route('seller.dashboard');
        }

        return view('seller.login');
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => 'Email atau password tidak valid.']);
        }

        $request->session()->regenerate();

        return redirect()->route('seller.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('seller.login');
    }

    public function dashboard()
    {
        $data = $this->buildSharedData();

        $stats            = $this->buildDashboardStats($data['products'], $data['orders'], $data['customers']);
        $recentOrders     = $data['orders']->take(5)->map(fn (Order $order) => $this->decorateOrder($order));
        $lowStockProducts = $data['products']
            ->filter(fn (Product $product) => (int) $product->stock < 10)
            ->values()
            ->map(fn (Product $product) => $this->decorateProduct($product));

        return view('seller.dashboard', [
            'pageTitle'        => 'Dashboard',
            'pageDescription'  => 'Selamat datang kembali! Berikut ringkasan bisnis Anda hari ini.',
            'stats'            => $stats,
            'recentOrders'     => $recentOrders,
            'lowStockProducts' => $lowStockProducts,
        ]);
    }

    public function products(Request $request)
    {
        $data = $this->buildSharedData();
        $query = mb_strtolower(trim((string) $request->query('q', '')));
        $categoryId = $request->integer('category_id');

        $products = $data['products']
            ->when($query !== '', fn (Collection $items) => $items->filter(fn ($product) => str_contains(mb_strtolower($product->name), $query)))
            ->when($categoryId, fn (Collection $items) => $items->filter(fn ($product) => (int) $product->category_id === $categoryId))
            ->values()
            ->map(fn (Product $product) => $this->decorateProduct($product));

        return view('seller.products', [
            'pageTitle' => 'Produk',
            'pageDescription' => 'Daftar produk aktif, kategori, harga, dan status stok dari database.',
            'products' => $products,
            'categories' => $data['categories'],
            'queryText' => $request->query('q', ''),
            'selectedCategory' => $categoryId,
        ]);
    }

    public function categories()
    {
        $data = $this->buildSharedData();

        $categoryCards = $this->buildCategoryCards($data['categories'], $data['products']);

        return view('seller.categories', [
            'pageTitle' => 'Kategori',
            'pageDescription' => 'Ringkasan kategori produk dan distribusi stok dari database.',
            'categoryCards' => $categoryCards,
        ]);
    }

    public function orders(Request $request)
    {
        $data = $this->buildSharedData();
        $status = (string) $request->query('status', 'all');
        $query = mb_strtolower(trim((string) $request->query('q', '')));

        $orders = $data['orders']
            ->when($status !== 'all', fn (Collection $items) => $items->filter(fn ($order) => $order->status === $status))
            ->when($query !== '', fn (Collection $items) => $items->filter(function ($order) use ($query) {
                return str_contains(mb_strtolower($order->order_number), $query)
                    || str_contains(mb_strtolower((string) optional($order->user)->name), $query);
            }))
            ->values()
            ->map(fn (Order $order) => $this->decorateOrder($order));

        return view('seller.orders', [
            'pageTitle' => 'Pesanan',
            'pageDescription' => 'Kelola pesanan, status, dan detail transaksi pelanggan.',
            'orders' => $orders,
            'status' => $status,
            'queryText' => $request->query('q', ''),
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function updateOrderStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,paid,processing,ready,shipping,delivered,cancelled'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $previousStatus = $order->status;
        $nextStatus = $validated['status'];

        if ($previousStatus === $nextStatus) {
            return redirect()
                ->route('seller.orders')
                ->with('status', 'Status pesanan tidak berubah.');
        }

        $order->status = $nextStatus;
        $order->save();

        OrderStatusHistory::query()->create([
            'order_id' => $order->id,
            'status' => $nextStatus,
            'note' => $validated['note'] ?? ('Status diubah dari ' . $previousStatus . ' ke ' . $nextStatus . ' melalui portal penjual.'),
            'created_by' => Auth::id(),
        ]);

        return redirect()
            ->route('seller.orders')
            ->with('status', 'Status pesanan berhasil diperbarui.');
    }

    public function destroyOrder(Order $order)
    {
        $orderNumber = $order->order_number;
        $order->delete();

        return redirect()
            ->route('seller.orders')
            ->with('status', 'Pesanan ' . $orderNumber . ' berhasil dihapus.');
    }

    public function customers(Request $request)
    {
        $q = (string) $request->query('q', '');

        $customers = \App\Models\User::query()
            ->with(['orders' => fn ($query) => $query->select('id', 'user_id', 'status', 'total_amount', 'created_at')->latest()])
            ->withCount('orders')
            ->when($q, fn ($query) => $query->where(fn ($inner) =>
                $inner->where('name', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%")
                      ->orWhere('phone', 'like', "%{$q}%")
            ))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        // Stat totals (from all customers, not just paginated)
        $allCustomers = \App\Models\User::withCount('orders')
            ->with(['orders' => fn ($orderQ) => $orderQ->select('id', 'user_id', 'status', 'total_amount')])
            ->get();
        $totalCustomers  = $allCustomers->count();
        $totalOrders     = $allCustomers->sum('orders_count');
        $allRevenue      = $allCustomers->sum(fn ($c) => $c->orders->where('status', '!=', 'cancelled')->sum('total_amount'));
        $avgTransaction  = $totalOrders > 0 ? $allRevenue / $totalOrders : 0;

        return view('seller.customers', [
            'pageTitle'       => 'Pelanggan',
            'pageDescription' => 'Kelola dan pantau data pelanggan Anda',
            'customers'       => $customers,
            'queryText'       => $q,
            'totalCustomers'  => $totalCustomers,
            'totalOrders'     => $totalOrders,
            'avgTransaction'  => 'Rp ' . number_format($avgTransaction, 0, ',', '.'),
        ]);
    }

    public function reviews(Request $request)
    {
        $q = mb_strtolower(trim((string) $request->query('q', '')));

        $reviews = \App\Models\ProductReview::query()
            ->with(['product', 'user', 'order'])
            ->when($q, function ($query) use ($q) {
                $query->whereHas('product', function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%");
                })->orWhereHas('user', function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%");
                })->orWhere('review', 'like', "%{$q}%");
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('seller.reviews', [
            'pageTitle'       => 'Ulasan',
            'pageDescription' => 'Kelola rating dan ulasan dari pelanggan.',
            'reviews'         => $reviews,
            'queryText'       => $q,
        ]);
    }

    public function reports(Request $request)
    {
        $data = $this->buildSharedData();
        $period = (string) $request->query('period', 'month');
        $filteredOrders = $this->filterOrdersByPeriod($data['orders'], $period);

        $reportStats = $this->buildReportStats($filteredOrders);
        $salesByDate = $this->groupSalesByDate($filteredOrders);
        $topProducts = $this->buildTopProducts($filteredOrders)->take(10);
        $reportNotes = ReportNote::query()->latest()->paginate(5)->withQueryString();

        return view('seller.reports', [
            'pageTitle' => 'Laporan',
            'pageDescription' => 'Laporan penjualan harian, mingguan, dan bulanan.',
            'period' => $period,
            'filteredOrders' => $filteredOrders,
            'reportStats' => $reportStats,
            'salesByDate' => $salesByDate,
            'topProducts' => $topProducts,
            'reportNotes' => $reportNotes,
        ]);
    }

    public function analytics()
    {
        $data = $this->buildSharedData();

        $analyticsStats      = $this->buildAnalyticsStats($data['products'], $data['orders'], $data['customers']);
        $categoryPerformance = $this->buildCategoryPerformance($data['orders'], $data['categories']);
        $topProducts         = $this->buildTopProducts($data['orders'])->take(5);
        $dailyRevenue        = $this->buildDailyRevenue($data['orders']);
        $insights            = AnalyticsInsight::query()->latest()->paginate(5)->withQueryString();

        return view('seller.analytics', [
            'pageTitle'           => 'Analytics',
            'pageDescription'     => 'Analisis performa dan insight bisnis Anda.',
            'analyticsStats'      => $analyticsStats,
            'categoryPerformance' => $categoryPerformance,
            'topProducts'         => $topProducts,
            'dailyRevenue'        => $dailyRevenue,
            'insights'            => $insights,
        ]);
    }

    public function settings()
    {
        $storeProfile = SellerSetting::query()->first() ?? SellerSetting::create([
            'shop_name' => config('app.name', 'Ulayya'),
            'shop_description' => 'Kue tradisional Aceh dengan resep turun temurun.',
            'shop_email' => 'info@ulayya.test',
            'shop_phone' => '0812-3456-7890',
            'shop_address' => 'Jl. Raya Banda Aceh No. 123',
            'bank_name' => 'Bank BCA',
            'bank_account_number' => '1234567890',
            'bank_account_name' => config('app.name', 'Ulayya'),
        ]);

        return view('seller.settings', [
            'pageTitle' => 'Pengaturan',
            'pageDescription' => 'Kelola pengaturan toko dan akun Anda.',
            'storeProfile' => $storeProfile,
        ]);
    }

    public function updateSettings(Request $request)
    {
        $settings = SellerSetting::query()->first() ?? SellerSetting::create([
            'shop_name' => config('app.name', 'Ulayya'),
            'shop_description' => 'Kue tradisional Aceh dengan resep turun temurun.',
            'shop_email' => 'info@ulayya.test',
            'shop_phone' => '0812-3456-7890',
            'shop_address' => 'Jl. Raya Banda Aceh No. 123',
            'bank_name' => 'Bank BCA',
            'bank_account_number' => '1234567890',
            'bank_account_name' => config('app.name', 'Ulayya'),
        ]);

        $formType = $request->input('form_type');

        if ($formType === 'security') {
            $validated = $request->validate([
                'current_password' => ['required', 'string'],
                'new_password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);

            if (! Hash::check($validated['current_password'], (string) $request->user()?->password)) {
                return back()
                    ->withErrors(['current_password' => 'Password lama tidak sesuai.'])
                    ->withInput();
            }

            $request->user()?->update([
                'password' => Hash::make($validated['new_password']),
            ]);

            return redirect()
                ->route('seller.settings')
                ->with('status', 'Password berhasil diperbarui.');
        }

        if ($formType === 'notifications') {
            $settings->fill([
                'email_notifications' => $request->boolean('email_notifications'),
                'order_notifications' => $request->boolean('order_notifications'),
                'low_stock_notifications' => $request->boolean('low_stock_notifications'),
            ]);
            $settings->save();

            return back()->with('status', 'Pengaturan notifikasi berhasil disimpan.');
        }

        if ($formType === 'payment_info') {
            $validated = $request->validate([
                'bank_name' => ['nullable', 'string', 'max:255'],
                'bank_account_number' => ['nullable', 'string', 'max:255'],
                'bank_account_name' => ['nullable', 'string', 'max:255'],
            ]);

            $settings->fill([
                'bank_name' => $validated['bank_name'] ?? null,
                'bank_account_number' => $validated['bank_account_number'] ?? null,
                'bank_account_name' => $validated['bank_account_name'] ?? null,
            ]);
            $settings->save();

            return back()->with('status', 'Data pembayaran berhasil disimpan.');
        }

        if ($formType === 'store_info') {
            $validated = $request->validate([
                'shop_name' => ['required', 'string', 'max:255'],
                'shop_description' => ['nullable', 'string'],
                'shop_email' => ['required', 'email', 'max:255'],
                'shop_phone' => ['nullable', 'string', 'max:30'],
                'shop_address' => ['nullable', 'string'],
                'shop_latitude' => ['nullable', 'numeric'],
                'shop_longitude' => ['nullable', 'numeric'],
            ]);

            $settings->fill([
                'shop_name' => $validated['shop_name'],
                'shop_description' => $validated['shop_description'] ?? null,
                'shop_email' => $validated['shop_email'],
                'shop_phone' => $validated['shop_phone'] ?? null,
                'shop_address' => $validated['shop_address'] ?? null,
                'shop_latitude' => $validated['shop_latitude'] ?? null,
                'shop_longitude' => $validated['shop_longitude'] ?? null,
            ]);
            $settings->save();

            return back()->with('status', 'Informasi toko berhasil disimpan.');
        }

        return redirect()
            ->route('seller.settings')
            ->with('status', 'Tidak ada perubahan yang disimpan.');
    }

    private function buildSharedData(): array
    {
        $products = Product::query()
            ->with(['category', 'images'])
            ->withCount('reviews')
            ->latest()
            ->get();

        $orders = Order::query()
            ->with(['items.product.category', 'payment', 'user', 'address'])
            ->latest()
            ->get();

        $categories = Category::query()
            ->withCount('products')
            ->latest()
            ->get();

        $customers = User::query()
            ->with(['orders' => fn ($query) => $query->select('id', 'user_id', 'status', 'total_amount', 'created_at')->latest()])
            ->withCount('orders')
            ->latest()
            ->get();

        return compact('products', 'orders', 'categories', 'customers');
    }

    private function buildDashboardStats(Collection $products, Collection $orders, Collection $customers): array
    {
        $now          = Carbon::now();
        $totalRevenue = (float) $orders->where('status', '!=', 'cancelled')->sum('total_amount');
        $lastMonth    = $orders->filter(
            fn ($o) => optional($o->created_at)->month === $now->copy()->subMonth()->month
                    && optional($o->created_at)->year  === $now->copy()->subMonth()->year
        )->where('status', '!=', 'cancelled')->sum('total_amount');
        $revenueChange = $lastMonth > 0
            ? round(($totalRevenue - (float)$lastMonth) / (float)$lastMonth * 100, 1)
            : null;

        $activeProducts  = $products->where('is_active', true)->count();
        $todayOrders     = $orders->filter(fn ($o) => optional($o->created_at)->isToday())->count();
        $pendingOrders   = $orders->where('status', 'pending')->count();
        $totalCustomers  = $customers->count();
        $newThisMonth    = $customers->filter(
            fn ($c) => optional($c->created_at)->month === $now->month
                    && optional($c->created_at)->year  === $now->year
        )->count();

        return [
            [
                'label'  => 'Total Pendapatan',
                'value'  => $this->formatMoney($totalRevenue),
                'sub'    => $revenueChange !== null
                    ? ($revenueChange >= 0 ? '+' : '') . $revenueChange . '%'
                    : null,
                'sub_up' => ($revenueChange ?? 0) >= 0,
                'icon'   => 'trend',
                'color'  => '#16a34a',
                'bg'     => 'rgba(22,163,74,.10)',
            ],
            [
                'label'  => 'Produk Aktif',
                'value'  => (string) $activeProducts,
                'sub'    => $products->count() . ' total',
                'sub_up' => true,
                'icon'   => 'box',
                'color'  => '#2563eb',
                'bg'     => 'rgba(37,99,235,.10)',
            ],
            [
                'label'  => 'Pesanan Hari Ini',
                'value'  => (string) $todayOrders,
                'sub'    => $pendingOrders . ' pending',
                'sub_up' => $pendingOrders === 0,
                'icon'   => 'cart',
                'color'  => '#0891b2',
                'bg'     => 'rgba(8,145,178,.10)',
            ],
            [
                'label'  => 'Total Pelanggan',
                'value'  => (string) $totalCustomers,
                'sub'    => '+' . $newThisMonth . ' bulan ini',
                'sub_up' => true,
                'icon'   => 'users',
                'color'  => '#7c3aed',
                'bg'     => 'rgba(124,58,237,.10)',
            ],
        ];
    }

    private function buildAnalyticsStats(Collection $products, Collection $orders, Collection $customers): array
    {
        $now           = Carbon::now();
        $thisMonth     = $orders->filter(fn ($o) => optional($o->created_at)->month === $now->month && optional($o->created_at)->year === $now->year);
        $lastMonth     = $orders->filter(fn ($o) => optional($o->created_at)->month === $now->copy()->subMonth()->month && optional($o->created_at)->year === $now->copy()->subMonth()->year);

        $thisRevenue   = (float) $thisMonth->where('status', '!=', 'cancelled')->sum('total_amount');
        $lastRevenue   = (float) $lastMonth->where('status', '!=', 'cancelled')->sum('total_amount');
        $revenueChange = $lastRevenue > 0 ? round(($thisRevenue - $lastRevenue) / $lastRevenue * 100, 1) : null;

        $thisOrders    = $thisMonth->count();
        $lastOrders    = $lastMonth->count();
        $ordersChange  = $lastOrders > 0 ? round(($thisOrders - $lastOrders) / $lastOrders * 100, 1) : null;

        $totalItems    = $orders->where('status', '!=', 'cancelled')->sum(fn ($o) => $o->items->sum('quantity'));
        $lastItems     = $lastMonth->where('status', '!=', 'cancelled')->sum(fn ($o) => $o->items->sum('quantity'));
        $thisItems     = $thisMonth->where('status', '!=', 'cancelled')->sum(fn ($o) => $o->items->sum('quantity'));
        $itemsChange   = $lastItems > 0 ? round(($thisItems - $lastItems) / $lastItems * 100, 1) : null;

        $totalRevAll   = (float) $orders->where('status', '!=', 'cancelled')->sum('total_amount');
        $totalOrdAll   = $orders->where('status', '!=', 'cancelled')->count();
        $avgOrder      = $totalOrdAll > 0 ? $totalRevAll / $totalOrdAll : 0;
        $lastAvg       = $lastOrders > 0 ? $lastRevenue / $lastOrders : 0;
        $avgChange     = $lastAvg > 0 ? round(($avgOrder - $lastAvg) / $lastAvg * 100, 1) : null;

        $pct = fn (?float $v): string => $v === null ? '' : ($v >= 0 ? '+' . $v . '%' : $v . '%');

        return [
            [
                'label'   => 'Total Pendapatan',
                'value'   => $this->formatMoney($totalRevAll),
                'change'  => $pct($revenueChange),
                'up'      => ($revenueChange ?? 0) >= 0,
                'detail'  => 'dari bulan lalu',
                'icon'    => 'dollar',
                'color'   => '#16a34a',
                'bg'      => 'rgba(22,163,74,.10)',
            ],
            [
                'label'   => 'Total Pesanan',
                'value'   => (string) $totalOrdAll,
                'change'  => $pct($ordersChange),
                'up'      => ($ordersChange ?? 0) >= 0,
                'detail'  => 'dari bulan lalu',
                'icon'    => 'cart',
                'color'   => '#0891b2',
                'bg'      => 'rgba(8,145,178,.10)',
            ],
            [
                'label'   => 'Produk Terjual',
                'value'   => (string) $totalItems,
                'change'  => $pct($itemsChange),
                'up'      => ($itemsChange ?? 0) >= 0,
                'detail'  => 'dari bulan lalu',
                'icon'    => 'bag',
                'color'   => '#7c3aed',
                'bg'      => 'rgba(124,58,237,.10)',
            ],
            [
                'label'   => 'Rata-rata Pesanan',
                'value'   => $this->formatMoney($avgOrder),
                'change'  => $pct($avgChange),
                'up'      => ($avgChange ?? 0) >= 0,
                'detail'  => 'dari bulan lalu',
                'icon'    => 'trend',
                'color'   => '#d97706',
                'bg'      => 'rgba(217,119,6,.10)',
            ],
        ];
    }

    private function buildDailyRevenue(Collection $orders): array
    {
        $days   = collect();
        $now    = Carbon::now();

        for ($i = 6; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i);
            $days->push([
                'label'   => $date->translatedFormat('D j'),
                'revenue' => (float) $orders
                    ->where('status', '!=', 'cancelled')
                    ->filter(fn ($o) => optional($o->created_at)->isSameDay($date))
                    ->sum('total_amount'),
            ]);
        }

        return [
            'labels' => $days->pluck('label')->toArray(),
            'data'   => $days->pluck('revenue')->toArray(),
        ];
    }

    private function buildReportStats(Collection $orders): array
    {
        $validOrders = $orders->where('status', '!=', 'cancelled');
        $totalItems = $validOrders->sum(fn ($order) => $order->items->sum('quantity'));

        return [
            ['label' => 'Total Pendapatan', 'value' => $this->formatMoney($validOrders->sum('total_amount'))],
            ['label' => 'Total Pesanan', 'value' => (string) $validOrders->count()],
            ['label' => 'Item Terjual', 'value' => (string) $totalItems],
            ['label' => 'Rata-rata Pesanan', 'value' => $this->formatMoney($validOrders->count() ? $validOrders->sum('total_amount') / $validOrders->count() : 0)],
        ];
    }

    private function buildCustomerRows(Collection $customers): Collection
    {
        return $customers->map(function ($customer) {
            $orders = $customer->orders;

            return [
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone ?: '-',
                'orders' => $customer->orders_count,
                'spent' => $this->formatMoney($orders->where('status', '!=', 'cancelled')->sum('total_amount')),
                'joinDate' => optional($customer->created_at)->translatedFormat('d M Y'),
                'lastOrder' => optional($orders->first()?->created_at)->translatedFormat('d M Y H:i') ?? '-',
            ];
        });
    }

    private function buildCategoryCards(Collection $categories, Collection $products): Collection
    {
        return $categories->map(function ($category) use ($products) {
            $categoryProducts = $products->where('category_id', $category->id);

            return [
                'name' => $category->name,
                'slug' => $category->slug,
                'count' => $category->products_count,
                'active' => $categoryProducts->where('is_active', true)->count(),
                'stock' => $categoryProducts->sum('stock'),
            ];
        });
    }

    private function buildCategoryPerformance(Collection $orders, Collection $categories): Collection
    {
        return $categories->map(function ($category) use ($orders) {
            $totalRevenue = $orders->flatMap(fn ($order) => $order->items)
                ->filter(fn ($item) => $item->product?->category_id === $category->id)
                ->sum('subtotal');

            return [
                'name' => $category->name,
                'revenue' => $this->formatMoney($totalRevenue),
                'raw_revenue' => (float) $totalRevenue,
            ];
        })->sortByDesc('raw_revenue')->values();
    }

    private function buildTopProducts(Collection $orders): Collection
    {
        // Use a plain PHP array to avoid "Indirect modification of overloaded element"
        // error in PHP 8.1+ when using Collection::offsetGet() (returns copy, not reference)
        $sales = [];

        foreach ($orders->where('status', '!=', 'cancelled') as $order) {
            foreach ($order->items as $item) {
                $key = $item->product_id ?: $item->product_name;

                if (! isset($sales[$key])) {
                    $sales[$key] = [
                        'name'     => $item->product_name,
                        'quantity' => 0,
                        'revenue'  => 0,
                    ];
                }

                $sales[$key]['quantity'] += (int) $item->quantity;
                $sales[$key]['revenue']  += (float) $item->subtotal;
            }
        }

        return collect($sales)->map(function ($item, $key) {
            return [
                'key'         => $key,
                'name'        => $item['name'],
                'quantity'    => $item['quantity'],
                'revenue'     => $this->formatMoney($item['revenue']),
                'raw_revenue' => $item['revenue'],
            ];
        })->sortByDesc('raw_revenue')->values();
    }

    private function groupSalesByDate(Collection $orders): Collection
    {
        return $orders
            ->where('status', '!=', 'cancelled')
            ->groupBy(fn ($order) => optional($order->created_at)->translatedFormat('d M Y'))
            ->map(function (Collection $group, $date) {
                return [
                    'date' => $date,
                    'orders' => $group->count(),
                    'revenue' => $this->formatMoney($group->sum('total_amount')),
                    'raw_revenue' => $group->sum('total_amount'),
                ];
            })
            ->sortByDesc('raw_revenue')
            ->values();
    }

    private function filterOrdersByPeriod(Collection $orders, string $period): Collection
    {
        $now = Carbon::now();

        return $orders->filter(function ($order) use ($period, $now) {
            $createdAt = $order->created_at;

            return match ($period) {
                'today' => $createdAt?->isSameDay($now),
                'week' => $createdAt?->greaterThanOrEqualTo($now->copy()->subDays(7)),
                'month' => $createdAt?->greaterThanOrEqualTo($now->copy()->subDays(30)),
                default => true,
            };
        })->values();
    }

    private function statusOptions(): array
    {
        return [
            ['value' => 'all', 'label' => 'Semua Status'],
            ['value' => 'pending', 'label' => 'Menunggu'],
            ['value' => 'paid', 'label' => 'Pembayaran Selesai'],
            ['value' => 'processing', 'label' => 'Diproses'],
            ['value' => 'ready', 'label' => 'Siap'],
            ['value' => 'shipping', 'label' => 'Dikirim'],
            ['value' => 'delivered', 'label' => 'Selesai'],
            ['value' => 'cancelled', 'label' => 'Dibatalkan'],
        ];
    }

    private function decorateOrder(Order $order): Order
    {
        $order->setAttribute('status_label', match ($order->status) {
            'pending'    => 'Menunggu',
            'paid'       => 'Pembayaran Selesai',
            'processing' => 'Diproses',
            'ready'      => 'Siap',
            'shipping'   => 'Dikirim',
            'delivered'  => 'Selesai',
            'cancelled'  => 'Dibatalkan',
            default      => $order->status,
        });

        $order->setAttribute('status_class', match ($order->status) {
            'pending'    => 'pill-warning',
            'paid'       => 'pill-blue',
            'processing' => 'pill-info',
            'ready'      => 'pill-violet',
            'shipping'   => 'pill-info',
            'delivered'  => 'pill-emerald',
            'cancelled'  => 'pill-red',
            default      => 'pill-neutral',
        });

        $order->setAttribute('display_total',     $this->formatMoney($order->total_amount));
        $order->setAttribute('display_date',      optional($order->created_at)->translatedFormat('d M Y · H:i'));
        $order->setAttribute('display_date_only', optional($order->created_at)->translatedFormat('d M Y'));
        $order->setAttribute('display_time',      optional($order->created_at)->format('H:i'));
        $order->setAttribute('display_customer',  optional($order->user)->name ?? 'Pelanggan umum');
        $order->setAttribute('display_phone',     optional($order->user)->phone ?? '-');
        $order->setAttribute('display_payment',   optional($order->payment)->method ?? $order->payment_method);

        return $order;
    }

    private function decorateProduct(Product $product): Product
    {
        $product->setAttribute('display_image', $this->productImage($product));
        $product->setAttribute('display_category', optional($product->category)->name ?? '-');
        $product->setAttribute('display_price', $this->formatMoney($product->price));
        $product->setAttribute('display_status_label', $product->is_active ? 'Aktif' : 'Nonaktif');
        $product->setAttribute('display_status_class', $product->is_active ? 'pill-emerald' : 'pill-red');

        return $product;
    }

    private function productImage(Product $product): string
    {
        if ($product->image_url) {
            return $product->image_url;
        }

        $firstImage = $product->images->first()?->image_url;

        if ($firstImage) {
            return $firstImage;
        }

        return 'https://images.unsplash.com/photo-1718395012128-a6d204223093?auto=format&fit=crop&w=1200&q=80';
    }

    private function formatMoney(float|int|string $amount): string
    {
        return 'Rp ' . number_format((float) $amount, 0, ',', '.');
    }
}