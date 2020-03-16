<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use DOMElement;
use SAML2\Constants;
use SAML2\XML\IDNameQualifiersTrait;
use Webmozart\Assert\Assert;

/**
 * SAML BaseID data type.
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
class BaseID extends AbstractSamlElement implements BaseIdentifierInterface
{
    use IDNameQualifiersTrait;

    /** @var string */
    protected $value;

    /** @var string */
    protected $type;


    /**
     * Initialize a saml:BaseID from scratch
     *
     * @param string $type
     * @param string $value
     * @param string|null $NameQualifier
     * @param string|null $SPNameQualifier
     */
    protected function __construct(
        string $type,
        string $value,
        ?string $NameQualifier = null,
        ?string $SPNameQualifier = null
    ) {
        $this->setType($type);
        $this->setValue($value);
        $this->setNameQualifier($NameQualifier);
        $this->setSPNameQualifier($SPNameQualifier);
    }


    /**
     * @inheritDoc
     */
    final public function getLocalName(): string
    {
        // All descendants of this class are supposed to be <saml:BaseID /> elements and shouldn't define a new element
        return 'BaseID';
    }


    /**
     * Get the type of this BaseID (expressed in the xsi:type attribute).
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }


    /**
     * Set the type of this BaseID (in the xsi:type attribute)
     *
     * @param string $type
     * @return void
     */
    protected function setType(string $type): void
    {
        Assert::notEmpty($type, 'The "xsi:type" attribute of an identifier cannot be empty.');
        $this->type = $type;
    }


    /**
     * Get the string value of this BaseID.
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }


    /**
     * Set the string value of this BaseID.
     *
     * @param string $value
     * @return void
     */
    protected function setValue(string $value): void
    {
        $this->value = $value;
    }


    /**
     * Convert XML into an BaseID
     *
     * @param \DOMElement $xml The XML element we should load
     *
     * @return \SAML2\XML\saml\BaseID
     * @throws \InvalidArgumentException  If xsi:type is not defined or does not implement IdentifierInterface
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'BaseID');
        Assert::notNull($xml->namespaceURI);
        Assert::same($xml->namespaceURI, BaseID::NS);
        Assert::true($xml->hasAttributeNS(Constants::NS_XSI, 'type'), 'Missing required xsi:type in <saml:BaseID> element.');

        $type = $xml->getAttributeNS(Constants::NS_XSI, 'type');

        return new self(
            $type,
            trim($xml->textContent),
            $xml->hasAttribute('NameQualifier') ? $xml->getAttribute('NameQualifier') : null,
            $xml->hasAttribute('SPNameQualifier') ? $xml->getAttribute('SPNameQualifier') : null
        );
    }


    /**
     * Convert this BaseID to XML.
     *
     * @param \DOMElement $parent The element we are converting to XML.
     * @return \DOMElement The XML element after adding the data corresponding to this BaseID.
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $element = $this->instantiateParentElement($parent);

        if ($this->NameQualifier !== null) {
            $element->setAttribute('NameQualifier', $this->NameQualifier);
        }

        if ($this->SPNameQualifier !== null) {
            $element->setAttribute('SPNameQualifier', $this->SPNameQualifier);
        }

        $element->textContent = $this->value;

        $element->setAttributeNS(Constants::NS_XSI, 'xsi:type', $this->type);

        return $element;
    }
}
