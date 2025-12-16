<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Debug\Debug;

// If you don't want to setup permissions the proper way, just uncomment the following PHP line
// umask(0000);

// This check prevents access to debug front controllers that are deployed by accident to production servers.
$remoteAddr = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
$isCliServer = php_sapi_name() === 'cli-server';
$isLocalhost = in_array($remoteAddr, ['127.0.0.1', '::1'], true);

if (
    isset($_SERVER['HTTP_CLIENT_IP'])
    || isset($_SERVER['HTTP_X_FORWARDED_FOR'])
    || (!$isCliServer && !$isLocalhost)
) {
    header('HTTP/1.0 403 Forbidden');
    exit('You are not allowed to access this file. Check ' . basename(__FILE__) . ' for more information.');
}

/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require __DIR__ . '/../app/autoload.php';
require __DIR__ . '/../app/AppKernel.php';

Debug::enable();

$kernel = new AppKernel('dev', true);

Request::enableHttpMethodParameterOverride();

$request  = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
