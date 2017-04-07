<?php

namespace SAML2\XML\ecp;

/**
 * Class representing the ECP Response element.
 *
 * @version $Id$
 */
class Response
{
    /**
     * The AssertionConsumerServiceURL.
     *
     * @var string
     */
    public $AssertionConsumerServiceURL;

    /**
     * Create a ECP Response element.
     *
     * @param UtilsDOMElement|null $xml The XML element we should load.
     */
    public function __construct(UtilsDOMElement $xml = null)
    {
        if ($xml === null) {
            return;
        }

        if (!$xml->hasAttributeNS(Constants::NS_SOAP, 'mustUnderstand')) {
            throw new Exception('Missing soap-env:mustUnderstand attribute in <ecp:Response>.');
        } elseif ($xml->getAttributeNS(Constants::NS_SOAP, 'mustUnderstand') !== '1') {
            throw new Exception('Invalid value of soap-env:mustUnderstand attribute in <ecp:Response>.');
        }

        if (!$xml->hasAttributeNS(Constants::NS_SOAP, 'actor')) {
            throw new Exception('Missing soap-env:mustUnderstand attribute in <ecp:Response>.');
        } elseif ($xml->getAttributeNS(Constants::NS_SOAP, 'actor') !== 'http://schemas.xmlsoap.org/soap/actor/next') {
            throw new Exception('Invalid value of soap-env:actor attribute in <ecp:Response>.');
        }

        if (!$xml->hasAttribute('AssertionConsumerServiceURL')) {
            throw new Exception('Missing AssertionConsumerServiceURL attribute in <ecp:Response>.');
        }
        $this->AssertionConsumerServiceURL = $xml->getAttribute('AssertionConsumerServiceURL');
    }

    /**
     * Convert this ECP Response to XML.
     *
     * @param UtilsDOMElement $parent The element we should append this element to.
     */
    public function toXML(UtilsDOMElement $parent)
    {
        assert('is_string($this->AssertionConsumerServiceURL)');

        $doc = $parent->ownerDocument;

        $e = $doc->createElementNS(Constants::NS_ECP, 'ecp:Response');
        $parent->appendChild($e);

        $e->setAttributeNS(Constants::NS_SOAP, 'SOAP-ENV:mustUnderstand', '1');
        $e->setAttributeNS(Constants::NS_SOAP, 'SOAP-ENV:actor', 'http://schemas.xmlsoap.org/soap/actor/next');

        $e->setAttribute('AssertionConsumerServiceURL', $this->AssertionConsumerServiceURL);

        return $e;
    }
}
