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

class StatusDetail extends \SAML2\XML\AbstractConvertable
{
    /** @var \DOMNodeList|null */
    private $detail = null;


    /**
     * Initialize a samlp:StatusDetail
     *
     * @param \DOMNodeList $detail
     */
    public function __construct(DOMNodeList $detail = null)
    {
        $this->setDetail($detail);
    }


    /**
     * Collect the detail
     *
     * @return \DOMNodeList|null
     */
    public function getDetail(): ?DOMNodeList
    {
        return $this->detail;
    }


    /**
     * Set the value of the detail-property
     *
     * @param \DOMNodeList|null $detail
     * @return void
     */
    private function setDetail(?DOMNodeList $detail): void
    {
        $this->detail = $detail;
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

        return new self($xml->childNodes);
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

        if (!empty($this->detail)) {
            foreach ($this->detail as $node) {
                $e->appendChild($e->ownerDocument->importNode($node, true));
            }
        }

        return $e;
    }
}
