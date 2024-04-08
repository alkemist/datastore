<?php

use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    $env = $context['APP_ENV'];
    $debug = (bool)$context['APP_DEBUG'];
    $devIp = $context['APP_DEV_IP'];
    $ignoreIp = (bool)$context['APP_IGNORE_IP'];

    if (!$ignoreIp && $_SERVER['REMOTE_ADDR'] === $devIp) {
        $env = 'dev';
        $debug = true;
    }

    return new Kernel($env, $debug);
};
