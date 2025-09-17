<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Set up test configuration
        config(['comments.banned_words' => ['spam', 'abuse', 'inappropriate', 'offensive']]);
        config(['comments.cache_ttl' => 60]);
        config(['comments.rate_limit' => 10]);
        
        // Set cache driver to redis for testing (supports tags)
        config(['cache.default' => 'redis']);
        config(['cache.stores.redis.driver' => 'redis']);
        config(['cache.stores.redis.connection' => 'cache']);
    }
}
