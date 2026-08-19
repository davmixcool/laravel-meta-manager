<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Robots
    |--------------------------------------------------------------------------
    |
    | Robots option tells search engines what to follow and what not to follow.
    | It's a simple option that gives you the power to decide about what
    | pages you want to hide from search engine crawlers and what pages you
    | want them to index and look at.
    |
    */
    'robots' => 'all',

    /*
    |--------------------------------------------------------------------------
    | Revisit After
    |--------------------------------------------------------------------------
    |
    | Here you may specify how search engines will re-visit and re-crawl your site.
    |
    | NOTE: this key is retained for backwards compatibility only. It was never
    | rendered by v1 and is not rendered by v2; no major crawler honours it.
    |
    */
    'revisit_after' => 'period',

    /*
    |--------------------------------------------------------------------------
    | Referrer
    |--------------------------------------------------------------------------
    |
    | Here you may specify how you want other sites to get referrer information
    | from your site.
    | options available are: none, unsafe-url, origin and none-when-downgrade
    |
    */
    'referrer' => 'no-referrer-when-downgrade',

    /*
    |--------------------------------------------------------------------------
    | Type
    |--------------------------------------------------------------------------
    |
    | Here you may specify the structure type of your website or a specific page
    |
    */
    'type' => 'website',

    /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    |
    | Here you may provide the title of your website or a specific page to help
    | search engines understand it better.
    |
    */
    'title' => '',

    /*
    |--------------------------------------------------------------------------
    | Description
    |--------------------------------------------------------------------------
    |
    | Here you may provide the description of your website or a specific page to
    | help search engines understand it better.
    |
    */
    'description' => '',

    /*
    |--------------------------------------------------------------------------
    | Image
    |--------------------------------------------------------------------------
    |
    | Here you may provide the url to the image you want search
    | engines and crawlers to make use of when displaying your website
    | or a specific page page.
    |
    */
    'image' => '',

    /*
    |--------------------------------------------------------------------------
    | Author
    |--------------------------------------------------------------------------
    |
    | Here you may provide the author's name you want search
    | engines to make use of when displaying your website
    | or a specific page page.
    |
    */
    'author' => '',

    /*
    |--------------------------------------------------------------------------
    | GEO REGION AND POSITION
    |--------------------------------------------------------------------------
    |
    | These are for use if you have a physical location that is important
    | for your business.
    |
    */
    'geo_region' => '', //e.g: Lagos
    'geo_position' => '', //e.g(lng,lat): 4.870467,6.993388

    /*
    |--------------------------------------------------------------------------
    | TWITTER SITE
    |--------------------------------------------------------------------------
    |
    | Here you may provide your twitter @username of your account
    |
    */
    'twitter_site' => '',

    /*
    |--------------------------------------------------------------------------
    | TWITTER CARD
    |--------------------------------------------------------------------------
    |
    | Here you may specify the way you want crawlers to understand your
    | twitter share type. Check twitter docs for more options.
    |
    */
    'twitter_card' => 'summary_large_image',

    /*
    |--------------------------------------------------------------------------
    | FACEBOOK APP ID
    |--------------------------------------------------------------------------
    |
    | Here you may provide your facebook app id
    |
    */
    'fb_app_id' => '',

    /*
    |--------------------------------------------------------------------------
    | KEYWORDS
    |--------------------------------------------------------------------------
    |
    | Here you may provide keywords relevant to your website and the specific page.
    |
    */
    'keywords' => '',

    /*
    |==========================================================================
    | v2 additions
    |==========================================================================
    |
    | Everything below is new in v2. All of it is optional: leaving these at
    | their defaults keeps output identical to v1.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Site Name
    |--------------------------------------------------------------------------
    |
    | Used for og:site_name. Falls back to config('app.name').
    |
    */
    'site_name' => '',

    /*
    |--------------------------------------------------------------------------
    | Locale
    |--------------------------------------------------------------------------
    |
    | Open Graph expects a language_TERRITORY pair such as en_US. When empty,
    | config('app.locale') is normalised into that shape.
    |
    */
    'locale' => '',

    /*
    |--------------------------------------------------------------------------
    | Title Template
    |--------------------------------------------------------------------------
    |
    | Applied to titles you set explicitly, e.g. '%s - Acme'. It is deliberately
    | NOT applied to the 'title' default above, which would double the site name
    | on every page. Pass Meta::title('Home', applyTemplate: false) to opt out.
    |
    */
    'title_template' => '',

    /*
    |--------------------------------------------------------------------------
    | Canonical
    |--------------------------------------------------------------------------
    |
    | Set to 'current' to canonicalise every page to url()->current(), or give a
    | fixed URL. Empty means no canonical tag is emitted.
    |
    | Left empty by default on purpose: url()->current() drops the query string,
    | so blanket canonicalisation can collapse paginated and filtered URLs.
    |
    */
    'canonical' => '',

    /*
    |--------------------------------------------------------------------------
    | Twitter Creator
    |--------------------------------------------------------------------------
    |
    | The @username of the content author, e.g. @davmixcool
    |
    */
    'twitter_creator' => '',

    /*
    |--------------------------------------------------------------------------
    | Default Image Dimensions And Alt Text
    |--------------------------------------------------------------------------
    |
    | Supplying these lets crawlers reserve layout space before the image loads.
    | 1200x630 is the standard Open Graph size.
    |
    */
    'image_width' => '',
    'image_height' => '',
    'image_alt' => '',

    /*
    |--------------------------------------------------------------------------
    | Geo Placename
    |--------------------------------------------------------------------------
    |
    | Here you may specify the geographic place name of your location, such as
    | a city or region.
    |
    | Leave empty to keep the previous behaviour of using your application name.
    | Set it to false to leave the tag out entirely.
    |
    */
    'geo_placename' => '',

    /*
    |--------------------------------------------------------------------------
    | Dublin Core Subject
    |--------------------------------------------------------------------------
    |
    | Here you may specify the Dublin Core subject of your content. A subject is
    | the topic of the page, which is not always the same as the keywords you
    | would use for search engines.
    |
    | Leave empty to keep the previous behaviour of reusing your keywords. Set it
    | to false to leave the tag out entirely.
    |
    */
    'dcterms_subject' => '',

    /*
    |--------------------------------------------------------------------------
    | Duplicate Render Guard
    |--------------------------------------------------------------------------
    |
    | Metadata should be rendered once per request. With this enabled, a second
    | render in the same request emits nothing and logs a warning when
    | app.debug is true.
    |
    */
    'prevent_duplicate_render' => true,

    /*
    |--------------------------------------------------------------------------
    | Blade Tags
    |--------------------------------------------------------------------------
    |
    | Which set of tags @include('meta::manager') prints.
    |
    | 'extended' prints everything, including Dublin Core, geo and keywords.
    | 'standard' prints only the tags most crawlers read today, which is what
    | Meta::render() prints.
    |
    | Only an explicit 'standard' switches; any other value keeps the extended
    | set, so a typo here cannot silently drop tags from your pages.
    |
    */
    'blade_tags' => 'extended',

];
