# Upgrading to v2

**You do not need to change anything.** Run `composer update` and you are done.

Your `@include('meta::manager')` keeps working. Your `config/meta.php` keeps working. Every tag v1
printed is still printed, with the same value.

## Requirements

| | v1 | v2 |
| :--- | :--- | :--- |
| PHP | 5.4 and above | 8.1 and above |
| Laravel | 5.x and above | 10, 11, 12 or 13 |

If your application runs an older PHP, Composer keeps you on v1 and nothing breaks. You can stay
there for as long as you need. The same applies on **Laravel 9 or below**: v2 requires Laravel 10+,
so Composer resolves those applications to v1 automatically — verified on Laravel 5.5 through 9. v1
remains supported for security fixes.

**On Laravel 10 or 11?** You can still upgrade. Both lines are end of life and carry an unpatched
security advisory, so Composer will not install them into a new project — but your application
already has Laravel in its lock file, and installing this package does not re-resolve it. This path
is covered by the test suite.

## Will my pages change?

**If you published `config/meta.php`**, no. The tags are identical, right down to the HTML comments.

**If you never published it**, three small things change. All three only affect tags that were blank
before.

#### 1. Blank tags now have real values

v1 had no default config, so these printed empty. They now use the documented defaults.

| Tag | v1 | v2 |
| :--- | :--- | :--- |
| `robots` | *(blank)* | `all` |
| `referrer` | *(blank)* | `no-referrer-when-downgrade` |
| `og:type` | *(blank)* | `website` |
| `twitter:card` | *(blank)* | `summary_large_image` |

To keep the blanks, publish the config and empty those keys.

#### 2. Blank geo and Facebook tags are no longer printed

`geo.region`, `geo.position`, `ICBM`, `fb:app_id` and `twitter:site` used to print as empty tags when
you had not set them. They are now left out. Once you set any of them in the config, they print
exactly as before.

#### 3. Unsafe image and canonical URLs are no longer printed

A URL like `javascript:alert(1)` used to be printed into your page as-is. It is now dropped.

Normal URLs are unaffected — `https://example.com/og.png`, `//cdn.example.com/og.png` and
`/images/og.png` all work as they always did.

## What you get for free

Without touching a line of your code:

* A `canonical` link tag, once you set the `canonical` option
* Image width, height and alt tags, once you set those options
* Unsafe URLs filtered out of your meta tags
* Duplicate tags removed automatically
* `php artisan meta:check` to check your meta from the command line
* Correct behaviour under Laravel Octane, where meta could previously leak from one page to the next

## Setting meta from a controller

Optional. Your layout keeps using `@include('meta::manager')` and you set what you need from a
controller;

```php
use Davmixcool\MetaManager\Facades\Meta;

Meta::title($post->title)
    ->description($post->excerpt)
    ->image($post->cover_url, width: 1200, height: 630, alt: $post->title);
```

Options passed to `@include` still take precedence, so any page already passing its own `title`
keeps winning.

See the [README](README.md) for the full list of methods.

## Using the newer tag set

`@include('meta::manager')` prints the same tags as v1, including Dublin Core, geo and keywords.

If you would rather print only the tags most crawlers read today, you can switch without editing your
Blade;

```php
// config/meta.php
'blade_tags' => 'standard',   // 'extended' is the default
```

This **removes** the `dcterms.*`, `geo.*` and `keywords` tags, so it stays on `extended` unless you
change it. Those tags are not deprecated and there is no plan to remove them.
