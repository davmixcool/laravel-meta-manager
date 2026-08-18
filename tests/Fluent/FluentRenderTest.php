<?php

namespace Davmixcool\MetaManager\Tests\Fluent;

use Davmixcool\MetaManager\Facades\Meta;
use Davmixcool\MetaManager\Support\Tag;
use Davmixcool\MetaManager\Support\TagBag;
use Davmixcool\MetaManager\Tests\TagSet;
use Davmixcool\MetaManager\Tests\TestCase;
use Illuminate\Support\HtmlString;
use PHPUnit\Framework\Attributes\Test;

class FluentRenderTest extends TestCase
{
    private function render(): TagSet
    {
        return TagSet::parse((string) Meta::render());
    }

    #[Test]
    public function it_renders_the_definition_of_done_example(): void
    {
        Meta::title('SaaSGate - Your SaaS. Built. Launched.')
            ->description('From idea to paying customers, we build the product in between.')
            ->canonical('https://example.com/saasgate')
            ->image('https://example.com/og/saasgate.png', width: 1200, height: 630, alt: 'SaaSGate');

        $tags = $this->render();

        $this->assertSame('SaaSGate - Your SaaS. Built. Launched.', $tags->first('title'));
        $this->assertSame('From idea to paying customers, we build the product in between.', $tags->first('meta:name:description'));
        $this->assertSame('https://example.com/saasgate', $tags->first('link:rel:canonical'));
        $this->assertSame('https://example.com/og/saasgate.png', $tags->first('meta:property:og:image'));
        $this->assertSame('1200', $tags->first('meta:property:og:image:width'));
        $this->assertSame('630', $tags->first('meta:property:og:image:height'));
        $this->assertSame('SaaSGate', $tags->first('meta:property:og:image:alt'));
        $this->assertSame('https://example.com/og/saasgate.png', $tags->first('meta:name:twitter:image'));
    }

    #[Test]
    public function render_returns_an_html_string(): void
    {
        $this->assertInstanceOf(HtmlString::class, Meta::render());
    }

    #[Test]
    public function title_populates_og_and_twitter_titles(): void
    {
        Meta::title('My Page');

        $tags = $this->render();

        $this->assertSame('My Page', $tags->first('title'));
        $this->assertSame('My Page', $tags->first('meta:property:og:title'));
        $this->assertSame('My Page', $tags->first('meta:name:twitter:title'));
    }

    #[Test]
    public function description_populates_og_and_twitter_descriptions(): void
    {
        Meta::description('My description');

        $tags = $this->render();

        $this->assertSame('My description', $tags->first('meta:name:description'));
        $this->assertSame('My description', $tags->first('meta:property:og:description'));
        $this->assertSame('My description', $tags->first('meta:name:twitter:description'));
    }

    #[Test]
    public function the_fallback_hierarchy_runs_twitter_then_og_then_base(): void
    {
        Meta::title('Base Title')->description('Base description');
        Meta::openGraph()->title('OG Title');
        Meta::twitter()->title('Twitter Title');

        $tags = $this->render();

        $this->assertSame('Base Title', $tags->first('title'));
        $this->assertSame('OG Title', $tags->first('meta:property:og:title'));
        $this->assertSame('Twitter Title', $tags->first('meta:name:twitter:title'));

        // Description has no twitter override, so it falls through to OG.
        $this->assertSame('Base description', $tags->first('meta:property:og:description'));
        $this->assertSame('Base description', $tags->first('meta:name:twitter:description'));
    }

    #[Test]
    public function twitter_falls_back_to_the_og_override_when_it_has_none(): void
    {
        Meta::title('Base');
        Meta::openGraph()->title('OG Only');

        $this->assertSame('OG Only', $this->render()->first('meta:name:twitter:title'));
    }

    #[Test]
    public function the_title_template_applies_to_explicit_titles_only(): void
    {
        config()->set('meta.title_template', '%s - SaaSGate');
        config()->set('meta.title', 'SaaSGate');

        Meta::title('Pricing');
        $this->assertSame('Pricing - SaaSGate', $this->render()->first('title'));

        // The config default must NOT be templated, or every page reads
        // "SaaSGate - SaaSGate".
        Meta::reset();
        $this->assertSame('SaaSGate', $this->render()->first('title'));
    }

    #[Test]
    public function the_title_template_can_be_disabled_per_page(): void
    {
        config()->set('meta.title_template', '%s - SaaSGate');

        Meta::title('Home', applyTemplate: false);

        $this->assertSame('Home', $this->render()->first('title'));
    }

    #[Test]
    public function robots_helpers_compose(): void
    {
        Meta::noIndex()->noFollow();
        $this->assertSame('noindex,nofollow', $this->render()->first('meta:name:robots'));

        Meta::reset();
        Meta::robots('index,follow');
        $this->assertSame('index,follow', $this->render()->first('meta:name:robots'));

        // Calling index() after noIndex() replaces rather than appends.
        Meta::reset();
        Meta::noIndex()->index();
        $this->assertSame('index', $this->render()->first('meta:name:robots'));
    }

    #[Test]
    public function og_url_uses_the_canonical_when_one_is_set(): void
    {
        Meta::canonical('https://example.com/real-page');

        $this->assertSame('https://example.com/real-page', $this->render()->first('meta:property:og:url'));
    }

    #[Test]
    public function og_url_falls_back_to_the_current_url(): void
    {
        $this->assertSame(url()->current(), $this->render()->first('meta:property:og:url'));
    }

    #[Test]
    public function canonical_can_be_set_to_current_via_config(): void
    {
        config()->set('meta.canonical', 'current');

        $this->assertSame(url()->current(), $this->render()->first('link:rel:canonical'));
    }

    #[Test]
    public function locale_is_normalised_for_open_graph(): void
    {
        $cases = [
            'en' => 'en_US',
            'ja' => 'ja_JP',
            'fr' => 'fr_FR',
            'sv' => 'sv_SE',
            'pt' => 'pt_PT',
            'en-GB' => 'en_GB',
            'pt_BR' => 'pt_BR',
        ];

        foreach ($cases as $appLocale => $expected) {
            config()->set('app.locale', $appLocale);
            config()->set('meta.locale', '');
            Meta::reset();

            $this->assertSame($expected, $this->render()->first('meta:property:og:locale'), "Locale {$appLocale}");
        }
    }

    #[Test]
    public function an_explicit_locale_overrides_normalisation(): void
    {
        Meta::locale('de_AT');

        $this->assertSame('de_AT', $this->render()->first('meta:property:og:locale'));
    }

    #[Test]
    public function custom_tags_are_supported(): void
    {
        Meta::add('author', 'David Oti')
            ->property('article:published_time', '2026-08-18T10:00:00+00:00')
            ->link('alternate', 'https://example.com/feed.xml', ['type' => 'application/rss+xml'])
            ->favicon('/favicon-32x32.png', ['sizes' => '32x32', 'type' => 'image/png']);

        $tags = $this->render();

        $this->assertSame('David Oti', $tags->first('meta:name:author'));
        $this->assertSame('2026-08-18T10:00:00+00:00', $tags->first('meta:property:article:published_time'));
        $this->assertSame('https://example.com/feed.xml', $tags->first('link:rel:alternate'));
        $this->assertSame('/favicon-32x32.png', $tags->first('link:rel:icon'));
    }

    #[Test]
    public function duplicate_tags_collapse_to_the_last_write(): void
    {
        Meta::title('First')->title('Second');
        Meta::add('author', 'A')->add('author', 'B');

        $tags = $this->render();

        $this->assertCount(1, $tags->values('title'));
        $this->assertSame('Second', $tags->first('title'));
        $this->assertCount(1, $tags->values('meta:name:author'));
        $this->assertSame('B', $tags->first('meta:name:author'));
    }

    #[Test]
    public function repeatable_properties_are_not_collapsed(): void
    {
        // og:image and article:tag legitimately repeat, so their dedup key
        // includes the value. Non-repeatable properties still collapse.
        $bag = new TagBag();
        $bag->add(Tag::property('article:tag', 'laravel'));
        $bag->add(Tag::property('article:tag', 'php'));
        $bag->add(Tag::property('og:image', 'https://example.com/a.png'));
        $bag->add(Tag::property('og:image', 'https://example.com/b.png'));
        $bag->add(Tag::property('og:title', 'First'));
        $bag->add(Tag::property('og:title', 'Second'));

        $tags = TagSet::parse($bag->render());

        $this->assertCount(2, $tags->values('meta:property:article:tag'));
        $this->assertCount(2, $tags->values('meta:property:og:image'));
        $this->assertCount(1, $tags->values('meta:property:og:title'));
        $this->assertSame('Second', $tags->first('meta:property:og:title'));
    }

    #[Test]
    public function multiple_favicons_with_different_sizes_all_survive(): void
    {
        Meta::favicon('/favicon-32x32.png', ['sizes' => '32x32'])
            ->favicon('/favicon-16x16.png', ['sizes' => '16x16']);

        $this->assertCount(2, $this->render()->values('link:rel:icon'));
    }

    #[Test]
    public function values_are_escaped(): void
    {
        Meta::title('<script>alert(1)</script>')
            ->description('"><img src=x onerror=alert(1)>');

        $html = (string) Meta::render();

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('<img', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    #[Test]
    public function unsafe_urls_are_dropped_everywhere(): void
    {
        Meta::canonical('javascript:alert(1)')
            ->image('javascript:alert(1)');

        $tags = $this->render();

        $this->assertFalse($tags->has('link:rel:canonical'));
        $this->assertFalse($tags->has('meta:property:og:image'));
        $this->assertFalse($tags->has('meta:name:twitter:image'));
    }

    #[Test]
    public function twitter_handles_get_an_at_prefix(): void
    {
        Meta::twitter()->site('saasgate')->creator('@davmixcool');

        $tags = $this->render();

        $this->assertSame('@saasgate', $tags->first('meta:name:twitter:site'));
        $this->assertSame('@davmixcool', $tags->first('meta:name:twitter:creator'));
    }

    #[Test]
    public function the_blade_directive_renders_the_same_output(): void
    {
        Meta::title('Directive Test');

        $compiled = \Illuminate\Support\Facades\Blade::compileString('@meta');

        $this->assertStringContainsString("app('meta')->render()", $compiled);
    }

    #[Test]
    public function to_array_exposes_the_resolved_state(): void
    {
        Meta::title('Pricing')
            ->description('Our plans')
            ->canonical('https://example.com/pricing')
            ->image('https://example.com/og.png', width: 1200, height: 630, alt: 'Plans')
            ->noIndex();

        $array = Meta::toArray();

        $this->assertSame('Pricing', $array['title']);
        $this->assertSame('Our plans', $array['description']);
        $this->assertSame('https://example.com/pricing', $array['canonical']);
        $this->assertSame('noindex', $array['robots']);
        $this->assertSame(1200, $array['image']['width']);
        $this->assertSame('Plans', $array['image']['alt']);
        $this->assertArrayHasKey('open_graph', $array);
        $this->assertArrayHasKey('twitter', $array);
    }
}
