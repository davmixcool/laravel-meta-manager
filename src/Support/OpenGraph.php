<?php

namespace Davmixcool\MetaManager\Support;

/**
 * Explicit Open Graph overrides. Anything not set here falls back to the base
 * metadata (see the fallback hierarchy in the renderer).
 */
final class OpenGraph
{
    private array $overrides = [];

    public function title(?string $title): self
    {
        return $this->set('title', $title);
    }

    public function description(?string $description): self
    {
        return $this->set('description', $description);
    }

    public function type(?string $type): self
    {
        return $this->set('type', $type);
    }

    public function url(?string $url): self
    {
        return $this->set('url', $url);
    }

    public function siteName(?string $siteName): self
    {
        return $this->set('site_name', $siteName);
    }

    public function locale(?string $locale): self
    {
        return $this->set('locale', $locale);
    }

    public function image(MetaImage|string|null $image, ?int $width = null, ?int $height = null, ?string $alt = null): self
    {
        return $this->set('image', MetaImage::make($image, $width, $height, $alt));
    }

    public function set(string $key, mixed $value): self
    {
        $this->overrides[$key] = $value;

        return $this;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->overrides) && $this->overrides[$key] !== null;
    }

    public function get(string $key): mixed
    {
        return $this->overrides[$key] ?? null;
    }

    public function toArray(): array
    {
        return array_map(
            static fn ($value) => $value instanceof MetaImage ? $value->toArray() : $value,
            $this->overrides,
        );
    }
}
