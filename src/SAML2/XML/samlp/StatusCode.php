<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\SchemaViolationException;

/**
 * SAML StatusCode data type.
 *
 * @package simplesamlphp/saml2
 */
final class StatusCode extends AbstractSamlpElement
{
    /** @var string */
    protected string $Value;

    /** @var \SimpleSAML\SAML2\XML\samlp\StatusCode[] */
    protected array $subCodes = [];


    /**
     * Initialize a samlp:StatusCode
     *
     * @param string $Value
     * @param \SimpleSAML\SAML2\XML\samlp\StatusCode[] $subCodes
     */
    public function __construct(string $Value = Constants::STATUS_SUCCESS, array $subCodes = [])
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
     * @throws \SimpleSAML\Assert\AssertionFailedException if the supplied $Value is empty
     */
    private function setValue(string $Value): void
    {
        Assert::validURI($Value, SchemaViolationException::class); // Covers the empty string
        $this->Value = $Value;
    }


    /**
     * Collect the subcodes
     *
     * @return \SimpleSAML\SAML2\XML\samlp\StatusCode[]
     */
    public function getSubCodes(): array
    {
        return $this->subCodes;
    }


    /**
     * Set the value of the subCodes-property
     *
     * @param \SimpleSAML\SAML2\XML\samlp\StatusCode[] $subCodes
     * @throws \SimpleSAML\Assert\AssertionFailedException if the supplied array contains anything other than StatusCode objects
     */
    private function setSubCodes(array $subCodes): void
    {
        Assert::allIsInstanceOf($subCodes, StatusCode::class);

        $this->subCodes = $subCodes;
    }


    /**
     * Convert XML into a StatusCode
     *
     * @param \DOMElement $xml The XML element we should load
     * @return \SimpleSAML\SAML2\XML\samlp\StatusCode
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingAttributeException if the supplied element is missing one of the mandatory attributes
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'StatusCode', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, StatusCode::NS, InvalidDOMElementException::class);

        $Value = self::getAttribute($xml, 'Value');
        $subCodes = StatusCode::getChildrenOfClass($xml);

        return new self(
            $Value,
            $subCodes
        );
    }


    /**
     * Convert this StatusCode to XML.
     *
     * @param \DOMElement|null $parent The element we should append this StatusCode to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);
        $e->setAttribute('Value', $this->getValue());

        foreach ($this->subCodes as $subCode) {
            $subCode->toXML($e);
        }

        return $e;
    }
}
