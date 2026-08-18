<?php

namespace Davmixcool\MetaManager\Tests\Compatibility;

use Davmixcool\MetaManager\Facades\Meta;
use Davmixcool\MetaManager\Tests\TagSet;
use Davmixcool\MetaManager\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * The complete set of places where v2 deliberately differs from v1.
 *
 * Anything not asserted here is a regression, not a decision. Each case is
 * mirrored by an entry in UPGRADE.md.
 */
class DocumentedDeltasTest extends TestCase
{
    /** The defaults mergeConfigFrom() supplies. */
    private function packageDefaults(): array
    {
        return require __DIR__.'/../../src/config/meta.php';
    }

    #[Test]
    public function delta_1_unpublished_config_now_receives_documented_defaults(): void
    {
        // v1 had no mergeConfigFrom: an app that never published the config
        // resolved every key as null and rendered empty content attributes.
        $this->useUnpublishedV1Config();
        $v1 = TagSet::parse($this->renderV1([]));

        $this->assertSame('', $v1->first('meta:name:robots'));
        $this->assertSame('', $v1->first('meta:property:og:type'));
        $this->assertSame('', $v1->first('meta:name:twitter:card'));

        // v2 merges the package defaults, so the same app now gets real values.
        config()->set('meta', $this->packageDefaults());
        Meta::reset();
        $v2 = TagSet::parse($this->renderV2Blade([]));

        $this->assertSame('all', $v2->first('meta:name:robots'));
        $this->assertSame('website', $v2->first('meta:property:og:type'));
        $this->assertSame('summary_large_image', $v2->first('meta:name:twitter:card'));
        $this->assertSame('no-referrer-when-downgrade', $v2->first('meta:name:referrer'));
    }

    #[Test]
    public function delta_2_empty_guarded_tags_are_no_longer_emitted_for_that_cohort(): void
    {
        // v1's guards were `config(...) !== ''`. With an unpublished config the
        // value was null, and null !== '' is TRUE, so v1 emitted these tags
        // with empty content.
        $this->useUnpublishedV1Config();
        $v1 = TagSet::parse($this->renderV1([]));

        $this->assertTrue($v1->has('meta:name:geo.region'));
        $this->assertSame('', $v1->first('meta:name:geo.region'));
        $this->assertTrue($v1->has('meta:property:fb:app_id'));
        $this->assertTrue($v1->has('meta:name:twitter:site'));

        // v2's merged default is '', so the same guards now correctly skip them.
        config()->set('meta', $this->packageDefaults());
        Meta::reset();
        $v2 = TagSet::parse($this->renderV2Blade([]));

        $this->assertFalse($v2->has('meta:name:geo.region'));
        $this->assertFalse($v2->has('meta:name:geo.position'));
        $this->assertFalse($v2->has('meta:name:ICBM'));
        $this->assertFalse($v2->has('meta:property:fb:app_id'));
        $this->assertFalse($v2->has('meta:name:twitter:site'));

        // Only ever empty-valued tags. A configured geo block still renders.
        config()->set('meta.geo_region', 'Lagos');
        config()->set('meta.twitter_site', '@acme');
        config()->set('meta.fb_app_id', '123');
        Meta::reset();
        $configured = TagSet::parse($this->renderV2Blade([]));
        $this->assertSame('Lagos', $configured->first('meta:name:geo.region'));
        $this->assertSame('@acme', $configured->first('meta:name:twitter:site'));
        $this->assertSame('123', $configured->first('meta:property:fb:app_id'));
    }

    #[Test]
    public function delta_3_non_http_urls_are_dropped_rather_than_rendered(): void
    {
        $this->useV1PublishedConfig();

        $payload = 'javascript:alert(1)';

        // v1 escaped the value but still emitted a live javascript: URL.
        $v1 = TagSet::parse($this->renderV1(['image' => $payload]));
        $this->assertSame($payload, $v1->first('meta:property:og:image'));

        // v2 refuses to render it.
        Meta::reset();
        $v2 = TagSet::parse($this->renderV2Blade(['image' => $payload]));
        $this->assertSame('', $v2->first('meta:property:og:image'));
        $this->assertSame('', $v2->first('meta:name:twitter:image'));
    }

    #[Test]
    public function safe_urls_of_every_shape_still_render(): void
    {
        $this->useV1PublishedConfig();

        $cases = [
            'https://example.com/og.png',
            'http://example.com/og.png',
            '//cdn.example.com/og.png',
            '/images/og.png',
            'images/og.png',
            'https://example.com/og.png?v=1&x=2',
        ];

        foreach ($cases as $url) {
            Meta::reset();
            $tags = TagSet::parse($this->renderV2Blade(['image' => $url]));

            $this->assertSame($url, $tags->first('meta:property:og:image'), "Safe URL was dropped: {$url}");
        }
    }

    #[Test]
    public function obfuscated_dangerous_urls_are_also_dropped(): void
    {
        $this->useV1PublishedConfig();

        $cases = [
            "java\nscript:alert(1)",
            "java\tscript:alert(1)",
            "JaVaScRiPt:alert(1)",
            "  javascript:alert(1)  ",
            'data:text/html;base64,PHNjcmlwdD5hbGVydCgxKTwvc2NyaXB0Pg==',
            'vbscript:msgbox(1)',
            'file:///etc/passwd',
        ];

        foreach ($cases as $url) {
            Meta::reset();
            $tags = TagSet::parse($this->renderV2Blade(['image' => $url]));

            $this->assertSame('', $tags->first('meta:property:og:image'), 'Dangerous URL was rendered: '.json_encode($url));
        }
    }
}
