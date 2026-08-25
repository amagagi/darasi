<?php

namespace App\Filament\Resources\Testimonials;

use App\Filament\Resources\Testimonials\Pages\CreateTestimonial;
use App\Filament\Resources\Testimonials\Pages\EditTestimonial;
use App\Filament\Resources\Testimonials\Pages\ListTestimonials;
use App\Models\Testimonial;
use App\Services\ImageOptimizerService;
use BackedEnum;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;

class TestimonialResource extends Resource
{
    protected static ?string $model = Testimonial::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?string $navigationLabel = 'Témoignages';

    protected static ?string $pluralModelLabel = 'Témoignages';

    protected static ?string $modelLabel = 'Témoignage';

    protected static ?string $slug = 'testimonials';

    protected static ?string $recordTitleAttribute = 'author_name';

    /** Compteur des témoignages actuellement affichés sur le site. */
    public static function getNavigationBadge(): ?string
    {
        try {
            $actifs = Testimonial::query()->active()->count();
        } catch (\Throwable) {
            return null;
        }

        return $actifs > 0 ? (string) $actifs : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([

            Section::make('Contenu')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('author_name')
                        ->label('Nom de l\'auteur')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('author_role')
                        ->label('Rôle / statut (optionnel)')
                        ->maxLength(255)
                        ->placeholder('Ex : Apprenante, promotion 2024'),

                    Forms\Components\Textarea::make('content')
                        ->label('Témoignage')
                        ->required()
                        ->rows(4)
                        ->columnSpanFull(),

                    Forms\Components\FileUpload::make('photo_path')
                        ->label('Photo (optionnelle)')
                        ->image()
                        ->directory('testimonials')
                        ->disk('public')
                        ->visibility('public')
                        ->maxSize(2048) // Ko — 2 Mo
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->helperText('Sans photo, les initiales du nom sont affichées à la place.')
                        ->afterStateUpdated(function (?string $state): void {
                            if ($state === null) {
                                return;
                            }

                            $optimizer = app(ImageOptimizerService::class);
                            $optimizer->optimize($state);
                            $optimizer->thumbnail($state);
                        }),

                    Forms\Components\Select::make('rating')
                        ->label('Note (optionnelle)')
                        ->options([
                            1 => '⭐',
                            2 => '⭐⭐',
                            3 => '⭐⭐⭐',
                            4 => '⭐⭐⭐⭐',
                            5 => '⭐⭐⭐⭐⭐',
                        ])
                        ->native(false),
                ]),

            Section::make('Affichage')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('display_order')
                        ->label('Ordre d\'affichage')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->helperText('Aussi réordonnable par glisser-déposer dans la liste.'),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Visible sur le site')
                        ->default(true),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo_path')
                    ->label('Photo')
                    ->disk('public')
                    ->circular(),

                Tables\Columns\TextColumn::make('author_name')
                    ->label('Auteur')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('author_role')
                    ->label('Rôle')
                    ->placeholder('—')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('content')
                    ->label('Témoignage')
                    ->limit(60),

                Tables\Columns\TextColumn::make('rating')
                    ->label('Note')
                    ->formatStateUsing(fn (?int $state) => $state ? str_repeat('⭐', $state) : '—')
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Visible')
                    ->boolean(),

                Tables\Columns\TextColumn::make('display_order')
                    ->label('Ordre')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('display_order')
            ->reorderable('display_order')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Visible')
                    ->trueLabel('Visibles')
                    ->falseLabel('Masqués')
                    ->placeholder('Tous'),
            ])
            ->recordActions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ])
            ->toolbarActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTestimonials::route('/'),
            'create' => CreateTestimonial::route('/create'),
            'edit' => EditTestimonial::route('/{record}/edit'),
        ];
    }
}
