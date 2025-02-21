<?php

namespace Backstage\Models;

use Backstage\Shared\HasPackageFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Staudenmeir\LaravelAdjacencyList\Eloquent\Collection;

class Menu extends Model
{
    use HasPackageFactory;

    protected $primaryKey = 'slug';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected function casts(): array
    {
        return [];
    }

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public static function tree($slug) {
        
        $menuItems = MenuItem::where('menu_slug', $slug)
            ->with('content.descendants')
            ->tree()
            ->get()
            ->toTree();

        $menu = [];
        foreach ($menuItems as $index => $menu) {
            $menuItems[$index] = self::mergeContentInChildren($menu);
        }

        dd($menuItems->toArray());

        

        dd($menuItems);
        
        $contentUlids = $menuItems->where('include_children', true)->pluck('content_ulid')->filter()->unique()->toArray();

        $content = Content::treeOf(function ($query) use ($contentUlids) {
                $query->whereIn('ulid', $contentUlids);
        })
        ->get()
        ->toTree()
        ->keyBy('ulid');

        $menu = [];

        foreach ($menuItems as $item) {
            $menuItem = $item->toArray();
            if ($item->include_children) {
                $menuItem['content_children'] = $content[$item->content_ulid]->children->toArray();
            }
            $menu[] = $menuItem;
        }

        dd($menu);
        foreach ($menuItems as $item) {
            $items[] = $item->toArray();
            if ($item->include_children) {
                $items[] = $content[$item->content_ulid]->toArray() + ['parent_ulid' => $item->ulid];
            }
        }

        dd(collect($items)->groupBy('parent_ulid')->filter());
dd($items);
        $tree = (new Collection($items))->toTree();

        dd($tree);
        
        dd($contentUlids, $content, $menuItems, $items);
    }

    private static function mergeContentInChildren($menu) {

        foreach ($menu->children as $index => $child) {
            $menu->children[$index] = self::mergeContentInChildren($child);
        }

        if ($menu->include_children) {
            dump($menu);
            if ($menu->children->isEmpty()) {
                $menu->children = $menu->content->descendants->toTree();
            }
            else {
                $menu->children->push($menu->content->descendants->toTree());
            }
        }

        return $menu;
    }
}
