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

    'fields' => [
        // App\Backstage\Core\Fields\CustomField::class;
    ],
];
