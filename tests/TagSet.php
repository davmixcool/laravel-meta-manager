<?php

namespace Davmixcool\MetaManager\Tests;

use DOMDocument;
use DOMElement;

/**
 * Parses rendered head markup into comparable tags.
 *
 * Comparison happens on parsed structure rather than raw strings so the suite
 * asserts what actually reaches a crawler - tag identity and decoded value -
 * and not incidental whitespace or attribute spacing.
 */
final class TagSet
{
    /** @var array<string, list<string>> */
    private array $tags = [];

    public static function parse(string $html): self
    {
        $set = new self();

        if (trim($html) === '') {
            return $set;
        }

        $document = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML(
            '<?xml encoding="UTF-8"><html><head>'.$html.'</head></html>',
            LIBXML_NOERROR | LIBXML_NOWARNING
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        foreach (['title', 'meta', 'link'] as $element) {
            foreach ($document->getElementsByTagName($element) as $node) {
                /** @var DOMElement $node */
                $set->record($element, $node);
            }
        }

        return $set;
    }

    private function record(string $element, DOMElement $node): void
    {
        if ($element === 'title') {
            $this->tags['title'][] = $node->textContent;

            return;
        }

        if ($element === 'link') {
            $key = 'link:rel:'.$node->getAttribute('rel');
            $this->tags[$key][] = $node->getAttribute('href');

            return;
        }

        foreach (['name', 'property', 'http-equiv'] as $identifier) {
            if ($node->hasAttribute($identifier)) {
                $key = 'meta:'.$identifier.':'.$node->getAttribute($identifier);
                $this->tags[$key][] = $node->getAttribute('content');

                return;
            }
        }
    }

    /** @return array<string, list<string>> */
    public function all(): array
    {
        return $this->tags;
    }

    public function keys(): array
    {
        return array_keys($this->tags);
    }

    public function has(string $key): bool
    {
        return isset($this->tags[$key]);
    }

    public function values(string $key): array
    {
        return $this->tags[$key] ?? [];
    }

    public function first(string $key): ?string
    {
        return $this->tags[$key][0] ?? null;
    }

    /**
     * Every tag in $other must exist here with the same value.
     *
     * @return list<string> human-readable differences; empty means superset
     */
    public function missingFrom(self $other): array
    {
        $differences = [];

        foreach ($other->all() as $key => $values) {
            if (! isset($this->tags[$key])) {
                $differences[] = sprintf('MISSING TAG   %s (v1 value: "%s")', $key, $values[0] ?? '');

                continue;
            }

            foreach ($values as $value) {
                if (! in_array($value, $this->tags[$key], true)) {
                    $differences[] = sprintf(
                        'VALUE CHANGED %s: v1 "%s" -> v2 "%s"',
                        $key,
                        $value,
                        implode('", "', $this->tags[$key]),
                    );
                }
            }
        }

        return $differences;
    }
}
