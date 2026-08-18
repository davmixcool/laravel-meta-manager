<?php

namespace Davmixcool\MetaManager\Tests\Fluent;

use Davmixcool\MetaManager\Facades\Meta;
use Davmixcool\MetaManager\Tests\TestCase;
use Davmixcool\MetaManager\Validation\MetaValidator;
use PHPUnit\Framework\Attributes\Test;

class ValidatorTest extends TestCase
{
    private function codes(): array
    {
        return array_column(Meta::validate(), 'code');
    }

    #[Test]
    public function it_reports_missing_metadata(): void
    {
        $codes = $this->codes();

        $this->assertContains('missing_title', $codes);
        $this->assertContains('missing_description', $codes);
        $this->assertContains('missing_image', $codes);
        $this->assertContains('missing_canonical', $codes);
    }

    #[Test]
    public function a_complete_page_reports_nothing(): void
    {
        Meta::title('A perfectly reasonable page title')
            ->description('A description of a sensible length that sits comfortably inside the recommended range for search results.')
            ->canonical('https://example.com/page')
            ->image('https://example.com/og.png', width: 1200, height: 630, alt: 'Preview');

        $this->assertSame([], Meta::validate());
    }

    #[Test]
    public function it_flags_lengths_outside_the_recommended_range(): void
    {
        Meta::title(str_repeat('Long title ', 20))
            ->description(str_repeat('Long description ', 30));

        $codes = $this->codes();

        $this->assertContains('title_too_long', $codes);
        $this->assertContains('description_too_long', $codes);
    }

    #[Test]
    public function it_flags_image_gaps_separately(): void
    {
        Meta::title('A perfectly reasonable page title')
            ->description('A description of a sensible length that sits comfortably inside the recommended range for search results.')
            ->canonical('https://example.com/page')
            ->image('https://example.com/og.png');

        $codes = $this->codes();

        $this->assertContains('missing_image_alt', $codes);
        $this->assertContains('missing_image_dimensions', $codes);
        $this->assertNotContains('missing_image', $codes);
    }

    #[Test]
    public function it_flags_unsafe_urls_as_errors(): void
    {
        Meta::canonical('javascript:alert(1)')->image('javascript:alert(1)');

        $issues = collect(Meta::validate())->keyBy('code');

        $this->assertSame(MetaValidator::ERROR, $issues['unsafe_canonical_url']['level']);
        $this->assertSame(MetaValidator::ERROR, $issues['unsafe_image_url']['level']);
    }

    #[Test]
    public function it_warns_when_a_page_is_set_to_noindex(): void
    {
        Meta::noIndex();

        $this->assertContains('noindex_set', $this->codes());
    }

    #[Test]
    public function every_issue_carries_a_level_code_and_message(): void
    {
        foreach (Meta::validate() as $issue) {
            $this->assertArrayHasKey('level', $issue);
            $this->assertArrayHasKey('code', $issue);
            $this->assertArrayHasKey('message', $issue);
            $this->assertContains($issue['level'], [MetaValidator::ERROR, MetaValidator::WARNING, MetaValidator::TIP]);
        }
    }
}
