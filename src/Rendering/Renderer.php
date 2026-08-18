<?php

namespace Davmixcool\MetaManager\Rendering;

use Davmixcool\MetaManager\MetaData;
use Davmixcool\MetaManager\Support\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;

/**
 * Picks a tag profile for an entry point, and guards against rendering twice in
 * one request (a layout using @include plus a stray Meta::render() would
 * otherwise emit two competing sets of tags).
 *
 * Entry points are `blade()` and `fluent()`. Tag sets are extended and standard.
 * The two are independent: the Blade include renders the extended set by
 * default, but can be pointed at the standard set with the `blade_tags` config.
 */
class Renderer
{
    private bool $hasRendered = false;

    public function __construct(
        private readonly ExtendedProfile $extended = new ExtendedProfile(),
        private readonly StandardProfile $standard = new StandardProfile(),
    ) {
    }

    /** Entry point for `Meta::render()`. Always the standard tag set. */
    public function fluent(MetaData $data): HtmlString
    {
        if ($this->shouldSkip('Meta::render()')) {
            return new HtmlString('');
        }

        return new HtmlString($this->standard->render($data));
    }

    /** Entry point for `@include('meta::manager')`. */
    public function blade(array $vars, MetaData $data): HtmlString
    {
        if ($this->shouldSkip("@include('meta::manager')")) {
            return new HtmlString('');
        }

        // Only an explicit 'standard' switches tag sets. Any other value falls
        // through to the extended set, so a typo cannot silently drop tags from
        // a page that was relying on them.
        if (Config::get('blade_tags') === 'standard') {
            return new HtmlString($this->standard->render($this->mergeBladeVars($vars, $data)));
        }

        return new HtmlString($this->extended->render($vars, $data));
    }

    public function reset(): void
    {
        $this->hasRendered = false;
    }

    /**
     * Folds include variables into MetaData so the standard profile can render
     * them when `blade_tags` is set to 'standard'.
     */
    private function mergeBladeVars(array $vars, MetaData $data): MetaData
    {
        $merged = clone $data;

        foreach (['title', 'description', 'keywords', 'author', 'type', 'referrer'] as $key) {
            if (! empty($vars[$key])) {
                $merged->{$key} = (string) $vars[$key];
            }
        }

        if (! empty($vars['image'])) {
            $merged->image((string) $vars['image']);
        }

        if (! empty($vars['robots'])) {
            $merged->robots((string) $vars['robots']);
        }

        if (! empty($vars['twitter_card'])) {
            $merged->twitter->card((string) $vars['twitter_card']);
        }

        if (! empty($vars['twitter_site'])) {
            $merged->twitter->site((string) $vars['twitter_site']);
        }

        return $merged;
    }

    private function shouldSkip(string $source): bool
    {
        if (! $this->hasRendered) {
            $this->hasRendered = true;

            return false;
        }

        if (Config::get('prevent_duplicate_render', true) !== true) {
            return false;
        }

        if (Config::app('debug') === true) {
            Log::warning(
                "[meta-manager] Metadata was already rendered for this request; {$source} produced nothing. ".
                'Render metadata once per page, or set meta.prevent_duplicate_render to false.'
            );
        }

        return true;
    }
}
