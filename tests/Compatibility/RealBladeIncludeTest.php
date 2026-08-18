<?php

namespace Davmixcool\MetaManager\Tests\Compatibility;

use Davmixcool\MetaManager\Tests\TagSet;
use Davmixcool\MetaManager\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Exercises the shim through real Blade compilation.
 *
 * The shim relies on get_defined_vars() inside a compiled view, so calling
 * Meta::blade() directly does not prove the include path works. These render
 * an actual layout the way a v1 application does.
 */
class RealBladeIncludeTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['view']->addNamespace('app', __DIR__.'/../fixtures/app');
    }

    #[Test]
    public function the_readme_example_layout_renders_correctly(): void
    {
        $this->useV1PublishedConfig();

        $tags = TagSet::parse(view('app::layout')->render());

        $this->assertSame('My Example Title', $tags->first('title'));
        $this->assertSame('This is my example description', $tags->first('meta:name:description'));
        $this->assertSame('https://example.com/og.png', $tags->first('meta:property:og:image'));
        $this->assertSame('My Example Title', $tags->first('meta:name:twitter:title'));
        $this->assertSame('Acme Corp', $tags->first('meta:property:og:site_name'));
    }

    #[Test]
    public function the_include_works_with_no_variables_at_all(): void
    {
        $this->useV1PublishedConfig(['title' => 'Config Title']);

        $tags = TagSet::parse(view('app::layout-no-vars')->render());

        $this->assertSame('Config Title', $tags->first('meta:property:og:title'));
        // No variable passed and none in scope, so @isset was false in v1 too.
        $this->assertFalse($tags->has('title'));
    }

    #[Test]
    public function parent_scope_variables_reach_the_include_exactly_as_in_v1(): void
    {
        $this->useV1PublishedConfig();

        // Blade shares the parent scope with @include, so a $title defined on
        // the parent view was visible to v1's template. That behaviour is
        // preserved rather than "fixed".
        $tags = TagSet::parse(
            view('app::layout-parent-scope', ['title' => 'From Parent Scope'])->render()
        );

        $this->assertSame('From Parent Scope', $tags->first('title'));
        $this->assertSame('From Parent Scope', $tags->first('meta:property:og:title'));
    }

    #[Test]
    public function the_rendered_layout_contains_no_leaked_internals(): void
    {
        $this->useV1PublishedConfig();

        $html = view('app::layout')->render();

        foreach (['__env', '__data', 'Illuminate\\', 'obLevel', '__path'] as $needle) {
            $this->assertStringNotContainsString($needle, $html, "Blade internals leaked: {$needle}");
        }
    }

    #[Test]
    public function the_v1_and_v2_layouts_agree_through_real_blade(): void
    {
        $this->useV1PublishedConfig();

        $vars = [
            'title' => 'My Example Title',
            'description' => 'This is my example description',
            'image' => 'https://example.com/og.png',
        ];

        $v1 = TagSet::parse($this->renderV1($vars));
        $v2 = TagSet::parse(view('app::layout')->render());

        $this->assertSame([], $v2->missingFrom($v1));
    }
}
