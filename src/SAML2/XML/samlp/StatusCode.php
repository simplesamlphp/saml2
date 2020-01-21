<?php

declare(strict_types=1);

namespace SAML2\XML\samlp;

use DOMElement;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\Utils;
use Webmozart\Assert\Assert;

/**
 * SAML StatusCode data type.
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
final class StatusCode extends AbstractSamlpElement
{
    /** @var string */
    protected $Value;

    /** @var StatusCode[]|null */
    protected $subCodes = null;


    /**
     * Initialize a samlp:StatusCode
     *
     * @param string $Value
     * @param StatusCode[]|null $subCodes
     */
    public function __construct(string $Value = Constants::STATUS_SUCCESS, ?array $subCodes = null)
    {
        $this->setValue($Value);
        $this->setSubCodes($subCodes);
    }


    /**
     * Collect the Value
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->Value;
    }


    /**
     * Set the value of the Value-property
     *
     * @param string $Value
     * @return void
     */
    private function setValue(string $Value): void
    {
        Assert::stringNotEmpty($Value);
        $this->Value = $Value;
    }


    /**
     * Collect the subcodes
     *
     * @return StatusCode[]|null
     */
    public function getSubCodes(): ?array
    {
        return $this->subCodes;
    }


    /**
     * Set the value of the subCodes-property
     *
     * @param StatusCode[]|null $subCodes
     * @return void
     */
    private function setSubCodes(?array $subCodes): void
    {
        if (!is_null($subCodes)) {
            Assert::allIsInstanceOf($subCodes, StatusCode::class);
        }
        $this->subCodes = $subCodes;
    }


    /**
     * Convert XML into a StatusCode
     *
     * @param \DOMElement $xml The XML element we should load
     * @return \SAML2\XML\samlp\StatusCode
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'StatusCode');
        Assert::same($xml->namespaceURI, Constants::NS_SAMLP);

        $Value = $xml->hasAttribute('Value') ? $xml->getAttribute('Value') : null;

        Assert::notNull($Value, 'Missing mandatory Value-attribute for StatusCode');

        /** @var \DOMElement[] $subCodes */
        $subCodes = Utils::xpQuery($xml, './saml_protocol:StatusCode');

        $subCodeObjs = null;
        if (!empty($subCodes)) {
            $subCodeObjs = [];
            foreach ($subCodes as $subCode) {
                $subCodeObjs[] = StatusCode::fromXML($subCode);
            }
        }

        return new self(
            $Value,
            $subCodeObjs
        );
    }


    /**
     * Convert this StatusCode to XML.
     *
     * @param \DOMElement|null $parent The element we should append this NameIDPolicy to.
     * @throws \Exception
     * @return \DOMElement
     *
     * @throws \InvalidArgumentException if assertions are false
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);
        $e->setAttribute('Value', $this->getValue());

        if (!empty($this->subCodes)) {
            foreach ($this->subCodes as $subCode) {
                $subCode->toXML($e);
            }
        }

        return $e;
    }
}
