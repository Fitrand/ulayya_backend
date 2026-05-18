<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Pesanan';
    protected static ?string $modelLabel = 'Pesanan';
    protected static ?string $pluralModelLabel = 'Pesanan';
    protected static ?int $navigationSort = 4;

    // Badge jumlah pesanan pending di nav
    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::where('status', 'pending')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'danger';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('status')
                ->label('Status Pesanan')
                ->options([
                    'pending'    => 'Menunggu',
                    'processing' => 'Diproses',
                    'shipped'    => 'Dikirim',
                    'delivered'  => 'Selesai',
                    'cancelled'  => 'Dibatalkan',
                ])
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID Pesanan')
                    ->formatStateUsing(fn ($state) => 'UKB' . str_pad($state, 6, '0', STR_PAD_LEFT))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pelanggan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Pembayaran')
                    ->badge()
                    ->color('info'),
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
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending'    => 'Menunggu',
                        'processing' => 'Diproses',
                        'shipped'    => 'Dikirim',
                        'delivered'  => 'Selesai',
                        'cancelled'  => 'Dibatalkan',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('proses')
                    ->label('Proses')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->color('info')
                    ->visible(fn (Order $record) => $record->status === 'pending')
                    ->action(fn (Order $record) => $record->update(['status' => 'processing']))
                    ->requiresConfirmation(),
                Tables\Actions\Action::make('kirim')
                    ->label('Kirim')
                    ->icon('heroicon-o-truck')
                    ->color('primary')
                    ->visible(fn (Order $record) => $record->status === 'processing')
                    ->action(fn (Order $record) => $record->update(['status' => 'shipped']))
                    ->requiresConfirmation(),
                Tables\Actions\Action::make('selesai')
                    ->label('Selesai')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Order $record) => $record->status === 'shipped')
                    ->action(fn (Order $record) => $record->update(['status' => 'delivered']))
                    ->requiresConfirmation(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Detail Pesanan')->schema([
                Infolists\Components\TextEntry::make('id')
                    ->label('ID Pesanan')
                    ->formatStateUsing(fn ($state) => 'UKB' . str_pad($state, 6, '0', STR_PAD_LEFT)),
                Infolists\Components\TextEntry::make('user.name')->label('Pelanggan'),
                Infolists\Components\TextEntry::make('user.phone')->label('No. HP'),
                Infolists\Components\TextEntry::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending'    => 'warning',
                        'processing' => 'info',
                        'shipped'    => 'primary',
                        'delivered'  => 'success',
                        'cancelled'  => 'danger',
                        default      => 'gray',
                    }),
                Infolists\Components\TextEntry::make('shipping_address')->label('Alamat Pengiriman'),
                Infolists\Components\TextEntry::make('payment_method')->label('Metode Bayar'),
                Infolists\Components\TextEntry::make('total_amount')
                    ->label('Total Pembayaran')
                    ->money('IDR'),
                Infolists\Components\TextEntry::make('created_at')
                    ->label('Tanggal Pesan')
                    ->dateTime('d M Y, H:i'),
            ])->columns(2),

            Infolists\Components\Section::make('Item Pesanan')->schema([
                Infolists\Components\RepeatableEntry::make('items')
                    ->label('')
                    ->schema([
                        Infolists\Components\TextEntry::make('product.name')->label('Produk'),
                        Infolists\Components\TextEntry::make('quantity')->label('Qty'),
                        Infolists\Components\TextEntry::make('price')->label('Harga')->money('IDR'),
                    ])
                    ->columns(3),
            ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view'  => Pages\ViewOrder::route('/{record}'),
        ];
    }
}
