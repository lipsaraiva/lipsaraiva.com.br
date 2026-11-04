<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Rascunhos também abrem: o site mostra posts não publicados para quem está logado.
            Action::make('ver')
                ->label('Ver no site')
                ->color('gray')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url(fn () => route('artigos.show', $this->record))
                ->openUrlInNewTab(),
            DeleteAction::make(),
        ];
    }
}
