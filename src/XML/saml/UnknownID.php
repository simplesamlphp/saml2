<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XMLSchema\Type\QNameValue;

/**
 * Class for unknown identifiers.
 *
 * @package simplesamlphp/saml2
 */
final class UnknownID extends AbstractBaseID
{
    /**
     * @param \SimpleSAML\XML\Chunk $chunk The whole BaseID element as a chunk object.
     * @param \SimpleSAML\XMLSchema\Type\QNameValue $type The xsi:type of this identifier.
     * @param \SimpleSAML\SAML2\Type\SAMLStringValue|null $NameQualifier
     * @param \SimpleSAML\SAML2\Type\SAMLStringValue|null $SPNameQualifier
     */
    public function __construct(
        protected Chunk $chunk,
        QNameValue $type,
        ?SAMLStringValue $NameQualifier = null,
        ?SAMLStringValue $SPNameQualifier = null,
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
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        return $this->getRawIdentifier()->toXML($parent);
    }
}
