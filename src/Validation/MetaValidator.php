<?php

namespace Davmixcool\MetaManager\Validation;

use Davmixcool\MetaManager\MetaData;
use Davmixcool\MetaManager\Support\Config;
use Davmixcool\MetaManager\Support\Url;

/**
 * Metadata-specific checks only. This is deliberately NOT an SEO audit
 * (PRD 29/36) - it reports on what the package itself can see.
 */
class MetaValidator
{
    public const ERROR = 'error';
    public const WARNING = 'warning';
    public const TIP = 'tip';

    private const TITLE_MAX = 60;
    private const TITLE_MIN = 10;
    private const DESCRIPTION_MAX = 160;
    private const DESCRIPTION_MIN = 50;

    public function validate(MetaData $data): array
    {
        $issues = [];

        $title = $data->title ?? Config::get('title');
        $description = $data->description ?? Config::get('description');
        $image = $data->image;

        if ($this->blank($title)) {
            $issues[] = $this->issue(self::ERROR, 'missing_title', 'No title is set. Every page should have one.');
        } else {
            $length = mb_strlen((string) $title);

            if ($length > self::TITLE_MAX) {
                $issues[] = $this->issue(self::WARNING, 'title_too_long', "Title is {$length} characters; search results truncate around ".self::TITLE_MAX.'.');
            } elseif ($length < self::TITLE_MIN) {
                $issues[] = $this->issue(self::TIP, 'title_too_short', "Title is only {$length} characters.");
            }
        }

        if ($this->blank($description)) {
            $issues[] = $this->issue(self::WARNING, 'missing_description', 'No meta description is set.');
        } else {
            $length = mb_strlen((string) $description);

            if ($length > self::DESCRIPTION_MAX) {
                $issues[] = $this->issue(self::WARNING, 'description_too_long', "Description is {$length} characters; aim for under ".self::DESCRIPTION_MAX.'.');
            } elseif ($length < self::DESCRIPTION_MIN) {
                $issues[] = $this->issue(self::TIP, 'description_too_short', "Description is only {$length} characters.");
            }
        }

        if ($image === null || $image->isEmpty()) {
            $issues[] = $this->issue(self::WARNING, 'missing_image', 'No social image is set; links will preview without a thumbnail.');
        } else {
            if ($image->safeUrl() === null) {
                $issues[] = $this->issue(self::ERROR, 'unsafe_image_url', 'The image URL uses a scheme that is not http(s) and will not be rendered.');
            }

            if ($this->blank($image->alt)) {
                $issues[] = $this->issue(self::TIP, 'missing_image_alt', 'The social image has no alt text.');
            }

            if ($image->width === null || $image->height === null) {
                $issues[] = $this->issue(self::TIP, 'missing_image_dimensions', 'Set image width/height so crawlers can lay out the preview before the image loads.');
            }
        }

        if ($this->blank($data->canonical) && $this->blank(Config::get('canonical'))) {
            $issues[] = $this->issue(self::TIP, 'missing_canonical', 'No canonical URL is set.');
        } elseif (! $this->blank($data->canonical) && ! Url::isSafe($data->canonical)) {
            $issues[] = $this->issue(self::ERROR, 'unsafe_canonical_url', 'The canonical URL uses a scheme that is not http(s) and will not be rendered.');
        }

        $robots = $data->robotsString();

        if ($robots !== null && str_contains($robots, 'noindex')) {
            $issues[] = $this->issue(self::WARNING, 'noindex_set', 'This page is set to noindex and will be excluded from search results.');
        }

        return $issues;
    }

    private function issue(string $level, string $code, string $message): array
    {
        return ['level' => $level, 'code' => $code, 'message' => $message];
    }

    private function blank(mixed $value): bool
    {
        return $value === null || trim((string) $value) === '';
    }
}
