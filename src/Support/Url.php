<?php

namespace Davmixcool\MetaManager\Support;

/**
 * URL scheme allowlisting for href/content attributes.
 *
 * Escaping alone does not make a URL safe: `javascript:alert(1)` survives
 * htmlspecialchars untouched and still executes. Anything carrying a scheme
 * must therefore be http(s); protocol-relative and relative URLs pass through.
 */
final class Url
{
    private const ALLOWED_SCHEMES = ['http', 'https'];

    public static function safe(mixed $url): ?string
    {
        $url = Escape::stringify($url);

        // Control characters (and NUL) are stripped first: `java\0script:` and
        // `java\nscript:` are both parsed as `javascript:` by browsers.
        $url = preg_replace('/[\x00-\x1F\x7F]/u', '', $url) ?? '';
        $url = trim($url);

        if ($url === '') {
            return null;
        }

        // Protocol-relative: //cdn.example.com/og.png
        if (str_starts_with($url, '//')) {
            return $url;
        }

        if (preg_match('/^([a-zA-Z][a-zA-Z0-9+.\-]*):/', $url, $matches)) {
            return in_array(strtolower($matches[1]), self::ALLOWED_SCHEMES, true) ? $url : null;
        }

        // No scheme at all: root-relative or relative path.
        return $url;
    }

    public static function isSafe(mixed $url): bool
    {
        return self::safe($url) !== null;
    }
}
