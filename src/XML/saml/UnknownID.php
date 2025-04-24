<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\XML\Chunk;

/**
 * Class for unknown identifiers.
 *
 * @package simplesamlphp/saml2
 */
final class UnknownID extends AbstractBaseID
{
    /**
     * @param \SimpleSAML\XML\Chunk $chunk The whole BaseID element as a chunk object.
     * @param string $type The xsi:type of this identifier.
     * @param string|null $NameQualifier
     * @param string|null $SPNameQualifier
     */
    public function __construct(
        protected Chunk $chunk,
        string $type,
        ?string $NameQualifier = null,
        ?string $SPNameQualifier = null,
    ) {
        parent::__construct($type, $NameQualifier, $SPNameQualifier);
    }


    /**
     * Get the raw version of this identifier as a Chunk
     *
     * @return \SimpleSAML\XML\Chunk
     */
    public function getRawIdentifier(): Chunk
    {
        return $this->chunk;
    }


    /**
     * Convert this unknown ID to XML.
     *
     * @param \DOMElement|null $parent The element we are converting to XML.
     * @return \DOMElement The XML element after adding the data corresponding to this unknown ID.
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        return $this->getRawIdentifier()->toXML($parent);
    }
}
