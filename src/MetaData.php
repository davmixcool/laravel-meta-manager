<?php

namespace Davmixcool\MetaManager;

use Davmixcool\MetaManager\Support\MetaImage;
use Davmixcool\MetaManager\Support\OpenGraph;
use Davmixcool\MetaManager\Support\TwitterCard;

/**
 * Mutable, request-scoped metadata state.
 *
 * Kept separate from MetaManager so a model can build a detached instance
 * (see the HasMeta direction in the PRD) without touching the container.
 */
class MetaData
{
    public ?string $title = null;
    public bool $applyTitleTemplate = true;
    public ?string $description = null;
    public ?string $canonical = null;
    public ?string $siteName = null;
    public ?string $locale = null;
    public ?string $type = null;
    public ?string $keywords = null;
    public ?string $author = null;
    public ?string $referrer = null;
    public ?MetaImage $image = null;

    /** @var array<int, string> Ordered robots directives. */
    public array $robots = [];

    /** @var array<string, mixed> */
    public array $customMeta = [];

    /** @var array<string, mixed> */
    public array $customProperties = [];

    /** @var array<int, array<string, mixed>> */
    public array $links = [];

    public OpenGraph $openGraph;
    public TwitterCard $twitter;

    public function __construct()
    {
        $this->openGraph = new OpenGraph();
        $this->twitter = new TwitterCard();
    }

    public static function make(): static
    {
        return new static();
    }

    /** Deep clone so a copied MetaData never mutates the original's sub-builders. */
    public function __clone(): void
    {
        $this->openGraph = clone $this->openGraph;
        $this->twitter = clone $this->twitter;
    }

    public function title(?string $title, bool $applyTemplate = true): static
    {
        $this->title = $title;
        $this->applyTitleTemplate = $applyTemplate;

        return $this;
    }

    public function description(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function canonical(?string $canonical): static
    {
        $this->canonical = $canonical;

        return $this;
    }

    public function image(
        MetaImage|string|null $image,
        ?int $width = null,
        ?int $height = null,
        ?string $alt = null,
        ?string $type = null,
    ): static {
        $this->image = MetaImage::make($image, $width, $height, $alt, $type);

        return $this;
    }

    public function siteName(?string $siteName): static
    {
        $this->siteName = $siteName;

        return $this;
    }

    public function locale(?string $locale): static
    {
        $this->locale = $locale;

        return $this;
    }

    public function type(?string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function keywords(string|array|null $keywords): static
    {
        $this->keywords = is_array($keywords) ? implode(', ', $keywords) : $keywords;

        return $this;
    }

    public function author(?string $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function referrer(?string $referrer): static
    {
        $this->referrer = $referrer;

        return $this;
    }

    /** Replaces the whole directive set, e.g. 'index,follow'. */
    public function robots(string|array|null $robots): static
    {
        if ($robots === null) {
            $this->robots = [];

            return $this;
        }

        $directives = is_array($robots) ? $robots : explode(',', $robots);

        $this->robots = array_values(array_filter(array_map(
            static fn ($directive) => strtolower(trim((string) $directive)),
            $directives,
        ), static fn ($directive) => $directive !== ''));

        return $this;
    }

    public function index(): static
    {
        return $this->toggleRobots('index', 'noindex');
    }

    public function noIndex(): static
    {
        return $this->toggleRobots('noindex', 'index');
    }

    public function follow(): static
    {
        return $this->toggleRobots('follow', 'nofollow');
    }

    public function noFollow(): static
    {
        return $this->toggleRobots('nofollow', 'follow');
    }

    public function robotsString(): ?string
    {
        return $this->robots === [] ? null : implode(',', $this->robots);
    }

    public function openGraph(): OpenGraph
    {
        return $this->openGraph;
    }

    public function twitter(): TwitterCard
    {
        return $this->twitter;
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'canonical' => $this->canonical,
            'robots' => $this->robotsString(),
            'image' => $this->image?->toArray(),
            'site_name' => $this->siteName,
            'locale' => $this->locale,
            'type' => $this->type,
            'keywords' => $this->keywords,
            'author' => $this->author,
            'open_graph' => $this->openGraph->toArray(),
            'twitter' => $this->twitter->toArray(),
            'meta' => $this->customMeta,
            'properties' => $this->customProperties,
            'links' => $this->links,
        ];
    }

    private function toggleRobots(string $add, string $remove): static
    {
        $this->robots = array_values(array_filter(
            $this->robots,
            static fn ($directive) => $directive !== $remove && $directive !== $add && $directive !== 'all' && $directive !== 'none',
        ));

        $this->robots[] = $add;

        return $this;
    }
}
