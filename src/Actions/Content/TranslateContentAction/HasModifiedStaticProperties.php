<?php

namespace Backstage\Actions\Content\TranslateContentAction;

use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Model;

trait HasModifiedStaticProperties
{
    /**
     * @var Model|null $translatedContent
     */
    protected static $translatedContent = null;

    /**
     * @var bool|null $translateOnSync
     */
    protected static $translateOnSync;

    /**
     * Use this to determine if the content translation should prefer synchronous processing.
     * Add this to the AppServiceProvider to set the default value.
     * 
     * @param bool $translateOnSync
     * @return void
     */
    public static function setTranslateOnSync(bool $translateOnSync): void
    {
        static::$translateOnSync = $translateOnSync;
    }

    /**
     * Use this to determine if the content translation should prefer synchronous processing.
     * If not set, it defaults to true in local environments.
     * 
     * @return bool
     */
    public static function shouldTranslateOnSync(): bool
    {
        return static::$translateOnSync ?? false;
    }

    /**
     * Set the translated content model.
     * This is used to statically store the content that has been translated.
     * 
     * @param Model|null $content
     * @return void
     */
    public static function setTranslatedContent(Model|null $content): void
    {
        static::$translatedContent = $content;
    }

    public static function getTranslatedContent(): ?Model
    {
        return static::$translatedContent;
    }
}
