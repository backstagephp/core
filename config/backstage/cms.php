<?php

return [
    'panel' => [
        'default' => true,

        'plugins' => [
            // Vormkracht10\FilamentRedirects\RedirectsPlugin::make(),
        ],

        'resources' => [
            Backstage\Resources\BlockResource::class,
            Backstage\Resources\ContentResource::class,
            Backstage\Resources\DomainResource::class,
            Backstage\Resources\FieldResource::class,
            Backstage\Resources\FormResource::class,
            Backstage\Resources\FormSubmissionResource::class,
            Backstage\Resources\LanguageResource::class,
            Backstage\Resources\MenuResource::class,
            Backstage\Resources\SettingResource::class,
            Backstage\Resources\SiteResource::class,
            Backstage\Resources\TagResource::class,
            // Backstage\Resources\TemplateResource::class,
            Backstage\Resources\TypeResource::class,
            Backstage\Resources\UserResource::class,
        ],

        'widgets' => [
            Backstage\Core\Widgets\ContentUpdatesWidget::class,
            Backstage\Core\Widgets\FormSubmissionsWidget::class,
        ],
    ],

    'components' => [
        'blocks' => [
            Backstage\Core\View\Components\Blocks\Heading::class,
            Backstage\Core\View\Components\Blocks\Paragraph::class,
            Backstage\Core\View\Components\Form::class,
        ],
    ],

    'fields' => [
        // App\Backstage\Core\Fields\CustomField::class;
    ],
];
