<?php

return [
    'panel' => [
        'default' => true,

        'plugins' => [
            // Vormkracht10\FilamentRedirects\RedirectsPlugin::make(),
        ],

        'resources' => [
            Backstage\Core\Resources\BlockResource::class,
            Backstage\Core\Resources\ContentResource::class,
            Backstage\Core\Resources\DomainResource::class,
            Backstage\Core\Resources\FieldResource::class,
            Backstage\Core\Resources\FormResource::class,
            Backstage\Core\Resources\FormSubmissionResource::class,
            Backstage\Core\Resources\LanguageResource::class,
            Backstage\Core\Resources\MenuResource::class,
            Backstage\Core\Resources\SettingResource::class,
            Backstage\Core\Resources\SiteResource::class,
            Backstage\Core\Resources\TagResource::class,
            // Backstage\Core\Resources\TemplateResource::class,
            Backstage\Core\Resources\TypeResource::class,
            Backstage\Core\Resources\UserResource::class,
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
