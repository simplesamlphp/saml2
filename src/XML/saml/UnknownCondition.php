<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\Type\QNameValue;

/**
 * Class for unknown conditions.
 *
 * @package simplesamlphp/saml2
 */
final class UnknownCondition extends AbstractCondition
{
    /**
     * @param \SimpleSAML\XML\Chunk $chunk The whole Condition element as a chunk object.
     * @param \SimpleSAML\XML\Type\QNameValue $type The xsi:type of this condition.
     */
    public function __construct(
        protected Chunk $chunk,
        QNameValue $type,
    ) {
        parent::__construct($type);
    }


    /**
     * Get the raw version of this condition as a Chunk.
     *
     * @return \SimpleSAML\XML\Chunk
     */
    public function getRawCondition(): Chunk
    {
        return $this->chunk;
    }


    /**
     * Convert this unknown condition to XML.
     *
     * @param \DOMElement|null $parent The element we are converting to XML.
     * @return \DOMElement The XML element after adding the data corresponding to this unknown condition.
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        return $this->getRawCondition()->toXML($parent);
    }
}
