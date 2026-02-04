<?php

namespace Backstage\Resources\ContentResource\Pages;

use Backstage\Models\Content;
use Backstage\Models\User;
use Backstage\Resources\ContentResource;
use Backstage\Translations\Filament\Resources\LanguageResource;
use Backstage\Translations\Laravel\Models\Language;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Icon;
use Filament\Schemas\Schema;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Enums\IconSize;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class ContentTranslations extends Page implements HasTable
{
    use InteractsWithRecord;
    use InteractsWithTable;

    protected static string $resource = ContentResource::class;

    protected static string | \BackedEnum | null $navigationIcon = Heroicon::OutlinedLanguage;

    public function mount($record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public static function canAccess(array $parameters = []): bool
    {
        return Filament::getCurrentOrDefaultPanel()->hasPlugin('translations');
    }

    public function getTitle(): string | Htmlable
    {
        $recordTitle = static::getResource()::getRecordTitle($this->getRecord());

        $recordTitle = $recordTitle instanceof Htmlable ? $recordTitle->toHtml() : $recordTitle;

        return __('Translations: :resource', [
            'resource' => $recordTitle,
        ]);
    }

    public function getBreadcrumb(): string
    {
        return __('Translations');
    }

    public static function getNavigationLabel(): string
    {
        return __('Translations');
    }

    public function getModelLabel(): string
    {
        return __('Translation');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Translations');
    }

    public function getHeaderActions(): array
    {
        return [
            Action::make('languages')
                ->label(__('Languages'))
                ->url(LanguageResource::getUrl('index', ['tenant' => Filament::getTenant()]))
                ->openUrlInNewTab()
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->iconPosition(IconPosition::After)
                ->color('gray'),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function () {
                return Content::query()->where('slug', $this->getRecord()->slug)->with('creator');
            })
            ->recordUrl(function (Content $record) {
                return static::getResource()::getUrl('edit', ['record' => $record]);
            })
            ->columns([
                IconColumn::make('language_code')
                    ->label(fn (): Htmlable => new HtmlString)
                    ->icon(fn (Content $record) => country_flag($record->language_code))
                    ->size(IconSize::ExtraLarge)
                    ->width(1)
                    ->tooltip(fn (Content $record) => Language::where('code', $record->language_code)->first()?->name)
                    ->alignCenter(),

                TextColumn::make('name')
                    ->label(__('Name')),

                ImageColumn::make('creator.avatar')
                    ->circular()
                    ->tooltip(fn (User | Model $record) => $record->creator->name)
                    ->alignCenter()
                    ->label(Icon::make(Heroicon::OutlinedUser)->tooltip(__('Creator'))->extraAttributes(['style' => 'position: relative; top: -1px; margin-right: 3px; display: inline-block;'])),

                TextColumn::make('updated_at')
                    ->label(__('Updated at'))
                    ->since(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->schema([
                $this->table,
            ]);
    }
}
