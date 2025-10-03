<?php

namespace Backstage\Mcp;

use Backstage\Mcp\BackstageMcpServer;
use Illuminate\Support\ServiceProvider;
use Laravel\Mcp\McpManager;

class BackstageMcpServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(BackstageMcpServer::class, function ($app) {
            return new BackstageMcpServer();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->bound(McpManager::class)) {
            $this->app->make(McpManager::class)->registerServer('backstage', function () {
                return $this->app->make(BackstageMcpServer::class);
            });
        }
    }
}
