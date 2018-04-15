<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

define('PUBLIC_DIR', __DIR__);

$app = new \SampleChat\Core\Application();

$app->run();
