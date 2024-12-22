<?php

return [
    'panel' => [
        'default' => true,

        'plugins' => [
            Vormkracht10\FilamentRedirects\RedirectsPlugin::make(),
        ],

        'resources' => [
            Vormkracht10\Backstage\Resources\BlockResource::class,
            Vormkracht10\Backstage\Resources\ContentResource::class,
            Vormkracht10\Backstage\Resources\DomainResource::class,
            Vormkracht10\Backstage\Resources\FieldResource::class,
            Vormkracht10\Backstage\Resources\FormResource::class,
            Vormkracht10\Backstage\Resources\FormSubmissionResource::class,
            Vormkracht10\Backstage\Resources\LanguageResource::class,
            Vormkracht10\Backstage\Resources\MenuResource::class,
            Vormkracht10\Backstage\Resources\SettingResource::class,
            Vormkracht10\Backstage\Resources\SiteResource::class,
            Vormkracht10\Backstage\Resources\TagResource::class,
            // Vormkracht10\Backstage\Resources\TemplateResource::class,
            Vormkracht10\Backstage\Resources\TypeResource::class,
            Vormkracht10\Backstage\Resources\UserResource::class,
        ],

        'widgets' => [
            Vormkracht10\Backstage\Widgets\ContentUpdatesWidget::class,
            Vormkracht10\Backstage\Widgets\FormSubmissionsWidget::class,
        ],
    ],

    'components' => [
        'blocks' => [
            Vormkracht10\Backstage\View\Components\Blocks\Heading::class,
            Vormkracht10\Backstage\View\Components\Blocks\Paragraph::class,
            Vormkracht10\Backstage\View\Components\Form::class,
        ],
    ],

    'fields' => [
        //
    ],
];
