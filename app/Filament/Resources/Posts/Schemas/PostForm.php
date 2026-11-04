<?php

namespace App\Filament\Resources\Posts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Título')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state, string $operation) => $operation === 'create' ? $set('slug', Str::slug($state)) : null)
                    ->columnSpanFull(),
                TextInput::make('slug')
                    ->label('Endereço')
                    ->prefix('/artigos/')
                    ->required()
                    ->alphaDash()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                DateTimePicker::make('published_at')
                    ->label('Publicar em')
                    ->seconds(false)
                    ->helperText('Vazio = rascunho. Data futura = agendado.'),
                Textarea::make('description')
                    ->label('Resumo')
                    ->helperText('Uma ou duas frases. Aparece no Google, nos cards e ao compartilhar.')
                    ->required()
                    ->maxLength(300)
                    ->rows(2)
                    ->columnSpanFull(),
                FileUpload::make('cover')
                    ->label('Capa')
                    ->image()
                    ->imageEditor()
                    ->disk('public')
                    ->directory('artigos')
                    ->visibility('public')
                    ->maxSize(4096)
                    ->required()
                    ->columnSpanFull(),
                MarkdownEditor::make('body')
                    ->label('Texto')
                    ->required()
                    ->fileAttachmentsDisk('public')
                    ->fileAttachmentsDirectory('artigos')
                    ->fileAttachmentsVisibility('public')
                    ->helperText('Links da Amazon, Mercado Livre, Shopee, AliExpress, Magalu e KaBuM viram afiliado sozinhos. Outro programa: [texto](url "afiliado"). Link de afiliado sozinho num parágrafo vira botão. Imagem com título vira legenda: ![alt](url "Legenda").')
                    ->columnSpanFull(),
            ]);
    }
}
