<?php

namespace Backstage\Resources\SiteResource;

use Backstage\Models\Site;
use Backstage\Resources\SiteResource;
use Filament\Actions\Action;
use Filament\Forms\Form;
use Filament\Pages\Tenancy\RegisterTenant;

class RegisterSite extends RegisterTenant
{
    protected static string $view = 'backstage::filament.sites.register';

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
