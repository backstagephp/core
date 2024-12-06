<div {{ $attributes }}>
    @if (config('app.debug'))
        <div class="border border-black p-4 bg-white text-black">
            This is the default component when no component is found. See <a href="https://github.com/vormkracht10/backstage" target="_blank">documentation</a>.<br />
            To quickstart, add one of the following files to edit this file:<br />
            - resources/views/components/blocks/{{ $_type }}.blade.php<br />
            - resources/views/components/default.blade.php<br />

            To add a custom component you can register like this. In AppServiceProvider for example:<br />
            <code>
                @php ($classType = \Illuminate\Support\Str::studly($_type))
                {!! \Illuminate\Support\Str::markdown("```php
use Vormkracht10\Backstage\Facades\Backstage;

Backstage::registerComponent(\App\View\Components\\$classType::class);
                    "
                )!!}
            </code>
        </div>
    @endif
</div>