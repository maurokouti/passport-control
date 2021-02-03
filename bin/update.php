<?php

declare(strict_types=1);

use App\Service\Updater;
use SebastianBergmann\Timer\Timer;

$container = require __DIR__ . '/../src/bootstrap.php';

$timer = new Timer();
$timer->start();;

// run
$container->get(Updater::class)->run();

$duration = $timer->stop();
$container->get(\Psr\Log\LoggerInterface::class)
    ->info(sprintf('Update finished. Time: %s', $duration->asString()));
