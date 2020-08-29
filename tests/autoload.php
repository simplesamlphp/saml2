<?php

declare(strict_types=1);

use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\SAML2\Compat\MockContainer;

// Load Composer autoloader
require __DIR__ . '/../vendor/autoload.php';

// And set the Mock container as the Container to use.
ContainerSingleton::setContainer(new MockContainer());
