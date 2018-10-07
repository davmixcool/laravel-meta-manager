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
    | Here you may provide the title of your website or a specific page to help search 
    | engines understand it better. 
    |
    */
	'title'	=> '',

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
    | TWITTER SITE
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


];