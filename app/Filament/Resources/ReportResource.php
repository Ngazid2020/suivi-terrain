<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReportResource\Pages;
use App\Models\Report;
use App\Forms\Components\SignaturePad;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TextArea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class ReportResource extends Resource
{
    protected static ?string $model = Report::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = 'Rapports de Terrain';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Section 1 : Informations Générales
                Section::make('Informations du Rapport')
                    ->description('Remplissez les détails de l\'activité sur le terrain.')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        TextInput::make('title')
                            ->label('Titre / Objet du rapport')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ex: Inspection mensuelle - Site A'),

                        Select::make('location_name')
                            ->label('Lieu d\'intervention')
                            ->options([
                                'site_a' => 'Site A - Usine Nord',
                                'site_b' => 'Site B - Entrepôt Sud',
                                'site_c' => 'Site C - Bureau Central',
                                'client' => 'Chez le Client',
                                'autre' => 'Autre lieu',
                            ])
                            ->required()
                            ->searchable()
                            ->native(false), // Utilise le select natif de Filament

                        TextArea::make('description')
                            ->label('Description détaillée de l\'activité')
                            ->rows(6)
                            ->required()
                            ->placeholder('Décrivez les travaux effectués, observations, etc.'),
                    ])->columns(2),

                // Section 2 : Localisation & Preuves
                Section::make('Localisation & Preuves')
                    ->description('Ajoutez la localisation et les photos si nécessaire.')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        TextInput::make('latitude')
                            ->label('Latitude')
                            ->numeric()
                            ->nullable()
                            ->disabled() // Sera rempli automatiquement par JS plus tard
                            ->hint('Automatique sur mobile'),

                        TextInput::make('longitude')
                            ->label('Longitude')
                            ->numeric()
                            ->nullable()
                            ->disabled()
                            ->hint('Automatique sur mobile'),

                        FileUpload::make('photos')
                            ->label('Photos de preuve')
                            ->multiple()
                            ->directory('reports-photos')
                            ->image()
                            ->imagePreviewHeight('150')
                            ->panelLayout('grid')
                            ->maxFiles(5)
                            ->maxSize(5120) // 5MB max par photo
                            ->nullable(),
                    ])->columns(2),

                // Section 3 : Validation & Signature
                Section::make('Validation')
                    ->description('Signez numériquement pour valider le rapport.')
                    ->icon('heroicon-o-pencil-square')
                    ->schema([
                        SignaturePad::make('signature_data')
                            ->label('Signature de l\'agent')
                            ->height('250px')
                            ->required()
                            ->helperText('Dessinez votre signature ci-dessus avec le doigt ou la souris.'),

                        Hidden::make('user_id')
                            ->default(auth()->id()),

                        Hidden::make('status')
                            ->default('submitted'),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('N°')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('title')
                    ->label('Titre')
                    ->searchable()
                    ->limit(30),

                TextColumn::make('user.name')
                    ->label('Agent')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('location_name')
                    ->label('Lieu')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'site_a' => 'primary',
                        'site_b' => 'success',
                        'site_c' => 'info',
                        'client' => 'warning',
                        default => 'gray',
                    }),

                BadgeColumn::make('status')
                    ->label('Statut')
                    ->colors([
                        'draft' => 'gray',
                        'submitted' => 'warning',
                        'validated' => 'success',
                        'rejected' => 'danger',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'draft' => 'Brouillon',
                        'submitted' => 'Soumis',
                        'validated' => 'Validé',
                        'rejected' => 'Rejeté',
                        default => $state,
                    }),

                TextColumn::make('created_at')
                    ->label('Date de soumission')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Brouillon',
                        'submitted' => 'Soumis',
                        'validated' => 'Validé',
                        'rejected' => 'Rejeté',
                    ]),

                SelectFilter::make('location_name')
                    ->options([
                        'site_a' => 'Site A',
                        'site_b' => 'Site B',
                        'site_c' => 'Site C',
                        'client' => 'Client',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Voir'),

                Tables\Actions\EditAction::make()
                    ->label('Modifier')
                    ->visible(fn(Report $record): bool => $record->status === 'draft'),

                Tables\Actions\Action::make('validate')
                    ->label('Valider')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Valider le rapport')
                    ->modalDescription('Êtes-vous sûr de vouloir valider ce rapport ? Cette action est irréversible.')
                    ->action(function (Report $record) {
                        $record->update(['status' => 'validated']);
                    })
                    ->visible(fn(Report $record): bool => $record->status === 'submitted'),

                Tables\Actions\Action::make('reject')
                    ->label('Rejeter')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Rejeter le rapport')
                    ->modalDescription('Veuillez fournir une raison pour le rejet.')
                    ->form([
                        TextArea::make('rejection_reason')
                            ->label('Raison du rejet')
                            ->required(),
                    ])
                    ->action(function (Report $record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            // Tu pourrais ajouter un champ rejection_reason dans la BDD
                        ]);
                    })
                    ->visible(fn(Report $record): bool => $record->status === 'submitted'),

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginationPageOptions([10, 25, 50, 100]);
    }

    public static function getRelations(): array
    {
        return [
            // Ajoute des relations ici si nécessaire (ex: commentaires, historique)
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReports::route('/'),
            'create' => Pages\CreateReport::route('/create'),
            // 'view' => Pages\ViewReport::route('/{record}'),
            'edit' => Pages\EditReport::route('/{record}/edit'),
        ];
    }

    // Permission : Qui peut voir les rapports ?
    public static function canViewAny(): bool
    {
        return auth()->check();
    }

    public static function canCreate(): bool
    {
        return auth()->check();
    }
}
