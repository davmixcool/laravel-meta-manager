<?php

namespace Davmixcool\MetaManager\Tests\Fluent;

use Davmixcool\MetaManager\Facades\Meta;
use Davmixcool\MetaManager\MetaManager;
use Davmixcool\MetaManager\Tests\TagSet;
use Davmixcool\MetaManager\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Metadata is request state. Under a long-lived worker (Octane, RoadRunner,
 * Swoole) a plain singleton would carry one page's title onto the next
 * request's response - a data-leak-shaped bug, not just a cosmetic one.
 */
class StateIsolationTest extends TestCase
{
    #[Test]
    public function the_manager_is_bound_as_scoped_not_as_a_plain_singleton(): void
    {
        Meta::title('Request One Title');

        $this->assertSame('Request One Title', $this->app->make('meta')->toArray()['title']);

        // Exactly what Octane does between requests.
        $this->app->forgetScopedInstances();

        $this->assertNull(
            $this->app->make('meta')->toArray()['title'],
            'Metadata leaked across a simulated request boundary; the binding is not scoped.'
        );
    }

    #[Test]
    public function a_fresh_scope_renders_without_the_previous_requests_tags(): void
    {
        Meta::title('Leaky Title')->description('Leaky description');
        $first = TagSet::parse((string) Meta::render());
        $this->assertSame('Leaky Title', $first->first('title'));

        $this->app->forgetScopedInstances();

        $second = TagSet::parse((string) $this->app->make('meta')->render());

        $this->assertFalse($second->has('title'));
        $this->assertFalse($second->has('meta:name:description'));
    }

    #[Test]
    public function the_duplicate_render_guard_also_resets_between_requests(): void
    {
        Meta::title('One');
        $this->assertNotEmpty(trim((string) Meta::render()));
        // Guard active within the same request.
        $this->assertSame('', trim((string) Meta::render()));

        $this->app->forgetScopedInstances();

        // ...but a new request renders normally again.
        $manager = $this->app->make('meta');
        $manager->title('Two');
        $this->assertNotEmpty(trim((string) $manager->render()));
    }

    #[Test]
    public function reset_clears_state_and_the_render_guard(): void
    {
        Meta::title('Before')->description('Before')->image('https://example.com/a.png');
        Meta::render();

        Meta::reset();

        $this->assertNull(Meta::toArray()['title']);
        $this->assertNull(Meta::toArray()['image']);
        $this->assertNotEmpty(trim((string) Meta::render()));
    }

    #[Test]
    public function sub_builders_do_not_leak_between_cloned_states(): void
    {
        Meta::twitter()->site('@original');

        $clone = clone Meta::data();
        $clone->twitter->site('@modified');

        // Mutating the clone must not reach back into the live state.
        $this->assertSame('@original', Meta::data()->twitter->get('site'));
        $this->assertSame('@modified', $clone->twitter->get('site'));
    }

    #[Test]
    public function a_replaced_data_object_is_used_for_rendering(): void
    {
        $data = (new \Davmixcool\MetaManager\MetaData())
            ->title('From a model')
            ->description('Built elsewhere');

        $this->app->make(MetaManager::class)->use($data);

        $tags = TagSet::parse((string) Meta::render());

        $this->assertSame('From a model', $tags->first('title'));
        $this->assertSame('Built elsewhere', $tags->first('meta:name:description'));
    }
}
