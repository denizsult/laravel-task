<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Comment Moderation Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for comment moderation system
    |
    */

    'cache_ttl' => env('COMMENT_CACHE_TTL', 60),
    'rate_limit' => env('COMMENT_RATE_LIMIT', 10),
    'banned_words' => array_filter(array_map('trim', explode(',', env('BANNED_WORDS', 'spam,abuse,inappropriate,offensive')))),
];
