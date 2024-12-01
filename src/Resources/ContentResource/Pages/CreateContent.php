<?php

namespace Vormkracht10\Backstage\Resources\ContentResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Vormkracht10\Backstage\Resources\ContentResource;

class CreateContent extends CreateRecord
{
    protected static string $resource = ContentResource::class;

    protected static ?string $slug = 'content/create/{type}';

    public function mount(): void
    {
        $this->data['type_slug'] = request()->route()->parameter('type')->slug;

        $this->authorizeAccess();

        $this->fillForm();

        $this->previousUrl = url()->previous();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        unset($data['values']);

        return $data;
    }

    protected function afterCreate(): void
    {
        dd($this->data['values']);
        $this->getRecord()->values()->create($this->data['values'] ?? []);
    }
}
