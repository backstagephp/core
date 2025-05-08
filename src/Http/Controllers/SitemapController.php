<?php

namespace Backstage\Http\Controllers;

use Backstage\Models\Content;
use Backstage\Models\Site;
use Illuminate\Http\Request;

class SitemapController
{
    public function __invoke(Request $request)
    {
        $xml = cache()
            ->tags(['sitemap'])
            ->rememberForever('xlm-sitemap', function () {
                $doc = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');

                $site = request()->get('site', Site::first());

                Content::where('site_ulid', $site->ulid)
                    ->with('site')
                    ->where('slug', '!=', '404')
                    ->where('slug', '!=', '500')
                    ->whereNotNull('path')
                    ->where(function ($query) {
                        $query->where(function ($query) {
                            $query->whereNotNull('meta_tags')
                                ->whereRaw('NOT JSON_CONTAINS(JSON_EXTRACT(meta_tags, "$.robots"), ?)', ['"noindex"']);
                        });
                    })
                    ->where('public', 1)
                    ->where('published_at', '<=', now())
                    ->orderBy('published_at', 'desc')
                    ->each(function ($content) use ($doc) {
                        $url = $doc->addChild('url');
                        $url->addChild('loc', $content->url);
                        $url->addChild('priority', '1.0');
                        $url->addChild('changefreq', 'weekly');
                        $url->addChild('lastmod', $content->updated_at->toIso8601String());
                    });

                return $doc->asXML();
            });

        return response($xml, 200, [
            'Content-Type' => 'text/xml',
            'X-Robots-Tag' => 'noindex',
        ]);
    }
}
