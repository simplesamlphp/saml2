<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\XML\Constants as C;
use SimpleSAML\XML\Exception\{InvalidDOMElementException, SchemaViolationException};
use SimpleSAML\XML\{SchemaValidatableElementInterface, SchemaValidatableElementTrait};

/**
 * Class representing a saml:Evidence element.
 *
 * @package simplesaml/saml2
 */
final class Evidence extends AbstractSamlElement implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;


    /**
     * @param \SimpleSAML\SAML2\XML\saml\AssertionIDRef[] $assertionIDRef
     * @param \SimpleSAML\SAML2\XML\saml\AssertionURIRef[] $assertionURIRef
     * @param \SimpleSAML\SAML2\XML\saml\Assertion[] $assertion
     * @param \SimpleSAML\SAML2\XML\saml\EncryptedAssertion[] $encryptedAssertion
     */
    public function __construct(
        protected array $assertionIDRef = [],
        protected array $assertionURIRef = [],
        protected array $assertion = [],
        protected array $encryptedAssertion = [],
    ) {
        Assert::maxCount($assertionIDRef, C::UNBOUNDED_LIMIT);
        Assert::maxCount($assertionURIRef, C::UNBOUNDED_LIMIT);
        Assert::maxCount($assertion, C::UNBOUNDED_LIMIT);
        Assert::maxCount($encryptedAssertion, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOf($assertionIDRef, AssertionIDRef::class, SchemaViolationException::class);
        Assert::allIsInstanceOf($assertionURIRef, AssertionURIRef::class, SchemaViolationException::class);
        Assert::allIsInstanceOf($assertion, Assertion::class, SchemaViolationException::class);
        Assert::allIsInstanceOf($encryptedAssertion, EncryptedAssertion::class, SchemaViolationException::class);
    }


    /**
     * Test if an object, at the state it's in, would produce an empty XML-element
     *
     * @return bool
     */
    public function isEmptyElement(): bool
    {
        return empty($this->assertionIDRef)
            && empty($this->assertionURIRef)
            && empty($this->assertion)
            && empty($this->encryptedAssertion);
    }


    /**
     * @return \SimpleSAML\SAML2\XML\saml\AssertionIDRef[]
     */
    public function getAssertionIDRef(): array
    {
        return $this->assertionIDRef;
    }


    /**
     * @return \SimpleSAML\SAML2\XML\saml\AssertionURIRef[]
     */
    public function getAssertionURIRef(): array
    {
        return $this->assertionURIRef;
    }


    /**
     * @return \SimpleSAML\SAML2\XML\saml\Assertion[]
     */
    public function getAssertion(): array
    {
        return $this->assertion;
    }


    /**
     * @return \SimpleSAML\SAML2\XML\saml\EncryptedAssertion[]
     */
    public function getEncryptedAssertion(): array
    {
        return $this->encryptedAssertion;
    }


    /**
     * Convert XML into an Evidence
     *
     * @param \DOMElement $xml The XML element we should load
     * @return static
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   If the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): static
    {
        $qualifiedName = static::getClassName(static::class);
        Assert::eq(
            $xml->localName,
            $qualifiedName,
            'Unexpected name for endpoint: ' . $xml->localName . '. Expected: ' . $qualifiedName . '.',
            InvalidDOMElementException::class,
        );

        $assertionIDRef = AssertionIDRef::getChildrenOfClass($xml);
        $assertionURIRef = AssertionURIRef::getChildrenOfClass($xml);
        $assertion = Assertion::getChildrenOfClass($xml);
        $encryptedAssertion = EncryptedAssertion::getChildrenOfClass($xml);

        return new static(
            $assertionIDRef,
            $assertionURIRef,
            $assertion,
            $encryptedAssertion,
        );
    }


    /**
     * Convert this Evidence to XML.
     *
     * @param \DOMElement $parent The element we are converting to XML.
     * @return \DOMElement The XML element after adding the data corresponding to this Condition.
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        foreach ($this->getAssertionIDRef() as $assertionIDRef) {
            $assertionIDRef->toXML($e);
        }

        foreach ($this->getAssertionURIRef() as $assertionURIRef) {
            $assertionURIRef->toXML($e);
        }

        foreach ($this->getAssertion() as $assertion) {
            $assertion->toXML($e);
        }

        foreach ($this->getEncryptedAssertion() as $encryptedAssertion) {
            $encryptedAssertion->toXML($e);
        }

        return $e;
    }
}
