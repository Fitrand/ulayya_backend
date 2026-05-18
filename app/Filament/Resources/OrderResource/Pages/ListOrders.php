<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    public function getTabs(): array
    {
        return [
            'all'        => Tab::make('Semua'),
            'pending'    => Tab::make('Menunggu')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending'))
                ->badge(fn () => \App\Models\Order::where('status', 'pending')->count()),
            'processing' => Tab::make('Diproses')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'processing')),
            'shipped'    => Tab::make('Dikirim')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'shipped')),
            'delivered'  => Tab::make('Selesai')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'delivered')),
            'cancelled'  => Tab::make('Dibatalkan')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'cancelled')),
        ];
    }
}
