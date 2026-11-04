<?php

namespace App\Filament\Resources\ContactMessages;

use App\Filament\Resources\ContactMessages\Pages\ListContactMessages;
use App\Models\ContactMessage;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

// Mensagens do formulário de contato do site (somente leitura).
class ContactMessageResource extends Resource
{
    protected static ?string $model = ContactMessage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInbox;

    protected static ?string $slug = 'mensagens';

    protected static ?string $modelLabel = 'mensagem';

    protected static ?string $pluralModelLabel = 'mensagens';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        return ContactMessage::whereNull('read_at')->count() ?: null;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('name')->label('Nome'),
            TextEntry::make('created_at')->label('Recebida em')->dateTime('d/m/Y H:i'),
            TextEntry::make('email')->label('E-mail')->copyable(),
            TextEntry::make('phone')->label('Telefone')->placeholder('—'),
            TextEntry::make('message')->label('Mensagem')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                IconColumn::make('read_at')->label('')->boolean()->state(fn (ContactMessage $record) => $record->read_at === null)
                    ->trueIcon(Heroicon::Envelope)->falseIcon(Heroicon::EnvelopeOpen)->trueColor('primary')->falseColor('gray'),
                TextColumn::make('name')->label('Nome')->searchable(),
                TextColumn::make('email')->label('E-mail')->searchable(),
                TextColumn::make('message')->label('Mensagem')->limit(70),
                TextColumn::make('created_at')->label('Recebida em')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('responder')
                    ->icon(Heroicon::OutlinedArrowUturnLeft)
                    ->url(fn (ContactMessage $record) => 'mailto:'.$record->email.'?subject='.rawurlencode('Re: contato pelo site')),
                Action::make('lida')
                    ->label('Marcar como lida')
                    ->icon(Heroicon::OutlinedCheck)
                    ->visible(fn (ContactMessage $record) => $record->read_at === null)
                    ->action(fn (ContactMessage $record) => $record->update(['read_at' => now()])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContactMessages::route('/'),
        ];
    }
}
