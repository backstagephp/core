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
        $contentTypes = Type::orderBy('name')->get()->map(function (Type $type) {
            return NavigationItem::make($type->slug)
                ->label($type->name_plural)
                ->parentItem('Content')
                ->isActiveWhen(fn (NavigationItem $item) => request()->input('tableFilters.type_slug.values.0') === $type->slug)
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
                ->isActiveWhen(fn (NavigationItem $item) => request()->routeIs('filament.backstage.resources.content.meta_tags'))
                ->url(route('filament.backstage.resources.content.meta_tags', ['tenant' => Filament::getTenant()])),
            ...$contentTypes,
        ]);

        return $next($request);
    }
}
