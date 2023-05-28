<?php

declare(strict_types=1);

use Beste\Clock\LocalizedClock;
use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\SAML2\Compat\MockContainer;

// Load Composer autoloader
require __DIR__ . '/../vendor/autoload.php';

// Load the system clock
$systemClock = LocalizedClock::in(new DateTimeZone('Z'));

// And set the Mock container as the Container to use.
$container = new MockContainer();
$container->setClock($systemClock);
ContainerSingleton::setContainer($container);
