<?php

namespace Davmixcool\MetaManager\Tests\Compatibility;

use Davmixcool\MetaManager\Facades\Meta;
use Davmixcool\MetaManager\Tests\TagSet;
use Davmixcool\MetaManager\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

/**
 * The backwards-compatibility contract.
 *
 * Renders the frozen v1 template and the v2 extended profile side by side and
 * asserts v2 is a strict SUPERSET: every tag v1 produced is still produced,
 * with an identical value.
 */
class BladeOutputTest extends TestCase
{
    public static function scenarios(): array
    {
        return [
            'no variables at all' => [[], []],

            'all ten documented variables' => [[
                'title' => 'My Example Title',
                'description' => 'This is my example description',
                'keywords' => 'laravel, meta, seo',
                'author' => 'David Oti',
                'image' => 'https://example.com/og.png',
                'type' => 'article',
                'robots' => 'index,follow',
                'referrer' => 'origin',
                'twitter_card' => 'summary',
                'twitter_site' => '@davmixcool',
            ], []],

            // v1 used !empty(), so '' falls back to config on every field
            // except <title>, which used isset() and renders empty.
            'empty string variables' => [[
                'title' => '',
                'description' => '',
                'keywords' => '',
                'author' => '',
                'image' => '',
                'type' => '',
                'robots' => '',
                'referrer' => '',
                'twitter_card' => '',
                'twitter_site' => '',
            ], []],

            // "0" is falsy in PHP: !empty('0') === false, so it falls back.
            'literal zero string variables' => [[
                'title' => '0',
                'description' => '0',
                'keywords' => '0',
                'image' => '0',
                'type' => '0',
            ], []],

            'title omitted entirely' => [[
                'description' => 'Only a description here',
            ], []],

            'html and script payloads' => [[
                'title' => '<script>alert(1)</script>',
                'description' => '"><img src=x onerror=alert(1)>',
                'keywords' => "single ' and double \" quotes",
                'author' => 'A & B <b>bold</b>',
                'image' => 'https://example.com/og.png?a=1&b=2',
            ], []],

            'unicode and emoji' => [[
                'title' => 'Ünïcødé — 日本語 — 🚀',
                'description' => 'Ünïcødé description with émojis 🎉 and ünusual characters',
            ], []],

            'very long values' => [[
                'title' => 'A very long title '.str_repeat('and more words ', 40),
                'description' => str_repeat('A long description sentence. ', 40),
            ], []],

            'newlines inside values' => [[
                'title' => "Line one\nLine two",
                'description' => "Para one\r\n\r\nPara two",
            ], []],

            'geo configured' => [[], [
                'geo_region' => 'Lagos',
                'geo_position' => '4.870467,6.993388',
            ]],

            'facebook app id configured' => [[], [
                'fb_app_id' => '1234567890',
            ]],

            'twitter site configured' => [[], [
                'twitter_site' => '@acme',
            ]],

            // v1 skipped the twitter:card tag entirely when config was ''.
            'twitter card disabled in config' => [[], [
                'twitter_card' => '',
            ]],

            'config defaults populated' => [[], [
                'title' => 'Config Title',
                'description' => 'Config description',
                'image' => 'https://example.com/config-og.png',
                'keywords' => 'config, keywords',
                'author' => 'Config Author',
                'type' => 'profile',
                'robots' => 'noindex,nofollow',
                'referrer' => 'origin',
            ]],

            'variables override config' => [[
                'title' => 'Page Title',
                'description' => 'Page description',
            ], [
                'title' => 'Config Title',
                'description' => 'Config description',
            ]],
        ];
    }

    #[Test]
    #[DataProvider('scenarios')]
    public function v2_blade_output_is_a_superset_of_v1(array $vars, array $configOverrides): void
    {
        $this->useV1PublishedConfig($configOverrides);

        $v1 = TagSet::parse($this->renderV1($vars));
        $v2 = TagSet::parse($this->renderV2Blade($vars));

        $differences = $v2->missingFrom($v1);

        $this->assertSame(
            [],
            $differences,
            "v2 Blade output is not a superset of v1:\n  ".implode("\n  ", $differences)."\n"
        );
    }

    #[Test]
    #[DataProvider('scenarios')]
    public function v1_always_produced_at_least_one_tag(array $vars, array $configOverrides): void
    {
        $this->useV1PublishedConfig($configOverrides);

        // Guards the suite itself: a fixture that silently renders nothing
        // would make every superset assertion vacuously pass.
        $this->assertNotEmpty(
            TagSet::parse($this->renderV1($vars))->keys(),
            'The frozen v1 fixture produced no tags, so the comparison proves nothing.'
        );
    }

    #[Test]
    public function the_full_v1_tag_vocabulary_is_still_emitted(): void
    {
        $this->useV1PublishedConfig([
            'geo_region' => 'Lagos',
            'geo_position' => '4.870467,6.993388',
            'fb_app_id' => '1234567890',
            'twitter_site' => '@acme',
        ]);

        $vars = [
            'title' => 'Title',
            'description' => 'Description',
            'keywords' => 'a, b',
            'author' => 'David Oti',
            'image' => 'https://example.com/og.png',
        ];

        $v2 = TagSet::parse($this->renderV2Blade($vars));

        $expected = [
            'title',
            'meta:http-equiv:Content-Type',
            'meta:name:referrer',
            'meta:name:robots',
            'meta:name:description',
            'meta:name:keywords',
            'meta:name:geo.region',
            'meta:name:geo.position',
            'meta:name:ICBM',
            'meta:name:geo.placename',
            'meta:name:dcterms.Format',
            'meta:name:dcterms.Language',
            'meta:name:dcterms.Identifier',
            'meta:name:dcterms.Relation',
            'meta:name:dcterms.Publisher',
            'meta:name:dcterms.Type',
            'meta:name:dcterms.Coverage',
            'meta:name:dcterms.Title',
            'meta:name:dcterms.Subject',
            'meta:name:dcterms.Contributor',
            'meta:name:dcterms.Description',
            'meta:property:og:locale',
            'meta:property:og:type',
            'meta:property:og:url',
            'meta:property:og:title',
            'meta:property:og:description',
            'meta:property:og:image',
            'meta:property:og:site_name',
            'meta:property:fb:app_id',
            'meta:name:twitter:card',
            'meta:name:twitter:site',
            'meta:name:twitter:title',
            'meta:name:twitter:description',
            'meta:name:twitter:image',
        ];

        foreach ($expected as $key) {
            $this->assertTrue($v2->has($key), "v2 Blade output is missing the v1 tag: {$key}");
        }
    }

    #[Test]
    public function blade_include_quirks_are_preserved(): void
    {
        $this->useV1PublishedConfig(['title' => 'Config Title']);

        // <title> used @isset, so an explicit '' renders an empty title tag
        // and does NOT fall back to config...
        $explicitlyEmpty = TagSet::parse($this->renderV2Blade(['title' => '']));
        $this->assertSame('', $explicitlyEmpty->first('title'));

        // ...while dcterms.Title on the same render used !empty() and does.
        $this->assertSame('Config Title', $explicitlyEmpty->first('meta:name:dcterms.Title'));

        // Omitting the variable emits no title tag at all.
        $this->app->make('meta')->reset();
        $omitted = TagSet::parse($this->renderV2Blade([]));
        $this->assertFalse($omitted->has('title'));

        // og:url is the current URL, never the canonical (v1 behaviour).
        $this->app->make('meta')->reset();
        $current = TagSet::parse($this->renderV2Blade([]));
        $this->assertSame(url()->current(), $current->first('meta:property:og:url'));

        // og:locale stays the raw app locale, not the normalised en_US.
        $this->assertSame('en', $current->first('meta:property:og:locale'));

        // author surfaces only as dcterms.Contributor - v1 had no meta[name=author].
        $this->app->make('meta')->reset();
        $withAuthor = TagSet::parse($this->renderV2Blade(['author' => 'David Oti']));
        $this->assertSame('David Oti', $withAuthor->first('meta:name:dcterms.Contributor'));
        $this->assertFalse($withAuthor->has('meta:name:author'));
    }

    #[Test]
    public function og_url_stays_the_current_url_even_when_a_canonical_is_set(): void
    {
        $this->useV1PublishedConfig();

        // Pointing og:url at the canonical is the "obvious" v2 improvement, but
        // it would CHANGE an existing v1 tag's value. The extended set keeps url()->current()
        // and gains the canonical as a separate <link> instead.
        Meta::canonical('https://example.com/canonical-target');

        $tags = TagSet::parse($this->renderV2Blade([]));

        $this->assertSame(url()->current(), $tags->first('meta:property:og:url'));
        $this->assertSame('https://example.com/canonical-target', $tags->first('link:rel:canonical'));
    }

    #[Test]
    public function config_driven_canonical_also_leaves_og_url_alone(): void
    {
        $this->useV1PublishedConfig(['canonical' => 'current']);

        $tags = TagSet::parse($this->renderV2Blade([]));

        $this->assertSame(url()->current(), $tags->first('meta:property:og:url'));
        $this->assertSame(url()->current(), $tags->first('link:rel:canonical'));
    }

    #[Test]
    public function escaping_matches_v1_exactly(): void
    {
        $this->useV1PublishedConfig();

        $payload = '<script>alert("xss")</script> & \'quotes\'';

        $v1Raw = $this->renderV1(['title' => $payload, 'description' => $payload]);
        $v2Raw = $this->renderV2Blade(['title' => $payload, 'description' => $payload]);

        // No executable markup survives in either.
        $this->assertStringNotContainsString('<script>', $v2Raw);

        // And the escaped byte sequence is identical to v1's.
        $this->assertStringContainsString('&lt;script&gt;', $v1Raw);
        $this->assertStringContainsString('&lt;script&gt;', $v2Raw);

        $v1Tags = TagSet::parse($v1Raw);
        $v2Tags = TagSet::parse($v2Raw);

        $this->assertSame($v1Tags->first('meta:name:description'), $v2Tags->first('meta:name:description'));
        $this->assertSame($v1Tags->first('title'), $v2Tags->first('title'));
    }
}
