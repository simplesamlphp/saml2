<?php

/**
 * Class representing the IDPList element.
 *
 * @package simpleSAMLphp
 * @version $Id$
 */
class SAML2_XML_samlp_IDPList {

	/**
	 * The list of IdPs
	 *
	 * @var array  Array of SAML2_XML_samlp_IDPEntry
	 */
	public $IDPEntry = array();


	/**
	 * URL to complete list of IdPs
	 *
	 * @var string|NULL
	 */
	public $GetComplete;


	/**
	 * Create a IDPList element.
	 *
	 * @param DOMElement|NULL $xml  The XML element we should load.
	 */
	public function __construct(DOMElement $xml = NULL) {

		if ($xml === NULL) {
			return;
		}

		$ies = SAML2_Utils::xpQuery($xml, './saml_protocol:IDPEntry');
		if (empty($ies)) {
			throw new Exception('Missing <samlp:IDPEntry> in <samlp:IDPList>.');
		}
		foreach ($ies as $ie) {
			$this->IDPEntry[] = new SAML2_XML_samlp_IDPEntry($ie);
		}


		$getComplete = SAML2_Utils::xpQuery($xml, './saml_protocol:GetComplete');
		if (count($getComplete) > 1) {
			throw new Exception('More than one <samlp:GetComplete> in <samlp:IDPList>.');
		} elseif (!empty($getComplete)) {
			$this->GetComplete = trim($getComplete[0]->textContent);
		}
	}


	/**
	 * Convert this IDPList to XML.
	 *
	 * @param DOMElement $parent  The element we should append this element to.
	 */
	public function toXML(DOMElement $parent) {
		assert('is_array($this->IDPEntry)');
		assert('!empty($this->IDPEntry)');
		assert('is_string($this->GetComplete) || is_null($this->GetComplete)');

		$doc = $parent->ownerDocument;

		$e = $doc->createElementNS(SAML2_Const::NS_SAMLP, 'samlp:IDPList');
		$parent->appendChild($e);

		foreach ($this->IDPEntry as $ie) {
			$ie->toXML($e);
		}

		if ($this->GetComplete !== NULL) {
			SAML2_Utils::addString($e, SAML2_Const::NS_SAMLP, 'samlp:GetComplete', $this->GetComplete);
		}

		return $e;
	}

}