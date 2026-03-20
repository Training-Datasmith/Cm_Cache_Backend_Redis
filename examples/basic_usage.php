<?php

declare(strict_types=1);

/**
 * Example: Use Cm_Cache_Backend_Redis as a Zend_Cache backend.
 *
 * Requires ext/redis (PhpRedis) and a running Redis server.
 */

require_once __DIR__ . '/../Cm/Cache/Backend/Redis.php';

$frontend = Zend_Cache::factory(
    'Core',
    new Cm_Cache_Backend_Redis([
        'server'     => '127.0.0.1',
        'port'       => 6379,
        'database'   => 0,
        'compress_data' => true,   // enable LZF compression
    ]),
    [
        'lifetime'                => 3600,
        'automatic_serialization' => true,
    ]
);

$cacheId = 'product_data_42';

if (($data = $frontend->load($cacheId)) === false) {
    $data = ['id' => 42, 'name' => 'Widget', 'price' => 9.99];
    $frontend->save($data, $cacheId, ['products', 'catalog']);
    echo "Cache miss — stored in Redis." . PHP_EOL;
} else {
    echo "Cache hit." . PHP_EOL;
}

var_dump($data);

// Tag-based invalidation: removes all entries tagged 'products'.
$frontend->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, ['products']);
echo "Cleaned all entries tagged 'products'." . PHP_EOL;
