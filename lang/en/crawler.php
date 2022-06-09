<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Crawler Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used for the web crawler page 
    | that we display to the user.
    |
    */

    'page_title' => 'PHP Web Crawler',
    'header_title' => 'PHP Web Crawler',

    'form' => [
        'website' => 'Website',
        'num_to_crawl' => 'Pages To Crawl',
        'button_start' => 'Start',
    ],

    'results' => [
        'crawled_pages' => [
            'page' => 'Page',
            'http_code' => 'HTTP Status Code',
            'word_count' => 'Word Count',
            'title_length' => 'Title Length',
        ],
        'unique' => [
            'pages_crawled' => 'Pages Crawled',
            'images' => 'Unique Images',
            'internal_links' => 'Internal Links',
            'external_links' => 'External Links',
        ],
        'average' => [
            'page_load' => 'Average Page Load (seconds)',
            'word_count' => 'Average Word Count',
            'title_length' => 'Average Title Length',
        ],
    ],

    'versions' => [
        'laravel_version' => 'Laravel :version',
        'php_version' => '(PHP :version)',
    ],
];
