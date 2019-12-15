# Laravel Meta Manager

Laravel Meta Manager is an SEO tool that is used to improve SEO of a website or specific page by adding recommended meta tags to your application. 


[![GitHub license](https://img.shields.io/github/license/davmixcool/laravel-meta-manager.svg)](https://github.com/davmixcool/laravel-meta-manager/blob/master/LICENSE)  [![GitHub issues](https://img.shields.io/github/issues/davmixcool/laravel-meta-manager.svg)](https://github.com/davmixcool/laravel-meta-manager/issues)  [![Twitter](https://img.shields.io/twitter/url/https/github.com/davmixcool/laravel-meta-manager.svg?style=social)](https://twitter.com/intent/tweet?text=Wow:&url=https%3A%2F%2Fgithub.com%2Fdavmixcool%2Flaravel-meta-manager)


## SEO Features

* Standard SEO
* Dublin Core
* Facebook OpenGraph
* Twitter Card

## Requirements

- PHP 5.4 and above

## Steps:

* [Install](#install)
* [Configuration](#configuration)
* [Usage](#usage)
* [Example](#example)
* [Maintainers](#maintainers)
* [License](#license)


### Install

**Composer**

Run the following to include this package via Composer

```shell
composer require davmixcool/laravel-meta-manager
```

#### Laravel 5.5+ uses Package Auto-Discovery, so doesn't require you to manually add the ServiceProvider.


Once download is complete, the next thing you have to do is include the service provider within `config/app.php`.

```php
'providers' => [
        Davmixcool\MetaManager\MetaServiceProvider::class,
];
```
Then run `php artisan vendor:publish --provider="Davmixcool\MetaManager\MetaServiceProvider"` to get the config of Laravel Meta Manager.


### Configuration

Setup default application meta in `meta.php` config. (Optional but recommended)

#### Available options:

Option | Description
--------- | -------
`robots` | Robots option tells search engines what to follow and what not to follow.
`revisit_after` | Here you may specify how search engines will re-visit and re-crawl your site.
`referrer` | Here you may specify how you want other sites to get referrer information from your site.
`type` | Here you may specify the structure type of your website or a specific page
`title` | Here you may provide the title of your website or a specific page to help search engines understand it better. 
`description` | Here you may provide the description of your website or a specific page to help search engines understand it better.
`image` | Here you may provide the url to the image you want search engines and crawlers to make use of when displaying your website or a specific page page.
`author` | Here you may provide the author's name you want search engines to make use of when displaying your website or a specific page page.
`geo_region` | Here you specify the region of your location. This is useful if you have a physical location that is important for your business.
`geo_position` | Here you specify the geo coordinates of your physical location in longitude and latitude. 
`twitter_site` | Here you may provide your twitter @username of your account
`twitter_card` | Here you may specify the way you want crawlers to understand your twitter share type.
`fb_app_id` | Here you may provide your facebook app id
`keywords` |  Here you may provide keywords relevant to your website and the specific page.



### Usage

Once configuration is complete you can then add the below at the meta area of the page you want to include meta tags;

```php
@include('meta::manager')
```

The above will use the predefined configurations to prefill the generated meta tags. However if you chose to define certain options on the fly then you can use the code below.


```php
@include('meta::manager', [
    'title'         => 'My Example Title',
    'description'   => 'This is my example description',
    'image'         => 'Url to the image',
])
```



### Example

```html
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


### Maintainers

This package is maintained by [David Oti](http://github.com/davmixcool) and you!


### License

This package is licensed under the [MIT license](https://github.com/davmixcool/laravel-meta-manager/blob/master/LICENSE).
