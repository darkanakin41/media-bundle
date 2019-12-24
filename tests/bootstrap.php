<?php

use Doctrine\Common\Annotations\AnnotationRegistry;

/*
 * This file is part of the Darkanakin41MediaBundle package.
 */
if (!($autoload = @include __DIR__.'/../vendor/autoload.php')) {
    echo <<<'EOT'
You need to install the project dependencies using Composer:
$ wget http://getcomposer.org/composer.phar
OR
$ curl -s https://getcomposer.org/installer | php
$ php composer.phar install --dev
$ phpunit
EOT;
    exit(1);
}


AnnotationRegistry::registerLoader(function ($class) use ($autoload) {
    $autoload->loadClass($class);

    return class_exists($class, false);
});

// Test Setup: remove all the contents in the build/ directory
// (PHP doesn't allow to delete directories unless they are empty)
if (is_dir($buildDir = __DIR__.'/../build')) {
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($buildDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($files as $fileinfo) {
        $fileinfo->isDir() ? rmdir($fileinfo->getRealPath()) : unlink($fileinfo->getRealPath());
    }
}

include __DIR__.'/Fixtures/App/AppKernel.php';
