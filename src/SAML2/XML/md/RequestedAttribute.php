<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\SAML2\XML\saml\Attribute;
use SimpleSAML\SAML2\XML\saml\AttributeValue;

/**
 * Class representing SAML 2 metadata RequestedAttribute.
 *
 * @package simplesamlphp/saml2
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
    protected ?bool $isRequired = null;


    /**
     * RequestedAttribute constructor.
     *
     * @param string      $Name
     * @param bool|null   $isRequired
     * @param string|null $NameFormat
     * @param string|null $FriendlyName
     * @param \SimpleSAML\SAML2\XML\saml\AttributeValue[]  $AttributeValues
     */
    public function __construct(
        string $Name,
        ?bool $isRequired = null,
        ?string $NameFormat = null,
        ?string $FriendlyName = null,
        array $AttributeValues = []
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
     * @return void
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
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingAttributeException if the supplied element is missing one of the mandatory attributes
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'RequestedAttribute', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, RequestedAttribute::NS, InvalidDOMElementException::class);

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

        return $e;
    }
}
