<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $todayRevenue     = Order::whereDate('created_at', today())->where('status', '!=', 'cancelled')->sum('total_amount');
        $totalRevenue     = Order::where('status', '!=', 'cancelled')->sum('total_amount');
        $pendingOrders    = Order::where('status', 'pending')->count();
        $todayOrders      = Order::whereDate('created_at', today())->count();
        $activeProducts   = Product::where('is_active', true)->count();
        $lowStockProducts = Product::where('is_active', true)->where('stock', '<', 10)->count();
        $totalCustomers   = User::where('is_admin', false)->count();
        $newThisMonth     = User::where('is_admin', false)->whereMonth('created_at', now()->month)->count();

        return [
            Stat::make('Total Pendapatan', 'Rp ' . number_format($totalRevenue, 0, ',', '.'))
                ->description('Hari ini: Rp ' . number_format($todayRevenue, 0, ',', '.'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Pesanan Pending', $pendingOrders)
                ->description("Hari ini: {$todayOrders} pesanan")
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color($pendingOrders > 0 ? 'warning' : 'success'),

            Stat::make('Produk Aktif', $activeProducts)
                ->description($lowStockProducts > 0 ? "{$lowStockProducts} stok menipis!" : 'Semua stok aman')
                ->descriptionIcon($lowStockProducts > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($lowStockProducts > 0 ? 'danger' : 'info'),

            Stat::make('Total Pelanggan', $totalCustomers)
                ->description("+{$newThisMonth} bulan ini")
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
        ];
    }
}
