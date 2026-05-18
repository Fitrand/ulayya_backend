<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Models\User;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CustomerResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Pelanggan';
    protected static ?string $modelLabel = 'Pelanggan';
    protected static ?string $pluralModelLabel = 'Pelanggan';
    protected static ?int $navigationSort = 5;

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        // Hanya tampilkan user biasa (bukan admin)
        return parent::getEloquentQuery()->where('is_admin', false);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('No. HP'),
                Tables\Columns\TextColumn::make('orders_count')
                    ->label('Jumlah Pesanan')
                    ->counts('orders')
                    ->badge()
                    ->color('warning'),
                Tables\Columns\TextColumn::make('orders_sum_total_amount')
                    ->label('Total Belanja')
                    ->sum('orders', 'total_amount')
                    ->money('IDR'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Bergabung')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Profil Pelanggan')->schema([
                Infolists\Components\TextEntry::make('name')->label('Nama'),
                Infolists\Components\TextEntry::make('email')->label('Email'),
                Infolists\Components\TextEntry::make('phone')->label('No. HP'),
                Infolists\Components\TextEntry::make('created_at')
                    ->label('Bergabung Sejak')
                    ->dateTime('d M Y'),
            ])->columns(2),

            Infolists\Components\Section::make('Riwayat Pesanan')->schema([
                Infolists\Components\RepeatableEntry::make('orders')
                    ->label('')
                    ->schema([
                        Infolists\Components\TextEntry::make('id')
                            ->label('ID')
                            ->formatStateUsing(fn ($state) => 'UKB' . str_pad($state, 6, '0', STR_PAD_LEFT)),
                        Infolists\Components\TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state) => match($state) {
                                'pending'    => 'warning',
                                'processing' => 'info',
                                'shipped'    => 'primary',
                                'delivered'  => 'success',
                                'cancelled'  => 'danger',
                                default      => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('total_amount')
                            ->label('Total')
                            ->money('IDR'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Tanggal')
                            ->dateTime('d M Y'),
                    ])
                    ->columns(4),
            ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'view'  => Pages\ViewCustomer::route('/{record}'),
        ];
    }
}
