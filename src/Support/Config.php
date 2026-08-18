<?php

namespace Davmixcool\MetaManager\Support;

use Illuminate\Support\Facades\Config as LaravelConfig;

/**
 * Thin reader over `config/meta.php`.
 *
 * v2 keeps v1's FLAT key shape as canonical, so an untouched v1 config file
 * keeps resolving with zero migration. New v2 keys sit at the same level.
 */
final class Config
{
    public static function get(string $key, mixed $default = null): mixed
    {
        return LaravelConfig::get('meta.'.$key, $default);
    }

    /** Mirrors v1's `config('meta.x') !== ''` guards. */
    public static function filled(string $key): bool
    {
        $value = self::get($key);

        return $value !== null && $value !== '' && $value !== [];
    }

    public static function app(string $key, mixed $default = null): mixed
    {
        return LaravelConfig::get('app.'.$key, $default);
    }
}
