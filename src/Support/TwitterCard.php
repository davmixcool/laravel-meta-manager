<?php

namespace Davmixcool\MetaManager\Support;

final class TwitterCard
{
    public const SUMMARY = 'summary';
    public const SUMMARY_LARGE_IMAGE = 'summary_large_image';

    private array $overrides = [];

    public function card(?string $card): self
    {
        return $this->set('card', $card);
    }

    public function site(?string $site): self
    {
        return $this->set('site', $this->handle($site));
    }

    public function creator(?string $creator): self
    {
        return $this->set('creator', $this->handle($creator));
    }

    public function title(?string $title): self
    {
        return $this->set('title', $title);
    }

    public function description(?string $description): self
    {
        return $this->set('description', $description);
    }

    public function image(MetaImage|string|null $image, ?string $alt = null): self
    {
        return $this->set('image', MetaImage::make($image, alt: $alt));
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

    /** Twitter handles are conventionally stored with a leading @. */
    private function handle(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $value = trim($value);

        return str_starts_with($value, '@') ? $value : '@'.$value;
    }
}
