<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use DOMElement;
use SAML2\Constants;
use SimpleSAML\Assert\Assert;

/**
 * SAML Condition data type.
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
class Condition extends AbstractConditionType
{
    /** @var string */
    protected $type;


    /**
     * Initialize a saml:Condition from scratch
     *
     * @param string $value
     * @param string $type
     */
    protected function __construct(
        string $value,
        string $type
    ) {
        parent::__construct($value);

        $this->setType($type);
    }


    /**
     * Get the type of this Condition (expressed in the xsi:type attribute).
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }


    /**
     * Set the type of this Condition (in the xsi:type attribute)
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
     * @inheritDoc
     */
    final public function getLocalName(): string
    {
        // All descendants of this class are supposed to be <saml:Condition /> elements and shouldn't define a new element
        return 'Condition';
    }


    /**
     * Convert this Condition to XML.
     *
     * @param \DOMElement $parent The element we are converting to XML.
     * @return \DOMElement The XML element after adding the data corresponding to this Condition.
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $element = parent::toXML($parent);
        $element->setAttributeNS(Constants::NS_XSI, 'xsi:type', $this->type);

        return $element;
    }


    /**
     * Convert XML into an Condition
     *
     * @param \DOMElement $xml The XML element we should load
     *
     * @return \SAML2\XML\saml\Condition
     * @throws \InvalidArgumentException  If xsi:type is not defined or does not implement IdentifierInterface
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'Condition');
        Assert::notNull($xml->namespaceURI);
        Assert::same($xml->namespaceURI, Condition::NS);
        Assert::true($xml->hasAttributeNS(Constants::NS_XSI, 'type'), 'Missing required xsi:type in <saml:Condition> element.');

        $type = $xml->getAttributeNS(Constants::NS_XSI, 'type');

        return new self(
            trim($xml->textContent),
            $type
        );
    }
}
