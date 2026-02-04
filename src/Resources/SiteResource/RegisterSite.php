<?php

namespace Backstage\Resources\SiteResource;

use Backstage\Models\Site;
use Backstage\Models\Type;
use Backstage\Resources\SiteResource;
use Filament\Actions\Action;
use Filament\Pages\Tenancy\RegisterTenant;
use Filament\Schemas\Schema;

class RegisterSite extends RegisterTenant
{
    protected string $view = 'backstage::filament.sites.register';

    public static function getLabel(): string
    {
        return 'Add new Site';
    }

    public static function canView(): bool
    {
        return true;
    }

    public function form(Schema $schema): Schema
    {
        return SiteResource::form($schema, fullWidth: true);
    }

    protected function handleRegistration(array $data): Site
    {
        $site = Site::create($data);

        $site->users()->attach(auth()->user());

        $domain = $site->domains()->firstOrCreate(
            ['name' => parse_url(config('app.url'), PHP_URL_HOST)], // search by domain name
            ['environment' => config('app.env')]
        );

        $language = $domain->languages()->firstOrCreate(
            ['code' => config('app.locale')], // search by code
            [
                'name' => config('app.locale'),
                'native' => config('app.locale'),
                'active' => true,
                'default' => true,
            ]
        );

        /** @var Type $type */
        $type = $site->types()->firstOrCreate(
            ['slug' => 'page'], // search by slug
            [
                'name' => 'Page',
                'name_plural' => 'Pages',
                'icon' => 'circle-stack',
                'public' => true,
            ]
        );

        $type->fields()->firstOrCreate(
            ['slug' => 'title'],
            [
                'name' => 'Title',
                'field_type' => 'text',
                'position' => 1,
            ]
        );

        $type->fields()->firstOrCreate(
            ['slug' => 'content'],
            [
                'name' => 'Content',
                'field_type' => 'rich-editor',
                'position' => 2,
            ]
        );

        $type->content()
            ->create([
                'site_ulid' => $site->getKey(),
                'language_code' => $language->getKey(),
                'name' => 'Home',
                'slug' => 'home',
                'path' => '/',
                'meta_tags' => ['title' => 'Home'],
                'published_at' => now(),
                'edited_at' => now(),
                'public' => true,
            ]);

        return $site;
    }

    public function getGoBackAction(): Action
    {
        return Action::make('goBack')
            ->label(__('Go back'))
            ->link()
            ->icon('heroicon-s-arrow-left')
            ->color('gray')
            ->url(url()->previous());
    }
}
