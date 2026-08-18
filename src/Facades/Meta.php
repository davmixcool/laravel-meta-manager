<?php

namespace Davmixcool\MetaManager\Facades;

use Davmixcool\MetaManager\MetaData;
use Davmixcool\MetaManager\MetaManager;
use Davmixcool\MetaManager\Support\MetaImage;
use Davmixcool\MetaManager\Support\OpenGraph;
use Davmixcool\MetaManager\Support\TwitterCard;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\HtmlString;

/**
 * @method static MetaManager title(?string $title, bool $applyTemplate = true)
 * @method static MetaManager description(?string $description)
 * @method static MetaManager canonical(?string $canonical)
 * @method static MetaManager image(MetaImage|string|null $image, ?int $width = null, ?int $height = null, ?string $alt = null, ?string $type = null)
 * @method static MetaManager robots(string|array|null $robots)
 * @method static MetaManager index()
 * @method static MetaManager noIndex()
 * @method static MetaManager follow()
 * @method static MetaManager noFollow()
 * @method static MetaManager siteName(?string $siteName)
 * @method static MetaManager locale(?string $locale)
 * @method static MetaManager type(?string $type)
 * @method static MetaManager keywords(string|array|null $keywords)
 * @method static MetaManager author(?string $author)
 * @method static MetaManager add(string $name, mixed $content)
 * @method static MetaManager property(string $property, mixed $content)
 * @method static MetaManager link(string $rel, ?string $href = null, array $attributes = [])
 * @method static MetaManager favicon(string $href, array $attributes = [])
 * @method static OpenGraph openGraph()
 * @method static TwitterCard twitter()
 * @method static HtmlString render()
 * @method static HtmlString blade(array $vars = [])
 * @method static array toArray()
 * @method static array validate()
 * @method static MetaData data()
 * @method static MetaManager use(MetaData $data)
 * @method static MetaManager reset()
 *
 * @see MetaManager
 */
class Meta extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'meta';
    }
}
