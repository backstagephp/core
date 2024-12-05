<?php

return [
    'components' => [
        'blocks' => [
            Vormkracht10\Backstage\View\Components\Blocks\Heading::class,
            Vormkracht10\Backstage\View\Components\Blocks\Paragraph::class,
        ],
    ],

    'panel' => [
        'default' => true,

        'theme' => [
            'path' => base_path('vendor/vormkracht10/backstage/resources/css/theme.css'),
        ],
    ],
];
