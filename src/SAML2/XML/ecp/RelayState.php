<?php

/**
 * Class representing the ECP RelayState element.
 *
 * @package simpleSAMLphp
 * @version $Id$
 */
class SAML2_XML_ecp_RelayState {

	/**
	 * The RelayState.
	 *
	 * @var string
	 */
	public $RelayState;


	/**
	 * Create a ECP RelayState element.
	 *
	 * @param DOMElement|NULL $xml  The XML element we should load.
	 */
	public function __construct(DOMElement $xml = NULL) {

		if ($xml === NULL) {
			return;
		}

		if (!$xml->hasAttributeNS(SAML2_Const::NS_SOAP, 'mustUnderstand')) {
			throw new Exception('Missing soap-env:mustUnderstand attribute in <ecp:RelayState>.');
		} elseif ($xml->getAttributeNS(SAML2_Const::NS_SOAP, 'mustUnderstand') !== '1') {
			throw new Exception('Invalid value of soap-env:mustUnderstand attribute in <ecp:RelayState>.');
		}

		if (!$xml->hasAttributeNS(SAML2_Const::NS_SOAP, 'actor')) {
			throw new Exception('Missing soap-env:mustUnderstand attribute in <ecp:RelayState>.');
		} elseif ($xml->getAttributeNS(SAML2_Const::NS_SOAP, 'actor') !== 'http://schemas.xmlsoap.org/soap/actor/next') {
			throw new Exception('Invalid value of soap-env:actor attribute in <ecp:RelayState>.');
		}

		$this->RelayState = $xml->textContent;
	}


	/**
	 * Convert this ECP RelayState to XML.
	 *
	 * @param DOMElement $parent  The element we should append this element to.
	 */
	public function toXML(DOMElement $parent) {
		assert('is_string($this->RelayState)');

		$doc = $parent->ownerDocument;

		$e = $doc->createElementNS(SAML2_Const::NS_ECP, 'ecp:RelayState');
		$parent->appendChild($e);

		$e->setAttributeNS(SAML2_Const::NS_SOAP, 'SOAP-ENV:mustUnderstand', '1');
		$e->setAttributeNS(SAML2_Const::NS_SOAP, 'SOAP-ENV:actor', 'http://schemas.xmlsoap.org/soap/actor/next');

		$e->appendChild($doc->createTextNode($this->RelayState));

		return $e;
	}

}