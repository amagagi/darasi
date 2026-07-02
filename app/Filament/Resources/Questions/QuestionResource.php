<?php

namespace App\Filament\Resources\Questions;

use App\Models\Question;
use App\Models\Test;
use App\Models\TestFinal;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;

class QuestionResource extends Resource
{
    protected static ?string $model = Question::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;

    protected static ?string $navigationLabel = 'Questions';

    protected static ?string $pluralModelLabel = 'Questions';

    protected static ?string $modelLabel = 'Question';

    // =========================
    // FORM
    // =========================
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([

            Forms\Components\Select::make('test_id')
                ->label('Test de module')
                ->options(Test::with('module.cours')->get()->mapWithKeys(fn ($test) => [
                    $test->id => $test->module->cours->titre . ' - ' . $test->module->titre . ' - ' . $test->titre
                ]))
                ->searchable()
                ->preload()
                ->nullable()
                ->reactive()
                ->helperText('Ou laisser vide pour un test final'),

            Forms\Components\Select::make('test_final_id')
                ->label('Test final')
                ->options(TestFinal::with('cours')->get()->mapWithKeys(fn ($test) => [
                    $test->id => $test->cours->titre . ' - ' . $test->titre
                ]))
                ->searchable()
                ->preload()
                ->nullable()
                ->reactive()
                ->helperText('Ou laisser vide pour un test de module'),

            Forms\Components\RichEditor::make('question')
                ->label('Question')
                ->required()
                ->columnSpanFull(),

            Forms\Components\Select::make('type')
                ->label('Type de question')
                ->options([
                    'qcm' => '🔘 QCM (Choix multiple)',
                    'ouverte' => '✏️ Question ouverte',
                ])
                ->required()
                ->reactive()
                ->native(false),

            Forms\Components\TextInput::make('points')
                ->label('Points')
                ->numeric()
                ->required()
                ->default(1)
                ->minValue(0)
                ->step(0.5)
                ->suffix('pts'),

            Forms\Components\TextInput::make('ordre')
                ->label('Ordre')
                ->numeric()
                ->default(0)
                ->integer(),

            // Repeater pour les choix QCM (sans Section)
            Forms\Components\Repeater::make('choix')
                ->label('Choix de réponse')
                ->relationship('choix')
                ->schema([
                    Forms\Components\TextInput::make('texte')
                        ->label('Texte du choix')
                        ->required(),
                    Forms\Components\Toggle::make('est_correct')
                        ->label('Bonne réponse')
                        ->default(false),
                    Forms\Components\TextInput::make('ordre')
                        ->label('Ordre')
                        ->numeric()
                        ->default(0),
                ])
                ->columns(3)
                ->minItems(2)
                ->maxItems(6)
                ->defaultItems(2)
                ->columnSpanFull()
                ->visible(fn ($get) => $get('type') === 'qcm'),
        ]);
    }

    // =========================
    // TABLE
    // =========================
    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('test.module.cours.titre')
                    ->label('Cours')
                    ->formatStateUsing(fn ($record) => 
                        $record->test ? $record->test->module->cours->titre : 
                        ($record->testFinal ? $record->testFinal->cours->titre : '-')
                    )
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('test.titre')
                    ->label('Test module')
                    ->formatStateUsing(fn ($state, $record) => $record->test?->titre ?? '-')
                    ->searchable(),

                Tables\Columns\TextColumn::make('testFinal.titre')
                    ->label('Test final')
                    ->formatStateUsing(fn ($state, $record) => $record->testFinal?->titre ?? '-')
                    ->searchable(),

                Tables\Columns\TextColumn::make('question')
                    ->label('Question')
                    ->html()
                    ->limit(50)
                    ->searchable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->formatStateUsing(fn ($state) => $state === 'qcm' ? '🔘 QCM' : '✏️ Ouverte')
                    ->badge()
                    ->color(fn ($state) => $state === 'qcm' ? 'primary' : 'warning'),

                Tables\Columns\TextColumn::make('points')
                    ->label('Points')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('choix_count')
                    ->label('Nb choix')
                    ->counts('choix')
                    ->sortable()
                    ->alignCenter()
                    ->badge(),

                Tables\Columns\TextColumn::make('ordre')
                    ->label('Ordre')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('ordre')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'qcm' => 'QCM',
                        'ouverte' => 'Question ouverte',
                    ]),
                Tables\Filters\SelectFilter::make('test_id')
                    ->label('Test module')
                    ->relationship('test', 'titre'),
                Tables\Filters\SelectFilter::make('test_final_id')
                    ->label('Test final')
                    ->relationship('testFinal', 'titre'),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\ViewAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Actions\DeleteBulkAction::make(),
            ]);
    }

    // =========================
    // PAGES
    // =========================
    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\Questions\Pages\ListQuestions::route('/'),
            'create' => \App\Filament\Resources\Questions\Pages\CreateQuestion::route('/create'),
            'edit' => \App\Filament\Resources\Questions\Pages\EditQuestion::route('/{record}/edit'),
        ];
    }
}