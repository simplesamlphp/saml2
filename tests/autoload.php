<?php

// Load Composer autoloader
require __DIR__ . '/../vendor/autoload.php';

spl_autoload_register(function ($class) {
    $path = str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';
    $file = __DIR__ . DIRECTORY_SEPARATOR . $path;
    if (!file_exists($file)) {
        return false;
    }

    require_once $file;
    if (!class_exists($class)) {
        throw new \Exception(sprintf(
            'The file "%s" has been found, but the class "%s" was not in it, perhaps you made a typo?',
            $path,
            $class
        ));
    }
});

// And set the Mock container as the Container to use.
SAML2_Compat_ContainerSingleton::setContainer(new SAML2_Compat_MockContainer());
