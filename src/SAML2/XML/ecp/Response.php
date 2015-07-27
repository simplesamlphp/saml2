<?php

/**
 * Class representing the ECP Response element.
 *
 * @package simpleSAMLphp
 * @version $Id$
 */
class SAML2_XML_ecp_Response
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
     * @param DOMElement|NULL $xml  The XML element we should load.
     */
    public function __construct(DOMElement $xml = NULL)
    {

        if ($xml === NULL) {
            return;
        }

        if (!$xml->hasAttributeNS(SAML2_Const::NS_SOAP, 'mustUnderstand')) {
            throw new Exception('Missing soap-env:mustUnderstand attribute in <ecp:Response>.');
        } elseif ($xml->getAttributeNS(SAML2_Const::NS_SOAP, 'mustUnderstand') !== '1') {
            throw new Exception('Invalid value of soap-env:mustUnderstand attribute in <ecp:Response>.');
        }

        if (!$xml->hasAttributeNS(SAML2_Const::NS_SOAP, 'actor')) {
            throw new Exception('Missing soap-env:mustUnderstand attribute in <ecp:Response>.');
        } elseif ($xml->getAttributeNS(SAML2_Const::NS_SOAP, 'actor') !== 'http://schemas.xmlsoap.org/soap/actor/next') {
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
     * @param DOMElement $parent  The element we should append this element to.
     */
    public function toXML(DOMElement $parent)
    {
        assert('is_string($this->AssertionConsumerServiceURL)');

        $doc = $parent->ownerDocument;

        $e = $doc->createElementNS(SAML2_Const::NS_ECP, 'ecp:Response');
        $parent->appendChild($e);

        $e->setAttributeNS(SAML2_Const::NS_SOAP, 'SOAP-ENV:mustUnderstand', '1');
        $e->setAttributeNS(SAML2_Const::NS_SOAP, 'SOAP-ENV:actor', 'http://schemas.xmlsoap.org/soap/actor/next');

        $e->setAttribute('AssertionConsumerServiceURL', $this->AssertionConsumerServiceURL);

        return $e;
    }

}
