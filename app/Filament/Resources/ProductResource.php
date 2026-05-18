<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Category;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel = 'Produk';
    protected static ?string $modelLabel = 'Produk';
    protected static ?string $pluralModelLabel = 'Produk';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Produk')->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Produk')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Forms\Set $set, ?string $state) =>
                        $set('slug', \Illuminate\Support\Str::slug($state ?? ''))
                    ),
                Forms\Components\TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(Product::class, 'slug', ignoreRecord: true),
                Forms\Components\Select::make('category_id')
                    ->label('Kategori')
                    ->options(Category::pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(4)
                    ->columnSpanFull(),
            ])->columns(2),

            Forms\Components\Section::make('Harga & Stok')->schema([
                Forms\Components\TextInput::make('price')
                    ->label('Harga (Rp)')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->minValue(0),
                Forms\Components\TextInput::make('stock')
                    ->label('Stok')
                    ->required()
                    ->numeric()
                    ->minValue(0),
                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ])->columns(3),

            Forms\Components\Section::make('Foto Produk')->schema([
                Forms\Components\TextInput::make('image_url')
                    ->label('URL Foto Utama')
                    ->url()
                    ->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')
                    ->label('Foto')
                    ->circular()
                    ->defaultImageUrl('https://via.placeholder.com/60'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Produk')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->badge()
                    ->color('warning'),
                Tables\Columns\TextColumn::make('price')
                    ->label('Harga')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock')
                    ->label('Stok')
                    ->sortable()
                    ->color(fn (Product $record) => $record->stock < 10 ? 'danger' : 'success'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Kategori')
                    ->options(Category::pluck('name', 'id')),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
                Tables\Filters\Filter::make('low_stock')
                    ->label('Stok Menipis (< 10)')
                    ->query(fn (Builder $query) => $query->where('stock', '<', 10)),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
