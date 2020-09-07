<?php
require __DIR__ . '/../vendor/autoload.php';

/**
 * Simple bootstrap without caching.
 */

use App\Application;


$app = Application::getInstance();
$app->boot();

