<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XMLSchema\Exception\InvalidDOMElementException;

use function strval;

/**
 * SAML StatusCode data type.
 *
 * @package simplesamlphp/saml2
 */
final class StatusCode extends AbstractSamlpElement implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;


    /**
     * Initialize a samlp:StatusCode
     *
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue $Value
     * @param \SimpleSAML\SAML2\XML\samlp\StatusCode[] $subCodes
     */
    public function __construct(
        protected SAMLAnyURIValue $Value,
        protected array $subCodes = [],
    ) {
        Assert::maxCount($subCodes, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOf($subCodes, StatusCode::class);
    }


    /**
     * Collect the Value
     *
     * @return \SimpleSAML\SAML2\Type\SAMLAnyURIValue
     */
    public function getValue(): SAMLAnyURIValue
    {
        return $this->Value;
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
     * Convert XML into a StatusCode
     *
     * @throws \SimpleSAML\XMLSchema\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XMLSchema\Exception\MissingAttributeException
     *   if the supplied element is missing one of the mandatory attributes
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'StatusCode', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, StatusCode::NS, InvalidDOMElementException::class);

        return new static(
            self::getAttribute($xml, 'Value', SAMLAnyURIValue::class),
            StatusCode::getChildrenOfClass($xml),
        );
    }


    /**
     * Convert this StatusCode to XML.
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);
        $e->setAttribute('Value', strval($this->getValue()));

        foreach ($this->getSubCodes() as $subCode) {
            $subCode->toXML($e);
        }

        return $e;
    }
}
