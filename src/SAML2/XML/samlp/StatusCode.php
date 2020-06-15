<?php

declare(strict_types=1);

namespace SAML2\XML\samlp;

use DOMElement;
use SAML2\Constants;
use SAML2\Exception\InvalidDOMElementException;
use SimpleSAML\Assert\Assert;

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

    /** @var \SAML2\XML\samlp\StatusCode[] */
    protected $subCodes = [];


    /**
     * Initialize a samlp:StatusCode
     *
     * @param string $Value
     * @param \SAML2\XML\samlp\StatusCode[] $subCodes
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
     * @return void
     * @throws \InvalidArgumentException if the supplied $Value is empty
     */
    private function setValue(string $Value): void
    {
        Assert::notEmpty($Value);
        $this->Value = $Value;
    }


    /**
     * Collect the subcodes
     *
     * @return \SAML2\XML\samlp\StatusCode[]
     */
    public function getSubCodes(): array
    {
        return $this->subCodes;
    }


    /**
     * Set the value of the subCodes-property
     *
     * @param \SAML2\XML\samlp\StatusCode[] $subCodes
     * @return void
     * @throws \InvalidArgumentException if the supplied array contains anything other than StatusCode objects
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
     * @return \SAML2\XML\samlp\StatusCode
     *
     * @throws \SAML2\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     * @throws \SAML2\Exception\MissingAttributeException if the supplied element is missing one of the mandatory attributes
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
