<?php

namespace Davmixcool\MetaManager\Support;

/**
 * Ordered, keyed tag collection. Last write wins per key, insertion order is
 * preserved so rendered output stays stable across requests (and snapshots).
 */
final class TagBag
{
    /** @var array<string, Tag> */
    private array $tags = [];

    public function add(?Tag $tag): self
    {
        if ($tag !== null) {
            $this->tags[$tag->key] = $tag;
        }

        return $this;
    }

    public function has(string $key): bool
    {
        return isset($this->tags[$key]);
    }

    public function isEmpty(): bool
    {
        return $this->tags === [];
    }

    /** @return array<string, Tag> */
    public function all(): array
    {
        return $this->tags;
    }

    public function render(string $separator = "\n", string $indent = ''): string
    {
        $lines = [];

        foreach ($this->tags as $tag) {
            $lines[] = $indent.$tag->render();
        }

        return implode($separator, $lines);
    }
}
