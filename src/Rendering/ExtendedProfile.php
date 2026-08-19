<?php

namespace Davmixcool\MetaManager\Rendering;

use Davmixcool\MetaManager\MetaData;
use Davmixcool\MetaManager\Support\Config;
use Davmixcool\MetaManager\Support\MetaImage;
use Davmixcool\MetaManager\Support\Tag;
use Davmixcool\MetaManager\Support\TagBag;
use Davmixcool\MetaManager\Support\Url;

/**
 * The extended tag set: everything v1 printed, as a STRICT SUPERSET.
 *
 * Every tag v1 emitted is emitted here with the same value, in the same order,
 * including quirks that look like bugs:
 *
 *  - `!empty()` fallback (so '', '0' and 0 all fall back to config)
 *  - `<title>` uses isset() and does NOT fall back to config
 *  - description/keywords/robots/referrer render even when empty
 *  - og:locale is the raw config('app.locale') (e.g. "en", not "en_US")
 *  - og:url is always url()->current(), never the canonical
 *  - fb:app_id is the one self-closing tag
 *  - `author` surfaces only as dcterms.Contributor
 *
 * Additive v2 gains (never overwrite a v1 value): canonical link,
 * og:image:width/height/alt, twitter:image:alt, URL scheme validation.
 *
 * @see tests/fixtures/v1/manager.blade.php  the frozen original
 */
class ExtendedProfile
{
    public function render(array $vars, MetaData $data): string
    {
        $tags = new TagBag();

        $appName = (string) (Config::app('name') ?? '');
        $appLocale = (string) (Config::app('locale') ?? '');
        $current = url()->current();

        $title = $this->value($vars, 'title', $data->title, 'title');
        $description = $this->value($vars, 'description', $data->description, 'description');
        $keywords = $this->value($vars, 'keywords', $data->keywords, 'keywords');
        $author = $this->value($vars, 'author', $data->author, 'author');
        $image = $this->value($vars, 'image', $data->image?->url, 'image');
        $type = $this->value($vars, 'type', $data->type, 'type');
        $robots = $this->value($vars, 'robots', $data->robotsString(), 'robots');
        $referrer = $this->value($vars, 'referrer', $data->referrer, 'referrer');
        $twitterCard = $this->value($vars, 'twitter_card', $data->twitter->get('card'), 'twitter_card');
        $twitterSite = $this->value($vars, 'twitter_site', $data->twitter->get('site'), 'twitter_site');

        // Issue #13: Dublin Core Subject is a topic, not SEO keywords. It is now
        // separately configurable; defaulting to keywords preserves v1 output.
        $subject = $this->optionalTagValue('dcterms_subject', $keywords);

        // --- Standard SEO -------------------------------------------------
        $tags->add(Tag::raw('<!-- Standard SEO -->', 'comment:standard-seo'));

        // v1 used @isset here, not !empty: an explicitly passed '' still
        // renders <title></title>, and there is no config fallback.
        $rawTitle = $this->issetTitle($vars, $data);

        if ($rawTitle !== null) {
            $tags->add(Tag::title($rawTitle));
        }

        $tags->add(Tag::httpEquiv('Content-Type', 'text/html; charset=utf-8'));
        $tags->add(Tag::meta('referrer', $referrer));
        $tags->add(Tag::meta('robots', $robots));
        $tags->add(Tag::meta('description', $description));
        $tags->add(Tag::meta('keywords', $keywords));

        if (Config::get('geo_region') !== '') {
            $tags->add(Tag::meta('geo.region', Config::get('geo_region')));
        }

        if (Config::get('geo_position') !== '') {
            $tags->add(Tag::meta('geo.position', Config::get('geo_position')));
            $tags->add(Tag::meta('ICBM', Config::get('geo_position')));
        }

        // Issue #11: v1 hardcoded config('app.name') here with no way to set or
        // suppress it. geo.placename is a geographic name, so it is now
        // configurable — defaulting to app.name keeps existing output identical.
        $placename = $this->optionalTagValue('geo_placename', $appName);

        if ($placename !== null) {
            $tags->add(Tag::meta('geo.placename', $placename));
        }

        // --- Dublin Core --------------------------------------------------
        $tags->add(Tag::raw('<!-- Dublin Core basic info -->', 'comment:dublin-core'));
        $tags->add(Tag::meta('dcterms.Format', 'text/html'));
        $tags->add(Tag::meta('dcterms.Language', $appLocale));
        $tags->add(Tag::meta('dcterms.Identifier', $current));
        $tags->add(Tag::meta('dcterms.Relation', $appName));
        $tags->add(Tag::meta('dcterms.Publisher', $appName));
        $tags->add(Tag::meta('dcterms.Type', 'text/html'));
        $tags->add(Tag::meta('dcterms.Coverage', $current));
        $tags->add(Tag::meta('dcterms.Title', $title));
        if ($subject !== null) {
            $tags->add(Tag::meta('dcterms.Subject', $subject));
        }
        $tags->add(Tag::meta('dcterms.Contributor', $author));
        $tags->add(Tag::meta('dcterms.Description', $description));

        // --- Open Graph ---------------------------------------------------
        $tags->add(Tag::raw('<!-- Facebook OpenGraph -->', 'comment:opengraph'));
        $tags->add(Tag::property('og:locale', $appLocale));
        $tags->add(Tag::property('og:type', $type));
        $tags->add(Tag::property('og:url', $current));
        $tags->add(Tag::property('og:title', $title));
        $tags->add(Tag::property('og:description', $description));
        $tags->add(Tag::property('og:image', Url::safe($image) ?? ''));
        $tags->add(Tag::property('og:site_name', $appName));

        if (Config::get('fb_app_id') !== '') {
            $tags->add(Tag::property('fb:app_id', Config::get('fb_app_id'), selfClosing: true));
        }

        // --- Twitter ------------------------------------------------------
        $tags->add(Tag::raw('<!-- Twitter Card -->', 'comment:twitter'));

        if (Config::get('twitter_card') !== '') {
            $tags->add(Tag::meta('twitter:card', $twitterCard));
        }

        if (Config::get('twitter_site') !== '' || ! empty($vars['twitter_site'])) {
            $tags->add(Tag::meta('twitter:site', $twitterSite));
        }

        $tags->add(Tag::meta('twitter:title', $title));
        $tags->add(Tag::meta('twitter:description', $description));
        $tags->add(Tag::meta('twitter:image', Url::safe($image) ?? ''));

        $this->addV2Enhancements($tags, $data, $image);

        return $tags->render();
    }

    /**
     * Purely additive. Nothing here may overwrite a tag emitted above, so each
     * one is guarded on being explicitly configured.
     */
    private function addV2Enhancements(TagBag $tags, MetaData $data, string $image): void
    {
        $canonical = $data->canonical ?? $this->configuredCanonical();

        if ($canonical !== null && ($safe = Url::safe($canonical)) !== null) {
            $tags->add(Tag::link(['rel' => 'canonical', 'href' => $safe]));
        }

        $resolved = $data->image ?? MetaImage::make(
            $image,
            width: $this->intConfig('image_width'),
            height: $this->intConfig('image_height'),
            alt: $this->stringConfig('image_alt'),
        );

        if ($resolved === null || $resolved->isEmpty() || $resolved->safeUrl() === null) {
            return;
        }

        $width = $resolved->width ?? $this->intConfig('image_width');
        $height = $resolved->height ?? $this->intConfig('image_height');
        $alt = $resolved->alt ?? $this->stringConfig('image_alt');

        if ($width !== null) {
            $tags->add(Tag::property('og:image:width', (string) $width));
        }

        if ($height !== null) {
            $tags->add(Tag::property('og:image:height', (string) $height));
        }

        if ($alt !== null && $alt !== '') {
            $tags->add(Tag::property('og:image:alt', $alt));
            $tags->add(Tag::meta('twitter:image:alt', $alt));
        }

        foreach ($data->customMeta as $name => $content) {
            $tags->add(Tag::meta($name, $content));
        }

        foreach ($data->customProperties as $property => $content) {
            $tags->add(Tag::property($property, $content));
        }

        foreach ($data->links as $attributes) {
            $tags->add(Tag::link($attributes));
        }
    }

    /**
     * v1 precedence, with fluent state slotted between the include var and
     * config so incremental adoption works without changing the output.
     */
    private function value(array $vars, string $key, ?string $fluent, string $configKey): string
    {
        if (! empty($vars[$key])) {
            return (string) $vars[$key];
        }

        if (! empty($fluent)) {
            return $fluent;
        }

        return (string) (Config::get($configKey) ?? '');
    }

    private function issetTitle(array $vars, MetaData $data): ?string
    {
        if (isset($vars['title'])) {
            return (string) $vars['title'];
        }

        return $data->title;
    }

    /**
     * Resolve a tag whose value may be configured, defaulted, or suppressed.
     *
     * An absent key and an empty string both mean "use the default" — the
     * compatibility suite replaces the whole meta config with v1's fourteen
     * keys, so these arrive as null rather than ''. Only an explicit false
     * suppresses the tag; a fallback that is itself '' still renders, because
     * v1 emitted these tags with empty content.
     *
     * @return string|null  null suppresses the tag entirely
     */
    private function optionalTagValue(string $key, string $fallback): ?string
    {
        $value = Config::get($key);

        if ($value === false) {
            return null;
        }

        return ($value === null || $value === '') ? $fallback : (string) $value;
    }

    private function configuredCanonical(): ?string
    {
        $canonical = Config::get('canonical');

        if ($canonical === 'current') {
            return url()->current();
        }

        return $canonical === '' ? null : $canonical;
    }

    private function intConfig(string $key): ?int
    {
        $value = Config::get($key);

        return ($value === null || $value === '') ? null : (int) $value;
    }

    private function stringConfig(string $key): ?string
    {
        $value = Config::get($key);

        return ($value === null || $value === '') ? null : (string) $value;
    }
}
