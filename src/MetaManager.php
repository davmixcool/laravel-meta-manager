<?php

namespace Davmixcool\MetaManager;

use Davmixcool\MetaManager\Rendering\Renderer;
use Davmixcool\MetaManager\Support\MetaImage;
use Davmixcool\MetaManager\Support\OpenGraph;
use Davmixcool\MetaManager\Support\TwitterCard;
use Davmixcool\MetaManager\Validation\MetaValidator;
use Illuminate\Support\HtmlString;

/**
 * The `Meta` facade root. Request-scoped: the service provider binds this with
 * scoped(), so state never leaks between requests under Octane.
 */
class MetaManager
{
    /**
     * The variables `@include('meta::manager')` accepts.
     *
     * This is part of the v1 contract and must not change.
     */
    public const INCLUDE_VARS = [
        'title', 'description', 'keywords', 'author', 'image',
        'type', 'robots', 'referrer', 'twitter_card', 'twitter_site',
    ];

    protected MetaData $data;

    public function __construct(protected Renderer $renderer = new Renderer())
    {
        $this->data = new MetaData();
    }

    // --- Fluent API ------------------------------------------------------

    public function title(?string $title, bool $applyTemplate = true): static
    {
        $this->data->title($title, $applyTemplate);

        return $this;
    }

    public function description(?string $description): static
    {
        $this->data->description($description);

        return $this;
    }

    public function canonical(?string $canonical): static
    {
        $this->data->canonical($canonical);

        return $this;
    }

    public function image(
        MetaImage|string|null $image,
        ?int $width = null,
        ?int $height = null,
        ?string $alt = null,
        ?string $type = null,
    ): static {
        $this->data->image($image, $width, $height, $alt, $type);

        return $this;
    }

    public function robots(string|array|null $robots): static
    {
        $this->data->robots($robots);

        return $this;
    }

    public function index(): static
    {
        $this->data->index();

        return $this;
    }

    public function noIndex(): static
    {
        $this->data->noIndex();

        return $this;
    }

    public function follow(): static
    {
        $this->data->follow();

        return $this;
    }

    public function noFollow(): static
    {
        $this->data->noFollow();

        return $this;
    }

    public function siteName(?string $siteName): static
    {
        $this->data->siteName($siteName);

        return $this;
    }

    public function locale(?string $locale): static
    {
        $this->data->locale($locale);

        return $this;
    }

    public function type(?string $type): static
    {
        $this->data->type($type);

        return $this;
    }

    public function keywords(string|array|null $keywords): static
    {
        $this->data->keywords($keywords);

        return $this;
    }

    public function author(?string $author): static
    {
        $this->data->author($author);

        return $this;
    }

    // --- Custom tags -----------------------------------------------------

    public function add(string $name, mixed $content): static
    {
        $this->data->customMeta[$name] = $content;

        return $this;
    }

    public function property(string $property, mixed $content): static
    {
        $this->data->customProperties[$property] = $content;

        return $this;
    }

    public function link(string $rel, ?string $href = null, array $attributes = []): static
    {
        $this->data->links[] = array_merge(['rel' => $rel, 'href' => $href], $attributes);

        return $this;
    }

    public function favicon(string $href, array $attributes = []): static
    {
        return $this->link('icon', $href, $attributes);
    }

    // --- Sub-builders ----------------------------------------------------

    public function openGraph(): OpenGraph
    {
        return $this->data->openGraph;
    }

    public function twitter(): TwitterCard
    {
        return $this->data->twitter;
    }

    // --- Output ----------------------------------------------------------

    public function render(): HtmlString
    {
        return $this->renderer->fluent($this->data);
    }

    /**
     * Entry point for the `@include('meta::manager')` view.
     *
     * @param  array<string, mixed>  $vars  raw Blade scope
     */
    public function blade(array $vars = []): HtmlString
    {
        return $this->renderer->blade($this->bladeVars($vars), $this->data);
    }

    public function toArray(): array
    {
        return $this->data->toArray();
    }

    public function validate(): array
    {
        return (new MetaValidator())->validate($this->data);
    }

    public function data(): MetaData
    {
        return $this->data;
    }

    /** Replace the whole state, e.g. from a model's meta(). */
    public function use(MetaData $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function reset(): static
    {
        $this->data = new MetaData();
        $this->renderer->reset();

        return $this;
    }

    /**
     * Blade's scope carries __env, app, errors and every other view variable.
     * Only the ten documented include variables are honoured.
     */
    protected function bladeVars(array $vars): array
    {
        return array_intersect_key($vars, array_flip(self::INCLUDE_VARS));
    }
}
