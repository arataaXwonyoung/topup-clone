<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BannerResource\Pages;
use App\Filament\Resources\BannerResource\RelationManagers;
use App\Models\Banner;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BannerResource extends Resource
{
    protected static ?string $model = Banner::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';
    
    protected static ?string $navigationGroup = 'CMS';
    
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Banner Information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(191)
                            ->helperText('Judul banner untuk identifikasi internal'),
                        
                        Forms\Components\FileUpload::make('image_path')
                            ->label('Banner Image')
                            ->image()
                            ->required()
                            ->directory('banners')
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('16:9')
                            ->imageResizeTargetWidth('1920')
                            ->imageResizeTargetHeight('1080')
                            ->helperText('Ukuran rekomendasi: 1920x1080px (16:9 aspect ratio)'),
                        
                        Forms\Components\TextInput::make('link')
                            ->label('Link URL')
                            ->url()
                            ->maxLength(191)
                            ->helperText('URL tujuan ketika banner diklik (opsional)'),
                    ]),
                
                Forms\Components\Section::make('Display Settings')
                    ->schema([
                        Forms\Components\Select::make('position')
                            ->label('Position')
                            ->required()
                            ->options([
                                'hero' => 'Hero Section',
                                'sidebar' => 'Sidebar',
                                'footer' => 'Footer'
                            ])
                            ->default('hero')
                            ->helperText('Posisi tampilan banner di website'),
                        
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Aktifkan/nonaktifkan banner'),
                        
                        Forms\Components\TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Urutan tampilan (angka kecil = lebih dulu)'),
                    ])
                    ->columns(3),
                
                Forms\Components\Section::make('Schedule (Optional)')
                    ->schema([
                        Forms\Components\DateTimePicker::make('starts_at')
                            ->label('Start Date')
                            ->helperText('Kapan banner mulai ditampilkan (kosongkan jika langsung aktif)'),
                        
                        Forms\Components\DateTimePicker::make('ends_at')
                            ->label('End Date')
                            ->helperText('Kapan banner berhenti ditampilkan (kosongkan jika tidak ada batas waktu)'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Preview')
                    ->width(80)
                    ->height(45),
                
                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('position')
                    ->label('Position')
                    ->colors([
                        'primary' => 'hero',
                        'success' => 'sidebar', 
                        'warning' => 'footer',
                    ]),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Order')
                    ->numeric()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Start Date')
                    ->date()
                    ->sortable()
                    ->placeholder('Immediate'),
                
                Tables\Columns\TextColumn::make('ends_at')
                    ->label('End Date')
                    ->date()
                    ->sortable()
                    ->placeholder('No expiry'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('position')
                    ->options([
                        'hero' => 'Hero Section',
                        'sidebar' => 'Sidebar',
                        'footer' => 'Footer'
                    ]),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
                
                Tables\Filters\Filter::make('scheduled')
                    ->query(fn (Builder $query): Builder => $query
                        ->where('starts_at', '>', now())
                        ->orWhere('ends_at', '<', now())
                    )
                    ->label('Scheduled/Expired'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order', 'asc')
            ->emptyStateHeading('No banners found')
            ->emptyStateDescription('Create your first banner to display promotional content.')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBanners::route('/'),
            'create' => Pages\CreateBanner::route('/create'),
            'edit' => Pages\EditBanner::route('/{record}/edit'),
        ];
    }
}
