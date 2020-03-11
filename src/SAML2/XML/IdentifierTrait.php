<?php

declare(strict_types=1);

namespace SAML2\XML;

use SAML2\XML\saml\IdentifierInterface;

/**
 * Trait grouping common functionality for elements that can hold identifiers.
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
trait IdentifierTrait
{
    /**
     * The identifier for this element.
     *
     * @var \SAML2\XML\saml\IdentifierInterface|null
     */
    protected $identifier = null;


    /**
     * Collect the value of the identifier-property
     *
     * @return \SAML2\XML\saml\IdentifierInterface|null
     */
    public function getIdentifier(): ?IdentifierInterface
    {
        return $this->identifier;
    }


    /**
     * Set the value of the identifier-property
     *
     * @param \SAML2\XML\saml\IdentifierInterface|null
     * @return void
     */
    protected function setIdentifier(?IdentifierInterface $identifier): void
    {
        $this->identifier = $identifier;
    }
}
