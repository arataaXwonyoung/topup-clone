<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArticleResource\Pages;
use App\Models\Article;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;

class ArticleResource extends Resource
{
    protected static ?string $model = Article::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    
    protected static ?string $navigationGroup = 'Content';
    
    protected static ?int $navigationSort = 1;
    
    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Article Content')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $state, Forms\Set $set) => $set('slug', Str::slug($state))),
                            
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('URL-friendly version of the title'),
                            
                        Forms\Components\Textarea::make('excerpt')
                            ->rows(3)
                            ->maxLength(500)
                            ->helperText('Short description for SEO and previews')
                            ->columnSpanFull(),
                            
                        Forms\Components\RichEditor::make('content')
                            ->required()
                            ->columnSpanFull()
                            ->toolbarButtons([
                                'blockquote',
                                'bold',
                                'bulletList',
                                'codeBlock',
                                'h2',
                                'h3',
                                'italic',
                                'link',
                                'orderedList',
                                'redo',
                                'strike',
                                'underline',
                                'undo',
                            ]),
                    ])
                    ->columns(2),
                    
                Forms\Components\Section::make('Media')
                    ->schema([
                        Forms\Components\FileUpload::make('featured_image')
                            ->label('Featured Image')
                            ->image()
                            ->directory('articles')
                            ->maxSize(2048)
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('16:9')
                            ->imageResizeTargetWidth('1200')
                            ->imageResizeTargetHeight('675'),
                            
                        Forms\Components\TextInput::make('featured_image_alt')
                            ->label('Image Alt Text')
                            ->maxLength(255)
                            ->helperText('Describe the image for accessibility'),
                    ])
                    ->columns(2),
                    
                Forms\Components\Section::make('Categorization')
                    ->schema([
                        Forms\Components\Select::make('category')
                            ->options([
                                'news' => 'News & Updates',
                                'guide' => 'Game Guides',
                                'tips' => 'Tips & Tricks', 
                                'event' => 'Events',
                                'promo' => 'Promotions',
                                'announcement' => 'Announcements',
                            ])
                            ->required()
                            ->default('news'),
                            
                        Forms\Components\TagsInput::make('tags')
                            ->separator(',')
                            ->suggestions([
                                'mobile legends',
                                'free fire',
                                'pubg mobile',
                                'genshin impact',
                                'valorant',
                                'tips',
                                'guide',
                                'update',
                                'event',
                                'promo',
                            ]),
                            
                        Forms\Components\TextInput::make('related_games')
                            ->label('Related Games (comma separated)')
                            ->helperText('Enter game names separated by commas'),
                    ])
                    ->columns(2),
                    
                Forms\Components\Section::make('SEO Settings')
                    ->schema([
                        Forms\Components\TextInput::make('meta_title')
                            ->label('SEO Title')
                            ->maxLength(60)
                            ->helperText('Leave empty to use article title'),
                            
                        Forms\Components\Textarea::make('meta_description')
                            ->label('Meta Description')
                            ->maxLength(160)
                            ->rows(3)
                            ->helperText('Brief description for search engines'),
                            
                        Forms\Components\TagsInput::make('meta_keywords')
                            ->label('Meta Keywords')
                            ->separator(','),
                    ])
                    ->columns(2)
                    ->collapsed(),
                    
                Forms\Components\Section::make('Publishing')
                    ->schema([
                        Forms\Components\Toggle::make('is_published')
                            ->label('Published')
                            ->default(false),
                            
                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('Publish Date')
                            ->default(now())
                            ->visible(fn (Forms\Get $get) => $get('is_published'))
                            ->required(fn (Forms\Get $get) => $get('is_published')),
                            
                        Forms\Components\Toggle::make('is_featured')
                            ->label('Featured Article')
                            ->helperText('Show on homepage'),
                            
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('featured_image')
                    ->label('Image')
                    ->circular()
                    ->defaultImageUrl(url('/images/default-article.png')),
                    
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->title),
                    
                Tables\Columns\BadgeColumn::make('category')
                    ->colors([
                        'primary' => 'news',
                        'success' => 'guide',
                        'warning' => 'tips',
                        'danger' => 'event',
                        'info' => 'promo',
                        'gray' => 'announcement',
                    ]),
                    
                Tables\Columns\IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean(),
                    
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean(),
                    
                Tables\Columns\TextColumn::make('views_count')
                    ->label('Views')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('author')
                    ->label('Author')
                    ->default('System')
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('published_at')
                    ->label('Published')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->color(fn ($record) => $record->published_at && $record->published_at->isFuture() ? 'warning' : 'gray'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Published'),
                    
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'news' => 'News & Updates',
                        'guide' => 'Game Guides',
                        'tips' => 'Tips & Tricks',
                        'event' => 'Events',
                        'promo' => 'Promotions',
                        'announcement' => 'Announcements',
                    ]),
                    
                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Featured'),
                    
                Tables\Filters\Filter::make('published_at')
                    ->form([
                        Forms\Components\DatePicker::make('published_from')
                            ->label('Published from'),
                        Forms\Components\DatePicker::make('published_until')
                            ->label('Published until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['published_from'], fn ($query, $date) => $query->whereDate('published_at', '>=', $date))
                            ->when($data['published_until'], fn ($query, $date) => $query->whereDate('published_at', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->visible(fn ($record) => $record->is_published)
                    ->url(fn ($record): string => route('articles.show', $record->slug) ?? '#')
                    ->openUrlInNewTab(),
                    
                Tables\Actions\Action::make('duplicate')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->action(function ($record) {
                        $newArticle = $record->replicate();
                        $newArticle->title = $record->title . ' (Copy)';
                        $newArticle->slug = $record->slug . '-copy';
                        $newArticle->is_published = false;
                        $newArticle->is_featured = false;
                        $newArticle->published_at = null;
                        $newArticle->save();
                    }),
                    
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('publish')
                        ->label('Publish Selected')
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update([
                            'is_published' => true,
                            'published_at' => now()
                        ])),
                        
                    Tables\Actions\BulkAction::make('unpublish')
                        ->label('Unpublish Selected')
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['is_published' => false])),
                        
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListArticles::route('/'),
            'create' => Pages\CreateArticle::route('/create'),
            'edit' => Pages\EditArticle::route('/{record}/edit'),
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_published', false)->count() ?: null;
    }
    
    public static function getNavigationBadgeColor(): ?string
    {
        return static::getNavigationBadge() > 0 ? 'warning' : null;
    }
}