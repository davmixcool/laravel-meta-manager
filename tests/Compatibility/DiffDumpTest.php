<?php

namespace Davmixcool\MetaManager\Tests\Compatibility;

use Davmixcool\MetaManager\Facades\Meta;
use Davmixcool\MetaManager\Tests\TestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * Dumps real rendered head blocks for eyeball review. Not an assertion suite.
 */
#[Group('dump')]
class DiffDumpTest extends TestCase
{
    private function dump(string $name, string $html): void
    {
        $dir = getenv('META_DUMP_DIR') ?: sys_get_temp_dir();
        $normalised = implode("\n", array_filter(array_map('trim', explode("\n", $html))));
        file_put_contents($dir.'/'.$name, $normalised."\n");
    }

    #[Test]
    public function dump_published_config_cohort(): void
    {
        $vars = [
            'title' => 'My Example Title',
            'description' => 'This is my example description',
            'image' => 'https://example.com/og.png',
        ];

        $this->useV1PublishedConfig();
        $this->dump('published-v1.html', $this->renderV1($vars));

        Meta::reset();
        $this->useV1PublishedConfig();
        $this->dump('published-v2.html', $this->renderV2Blade($vars));

        $this->assertTrue(true);
    }

    #[Test]
    public function dump_unpublished_config_cohort(): void
    {
        $this->useUnpublishedV1Config();
        $this->dump('unpublished-v1.html', $this->renderV1([]));

        Meta::reset();
        config()->set('meta', require __DIR__.'/../../src/config/meta.php');
        $this->dump('unpublished-v2.html', $this->renderV2Blade([]));

        $this->assertTrue(true);
    }
}
