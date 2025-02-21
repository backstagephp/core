<?php

use Backstage\Media\MediaPlugin;

return [
    'panel' => [
        'default' => true,

        'plugins' => [
            // Vormkracht10\FilamentRedirects\RedirectsPlugin::make(),
            MediaPlugin::make(), // TODO: Deze toevoegen in ServiceProvider
        ],

        'resources' => [
            Backstage\Resources\BlockResource::class,
            Backstage\Resources\ContentResource::class,
            Backstage\Resources\DomainResource::class,
            Backstage\Resources\FieldResource::class,
            Backstage\Resources\FormResource::class,
            Backstage\Resources\FormSubmissionResource::class,
            Backstage\Resources\MenuResource::class,
            Backstage\Resources\LanguageResource::class,
            Backstage\Resources\SettingResource::class,
            Backstage\Resources\SiteResource::class,
            Backstage\Resources\TagResource::class,
            Backstage\Media\Resources\MediaResource::class, // TODO: Deze toevoegen in ServiceProvider
            // Backstage\Resources\TemplateResource::class,
            Backstage\Resources\TypeResource::class,
            Backstage\Resources\UserResource::class,

            Backstage\Translations\Filament\Resources\TranslationResource::class,
        ],

        'widgets' => [
            Backstage\Widgets\ContentUpdatesWidget::class,
            Backstage\Widgets\FormSubmissionsWidget::class,
        ],
    ],

    'components' => [
        'blocks' => [
            Backstage\View\Components\Blocks\Heading::class,
            Backstage\View\Components\Blocks\Paragraph::class,
            Backstage\View\Components\Form::class,
        ],
    ],
];
