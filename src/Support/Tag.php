<?php

namespace Davmixcool\MetaManager\Support;

/**
 * A single rendered element. `$key` drives duplicate prevention in TagBag.
 */
final class Tag
{
    public const META = 'meta';
    public const LINK = 'link';
    public const TITLE = 'title';
    public const RAW = 'raw';

    /**
     * Properties/rels that legitimately repeat and must never collapse.
     */
    private const REPEATABLE = [
        'og:image',
        'og:image:url',
        'og:image:secure_url',
        'og:image:width',
        'og:image:height',
        'og:image:alt',
        'og:image:type',
        'og:locale:alternate',
        'og:video',
        'article:tag',
        'article:author',
    ];

    private const REPEATABLE_RELS = [
        'icon',
        'apple-touch-icon',
        'alternate',
        'preload',
        'mask-icon',
    ];

    private function __construct(
        public readonly string $element,
        public readonly array $attributes,
        public readonly string $key,
        public readonly bool $selfClosing = false,
        public readonly ?string $text = null,
    ) {
    }

    /** Verbatim markup, e.g. v1's section comments. */
    public static function raw(string $html, string $key): self
    {
        return new self(self::RAW, [], $key, false, $html);
    }

    public static function title(mixed $text): self
    {
        return new self(self::TITLE, [], 'title', false, Escape::stringify($text));
    }

    public static function meta(string $name, mixed $content): self
    {
        return new self(self::META, ['name' => $name, 'content' => $content], 'meta:name:'.$name);
    }

    public static function property(string $property, mixed $content, bool $selfClosing = false): self
    {
        $key = 'meta:property:'.$property;

        if (in_array($property, self::REPEATABLE, true)) {
            $key .= '|'.Escape::stringify($content);
        }

        return new self(self::META, ['property' => $property, 'content' => $content], $key, $selfClosing);
    }

    public static function httpEquiv(string $equiv, mixed $content): self
    {
        return new self(self::META, ['http-equiv' => $equiv, 'content' => $content], 'meta:http-equiv:'.$equiv);
    }

    public static function link(array $attributes): self
    {
        $rel = (string) ($attributes['rel'] ?? '');
        $key = 'link:rel:'.$rel;

        if (in_array($rel, self::REPEATABLE_RELS, true)) {
            $key .= '|'.Escape::stringify($attributes['href'] ?? '').'|'.Escape::stringify($attributes['sizes'] ?? '');
        }

        return new self(self::LINK, $attributes, $key);
    }

    public function render(): string
    {
        if ($this->element === self::RAW) {
            return (string) $this->text;
        }

        if ($this->element === self::TITLE) {
            return '<title>'.Escape::attribute($this->text).'</title>';
        }

        $html = '<'.$this->element;

        foreach ($this->attributes as $attribute => $value) {
            $html .= ' '.$attribute.'="'.Escape::attribute($value).'"';
        }

        return $html.($this->selfClosing ? '/>' : '>');
    }
}
