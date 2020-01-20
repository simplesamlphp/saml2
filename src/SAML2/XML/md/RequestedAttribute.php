<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use DOMElement;
use SAML2\Constants;
use SAML2\XML\saml\Attribute;
use SAML2\XML\saml\AttributeValue;
use Webmozart\Assert\Assert;

/**
 * Class representing SAML 2 metadata RequestedAttribute.
 *
 * @package SimpleSAMLphp
 */
final class RequestedAttribute extends Attribute
{
    /** @var string */
    public const NS = Constants::NS_MD;

    /** @var string */
    public const NS_PREFIX = 'md';

    /**
     * Whether this attribute is required.
     *
     * @var bool|null
     */
    protected $isRequired = null;


    /**
     * RequestedAttribute constructor.
     *
     * @param string      $Name
     * @param bool|null   $isRequired
     * @param string|null $NameFormat
     * @param string|null $FriendlyName
     * @param \SAML2\XML\saml\AttributeValue[]|null  $AttributeValues
     */
    public function __construct(
        string $Name,
        ?bool $isRequired = null,
        ?string $NameFormat = null,
        ?string $FriendlyName = null,
        ?array $AttributeValues = null
    ) {
        parent::__construct($Name, $NameFormat, $FriendlyName, $AttributeValues);
        $this->setIsRequired($isRequired);
    }


    /**
     * Collect the value of the isRequired-property
     *
     * @return bool|null
     */
    public function getIsRequired(): ?bool
    {
        return $this->isRequired;
    }


    /**
     * Set the value of the isRequired-property
     *
     * @param bool|null $flag
     */
    protected function setIsRequired(?bool $flag): void
    {
        $this->isRequired = $flag;
    }


    /**
     * Convert XML into a RequestedAttribute
     *
     * @param \DOMElement $xml The XML element we should load
     * @return self
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'RequestedAttribute');
        Assert::same($xml->namespaceURI, Constants::NS_MD);

        return new self(
            self::getAttribute($xml, 'Name'),
            self::getBooleanAttribute($xml, 'isRequired', null),
            self::getAttribute($xml, 'NameFormat', null),
            self::getAttribute($xml, 'FriendlyName', null),
            AttributeValue::getChildrenOfClass($xml)
        );
    }


    /**
     * Convert this RequestedAttribute to XML.
     *
     * @param \DOMElement|null $parent The element we should append this RequestedAttribute to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = parent::toXML($parent);

        if (is_bool($this->isRequired)) {
            $e->setAttribute('isRequired', $this->isRequired ? 'true' : 'false');
        }

        $e->setAttribute('Name', $attribute->getName());

        $nameFormat = $attribute->getNameFormat();
        if ($nameFormat !== null) {
            $e->setAttribute('NameFormat', $nameFormat());
        }

        $friendlyName = $attribute->getFriendlyName();
        if ($friendlyName !== null) {
            $e->setAttribute('FriendlyName', $friendlyName);
        }

        $attributeValues = $attribute->getAttributeValues();
        if (!empty($attributeValues)) {
            foreach ($attributeValues as $av) {
                $e->appendChild($e->ownerDocument->importNode($av->toXML(), true));
            }
        }

        return $e;
    }
}
