<?php

namespace Davmixcool\MetaManager\Support;

final class MetaImage
{
    public function __construct(
        public readonly ?string $url = null,
        public readonly ?int $width = null,
        public readonly ?int $height = null,
        public readonly ?string $alt = null,
        public readonly ?string $type = null,
    ) {
    }

    public static function make(
        MetaImage|string|null $url,
        ?int $width = null,
        ?int $height = null,
        ?string $alt = null,
        ?string $type = null,
    ): ?self {
        if ($url instanceof self) {
            return $url;
        }

        if ($url === null || trim($url) === '') {
            return null;
        }

        return new self($url, $width, $height, $alt, $type);
    }

    public function isEmpty(): bool
    {
        return $this->url === null || trim($this->url) === '';
    }

    /** Safe URL, or null when the scheme is not allowlisted. */
    public function safeUrl(): ?string
    {
        return Url::safe($this->url);
    }

    public function toArray(): array
    {
        return array_filter([
            'url' => $this->url,
            'width' => $this->width,
            'height' => $this->height,
            'alt' => $this->alt,
            'type' => $this->type,
        ], static fn ($value) => $value !== null);
    }
}
