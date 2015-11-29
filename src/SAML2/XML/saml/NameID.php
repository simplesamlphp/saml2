<?php

namespace SAML2\XML\saml;

use SAML2\Constants;
use SAML2\DOMDocumentFactory;

/**
 * Class representing the saml:NameID element.
 *
 * @package SimpleSAMLphp
 */
class NameID
{
    /**
     * The NameQualifier or the NameID.
     *
     * @var string|null
     */
    public $NameQualifier;

    /**
     * The SPNameQualifier or the NameID.
     *
     * @var string|null
     */
    public $SPNameQualifier;

    /**
     * The Format or the NameID.
     *
     * @var string|null
     */
    public $Format;

    /**
     * The SPProvidedID or the NameID.
     *
     * @var string|null
     */
    public $SPProvidedID;

    /**
     * The value of this NameID.
     *
     * @var string
     */
    public $value;

    /**
     * Initialize a saml:NameID.
     *
     * @param \DOMElement|null $xml The XML element we should load.
     */
    public function __construct(\DOMElement $xml = null)
    {
        if ($xml === null) {
            return;
        }

        if ($xml->hasAttribute('SPNameQualifier')) {
            $this->SPNameQualifier = $xml->getAttribute('SPNameQualifier');
        }

        if ($xml->hasAttribute('NameQualifier')) {
            $this->NameQualifier = $xml->getAttribute('NameQualifier');
        }

        if ($xml->hasAttribute('Format')) {
            $this->Format = $xml->getAttribute('Format');
        }

        if ($xml->hasAttribute('SPProvidedID')) {
            $this->SPProvidedID = $xml->getAttribute('SPProvidedID');
        }

        $this->value = trim($xml->textContent);
    }

    /**
     * Convert this NameID to XML.
     *
     * @param  \DOMElement|null $parent The element we should append to.
     * @return \DOMElement      This AdditionalMetadataLocation-element.
     */
    public function toXML(\DOMElement $parent = null)
    {
        assert('is_string($this->NameQualifier) || is_null($this->NameQualifier)');
        assert('is_string($this->SPNameQualifier) || is_null($this->SPNameQualifier)');
        assert('is_string($this->Format) || is_null($this->Format)');
        assert('is_string($this->SPProvidedID) || is_null($this->SPProvidedID)');
        assert('is_string($this->value)');

        if ($parent === null) {
            $parent = DOMDocumentFactory::create();
            $doc = $parent;
        } else {
            $doc = $parent->ownerDocument;
        }
        $e = $doc->createElementNS(Constants::NS_SAML, 'saml:NameID');
        $parent->appendChild($e);

        if ($this->NameQualifier !== null) {
            $e->setAttribute('NameQualifier', $this->NameQualifier);
        }

        if ($this->SPNameQualifier !== null) {
            $e->setAttribute('SPNameQualifier', $this->SPNameQualifier);
        }

        if ($this->Format !== null) {
            $e->setAttribute('Format', $this->Format);
        }

        if ($this->SPProvidedID !== null) {
            $e->setAttribute('SPProvidedID', $this->SPProvidedID);
        }

        $t = $doc->createTextNode($this->value);
        $e->appendChild($t);

        return $e;
    }
}
