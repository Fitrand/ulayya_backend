<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('proses')
                ->label('Tandai Diproses')
                ->color('info')
                ->icon('heroicon-o-arrow-right-circle')
                ->visible(fn () => $this->record->status === 'pending')
                ->action(fn () => $this->record->update(['status' => 'processing']))
                ->requiresConfirmation(),
            Actions\Action::make('kirim')
                ->label('Tandai Dikirim')
                ->color('primary')
                ->icon('heroicon-o-truck')
                ->visible(fn () => $this->record->status === 'processing')
                ->action(fn () => $this->record->update(['status' => 'shipped']))
                ->requiresConfirmation(),
            Actions\Action::make('selesai')
                ->label('Tandai Selesai')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->visible(fn () => $this->record->status === 'shipped')
                ->action(fn () => $this->record->update(['status' => 'delivered']))
                ->requiresConfirmation(),
        ];
    }
}
