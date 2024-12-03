<?php

namespace Vormkracht10\Backstage\Http\Middleware\Filament;

use Closure;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationItem;
use Illuminate\Http\Request;
use Vormkracht10\Backstage\Models\Type;

class ContentNavigationItems
{
    public function handle(Request $request, Closure $next)
    {
        $items = Type::orderBy('name')->get()->map(function (Type $type) {
            return NavigationItem::make($type->slug)
                ->label($type->name_plural)
                ->parentItem('Content')
                ->url(route('filament.backstage.resources.content.index', [
                    'tenant' => Filament::getTenant(),
                    'tableFilters[type_slug][values]' => ['page'],
                ]));
        })->toArray();

        Filament::registerNavigationItems([
            NavigationItem::make('meta_tags')
                ->label('Meta Tags')
                ->icon('heroicon-o-code-bracket-square')
                ->group('SEO')
                ->url(route('filament.backstage.resources.content.meta_tags', ['tenant' => Filament::getTenant()])),
            ...$items,
        ]);

        return $next($request);
    }
}
