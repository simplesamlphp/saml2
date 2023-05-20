<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Utilities;

use Psr\Clock\ClockInterface;

trait ClockTrait
{
    protected ClockInterface $clock;


    public function setClock(ClockInterface $clock): void
    {
        $this->clock = $clock;
    }


    public function getClock(): ClockInterface
    {
        return $this->clock;
    }
}
