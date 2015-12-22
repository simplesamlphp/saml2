<?php

// Load Composer autoloader
require __DIR__ . '/../vendor/autoload.php';

// And set the Mock container as the Container to use.
\SAML2\Compat\ContainerSingleton::setContainer(new \SAML2\Compat\MockContainer());
