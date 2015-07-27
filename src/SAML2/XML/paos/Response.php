<?php

/**
 * Class representing the PAOS Response element.
 *
 * @package simpleSAMLphp
 * @version $Id$
 */
class SAML2_XML_paos_Response
{

    /**
     * The message ID from the request.
     *
     * @var string|NULL
     */
    public $refToMessageID;


    /**
     * Create a PAOS Response element.
     *
     * @param DOMElement|NULL $xml  The XML element we should load.
     */
    public function __construct(DOMElement $xml = NULL)
    {

        if ($xml === NULL) {
            return;
        }

        if (!$xml->hasAttributeNS(SAML2_Const::NS_SOAP, 'mustUnderstand')) {
            throw new Exception('Missing soap-env:mustUnderstand attribute in <paos:Response>.');
        } elseif ($xml->getAttributeNS(SAML2_Const::NS_SOAP, 'mustUnderstand') !== '1') {
            throw new Exception('Invalid value of soap-env:mustUnderstand attribute in <paos:Response>.');
        }

        if (!$xml->hasAttributeNS(SAML2_Const::NS_SOAP, 'actor')) {
            throw new Exception('Missing soap-env:mustUnderstand attribute in <paos:Response>.');
        } elseif ($xml->getAttributeNS(SAML2_Const::NS_SOAP, 'actor') !== 'http://schemas.xmlsoap.org/soap/actor/next') {
            throw new Exception('Invalid value of soap-env:actor attribute in <paos:Response>.');
        }

        if ($xml->hasAttribute('refToMessageID')) {
            $this->refToMessageID = $xml->getAttribute('refToMessageID');
        }
    }


    /**
     * Convert this PAOS Response to XML.
     *
     * @param DOMElement $parent  The element we should append this element to.
     */
    public function toXML(DOMElement $parent)
    {
        assert('is_string($this->refToMessageID) || is_null($this->refToMessageID)');

        $doc = $parent->ownerDocument;

        $e = $doc->createElementNS('urn:liberty:paos:2003-08', 'paos:Response');
        $parent->appendChild($e);

        $e->setAttributeNS(SAML2_Const::NS_SOAP, 'SOAP-ENV:mustUnderstand', '1');
        $e->setAttributeNS(SAML2_Const::NS_SOAP, 'SOAP-ENV:actor', 'http://schemas.xmlsoap.org/soap/actor/next');

        if ($this->refToMessageID !== NULL) {
            $e->setAttribute('refToMessageID', $this->refToMessageID);
        }

        return $e;
    }

}
