<?php

use Symfony\Component\HttpFoundation\Request;

// Pour l'instant en prod on masque les warnings / notices / deprecated
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_USER_DEPRECATED);
ini_set('display_errors', 0);

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/../app/AppKernel.php';

$kernel = new AppKernel('prod', false);

// Utile pour les formulaires avec _method (PUT/DELETE simulés)
Request::enableHttpMethodParameterOverride();

$request  = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
