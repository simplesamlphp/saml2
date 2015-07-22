<?php

/**
 * Class representing the ECP Request element.
 *
 * @package simpleSAMLphp
 * @version $Id$
 */
class SAML2_XML_ecp_Request {

	/**
	 * Name of the service provider.
	 *
	 * @var string|NULL
	 */
	public $ProviderName;


	/**
	 * Whether this is a passive request.
	 *
	 * @var boolean|NULL
	 */
	public $IsPassive;


	/**
	 * The issuer of this message.
	 *
	 * @var string
	 */
	public $Issuer;


	/**
	 * The list of acceptable IdPs.
	 *
	 * @var SAML2_XML_samlp_IDPList|NULL
	 */
	public $IDPList;


	/**
	 * Create a ECP Request element.
	 *
	 * @param DOMElement|NULL $xml  The XML element we should load.
	 */
	public function __construct(DOMElement $xml = NULL) {

		if ($xml === NULL) {
			return;
		}

		if (!$xml->hasAttributeNS(SAML2_Const::NS_SOAP, 'mustUnderstand')) {
			throw new Exception('Missing soap-env:mustUnderstand attribute in <ecp:Request>.');
		} elseif ($xml->getAttributeNS(SAML2_Const::NS_SOAP, 'mustUnderstand') !== '1') {
			throw new Exception('Invalid value of soap-env:mustUnderstand attribute in <ecp:Request>.');
		}

		if (!$xml->hasAttributeNS(SAML2_Const::NS_SOAP, 'actor')) {
			throw new Exception('Missing soap-env:mustUnderstand attribute in <ecp:Request>.');
		} elseif ($xml->getAttributeNS(SAML2_Const::NS_SOAP, 'actor') !== 'http://schemas.xmlsoap.org/soap/actor/next') {
			throw new Exception('Invalid value of soap-env:actor attribute in <ecp:Request>.');
		}

		if ($xml->hasAttribute('ProviderName')) {
			$this->ProviderName = $xml->getAttribute('ProviderName');
		}

		$this->IsPassive = SAML2_Utils::parseBoolean($xml, 'IsPassive', NULL);

		$issuer = SAML2_Utils::xpQuery($xml, './saml_assertion:Issuer');
		if (empty($issuer)) {
			throw new Exception('Missing <saml:Issuer> in <ecp:Request>.');
		} elseif (count($issuer) > 1) {
			throw new Exception('More than one <saml:Issuer> in <ecp:Request>.');
		}
		$this->Issuer = trim($issuer[0]->textContent);

		$idpList = SAML2_Utils::xpQuery($xml, './saml_protocol:IDPList');
		if (count($idpList) === 1) {
			$this->IDPList = new SAML2_XML_samlp_IDPList($idpList[0]);
		} elseif (count($idpList) > 1) {
			throw new Exception('More than one <samlp:IDPList> in ECP Request.');
		}
	}


	/**
	 * Convert this ECP Request to XML.
	 *
	 * @param DOMElement $parent  The element we should append this element to.
	 */
	public function toXML(DOMElement $parent) {
		assert('is_string($this->ProviderName) || is_null($this->ProviderName)');
		assert('is_bool($this->IsPassive) || is_null($this->IsPassive)');
		assert('is_string($this->Issuer)');
		assert('is_null($this->IDPList) || $this->IDPList instanceof SAML2_XML_samlp_IDPList');

		$doc = $parent->ownerDocument;

		$e = $doc->createElementNS(SAML2_Const::NS_ECP, 'ecp:Request');
		$parent->appendChild($e);

		$e->setAttributeNS(SAML2_Const::NS_SOAP, 'SOAP-ENV:mustUnderstand', '1');
		$e->setAttributeNS(SAML2_Const::NS_SOAP, 'SOAP-ENV:actor', 'http://schemas.xmlsoap.org/soap/actor/next');

		if ($this->ProviderName !== NULL) {
			$e->setAttribute('ProviderName', $this->ProviderName);
		}

		if ($this->IsPassive === TRUE) {
			$e->setAttribute('IsPassive', 'true');
		} elseif ($this->IsPassive === FALSE) {
			$e->setAttribute('IsPassive', 'false');
		}

		SAML2_Utils::addString($e, SAML2_Const::NS_SAML, 'saml:Issuer', $this->Issuer);

		if ($this->IDPList !== NULL) {
			$this->IDPList->toXML($e);
		}

		return $e;
	}

}