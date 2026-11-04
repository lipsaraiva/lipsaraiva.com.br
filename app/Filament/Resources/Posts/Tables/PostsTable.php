<?php

namespace App\Filament\Resources\Posts\Tables;

use App\Models\Post;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('cover')->label('')->disk('public'),
                TextColumn::make('title')->label('Título')->searchable()->limit(60),
                TextColumn::make('status')
                    ->state(fn (Post $record) => match (true) {
                        $record->published_at === null => 'Rascunho',
                        $record->published_at->isFuture() => 'Agendado',
                        default => 'Publicado',
                    })
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'Publicado' => 'success',
                        'Agendado' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('published_at')->label('Data')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->recordActions([
                Action::make('ver')
                    ->label('Ver')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Post $record) => route('artigos.show', $record))
                    ->openUrlInNewTab(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
