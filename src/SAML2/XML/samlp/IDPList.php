<?php

namespace SAML2\XML\samlp;

use SAML2\Constants;
use SAML2\Utils;
use SAML2\XML\samlp;

/**
 * Class representing the IDPList element.
 *
 * @version $Id$
 */
class IDPList
{
    /**
     * The list of IdPs.
     *
     * @var array Array of SAML2_XML_samlp_IDPEntry
     */
    public $IDPEntry = array();

    /**
     * URL to complete list of IdPs.
     *
     * @var string|null
     */
    public $GetComplete;

    /**
     * Create a IDPList element.
     *
     * @param \DOMElement|null $xml The XML element we should load.
     */
    public function __construct(\DOMElement $xml = null)
    {
        if ($xml === null) {
            return;
        }

        $ies = Utils::xpQuery($xml, './saml_protocol:IDPEntry');
        if (empty($ies)) {
            throw new Exception('Missing <samlp:IDPEntry> in <samlp:IDPList>.');
        }
        foreach ($ies as $ie) {
            $this->IDPEntry[] = new \SAML2\XML\samlp\IDPEntry($ie);
        }

        $getComplete = Utils::xpQuery($xml, './saml_protocol:GetComplete');
        if (count($getComplete) > 1) {
            throw new Exception('More than one <samlp:GetComplete> in <samlp:IDPList>.');
        } elseif (!empty($getComplete)) {
            $this->GetComplete = trim($getComplete[0]->textContent);
        }
    }

    /**
     * Convert this IDPList to XML.
     *
     * @param \DOMElement $parent The element we should append this element to.
     */
    public function toXML(\DOMElement $parent)
    {
        assert('is_array($this->IDPEntry)');
        assert('!empty($this->IDPEntry)');
        assert('is_string($this->GetComplete) || is_null($this->GetComplete)');

        $doc = $parent->ownerDocument;

        $e = $doc->createElementNS(Constants::NS_SAMLP, 'samlp:IDPList');
        $parent->appendChild($e);

        foreach ($this->IDPEntry as $ie) {
            $ie->toXML($e);
        }

        if ($this->GetComplete !== null) {
            Utils::addString($e, Constants::NS_SAMLP, 'samlp:GetComplete', $this->GetComplete);
        }

        return $e;
    }
}
