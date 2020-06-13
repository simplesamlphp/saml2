<?php

declare(strict_types=1);

namespace SAML2\XML\samlp;

use DOMElement;
use SAML2\XML\Chunk;
use SimpleSAML\Assert\Assert;

/**
 * SAML StatusDetail data type.
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
final class StatusDetail extends AbstractSamlpElement
{
    /** @var \SAML2\XML\Chunk[] */
    protected $details = [];


    /**
     * Initialize a samlp:StatusDetail
     *
     * @param \SAML2\XML\Chunk[] $details
     */
    public function __construct(array $details = [])
    {
        $this->setDetails($details);
    }


    /**
     * Collect the details
     *
     * @return \SAML2\XML\Chunk[]
     */
    public function getDetails(): array
    {
        return $this->details;
    }


    /**
     * Set the value of the details-property
     *
     * @param \SAML2\XML\Chunk[] $details
     * @return void
     * @throws \InvalidArgumentException if the supplied array contains anything other than Chunk objects
     */
    private function setDetails(array $details): void
    {
        Assert::allIsInstanceOf($details, Chunk::class);
        $this->details = $details;
    }


    /**
     * Test if an object, at the state it's in, would produce an empty XML-element
     *
     * @return bool
     */
    public function isEmptyElement(): bool
    {
        return empty($this->details);
    }


    /**
     * Convert XML into a StatusDetail
     *
     * @param \DOMElement $xml The XML element we should load
     * @return \SAML2\XML\samlp\StatusDetail
     * @throws \InvalidArgumentException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'StatusDetail');
        Assert::same($xml->namespaceURI, StatusDetail::NS);

        $details = [];
        foreach ($xml->childNodes as $detail) {
            if (!($detail instanceof DOMElement)) {
                continue;
            }

            $details[] = new Chunk($detail);
        }

        return new self($details);
    }


    /**
     * Convert this StatusDetail to XML.
     *
     * @param \DOMElement|null $element The element we are converting to XML.
     * @return \DOMElement The XML element after adding the data corresponding to this StatusDetail.
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        foreach ($this->details as $detail) {
            $e->appendChild($e->ownerDocument->importNode($detail->getXML(), true));
        }

        return $e;
    }
}
