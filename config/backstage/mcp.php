<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Backstage MCP Server Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration file contains settings for the Backstage MCP server
    | that provides knowledge retrieval and documentation tools.
    |
    */

    'server' => [
        'name' => 'Backstage CMS',
        'version' => '1.0.0',
        'description' => 'MCP server for Backstage CMS knowledge retrieval and documentation',
    ],

    'search' => [
        'default_limit' => 10,
        'max_limit' => 50,
    ],
];
