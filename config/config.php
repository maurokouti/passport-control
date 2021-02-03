<?php

declare(strict_types=1);

return [
// redis
    'redis.host' => \DI\env('REDIS_HOST', 'redis'),
    'redis.port' => \DI\env('REDIS_PORT', 6379),
    'redis.password' => \DI\env('REDIS_PASSWORD', null),
// status
    'status.type' => 'redis', // dummy|file|redis
    'status.file.path' => \DI\env('STATUS_FILE_PATH', __DIR__ . '/../var/status.json'),
    'status.redis.db' => \DI\env('STATUS_REDIS_DB', 0),
// source
    'source.type' => 'http', // file|http
    'source.file.path' => \DI\env('SOURCE_FILE_PATH', __DIR__ . '/../data/list_of_expired_passports.50k.csv.bz2'),
    'source.http.url' => \DI\env('SOURCE_HTTP_URL', 'http://guvm.mvd.ru/upload/expired-passports/list_of_expired_passports.csv.bz2'),
// storage
    'storage.type' => 'redis', // redis|file|pilosa
    'storage.redis.primary_db' => \DI\env('STORAGE_REDIS_PRIMARY_DB', 1),
    'storage.redis.temporary_db' => \DI\env('STORAGE_REDIS_TEMPORARY_DB', 2),
    'storage.redis.bath_size' => \DI\env('STORAGE_REDIS_BATCH_SIZE', 50000),
// http server
    'http.address' => \DI\env('HTTP_ADDRESS', '0.0.0.0'),
    'http.port' => \DI\env('HTTP_PORT', 8080),
];
