<?php

namespace Davmixcool\MetaManager\Tests\Fluent;

use Davmixcool\MetaManager\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CheckCommandTest extends TestCase
{
    #[Test]
    public function it_reports_findings_in_the_terminal(): void
    {
        config()->set('meta.title', '');
        config()->set('meta.description', '');

        $this->artisan('meta:check')
            ->expectsOutputToContain('missing_title')
            ->expectsOutputToContain('missing_description')
            ->assertSuccessful();
    }

    #[Test]
    public function it_prints_a_metamanager_dev_link_for_the_given_url(): void
    {
        $this->artisan('meta:check', ['url' => 'https://example.com/pricing'])
            ->expectsOutputToContain('https://metamanager.dev/check?url=https%3A%2F%2Fexample.com%2Fpricing')
            ->assertSuccessful();
    }

    #[Test]
    public function it_falls_back_to_the_app_url(): void
    {
        $this->artisan('meta:check')
            ->expectsOutputToContain('metamanager.dev/check')
            ->assertSuccessful();
    }

    #[Test]
    public function it_reports_a_clean_bill_of_health(): void
    {
        config()->set('meta.title', 'A perfectly reasonable page title');
        config()->set('meta.description', 'A description of a sensible length that sits comfortably inside the recommended range for search results.');
        config()->set('meta.image', 'https://example.com/og.png');
        config()->set('meta.image_width', 1200);
        config()->set('meta.image_height', 630);
        config()->set('meta.image_alt', 'Preview');
        config()->set('meta.canonical', 'https://example.com');

        $this->artisan('meta:check')
            ->expectsOutputToContain('No issues found')
            ->assertSuccessful();
    }
}
