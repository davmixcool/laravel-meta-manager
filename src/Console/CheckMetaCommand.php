<?php

namespace Davmixcool\MetaManager\Console;

use Davmixcool\MetaManager\MetaData;
use Davmixcool\MetaManager\Support\Config;
use Davmixcool\MetaManager\Support\MetaImage;
use Davmixcool\MetaManager\Validation\MetaValidator;
use Illuminate\Console\Command;

/**
 * Answers in the terminal first, then offers the metamanager.dev preview.
 *
 * Deliberately makes no network calls: the package must keep working with no
 * external service available (PRD 30).
 */
class CheckMetaCommand extends Command
{
    protected $signature = 'meta:check
                            {url? : A deployed URL to preview on metamanager.dev}';

    protected $description = 'Validate this application\'s metadata defaults and print a MetaManager.dev preview link';

    public function handle(): int
    {
        $data = $this->defaultsFromConfig();

        $this->components->info('Metadata defaults from config/meta.php');

        $this->table(['Key', 'Value'], [
            ['title', $this->display($data->title)],
            ['description', $this->display($data->description)],
            ['image', $this->display($data->image?->url)],
            ['canonical', $this->display(Config::get('canonical'))],
            ['robots', $this->display(Config::get('robots'))],
            ['type', $this->display(Config::get('type'))],
            ['twitter_card', $this->display(Config::get('twitter_card'))],
            ['twitter_site', $this->display(Config::get('twitter_site'))],
        ]);

        $issues = (new MetaValidator())->validate($data);

        if ($issues === []) {
            $this->components->info('No issues found in your metadata defaults.');
        } else {
            $this->newLine();
            $this->components->info(sprintf('%d finding%s', count($issues), count($issues) === 1 ? '' : 's'));

            foreach ($issues as $issue) {
                $this->line(sprintf(
                    '  <fg=%s>%-7s</> %-28s %s',
                    $this->color($issue['level']),
                    $issue['level'],
                    $issue['code'],
                    $issue['message'],
                ));
            }
        }

        $url = $this->argument('url') ?: Config::app('url');

        if (! empty($url)) {
            $this->newLine();
            $this->components->info('Check deployed metadata at:');
            $this->line('  https://metamanager.dev/check?url='.urlencode((string) $url));
        }

        $this->newLine();

        return self::SUCCESS;
    }

    private function defaultsFromConfig(): MetaData
    {
        $data = new MetaData();

        $data->title = $this->nullable(Config::get('title'));
        $data->description = $this->nullable(Config::get('description'));
        $data->keywords = $this->nullable(Config::get('keywords'));
        $data->author = $this->nullable(Config::get('author'));
        $data->canonical = $this->nullable(Config::get('canonical'));
        $data->type = $this->nullable(Config::get('type'));
        $data->robots($this->nullable(Config::get('robots')));

        $data->image = MetaImage::make(
            $this->nullable(Config::get('image')),
            width: $this->nullableInt(Config::get('image_width')),
            height: $this->nullableInt(Config::get('image_height')),
            alt: $this->nullable(Config::get('image_alt')),
        );

        return $data;
    }

    private function color(string $level): string
    {
        return match ($level) {
            MetaValidator::ERROR => 'red',
            MetaValidator::WARNING => 'yellow',
            default => 'cyan',
        };
    }

    private function display(mixed $value): string
    {
        $value = $this->nullable($value);

        return $value === null ? '<fg=gray>(not set)</>' : (string) $value;
    }

    private function nullable(mixed $value): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        return (string) $value;
    }

    private function nullableInt(mixed $value): ?int
    {
        $value = $this->nullable($value);

        return $value === null ? null : (int) $value;
    }
}
