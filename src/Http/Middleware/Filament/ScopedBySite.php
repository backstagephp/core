<?php

namespace Vormkracht10\Backstage\Http\Middleware\Filament;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Vormkracht10\Backstage\Models\Block;
use Vormkracht10\Backstage\Models\Content;
use Vormkracht10\Backstage\Models\Domain;
use Vormkracht10\Backstage\Models\Form;
use Vormkracht10\Backstage\Models\Language;
use Vormkracht10\Backstage\Models\Media;
use Vormkracht10\Backstage\Models\Menu;
use Vormkracht10\Backstage\Models\Setting;
use Vormkracht10\Backstage\Models\Tag;
use Vormkracht10\Backstage\Models\Template;
use Vormkracht10\Backstage\Models\Type;
use Vormkracht10\Backstage\Models\User;

class ScopedBySite
{
    public function handle(Request $request, Closure $next)
    {
        Block::addGlobalScope(
            fn (Builder $query) => $query->whereHas('sites', fn ($query) => $query->where('sites.ulid', Filament::getTenant()->ulid)),
        );

        Content::addGlobalScope(
            fn (Builder $query) => $query->whereBelongsTo(Filament::getTenant()),
        );

        Domain::addGlobalScope(
            fn (Builder $query) => $query->whereBelongsTo(Filament::getTenant()),
        );

        Form::addGlobalScope(
            fn (Builder $query) => $query->whereHas('site', fn ($query) => $query->where('sites.ulid', Filament::getTenant()->ulid)),
        );

        Language::addGlobalScope(
            fn (Builder $query) => $query->whereHas('domains.site', fn ($query) => $query->where('sites.ulid', Filament::getTenant()->ulid)),
        );

        // Media::addGlobalScope(
        //     fn (Builder $query) => $query->whereBelongsTo(Filament::getTenant()),
        // );

        Menu::addGlobalScope(
            fn (Builder $query) => $query->whereBelongsTo(Filament::getTenant()),
        );

        Setting::addGlobalScope(
            fn (Builder $query) => $query->whereBelongsTo(Filament::getTenant()),
        );

        Tag::addGlobalScope(
            fn (Builder $query) => $query->whereHas('sites', fn ($query) => $query->where('sites.ulid', Filament::getTenant()->ulid)),
        );

        Template::addGlobalScope(
            fn (Builder $query) => $query->whereHas('sites', fn ($query) => $query->where('sites.ulid', Filament::getTenant()->ulid)),
        );

        Type::addGlobalScope(
            fn (Builder $query) => $query->whereHas('sites', fn ($query) => $query->where('sites.ulid', Filament::getTenant()->ulid)),
        );

        User::addGlobalScope(
            fn (Builder $query) => $query->whereHas('sites', fn ($query) => $query->where('sites.ulid', Filament::getTenant()->ulid)),
        );

        return $next($request);
    }
}
