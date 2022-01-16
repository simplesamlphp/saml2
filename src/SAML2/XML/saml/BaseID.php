<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\XML\IDNameQualifiersTrait;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\XMLStringElementTrait;

use function trim;

/**
 * SAML BaseID data type.
 *
 * @package simplesamlphp/saml2
 */
class BaseID extends AbstractSamlElement implements BaseIdentifierInterface
{
    use IDNameQualifiersTrait;
    use XMLStringElementTrait;

    /** @var string */
    public const LOCALNAME = 'BaseID';

    /** @var string */
    protected string $type;


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
        $this->setContent($value);
        $this->setNameQualifier($NameQualifier);
        $this->setSPNameQualifier($SPNameQualifier);
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
     */
    protected function setType(string $type): void
    {
        Assert::notWhitespaceOnly($type, 'The "xsi:type" attribute of an identifier cannot be empty.');

        $this->type = $type;
    }


    /**
     * Validate the content of the element.
     *
     * @param string $content  The value to go in the XML textContent
     * @throws \Exception on failure
     * @return void
     */
    protected function validateContent(string $content): void
    {
        Assert::notWhitespaceOnly($content);
    }


    /**
     * Convert XML into an BaseID
     *
     * @param \DOMElement $xml The XML element we should load
     *
     * @return \SimpleSAML\SAML2\XML\saml\BaseID
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'BaseID', InvalidDOMElementException::class);
        Assert::notNull($xml->namespaceURI, InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, BaseID::NS, InvalidDOMElementException::class);
        Assert::true(
            $xml->hasAttributeNS(Constants::NS_XSI, 'type'),
            'Missing required xsi:type in <saml:BaseID> element.',
            InvalidDOMElementException::class
        );

        $type = $xml->getAttributeNS(Constants::NS_XSI, 'type');

        return new self(
            $type,
            trim($xml->textContent),
            self::getAttribute($xml, 'NameQualifier', null),
            self::getAttribute($xml, 'SPNameQualifier', null)
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

        $element->textContent = $this->content;

        $element->setAttributeNS(Constants::NS_XSI, 'xsi:type', $this->type);

        return $element;
    }
}
