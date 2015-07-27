<?php

/**
 * Class representing the IDPEntry element.
 *
 * @package simpleSAMLphp
 * @version $Id$
 */
class SAML2_XML_samlp_IDPEntry
{

    /**
     * The entity ID of the IdP.
     *
     * @var string
     */
    public $ProviderID;


    /**
     * Name of this entry.
     *
     * @var string|NULL
     */
    public $Name;


    /**
     * URL to an endpoint that can receive authentication requests.
     *
     * @var string|NULL
     */
    public $Loc;


    /**
     * Create a IDPEntry element.
     *
     * @param DOMElement|NULL $xml  The XML element we should load.
     */
    public function __construct(DOMElement $xml = NULL)
    {

        if ($xml === NULL) {
            return;
        }

        if (!$xml->hasAttribute('ProviderID')) {
            throw new Exception('Missing required attribute "ProviderID" in <samlp:IDPEntry>.');
        }
        $this->ProviderID = $xml->getAttribute('ProviderID');

        if ($xml->hasAttribute('Name')) {
            $this->Name = $xml->getAttribute('Name');
        }

        if ($xml->hasAttribute('Loc')) {
            $this->Loc = $xml->getAttribute('Loc');
        }
    }


    /**
     * Convert this IDPEntry to XML.
     *
     * @param DOMElement $parent  The element we should append this element to.
     */
    public function toXML(DOMElement $parent)
    {
        assert('is_string($this->ProviderID)');
        assert('is_string($this->Name) || is_null($this->Name)');
        assert('is_string($this->Loc) || is_null($this->Loc)');

        $doc = $parent->ownerDocument;

        $e = $doc->createElementNS(SAML2_Const::NS_SAMLP, 'samlp:IDPEntry');
        $parent->appendChild($e);

        $e->setAttribute('ProviderID', $this->ProviderID);

        if ($this->Name !== NULL) {
            $e->setAttribute('Name', $this->Name);
        }

        if ($this->Loc !== NULL) {
            $e->setAttribute('Loc', $this->Loc);
        }

        return $e;
    }

}
