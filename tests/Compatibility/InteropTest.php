<?php

namespace Davmixcool\MetaManager\Tests\Compatibility;

use Davmixcool\MetaManager\Facades\Meta;
use Davmixcool\MetaManager\MetaManager;
use Davmixcool\MetaManager\MetaServiceProvider;
use Davmixcool\MetaManager\Tests\TagSet;
use Davmixcool\MetaManager\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * The upgrade path: v1 entry points keep working, and a v1 application can
 * adopt the fluent API one controller at a time without touching its layout.
 */
class InteropTest extends TestCase
{
    #[Test]
    public function the_v1_service_provider_class_still_exists_at_its_documented_location(): void
    {
        // v1's README tells users to add this exact FQCN to config/app.php.
        $this->assertTrue(class_exists(MetaServiceProvider::class));
        $this->assertSame(
            'Davmixcool\MetaManager\MetaServiceProvider',
            MetaServiceProvider::class,
        );
    }

    #[Test]
    public function the_v1_view_namespace_still_resolves(): void
    {
        $this->assertTrue(view()->exists('meta::manager'));
    }

    #[Test]
    public function the_v1_include_renders_without_any_variables(): void
    {
        $this->useV1PublishedConfig();

        $html = view()->make('meta::manager')->render();

        $this->assertNotEmpty(TagSet::parse($html)->keys());
    }

    #[Test]
    public function fluent_state_is_picked_up_by_the_blade_include(): void
    {
        $this->useV1PublishedConfig();

        // A v1 app adds this to a controller and changes nothing in its Blade.
        Meta::title('Set Fluently')->description('Fluent description');

        $tags = TagSet::parse($this->renderV2Blade([]));

        $this->assertSame('Set Fluently', $tags->first('meta:property:og:title'));
        $this->assertSame('Fluent description', $tags->first('meta:name:description'));
        $this->assertSame('Set Fluently', $tags->first('meta:name:twitter:title'));
    }

    #[Test]
    public function include_variables_win_over_fluent_state(): void
    {
        $this->useV1PublishedConfig(['title' => 'Config Title']);

        Meta::title('Fluent Title');

        $tags = TagSet::parse($this->renderV2Blade(['title' => 'Include Title']));

        // Precedence: include var > fluent > config.
        $this->assertSame('Include Title', $tags->first('meta:property:og:title'));
    }

    #[Test]
    public function fluent_state_wins_over_config(): void
    {
        $this->useV1PublishedConfig(['title' => 'Config Title']);

        Meta::title('Fluent Title');

        $tags = TagSet::parse($this->renderV2Blade([]));

        $this->assertSame('Fluent Title', $tags->first('meta:property:og:title'));
    }

    #[Test]
    public function blade_output_gains_image_dimensions_without_losing_the_image_tag(): void
    {
        $this->useV1PublishedConfig();

        Meta::image('https://example.com/og.png', width: 1200, height: 630, alt: 'Acme');

        $tags = TagSet::parse($this->renderV2Blade([]));

        // The v1 tag is untouched...
        $this->assertSame('https://example.com/og.png', $tags->first('meta:property:og:image'));
        // ...and the v2 detail tags are added alongside it.
        $this->assertSame('1200', $tags->first('meta:property:og:image:width'));
        $this->assertSame('630', $tags->first('meta:property:og:image:height'));
        $this->assertSame('Acme', $tags->first('meta:property:og:image:alt'));
        $this->assertSame('Acme', $tags->first('meta:name:twitter:image:alt'));
    }

    #[Test]
    public function image_dimensions_can_come_from_config_alone(): void
    {
        $this->useV1PublishedConfig([
            'image' => 'https://example.com/config-og.png',
            'image_width' => 1200,
            'image_height' => 630,
            'image_alt' => 'From config',
        ]);

        $tags = TagSet::parse($this->renderV2Blade([]));

        $this->assertSame('1200', $tags->first('meta:property:og:image:width'));
        $this->assertSame('From config', $tags->first('meta:property:og:image:alt'));
    }

    #[Test]
    public function blade_users_can_opt_into_the_standard_tag_set_via_config(): void
    {
        $this->useV1PublishedConfig([
            'blade_tags' => 'standard',
        ]);

        $tags = TagSet::parse($this->renderV2Blade([
            'title' => 'Modernised',
            'description' => 'Now on the v2 tag set',
            'image' => 'https://example.com/og.png',
        ]));

        // The extended-only tags are gone...
        $this->assertFalse($tags->has('meta:name:dcterms.Title'));
        $this->assertFalse($tags->has('meta:name:geo.placename'));
        // ...and the modern equivalents are present.
        $this->assertSame('Modernised', $tags->first('title'));
        $this->assertSame('Modernised', $tags->first('meta:property:og:title'));
        $this->assertSame('en_US', $tags->first('meta:property:og:locale'));
    }

    #[Test]
    public function an_unrecognised_blade_tags_value_falls_back_to_the_extended_set(): void
    {
        // Failing safe matters here: a typo must not silently strip Dublin Core,
        // geo and keywords tags from pages that were relying on them.
        foreach (['extended', 'nonsense', '', null, true, 'STANDARD'] as $value) {
            $this->useV1PublishedConfig(['blade_tags' => $value]);
            Meta::reset();

            $tags = TagSet::parse($this->renderV2Blade(['title' => 'Fail safe']));

            $this->assertTrue(
                $tags->has('meta:name:dcterms.Title'),
                'Extended tags were dropped for blade_tags value: '.json_encode($value)
            );
        }

        // Only the exact string switches sets.
        $this->useV1PublishedConfig(['blade_tags' => 'standard']);
        Meta::reset();

        $this->assertFalse(
            TagSet::parse($this->renderV2Blade(['title' => 'Standard']))->has('meta:name:dcterms.Title')
        );
    }

    #[Test]
    public function metadata_renders_only_once_per_request(): void
    {
        $this->useV1PublishedConfig();

        $first = $this->renderV2Blade(['title' => 'First']);
        $second = $this->renderV2Blade(['title' => 'Second']);

        $this->assertNotEmpty(TagSet::parse($first)->keys());
        $this->assertSame('', trim($second), 'A second render should emit nothing.');
    }

    #[Test]
    public function the_duplicate_render_guard_can_be_disabled(): void
    {
        $this->useV1PublishedConfig(['prevent_duplicate_render' => false]);

        $this->renderV2Blade(['title' => 'First']);
        $second = $this->renderV2Blade(['title' => 'Second']);

        $this->assertNotEmpty(TagSet::parse($second)->keys());
    }

    #[Test]
    public function mixing_the_blade_include_with_render_does_not_double_up(): void
    {
        $this->useV1PublishedConfig();

        $this->renderV2Blade(['title' => 'From include']);
        $modern = (string) Meta::render();

        $this->assertSame('', trim($modern));
    }

    #[Test]
    public function blade_scope_leakage_is_filtered_to_v1_documented_variables(): void
    {
        $this->useV1PublishedConfig();

        // get_defined_vars() in the shim hands over the whole Blade scope.
        $tags = TagSet::parse($this->renderV2Blade([
            'title' => 'Real Title',
            '__env' => 'should be ignored',
            'errors' => 'should be ignored',
            'app' => 'should be ignored',
            'unrelated' => 'should be ignored',
        ]));

        $this->assertSame('Real Title', $tags->first('title'));
        $this->assertNotContains('should be ignored', array_merge(...array_values($tags->all())));
    }

    #[Test]
    public function the_manager_is_resolvable_by_both_binding_names(): void
    {
        $this->assertInstanceOf(MetaManager::class, $this->app->make('meta'));
        $this->assertInstanceOf(MetaManager::class, $this->app->make(MetaManager::class));
        $this->assertSame($this->app->make('meta'), $this->app->make(MetaManager::class));
    }
}
