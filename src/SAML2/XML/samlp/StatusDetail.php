<?php

declare(strict_types=1);

namespace SAML2\XML\samlp;

use DOMElement;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use Webmozart\Assert\Assert;

/**
 * SAML StatusDetail data type.
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */

class StatusDetail extends \SAML2\XML\AbstractConvertable
{
    /** @var \DOMElement|null */
    private $detail = null;


    /**
     * Initialize a samlp:StatusDetail
     *
     * @param \DOMElement $detail
     */
    public function __construct(\DOMElement $detail = null)
    {
        $this->detail = $detail;
    }


    /**
     * Collect the detail
     *
     * @return \DOMElement|null
     */
    public function getDetail(): ?DOMElement
    {
        return $this->detail;
    }


    /**
     * Set the value of the detail-property
     *
     * @param \DOMElement|null $detail
     * @return void
     */
    public function setDetail(?\DOMElement $detail): void
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
        return new self($xml);
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
            $e->appendChild($e->ownerDocument->importNode($this->detail, true));
        }

        return $e;
    }
}
