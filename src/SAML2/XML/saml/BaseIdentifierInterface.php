<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use DOMElement;

/**
 * Interface for BaseID objects.
 *
 * @package simplesamlphp/saml2
 */
interface BaseIdentifierInterface extends IdentifierInterface
{
    /**
     * @return string|null
     */
    public function getNameQualifier(): ?string;


    /**
     * @return string|null
     */
    public function getSPNameQualifier(): ?string;


    /**
     * @return string
     */
    public function getValue(): string;


    /**
     * @param \DOMElement $xml
     *
     * @return BaseID
     */
    public static function fromXML(\DOMElement $xml): object;


    /**
     * @param \DOMElement|null $parent
     *
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement;
}
