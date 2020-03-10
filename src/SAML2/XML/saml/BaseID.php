<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use DOMElement;
use InvalidArgumentException;
use SAML2\Compat\ContainerSingleton;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\Utils;
use SAML2\XML\IDNameQualifiersTrait;
use SAML2\XML\saml\IdentifierInterface;
use Webmozart\Assert\Assert;

/**
 * SAML BaseID data type.
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
class BaseID extends AbstractSamlElement implements IdentifierInterface
{
    use IDNameQualifiersTrait;

    /** @var string|null */
    protected $value;

    /** @var string */
    protected $type;


    /**
     * Initialize a saml:BaseID from scratch
     *
     * @param string $type
     * @param string|null $value
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
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }


    /**
     * @param string $type
     * @return void
     */
    protected function setType(string $type): void
    {
        $this->type = $type;
    }


    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }


    /**
     * @param string $value
     * @return void
     */
    protected function setValue(string $value): void
    {
        $this->value = $value;
    }


    /**
     * Get the XML local name of the element represented by this class.
     *
     * @return string
     */
    public function getLocalName(): string
    {
        // All descendants of this class are supposed to be <saml:BaseID /> elements and shouldn't define a new element
        return 'BaseID';
    }


    /**
     * Convert XML into an BaseID
     *
     * @param \DOMElement $xml The XML element we should load
     *
     * @return \SAML2\XML\AbstractXMLElement
     * @throws \InvalidArgumentException  If xsi:type is not defined or does not implement IdentifierInterface
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'BaseID');
        Assert::same($xml->namespaceURI, BaseID::NS);
        Assert::true($xml->hasAttributeNS(Constants::NS_XSI, 'type'), 'Missing required xsi:type in <saml:BaseID> element.');

        $type = $xml->getAttributeNS(Constants::NS_XSI, 'type');
        Assert::notSame($type, 'BaseID', 'Cannot inherit from tBaseID directly;  please define your own sub-class.');

        $registeredClass = ContainerSingleton::getRegisteredClass($xml->namespaceURI, $type);
        if ($registeredClass !== false) {
            if (in_array(IdentifierInterface::class, class_implements($registeredClass))) {
                return $registeredClass::fromXML($xml);
            }
            throw new InvalidArgumentException('The type \'' . $type . '\' was not found as a descendant of BaseID.');
        }

        throw new InvalidArgumentException('The type \'' . $type . '\' was not found as a registered extension.');
    }


    /**
     * Convert this NameIDType to XML.
     *
     * @param \DOMElement $parent The element we are converting to XML.
     * @return \DOMElement The XML element after adding the data corresponding to this NameIDType.
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

        if ($this->value !== null) {
            $element->textContent = $this->value;
        }

        $element->setAttributeNS(Constants::NS_XSI, 'xsi:type', $this->type);

        return $element;
    }
}
