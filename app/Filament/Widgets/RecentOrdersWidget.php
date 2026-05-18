<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentOrdersWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';
    protected static ?string $heading = 'Pesanan Terbaru';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::with('user')->latest()->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->formatStateUsing(fn ($state) => 'UKB' . str_pad($state, 6, '0', STR_PAD_LEFT)),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pelanggan'),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending'    => 'warning',
                        'processing' => 'info',
                        'shipped'    => 'primary',
                        'delivered'  => 'success',
                        'cancelled'  => 'danger',
                        default      => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending'    => 'Menunggu',
                        'processing' => 'Diproses',
                        'shipped'    => 'Dikirim',
                        'delivered'  => 'Selesai',
                        'cancelled'  => 'Dibatalkan',
                        default      => $state,
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y, H:i'),
            ])
            ->actions([
                Tables\Actions\Action::make('lihat')
                    ->label('Detail')
                    ->url(fn (Order $record) => route('filament.admin.resources.orders.view', $record))
                    ->icon('heroicon-o-eye')
                    ->color('warning'),
            ]);
    }
}
