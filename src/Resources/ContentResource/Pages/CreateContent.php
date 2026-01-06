<?php

namespace Backstage\Resources\ContentResource\Pages;

use Backstage\Fields\Concerns\CanMapDynamicFields;
use Backstage\Fields\Concerns\PersistsContentData;
use Backstage\Resources\ContentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateContent extends CreateRecord
{
    public int $formVersion = 0;

    protected static string $resource = ContentResource::class;

    use CanMapDynamicFields;
    use PersistsContentData;

    protected static ?string $slug = 'content/create/{type}';

    public function getTitle(): string
    {
        return '';
    }

    public function mount(): void
    {
        $this->data['type_slug'] = request()->route()->parameter('type')->slug;

        $this->authorizeAccess();

        $this->fillForm();

        $this->previousUrl = url()->previous();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->record = $this->getModel()::make($data);

        if (! $this->record->type_slug && isset($this->data['type_slug'])) {
            $this->record->type_slug = $this->data['type_slug'];
        }

        $data = $this->mutateBeforeSave($data);

        $this->data['values'] = $data['values'] ?? [];

        unset($data['tags']);
        unset($data['values']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->handleTags();
        $this->handleValues();

        $this->getRecord()->update([
            'creator_id' => auth()->id(),
            'edited_at' => now(),
        ]);

        $this->getRecord()->authors()->attach(auth()->id());
    }

    protected function getRedirectUrl(): string
    {
        // Store the created record before resetting for "Create Another"
        $createdRecord = $this->getRecord();

        // Reset state for "Create Another"
        $typeSlug = $this->data['type_slug'] ?? null;

        $this->data = [
            'type_slug' => $typeSlug,
            'values' => [],
        ];

        // Re-initialize static type property to prevent it being null during fill() hydration
        ContentResource::setStaticType(\Backstage\Models\Type::firstWhere('slug', $typeSlug));

        $this->form->fill([]);

        $this->formVersion++;

        // Temporarily restore the created record for URL generation
        $this->record = $createdRecord;

        // Get the default redirect URL (to edit page)
        return parent::getRedirectUrl();
    }
}
