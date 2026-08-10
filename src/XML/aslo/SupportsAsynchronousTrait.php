<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\aslo;

use SimpleSAML\XMLSchema\Type\BooleanValue;

/**
 * @package simplesamlphp/saml2
 */
trait SupportsAsynchronousTrait
{
    /** @var \SimpleSAML\XMLSchema\Type\BooleanValue|null */
    protected ?BooleanValue $supportsAsynchronous;


    /**
     * @return \SimpleSAML\XMLSchema\Type\BooleanValue|null
     */
    public function getSupportsAsynchronous(): ?BooleanValue
    {
        return $this->supportsAsynchronous;
    }


    /**
     * @param \SimpleSAML\XMLSchema\Type\BooleanValue $supportsAsynchronous|null
     */
    private function setSupportsAsynchronous(?BooleanValue $supportsAsynchronous): void
    {
        $this->supportsAsynchronous = $supportsAsynchronous;
    }
}
