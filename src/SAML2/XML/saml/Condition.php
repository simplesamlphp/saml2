<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\XML\Exception\InvalidDOMElementException;

use function trim;

/**
 * SAML Condition data type.
 *
 * @package simplesamlphp/saml2
 */
class Condition extends AbstractConditionType
{
    /** @var string */
    public const LOCALNAME = 'Condition';

    /** @var string */
    protected string $type;


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
     */
    protected function setType(string $type): void
    {
        Assert::notWhitespaceOnly($type, 'The "xsi:type" attribute of an identifier cannot be empty.');

        $this->type = $type;
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
     * @return \SimpleSAML\SAML2\XML\saml\Condition
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'Condition', InvalidDOMElementException::class);
        Assert::notNull($xml->namespaceURI, InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, Condition::NS, InvalidDOMElementException::class);
        Assert::true(
            $xml->hasAttributeNS(Constants::NS_XSI, 'type'),
            'Missing required xsi:type in <saml:Condition> element.',
            InvalidDOMElementException::class
        );

        $type = $xml->getAttributeNS(Constants::NS_XSI, 'type');

        return new self(
            trim($xml->textContent),
            $type
        );
    }
}
