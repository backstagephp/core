<?php

namespace Backstage\Resources\ContentResource\Pages;

use Backstage\Resources\ContentResource;
use Filament\Forms\Form;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;

class ManageChildrenContent extends ManageRelatedRecords
{
    protected static string $resource = ContentResource::class;

    protected static string $relationship = 'children';

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    public function getTitle(): string | Htmlable
    {
        $recordTitle = $this->getRecordTitle();

        $recordTitle = $recordTitle instanceof Htmlable ? $recordTitle->toHtml() : $recordTitle;

        return __('Manage :resource related content', [
            'resource' => $recordTitle,
        ]);
    }

    public function getBreadcrumb(): string
    {
        return __('Related Content');
    }

    public static function getNavigationLabel(): string
    {
        return __('Related Content');
    }

    public static function getModelLabel(): string
    {
        return __('Related Content');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Related Content');
    }

    public function form(Form $form): Form
    {
        return ContentResource::form($form);
    }

    public function table(Table $table): Table
    {
        return ContentResource::table($table)->reorderable('position');
    }
}
