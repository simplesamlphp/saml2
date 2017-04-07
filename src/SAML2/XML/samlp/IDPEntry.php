<?php

namespace SAML2\XML\samlp;

use SAML2\Constants;
use SAML2\Utils;

/**
 * Class representing the IDPEntry element.
 *
 * @version $Id$
 */
class IDPEntry
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
     * @var string|null
     */
    public $Name;

    /**
     * URL to an endpoint that can receive authentication requests.
     *
     * @var string|null
     */
    public $Loc;

    /**
     * Create a IDPEntry element.
     *
     * @param \DOMElement|null $xml The XML element we should load.
     */
    public function __construct(\DOMElement $xml = null)
    {
        if ($xml === null) {
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
     * @param \DOMElement $parent The element we should append this element to.
     */
    public function toXML(\DOMElement $parent)
    {
        assert('is_string($this->ProviderID)');
        assert('is_string($this->Name) || is_null($this->Name)');
        assert('is_string($this->Loc) || is_null($this->Loc)');

        $doc = $parent->ownerDocument;

        $e = $doc->createElementNS(Constants::NS_SAMLP, 'samlp:IDPEntry');
        $parent->appendChild($e);

        $e->setAttribute('ProviderID', $this->ProviderID);

        if ($this->Name !== null) {
            $e->setAttribute('Name', $this->Name);
        }

        if ($this->Loc !== null) {
            $e->setAttribute('Loc', $this->Loc);
        }

        return $e;
    }
}
