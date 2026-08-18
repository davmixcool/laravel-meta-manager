<?php

namespace Davmixcool\MetaManager\Support;

use Illuminate\Contracts\Support\Htmlable;
use Stringable;

/**
 * Attribute escaping.
 *
 * Deliberately mirrors Blade's `{{ }}` (which compiles to `e($value)`), i.e.
 * htmlspecialchars with ENT_QUOTES / UTF-8 / double-encode. v1 rendered every
 * value through Blade echo tags, so matching this exactly is what keeps the
 * extended profile byte-identical.
 */
final class Escape
{
    public static function attribute(mixed $value): string
    {
        return htmlspecialchars(self::stringify($value), ENT_QUOTES, 'UTF-8', true);
    }

    public static function stringify(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? '1' : '';
        }

        if ($value instanceof Htmlable) {
            return $value->toHtml();
        }

        if ($value instanceof Stringable || (is_object($value) && method_exists($value, '__toString'))) {
            return (string) $value;
        }

        if (is_array($value)) {
            return '';
        }

        return (string) $value;
    }
}
