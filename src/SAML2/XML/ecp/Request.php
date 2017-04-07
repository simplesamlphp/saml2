<?php

namespace SAML2\XML\ecp;

use SAML2\Constants;
use SAML2\Utils;
use SAML2\XML\samlp;

/**
 * Class representing the ECP Request element.
 *
 * @version $Id$
 */
class Request
{
    /**
     * Name of the service provider.
     *
     * @var string|null
     */
    public $ProviderName;

    /**
     * Whether this is a passive request.
     *
     * @var bool|null
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
     * @var SAML2_XML_samlp_IDPList|null
     */
    public $IDPList;

    /**
     * Create a ECP Request element.
     *
     * @param \DOMElement|null $xml The XML element we should load.
     */
    public function __construct(\DOMElement $xml = null)
    {
        if ($xml === null) {
            return;
        }

        if ($this->checkXML($xml) !== true) {
            throw new Exception($this->checkXML($xml));
        }

        if ($xml->hasAttribute('ProviderName')) {
            $this->ProviderName = $xml->getAttribute('ProviderName');
        }

        $this->IsPassive = Utils::parseBoolean($xml, 'IsPassive', null);

        $issuer = Utils::xpQuery($xml, './saml_assertion:Issuer');
        if (empty($issuer)) {
            throw new Exception('Missing <saml:Issuer> in <ecp:Request>.');
        } elseif (count($issuer) > 1) {
            throw new Exception('More than one <saml:Issuer> in <ecp:Request>.');
        }
        $this->Issuer = trim($issuer[0]->textContent);

        $idpList = Utils::xpQuery($xml, './saml_protocol:IDPList');
        if (count($idpList) === 1) {
            $this->IDPList = new \SAML2\XML\samlp\IDPList($idpList[0]);
        } elseif (count($idpList) > 1) {
            throw new Exception('More than one <samlp:IDPList> in ECP Request.');
        }
    }

    /**
     * Basic check of the given XML, it is not part of __construct in order to reduce NPath Complexity.
     *
     * @param \DOMElement|null $xml The XML element we should check before loading.
     */
    public function checkXML(\DOMElement $xml)
    {
        if (!$xml->hasAttributeNS(Constants::NS_SOAP, 'mustUnderstand')) {
            return 'Missing soap-env:mustUnderstand attribute in <ecp:Request>.';
        } elseif ($xml->getAttributeNS(Constants::NS_SOAP, 'mustUnderstand') !== '1') {
            return 'Invalid value of soap-env:mustUnderstand attribute in <ecp:Request>.';
        }

        if (!$xml->hasAttributeNS(Constants::NS_SOAP, 'actor')) {
            return 'Missing soap-env:mustUnderstand attribute in <ecp:Request>.';
        } elseif ($xml->getAttributeNS(Constants::NS_SOAP, 'actor') !== 'http://schemas.xmlsoap.org/soap/actor/next') {
            return 'Invalid value of soap-env:actor attribute in <ecp:Request>.';
        }

        return true;
    }

    /**
     * Convert this ECP Request to XML.
     *
     * @param \DOMElement $parent The element we should append this element to.
     */
    public function toXML(\DOMElement $parent)
    {
        assert('is_string($this->ProviderName) || is_null($this->ProviderName)');
        assert('is_bool($this->IsPassive) || is_null($this->IsPassive)');
        assert('is_string($this->Issuer)');
        assert('is_null($this->IDPList) || $this->IDPList instanceof \SAML2\XML\samlp\IDPList');

        $doc = $parent->ownerDocument;

        $e = $doc->createElementNS(Constants::NS_ECP, 'ecp:Request');
        $parent->appendChild($e);

        $e->setAttributeNS(Constants::NS_SOAP, 'SOAP-ENV:mustUnderstand', '1');
        $e->setAttributeNS(Constants::NS_SOAP, 'SOAP-ENV:actor', 'http://schemas.xmlsoap.org/soap/actor/next');

        if ($this->ProviderName !== null) {
            $e->setAttribute('ProviderName', $this->ProviderName);
        }

        if ($this->IsPassive === true) {
            $e->setAttribute('IsPassive', 'true');
        } elseif ($this->IsPassive === false) {
            $e->setAttribute('IsPassive', 'false');
        }

        Utils::addString($e, Constants::NS_SAML, 'saml:Issuer', $this->Issuer);

        if ($this->IDPList !== null) {
            $this->IDPList->toXML($e);
        }

        return $e;
    }
}
