<?php

namespace Davmixcool\MetaManager\Rendering;

use Davmixcool\MetaManager\MetaData;
use Davmixcool\MetaManager\Support\Config;
use Davmixcool\MetaManager\Support\MetaImage;
use Davmixcool\MetaManager\Support\Tag;
use Davmixcool\MetaManager\Support\TagBag;
use Davmixcool\MetaManager\Support\TwitterCard;
use Davmixcool\MetaManager\Support\Url;

/**
 * The v2 tag set: metadata that consumers actually read.
 *
 * Fallback hierarchy (PRD 18):
 *   twitter:x -> og:x -> base x -> config default
 */
class StandardProfile
{
    public function render(MetaData $data): string
    {
        $tags = new TagBag();

        $title = $this->title($data);
        $description = $this->description($data);
        $image = $this->image($data);
        $canonical = $this->canonical($data);

        if ($title !== null && $title !== '') {
            $tags->add(Tag::title($title));
        }

        if ($description !== null && $description !== '') {
            $tags->add(Tag::meta('description', $description));
        }

        $robots = $data->robotsString() ?? $this->config('robots');

        if ($robots !== null && $robots !== '') {
            $tags->add(Tag::meta('robots', $robots));
        }

        if (($author = $data->author ?? $this->config('author')) !== null && $author !== '') {
            $tags->add(Tag::meta('author', $author));
        }

        if (($keywords = $data->keywords ?? $this->config('keywords')) !== null && $keywords !== '') {
            $tags->add(Tag::meta('keywords', $keywords));
        }

        if ($canonical !== null) {
            $tags->add(Tag::link(['rel' => 'canonical', 'href' => $canonical]));
        }

        $this->openGraph($tags, $data, $title, $description, $image, $canonical);
        $this->twitter($tags, $data, $title, $description, $image);

        foreach ($data->customMeta as $name => $content) {
            $tags->add(Tag::meta($name, $content));
        }

        foreach ($data->customProperties as $property => $content) {
            $tags->add(Tag::property($property, $content));
        }

        foreach ($data->links as $attributes) {
            $tags->add(Tag::link($attributes));
        }

        return $tags->render();
    }

    private function openGraph(
        TagBag $tags,
        MetaData $data,
        ?string $title,
        ?string $description,
        ?MetaImage $image,
        ?string $canonical,
    ): void {
        $og = $data->openGraph;

        $ogTitle = $og->get('title') ?? $title;
        $ogDescription = $og->get('description') ?? $description;
        $ogType = $og->get('type') ?? $data->type ?? $this->config('type') ?? 'website';
        $ogSiteName = $og->get('site_name') ?? $data->siteName ?? $this->config('site_name') ?? Config::app('name');
        $ogLocale = $og->get('locale') ?? $data->locale ?? $this->config('locale') ?? $this->normalizeLocale(Config::app('locale'));

        // og:url is the canonical when one exists, else the current URL.
        $ogUrl = Url::safe($og->get('url') ?? $canonical ?? url()->current());

        if ($ogTitle !== null && $ogTitle !== '') {
            $tags->add(Tag::property('og:title', $ogTitle));
        }

        if ($ogDescription !== null && $ogDescription !== '') {
            $tags->add(Tag::property('og:description', $ogDescription));
        }

        $tags->add(Tag::property('og:type', $ogType));

        if ($ogUrl !== null) {
            $tags->add(Tag::property('og:url', $ogUrl));
        }

        if ($ogSiteName !== null && $ogSiteName !== '') {
            $tags->add(Tag::property('og:site_name', $ogSiteName));
        }

        if ($ogLocale !== null && $ogLocale !== '') {
            $tags->add(Tag::property('og:locale', $ogLocale));
        }

        $ogImage = $og->get('image') ?? $image;

        if ($ogImage instanceof MetaImage && ($url = $ogImage->safeUrl()) !== null) {
            $tags->add(Tag::property('og:image', $url));

            if ($ogImage->width !== null) {
                $tags->add(Tag::property('og:image:width', (string) $ogImage->width));
            }

            if ($ogImage->height !== null) {
                $tags->add(Tag::property('og:image:height', (string) $ogImage->height));
            }

            if ($ogImage->alt !== null && $ogImage->alt !== '') {
                $tags->add(Tag::property('og:image:alt', $ogImage->alt));
            }

            if ($ogImage->type !== null && $ogImage->type !== '') {
                $tags->add(Tag::property('og:image:type', $ogImage->type));
            }
        }

        if (($fbAppId = $this->config('fb_app_id')) !== null && $fbAppId !== '') {
            $tags->add(Tag::property('fb:app_id', $fbAppId));
        }
    }

    private function twitter(
        TagBag $tags,
        MetaData $data,
        ?string $title,
        ?string $description,
        ?MetaImage $image,
    ): void {
        $twitter = $data->twitter;
        $og = $data->openGraph;

        // Read raw: an explicit '' in config disables the card entirely, which
        // is v1 behaviour and must survive the empty-string-is-null rule above.
        $configuredCard = Config::get('twitter_card');

        $card = $twitter->get('card')
            ?? ($configuredCard === null ? TwitterCard::SUMMARY_LARGE_IMAGE : (string) $configuredCard);

        if ($card === '') {
            return;
        }

        $tags->add(Tag::meta('twitter:card', $card));

        if (($site = $twitter->get('site') ?? $this->config('twitter_site')) !== null && $site !== '') {
            $tags->add(Tag::meta('twitter:site', $site));
        }

        if (($creator = $twitter->get('creator') ?? $this->config('twitter_creator')) !== null && $creator !== '') {
            $tags->add(Tag::meta('twitter:creator', $creator));
        }

        $twitterTitle = $twitter->get('title') ?? $og->get('title') ?? $title;
        $twitterDescription = $twitter->get('description') ?? $og->get('description') ?? $description;

        if ($twitterTitle !== null && $twitterTitle !== '') {
            $tags->add(Tag::meta('twitter:title', $twitterTitle));
        }

        if ($twitterDescription !== null && $twitterDescription !== '') {
            $tags->add(Tag::meta('twitter:description', $twitterDescription));
        }

        $twitterImage = $twitter->get('image') ?? $og->get('image') ?? $image;

        if ($twitterImage instanceof MetaImage && ($url = $twitterImage->safeUrl()) !== null) {
            $tags->add(Tag::meta('twitter:image', $url));

            if ($twitterImage->alt !== null && $twitterImage->alt !== '') {
                $tags->add(Tag::meta('twitter:image:alt', $twitterImage->alt));
            }
        }
    }

    /**
     * The template applies only to an explicitly set page title. Applying it to
     * the config default would produce "SaaSGate - SaaSGate" on every page.
     */
    private function title(MetaData $data): ?string
    {
        if ($data->title === null || $data->title === '') {
            return $this->config('title') ?: null;
        }

        $template = $this->config('title_template');

        if (! $data->applyTitleTemplate || $template === null || $template === '' || ! str_contains($template, '%s')) {
            return $data->title;
        }

        return str_replace('%s', $data->title, $template);
    }

    private function description(MetaData $data): ?string
    {
        return $data->description ?? $this->config('description') ?: null;
    }

    private function image(MetaData $data): ?MetaImage
    {
        if ($data->image !== null) {
            return $data->image;
        }

        return MetaImage::make(
            $this->config('image'),
            width: $this->intConfig('image_width'),
            height: $this->intConfig('image_height'),
            alt: $this->config('image_alt') ?: null,
        );
    }

    private function canonical(MetaData $data): ?string
    {
        $canonical = $data->canonical ?? $this->config('canonical');

        if ($canonical === 'current') {
            $canonical = url()->current();
        }

        return ($canonical === null || $canonical === '') ? null : Url::safe($canonical);
    }

    /**
     * Open Graph wants language_TERRITORY (en_US); Laravel's app.locale is
     * usually just a language (en).
     *
     * Uppercasing the language is right for fr_FR / de_DE / es_ES but wrong for
     * plenty of others - en is not en_EN - so the exceptions are listed and
     * everything else falls back to the doubled form.
     */
    private const LOCALE_REGIONS = [
        'af' => 'ZA', 'ar' => 'AR', 'be' => 'BY', 'bs' => 'BA', 'ca' => 'ES',
        'cs' => 'CZ', 'da' => 'DK', 'el' => 'GR', 'en' => 'US', 'et' => 'EE',
        'eu' => 'ES', 'fa' => 'IR', 'ga' => 'IE', 'gl' => 'ES', 'he' => 'IL',
        'hi' => 'IN', 'ja' => 'JP', 'ko' => 'KR', 'ms' => 'MY', 'nb' => 'NO',
        'nn' => 'NO', 'pt' => 'PT', 'sl' => 'SI', 'sq' => 'AL', 'sr' => 'RS',
        'sv' => 'SE', 'ta' => 'IN', 'te' => 'IN', 'uk' => 'UA', 'ur' => 'PK',
        'vi' => 'VN', 'zh' => 'CN',
    ];

    private function normalizeLocale(mixed $locale): ?string
    {
        $locale = is_string($locale) ? trim($locale) : '';

        if ($locale === '') {
            return null;
        }

        $locale = str_replace('-', '_', $locale);

        if (str_contains($locale, '_')) {
            [$language, $region] = explode('_', $locale, 2);

            return strtolower($language).'_'.strtoupper($region);
        }

        $language = strtolower($locale);

        return $language.'_'.(self::LOCALE_REGIONS[$language] ?? strtoupper($language));
    }

    /**
     * An empty string in config means "not set", so it must collapse to null.
     * `?? ` only skips null, so returning '' here would silently suppress the
     * package defaults for locale, type, site_name and robots.
     */
    private function config(string $key): ?string
    {
        $value = Config::get($key);

        if ($value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }

    private function intConfig(string $key): ?int
    {
        $value = Config::get($key);

        return ($value === null || $value === '') ? null : (int) $value;
    }
}
