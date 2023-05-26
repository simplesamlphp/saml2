<?php

declare(strict_types=1);

namespace SimpleSAML\TestUtils\SAML2;

use DateTimeImmutable;
use DateTimeZone;

/**
 * @package simplesamlphp\saml2
 */
trait ControlledTimeTestTrait
{
    /** @var \DateTimeImmutable */
    private static DateTimeImmutable $currentTime;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$currentTime = new DateTimeImmutable('now', new DateTimeZone('Z'));
    }
}
