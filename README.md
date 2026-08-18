# Laravel Meta Manager

Laravel Meta Manager is an SEO tool that is used to improve the SEO of a website or specific page by adding recommended meta tags to your application.

[![GitHub license](https://img.shields.io/github/license/davmixcool/laravel-meta-manager.svg)](https://github.com/davmixcool/laravel-meta-manager/blob/master/LICENSE) [![GitHub issues](https://img.shields.io/github/issues/davmixcool/laravel-meta-manager.svg)](https://github.com/davmixcool/laravel-meta-manager/issues) [![CodeFactor](https://www.codefactor.io/repository/github/davmixcool/laravel-meta-manager/badge)](https://www.codefactor.io/repository/github/davmixcool/laravel-meta-manager) [![Stable](https://poser.pugx.org/davmixcool/laravel-meta-manager/v/stable.svg)](https://poser.pugx.org/davmixcool/laravel-meta-manager/v/stable.svg) [![Download](https://poser.pugx.org/davmixcool/laravel-meta-manager/d/total.svg)](https://poser.pugx.org/davmixcool/laravel-meta-manager/d/total.svg) ![Twitter](https://img.shields.io/twitter/url/https/github.com/davmixcool/laravel-meta-manager.svg?style=social)

## SEO Features

* Standard Meta Tags
* Facebook OpenGraph Meta Tags
* Twitter Card Meta Tags
* Dublin Core Meta Tags
* Link Tags
* Canonical URLs
* Social image dimensions and alt text

## Requirements

* PHP 8.1 and above
* Laravel 10, 11 or 12

> Upgrading from v1? Nothing in your application needs to change. See [UPGRADE.md](UPGRADE.md).

## Steps:

* [Install](./#install)
* [Configuration](./#configuration)
* [Usage](./#usage)
* [Example](./#example)
* [Fluent API](./#fluent-api)
* [Stargazers](./#stargazers)
* [Forkers](./#forkers)
* [Maintainers](./#maintainers)
* [License](./#license)

### Install

**Composer**

Run the following to include this package via Composer

```text
composer require davmixcool/laravel-meta-manager
```

#### Laravel uses Package Auto-Discovery, so doesn't require you to manually add the ServiceProvider.

Once the download is complete, the next thing you have to do is include the service provider within `config/app.php`.

```php
'providers' => [
        Davmixcool\MetaManager\MetaServiceProvider::class,
];
```

Then run;`php artisan vendor:publish --provider="Davmixcool\MetaManager\MetaServiceProvider"` to get the config of Laravel Meta Manager.

### Configuration

Setup default application meta in`meta.php`config. \(Optional but recommended\)

#### Available options:

| Option | Description |
| :--- | :--- |
| `robots` | Robots option tells search engines what to follow and what not to follow. |
| `revisit_after` | Here you may specify how search engines will re-visit and re-crawl your site. |
| `referrer` | Here you may specify how you want other sites to get referrer information from your site. |
| `type` | Here you may specify the structure type of your website or a specific page |
| `title` | Here you may provide the title of your website or a specific page to help search engines understand it better. |
| `description` | Here you may provide the description of your website or a specific page to help search engines understand it better. |
| `image` | Here you may provide the URL to the image you want search engines and crawlers to make use of when displaying your website or a specific page. |
| `author` | Here you may provide the author's name you want search engines to make use of when displaying your website or a specific page. |
| `geo_region` | Here you specify the region of your location. This is useful if you have a physical location that is important for your business. |
| `geo_position` | Here you specify the geo-coordinates of your physical location in longitude and latitude. |
| `twitter_site` | Here you may provide your Twitter @username of your account |
| `twitter_card` | Here you may specify the way you want crawlers to understand your Twitter share type. |
| `fb_app_id` | Here you may provide your Facebook app id |
| `keywords` | Here you may provide keywords relevant to your website and the specific page. |
| `site_name` | Here you may provide the name of your site. Defaults to your application name. |
| `locale` | Here you may provide the locale of your content, such as `en_US`. Defaults to your application locale. |
| `title_template` | Here you may provide a template applied to titles you set, such as `%s — Acme`. |
| `canonical` | Here you may provide the canonical URL of your page, or `current` to use the current URL. |
| `twitter_creator` | Here you may provide the Twitter @username of the content author. |
| `image_width` | Here you may provide the width of your image so crawlers can lay out the preview before it loads. |
| `image_height` | Here you may provide the height of your image so crawlers can lay out the preview before it loads. |
| `image_alt` | Here you may provide the alternative text describing your image. |
| `blade_tags` | Here you may specify which tags `@include('meta::manager')` prints, either `extended` or `standard`. |

### Usage

Once the configuration is complete you can then add the below at the meta area of the page you want to include meta tags;

```php
@include('meta::manager')
```

The above will use the predefined configurations to prefill the generated meta tags. However, if you chose to define certain options on the fly then you can use the code below.

```php
@include('meta::manager', [
    'title'         => 'My Example Title',
    'description'   => 'This is my example description',
    'image'         => 'Url to the image',
])
```

The available options are `title`, `description`, `keywords`, `author`, `image`, `type`, `robots`, `referrer`, `twitter_card` and `twitter_site`.

### Example

```markup
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Document</title>
        @include('meta::manager', [
            'title'         => 'My Example Title',
            'description'   => 'This is my example description',
            'image'         => '',
        ])
    </head>
    <body>

    </body>
    </html>
```

### Fluent API

If you would rather set your meta from a controller than pass it through the view, you can. Both work together, and options passed to `@include` still take precedence.

```php
use Davmixcool\MetaManager\Facades\Meta;

Meta::title('My Example Title')
    ->description('This is my example description')
    ->image(asset('og.png'), width: 1200, height: 630, alt: 'My Example Title');
```

Your layout keeps using `@include('meta::manager')`, or you can render the tags directly;

```php
{!! Meta::render() !!}
```

#### Available methods:

| Method | Description |
| :--- | :--- |
| `title` | Here you may provide the title of the page. Also fills the OpenGraph and Twitter titles. |
| `description` | Here you may provide the description of the page. Also fills the OpenGraph and Twitter descriptions. |
| `image` | Here you may provide the image, along with its width, height and alt text. |
| `canonical` | Here you may provide the canonical URL of the page. |
| `robots` | Here you may specify what search engines should follow. `index()`, `noIndex()`, `follow()` and `noFollow()` are also available. |
| `type` | Here you may specify the structure type of the page. |
| `siteName` | Here you may provide the name of your site. |
| `locale` | Here you may provide the locale of your content. |
| `keywords` | Here you may provide keywords relevant to the page, as a string or an array. |
| `author` | Here you may provide the author's name. |
| `add` | Here you may add any other meta tag by name. |
| `property` | Here you may add any other meta tag by property. |
| `link` | Here you may add any link tag. `favicon()` is also available. |
| `openGraph` | Here you may override the OpenGraph tags individually. |
| `twitter` | Here you may override the Twitter card tags individually. |
| `toArray` | Here you may inspect the generated meta, which is useful in tests. |
| `validate` | Here you may check your meta for common problems. |

You can also check your meta from the command line;

```text
php artisan meta:check
```

### Stargazers

[![Stargazers repo roster for @davmixcool/laravel-meta-manager](https://reporoster.com/stars/davmixcool/laravel-meta-manager)](https://github.com/davmixcool/laravel-meta-manager/stargazers)

### Forkers

[![Forkers repo roster for @davmixcool/laravel-meta-manager](https://reporoster.com/forks/davmixcool/laravel-meta-manager)](https://github.com/davmixcool/laravel-meta-manager/network/members)

### Maintainers

This package is maintained by [David Oti](http://github.com/davmixcool) and you!

### License

This package is licensed under the [MIT license](https://github.com/davmixcool/laravel-meta-manager/blob/master/LICENSE).
