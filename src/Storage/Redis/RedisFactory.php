<?php

declare(strict_types=1);

namespace App\Storage\Redis;

use Redis;

class RedisFactory
{
    public static function create(
        string $host,
        ?int $port = 6379,
        ?string $password = '',
    ): Redis {
        $redis = new Redis();
        $redis->connect($host, $port);

        if ($password) {
            $redis->auth($password);
        }

        return $redis;
    }
}
