<?php

namespace Vormkracht10\Backstage\Providers;

use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Vormkracht10\Backstage\Models\Content;
use Vormkracht10\Backstage\Models\Domain;

class RequestServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Request::macro('content', function () {
            return once(function () {
                $content = Content::join('sites', 'sites.ulid', 'content.site_ulid')
                    ->join('domains', 'domains.site_ulid', 'sites.ulid')
                    ->join('domain_language', function (JoinClause $join) {
                        $join->on('domain_language.domain_ulid', '=', 'domains.ulid')
                            ->on('domain_language.language_code', '=', 'content.language_code');
                    })
                    ->whereRaw('CONCAT(IFNULL(sites.path, ""), IFNULL(domain_language.path, ""), IFNULL(content.path, "")) = ?', [$this->path()])
                    ->whereRaw("REPLACE(domains.name, 'www.', '') = ?", [str_replace('www.', '', $this->getHost())]);

                return $content->first();
            });
        });

        Request::macro('domain', function () {
            return once(fn () => Domain::whereRaw("REPLACE('www.', '', name)", str_replace('www.', '', $this->getHost()))->first());
        });

        Request::macro('language', function () {
            return once(fn () => $this->content()->language);
        });

        Request::macro('site', function () {
            return once(fn () => $this->domain()->site);
        });
    }
}
