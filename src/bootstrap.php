<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Source\FileSource;
use App\Source\HttpSource;
use App\Source\SourceInterface;
use App\Status\DummyStatusStorage;
use App\Status\FileStatusStorage;
use App\Status\RedisStatusStorage;
use App\Status\StatusStorageInterface;
use App\Storage\ItemStorageInterface;
use App\Storage\Redis\RedisFactory;
use App\Storage\RedisItemStorage;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

$containerConfig = [
// redis
    \Redis::class => \DI\factory([RedisFactory::class, 'create'])
        ->parameter('host', \DI\get('redis.host'))
        ->parameter('port', \DI\get('redis.port'))
        ->parameter('password', \DI\get('redis.password'))
    ,
// logger
    LoggerInterface::class => \DI\factory(function () {
        return (new Logger('update'))
            ->pushHandler(new StreamHandler('php://stdout', Logger::INFO));
    }),
// source
    SourceInterface::class => \DI\factory(function (\Psr\Container\ContainerInterface $c) {
        try {
            return match ((string) $c->get('source.type')) {
                'file' => new FileSource(
                    path: $c->get('source.file.path'),
                ),
                'http' => new HttpSource(
                    url: $c->get('source.http.url'),
                ),
            };
        } catch (\UnhandledMatchError) {
            trigger_error(sprintf("Unknown status type '%s'", $c->get('storage.type')), E_USER_ERROR);
        }
    }),
// status
    StatusStorageInterface::class => \DI\factory(function (\Psr\Container\ContainerInterface $c) {
        try {
            return match ((string) $c->get('status.type')) {
                'dummy' => new DummyStatusStorage(),
                'file' => new FileStatusStorage(
                    path: $c->get('status.file.path')
                ),
                'redis' => new RedisStatusStorage(
                    redis: $c->get(Redis::class),
                    statusDb: $c->get('status.redis.db'),
                ),
            };
        } catch (\UnhandledMatchError) {
            trigger_error(sprintf("Unknown status type '%s'", $c->get('storage.type')), E_USER_ERROR);
        }
    }),
// storage
    ItemStorageInterface::class => \DI\factory(function (\Psr\Container\ContainerInterface $c) {
        try {
            return match ((string) $c->get('storage.type')) {
                'redis' => new RedisItemStorage(
                    redis: $c->get(Redis::class),
                    primaryDb: $c->get('storage.redis.primary_db'),
                    temporaryDb: $c->get('storage.redis.temporary_db'),
                ),
            };
        } catch (\UnhandledMatchError) {
            trigger_error(sprintf("Unknown storage type '%s'", $c->get('storage.type')), E_USER_ERROR);
        }
    }),
// updater service
    \App\Service\Updater::class => \DI\create()->constructor(
        source: \DI\get(SourceInterface::class),
        statusStorage: \DI\get(StatusStorageInterface::class),
        itemStorage: \DI\get(ItemStorageInterface::class),
        logger: \DI\get(LoggerInterface::class),
    ),
];

return (new \DI\ContainerBuilder())
    ->addDefinitions(__DIR__ . '/../config/config.php')
    ->addDefinitions($containerConfig)
//    ->enableCompilation(__DIR__ . '/../var/cache')
    ->build()
;
