<?php

use Doctrine\Common\Annotations\AnnotationRegistry;
use Composer\Autoload\ClassLoader;

/**
 * @var ClassLoader $loader
 */
$loader = require __DIR__.'/../vendor/autoload.php';

if (class_exists(AnnotationRegistry::class)) {
    AnnotationRegistry::registerLoader([$loader, 'loadClass']);
}

return $loader;
