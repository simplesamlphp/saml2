<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Utils;

use Beste\Clock;
use DateTimeImmutable;
use DateTimeZone;

/**
 * @package simplesaml/saml2
 */
final class ZuluClock implements Clock
{
    /** @var \DateTimeZone */
    private DateTimeZone $timeZone;


    /**
     */
    private function __construct()
    {
        $this->timeZone = new DateTimeZone('Z');
    }


    /**
     * @return self
     */
    public static function create(): self
    {
        return new self();
    }


    /**
     * @return \DateTimeImmutable
     */
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', $this->timeZone);
    }
}
