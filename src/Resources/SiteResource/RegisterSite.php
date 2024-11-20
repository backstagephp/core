<?php

namespace Vormkracht10\Backstage\Resources\SiteResource;

use Filament\Forms\Form;
use Filament\Pages\Tenancy\RegisterTenant;
use Vormkracht10\Backstage\Models\Site;
use Vormkracht10\Backstage\Resources\SiteResource;

class RegisterSite extends RegisterTenant
{
    public static function getLabel(): string
    {
        return 'Add new Site';
    }

    public static function canView(): bool
    {
        return true;
        // return auth()->user()->hasRole('Admin');
    }

    public function form(Form $form): Form
    {
        return SiteResource::form($form, fullWidth: true);
    }

    protected function handleRegistration(array $data): Site
    {
        $site = Site::create($data);

        $site->users()->attach(auth()->user());

        return $site;
    }
}
