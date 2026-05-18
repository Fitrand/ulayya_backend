<?php

namespace App\Filament\Pages;

use App\Models\SellerSetting;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;

class ManageStoreSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Pengaturan Toko';
    protected static ?string $title = 'Pengaturan Toko & Banner';
    protected static ?string $navigationGroup = 'Sistem';

    protected static string $view = 'filament.pages.manage-store-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $setting = SellerSetting::first();
        if ($setting) {
            $this->form->fill($setting->toArray());
        } else {
            $this->form->fill();
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Dasar Toko')
                    ->schema([
                        TextInput::make('shop_name')
                            ->label('Nama Toko')
                            ->required(),
                        TextInput::make('shop_email')
                            ->label('Email Toko')
                            ->email()
                            ->required(),
                        TextInput::make('shop_phone')
                            ->label('Nomor Telepon / WA')
                            ->required(),
                        Textarea::make('shop_address')
                            ->label('Alamat Lengkap')
                            ->required()
                            ->columnSpanFull(),
                        Textarea::make('shop_description')
                            ->label('Deskripsi Toko')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Banner Promosi Aplikasi')
                    ->description('Banner ini akan ditampilkan di bagian paling atas halaman utama aplikasi mobile.')
                    ->schema([
                        TextInput::make('promo_banner_text')
                            ->label('Teks Banner Promosi')
                            ->helperText('Contoh: Diskon 20% khusus hari ini!')
                            ->columnSpanFull(),
                        FileUpload::make('promo_banner_image')
                            ->label('Gambar Banner')
                            ->image()
                            ->directory('banners')
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $setting = SellerSetting::first();
        if ($setting) {
            $setting->update($data);
        } else {
            SellerSetting::create($data);
        }

        Notification::make()
            ->success()
            ->title('Berhasil disimpan')
            ->body('Pengaturan toko dan banner berhasil diperbarui.')
            ->send();
    }
}
