<?php

declare(strict_types=1);

namespace SAML2\XML\samlp;

use DOMElement;
use DOMNodeList;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\XML\Chunk;
use Webmozart\Assert\Assert;

/**
 * SAML StatusDetail data type.
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
final class StatusDetail extends AbstractSamlpElement
{
    /** @var \SAML2\XML\Chunk[]|null */
    protected $details = null;


    /**
     * Initialize a samlp:StatusDetail
     *
     * @param \SAML2\XML\Chunk[] $details
     */
    public function __construct(array $details = null)
    {
        $this->setDetails($details);
    }


    /**
     * Collect the details
     *
     * @return \SAML2\XML\Chunk[]|null
     */
    public function getDetails(): ?array
    {
        return $this->details;
    }


    /**
     * Set the value of the details-property
     *
     * @param \SAML2\XML\Chunk[]|null $details
     * @return void
     */
    private function setDetails(?array $details): void
    {
        Assert::allIsInstanceOf($details, Chunk::class);
        $this->details = $details;
    }


    /**
     * Convert XML into a StatusDetail
     *
     * @param \DOMElement $xml The XML element we should load
     * @return \SAML2\XML\samlp\StatusDetail
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'StatusDetail');
        Assert::same($xml->namespaceURI, Constants::NS_SAMLP);
        Assert::allIsInstanceOf($xml->childNodes, DOMElement::class);

        $details = [];
        foreach ($xml->childNodes as $detail) {
            /** @psalm-var \DOMElement $detail */
            $details[] = new Chunk($detail);
        }

        return new self($details);
    }


    /**
     * Convert this StatusDetail to XML.
     *
     * @param \DOMElement $element The element we are converting to XML.
     * @return \DOMElement The XML element after adding the data corresponding to this StatusDetail.
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        if ($parent === null) {
            $doc = DOMDocumentFactory::create();
            $e = $doc->createElementNS(Constants::NS_SAMLP, 'samlp:StatusDetail');
            $doc->appendChild($e);
        } else {
            $e = $parent->ownerDocument->createElementNS(Constants::NS_SAMLP, 'samlp:StatusDetail');
            $parent->appendChild($e);
        }

        if (!empty($this->details)) {
            foreach ($this->details as $detail) {
                $e->appendChild($e->ownerDocument->importNode($detail->getXML(), true));
            }
        }

        return $e;
    }
}
