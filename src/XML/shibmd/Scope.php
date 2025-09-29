<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\shibmd;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\{SchemaValidatableElementInterface, SchemaValidatableElementTrait};
use SimpleSAML\XML\Type\BooleanValue;
use SimpleSAML\XML\TypedTextContentTrait;

/**
 * Class which represents the Scope element found in Shibboleth metadata.
 *
 * @link https://wiki.shibboleth.net/confluence/display/SC/ShibMetaExt+V1.0
 * @package simplesamlphp/saml2
 */
final class Scope extends AbstractShibmdElement implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;
    use TypedTextContentTrait;

    /** @var string */
    public const TEXTCONTENT_TYPE = SAMLStringValue::class;


    /**
     * Create a Scope.
     *
     * @param \SimpleSAML\SAML2\Type\SAMLStringValue $scope
     * @param \SimpleSAML\XML\Type\BooleanValue|null $regexp
     */
    public function __construct(
        SAMLStringValue $scope,
        protected ?BooleanValue $regexp = null,
    ) {
        $this->setContent($scope);
    }


    /**
     * Collect the value of the regexp-property
     *
     * @return \SimpleSAML\XML\Type\BooleanValue|null
     */
    public function isRegexpScope(): ?BooleanValue
    {
        return $this->regexp;
    }


    /**
     * Convert XML into a Scope
     *
     * @param \DOMElement $xml The XML element we should load
     * @return static
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'Scope', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, Scope::NS, InvalidDOMElementException::class);

        return new static(
            SAMLStringValue::fromString($xml->textContent),
            self::getOptionalAttribute($xml, 'regexp', BooleanValue::class, null),
        );
    }


    /**
     * Convert this Scope to XML.
     *
     * @param \DOMElement|null $parent The element we should append this Scope to.
     * @return \DOMElement
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);
        $e->textContent = strval($this->getContent());

        if ($this->isRegexpScope() !== null) {
            $e->setAttribute('regexp', strval($this->isRegexpScope()));
        }

        return $e;
    }
}
