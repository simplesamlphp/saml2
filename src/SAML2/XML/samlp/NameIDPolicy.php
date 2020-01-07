<?php

declare(strict_types=1);

namespace SAML2\XML\samlp;

use DOMElement;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;

/**
 * Class for handling SAML2 NameIDPolicy.
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package SimpleSAMLphp
 */
class NameIDPolicy extends \SAML2\XML\AbstractConvertable
{
    /** @var string|null */
    private $Format = null;

    /** @var string|null */
    private $SPNameQualifier = null;

    /** @var bool|null */
    private $AllowCreate = null;


    /**
     * Initialize a NameIDPolicy.
     *
     * @param string|null $Format
     * @param string|null $SPNameQualifier
     * @param bool|null $AllowCreate
     */
    public function __construct(?string $Format = null, ?string $SPNameQualifier = null, ?bool $AllowCreate = null)
    {
        $this->setFormat($Format);
        $this->setSPNameQualifier($SPNameQualifier);
        $this->setAllowCreate($AllowCreate);
    }


    /**
     * @return string|null
     */
    public function getFormat(): ?string
    {
        return $this->Format;
    }


    /**
     * @param string|null $Format
     * @return void
     */
    public function setFormat(?string $Format): void
    {
        $this->Format = $Format;
    }


    /**
     * @return string|null
     */
    public function getSPNameQualifier(): ?string
    {
        return $this->SPNameQualifier;
    }


    /**
     * @param string|null $SPNameQualifier
     * @return void
     */
    public function setSPNameQualifier(?string $SPNameQualifier): void
    {
        $this->SPNameQualifier = $SPNameQualifier;
    }


    /**
     * @return bool|null
     */
    public function getAllowCreate(): ?bool
    {
        return $this->AllowCreate;
    }


    /**
     * @param bool|null $AllowCreate
     * @return void
     */
    public function setAllowCreate(?bool $AllowCreate): void
    {
        $this->AllowCreate = $AllowCreate;
    }


    /**
     * Convert XML into a NameIDPolicy
     *
     * @param \DOMElement $xml The XML element we should load
     * @return \SAML2\XML\samlp\NameIDPolicy
     */
    public static function fromXML(DOMElement $xml): object
    {
        $Format = $xml->hasAttribute('Format') ? $xml->getAttribute('Format') : null;
        $SPNameQualifier = $xml->hasAttribute('SPNameQualifier') ? $xml->getAttribute('SPNameQualifier') : null;
        $AllowCreate = $xml->hasAttribute('AllowCreate') ? $xml->getAttribute('AllowCreate') : null;

        return new self(
            $Format,
            $SPNameQualifier,
            ($AllowCreate === 'true') ? true : false
        );
    }
     

    /**
     * Convert this NameIDPolicy to XML.
     *
     * @param \DOMElement|null $parent The element we should append this NameIDPolicy to.
     * @throws \Exception
     * @return \DOMElement
     *
     * @throws \InvalidArgumentException if assertions are false
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        if ($parent === null) {
            $doc = DOMDocumentFactory::create();
            $e = $doc->createElementNS(Constants::NS_SAMLP, 'samlp:NameIDPolicy');
            $doc->appendChild($e);
        } else {
            $e = $parent->ownerDocument->createElementNS(Constants::NS_SAMLP, 'samlp:NameIDPolicy');
            $parent->appendChild($e);
        }

        if (isset($this->Format)) {
            $e->setAttribute('Format', $this->Format);
        }

        if (isset($this->SPNameQualifier)) {
            $e->setAttribute('SPNameQualifier', $this->SPNameQualifier);
        }

        if (isset($this->AllowCreate)) {
            $e->setAttribute('AllowCreate', var_export($this->AllowCreate, true));
        }

        return $e;
    }
}
