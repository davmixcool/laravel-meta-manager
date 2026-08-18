<?php

namespace Davmixcool\MetaManager\Tests;

use Davmixcool\MetaManager\Facades\Meta;
use Davmixcool\MetaManager\MetaServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /**
     * The exact contents of v1's published config/meta.php.
     *
     * An application that ran `vendor:publish` on v1 has precisely this file
     * sitting in its config directory, untouched by the upgrade.
     */
    public const V1_PUBLISHED_CONFIG = [
        'robots' => 'all',
        'revisit_after' => 'period',
        'referrer' => 'no-referrer-when-downgrade',
        'type' => 'website',
        'title' => '',
        'description' => '',
        'image' => '',
        'author' => '',
        'geo_region' => '',
        'geo_position' => '',
        'twitter_site' => '',
        'twitter_card' => 'summary_large_image',
        'fb_app_id' => '',
        'keywords' => '',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        Meta::reset();
    }

    protected function getPackageProviders($app): array
    {
        return [MetaServiceProvider::class];
    }

    protected function getPackageAliases($app): array
    {
        return ['Meta' => Meta::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.name', 'Acme Corp');
        $app['config']->set('app.locale', 'en');
        $app['config']->set('app.url', 'https://example.com');

        // The frozen v1 template renders under its own namespace so the
        // compatibility suite can diff old against new in one process.
        $app['view']->addNamespace('v1', __DIR__.'/fixtures/v1');
    }

    /** Put the app in the state of a v1 user who published the config. */
    protected function useV1PublishedConfig(array $overrides = []): void
    {
        config()->set('meta', array_merge(self::V1_PUBLISHED_CONFIG, $overrides));
    }

    /** Put the app in the state of a v1 user who never published the config. */
    protected function useUnpublishedV1Config(): void
    {
        config()->set('meta', []);
    }

    protected function renderV1(array $vars = []): string
    {
        return view('v1::manager', $vars)->render();
    }

    protected function renderV2Blade(array $vars = []): string
    {
        return (string) Meta::blade($vars);
    }
}
