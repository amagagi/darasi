<?php

namespace App\Filament\Resources\ConversationMessages;

use App\Filament\Resources\ConversationMessages\Pages\ListConversationMessages;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Supervision de la messagerie.
 *
 * Volontairement en lecture seule, à une exception près : masquer un message.
 * On ne supprime jamais — masquer conserve la trace pour un éventuel litige,
 * tandis que le message disparaît immédiatement de l'API (les endpoints
 * filtrent sur `est_masque`).
 */
class ConversationMessageResource extends Resource
{
    protected static ?string $model = ConversationMessage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?string $navigationLabel = 'Messagerie';

    protected static ?string $pluralModelLabel = 'Messages';

    protected static ?string $modelLabel = 'Message';

    protected static ?string $slug = 'messagerie';

    /** Aucune création ni édition manuelle depuis le back-office. */
    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Envoyé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('conversation.cours.titre')
                    ->label('Cours')
                    ->limit(30)
                    ->searchable(),

                Tables\Columns\TextColumn::make('conversation.type')
                    ->label('Fil')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === Conversation::TYPE_GROUPE ? 'Groupe' : 'Privé')
                    ->color(fn ($state) => $state === Conversation::TYPE_GROUPE ? 'info' : 'gray'),

                Tables\Columns\TextColumn::make('expediteur.nom')
                    ->label('Expéditeur')
                    ->formatStateUsing(fn ($state, ConversationMessage $record) => trim(
                        ($record->expediteur?->prenom ?? '').' '.($record->expediteur?->nom ?? '')
                    ))
                    ->description(fn (ConversationMessage $record) => $record->expediteur?->role)
                    ->searchable(),

                Tables\Columns\TextColumn::make('contenu')
                    ->label('Message')
                    ->limit(70)
                    ->wrap()
                    ->searchable(),

                Tables\Columns\IconColumn::make('est_masque')
                    ->label('Masqué')
                    ->boolean()
                    ->trueIcon(Heroicon::OutlinedEyeSlash)
                    ->falseIcon(Heroicon::OutlinedEye)
                    ->trueColor('danger')
                    ->falseColor('success'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('est_masque')
                    ->label('Modération')
                    ->trueLabel('Masqués')
                    ->falseLabel('Visibles')
                    ->placeholder('Tous'),

                Tables\Filters\SelectFilter::make('conversation')
                    ->label('Cours')
                    ->relationship('conversation.cours', 'titre')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                \Filament\Actions\Action::make('basculerMasquage')
                    ->label(fn (ConversationMessage $record) => $record->est_masque ? 'Rendre visible' : 'Masquer')
                    ->icon(fn (ConversationMessage $record) => $record->est_masque
                        ? Heroicon::OutlinedEye
                        : Heroicon::OutlinedEyeSlash)
                    ->color(fn (ConversationMessage $record) => $record->est_masque ? 'success' : 'danger')
                    ->requiresConfirmation()
                    ->action(function (ConversationMessage $record): void {
                        $record->est_masque = ! $record->est_masque;
                        $record->masque_par = $record->est_masque ? auth()->id() : null;
                        $record->save();
                    }),
            ])
            ->toolbarActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListConversationMessages::route('/'),
        ];
    }
}
