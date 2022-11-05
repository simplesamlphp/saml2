<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\XML\Chunk;

/**
 * Class for unknown statements.
 *
 * @package simplesamlphp/saml2
 */
final class UnknownStatement extends AbstractStatement
{
    /** @var \SimpleSAML\XML\Chunk */
    protected Chunk $chunk;


    /**
     * @param \SimpleSAML\XML\Chunk $chunk The whole Statement element as a chunk object.
     * @param string $type The xsi:type of this statement
     */
    public function __construct(
        Chunk $chunk,
        string $type
    ) {
        parent::__construct($type);
        $this->chunk = $chunk;
    }


    /**
     * Get the raw version of this statement as a Chunk.
     *
     * @return \SimpleSAML\XML\Chunk
     */
    public function getRawStatement(): Chunk
    {
        return $this->chunk;
    }


    /**
     * Convert this unknown statement to XML.
     *
     * @param \DOMElement|null $parent The element we are converting to XML.
     * @return \DOMElement The XML element after adding the data corresponding to this unknown statement.
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        return $this->getRawStatement()->toXML($parent);
    }
}
