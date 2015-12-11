#!/usr/bin/env php
<?php
// application.php

define('APP_PATH', __DIR__);

require __DIR__.'/vendor/autoload.php';

use RedstoneTechnology\SiteBuild\Commands;
use Symfony\Component\Console\Application;

$templates = new League\Plates\Engine();
$theme = new RedstoneTechnology\SiteBuild\Utilities\Theme($templates);
$application = new Application();
$application->add(
    new Commands\SiteBuild(
        new \RedstoneTechnology\SiteBuild\Utilities\SiteBuild($theme)
    )
);
$application->run();
