<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\emd;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Exception\ArrayValidationException;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\XML\ArrayizableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XMLSchema\Exception\InvalidDOMElementException;
use SimpleSAML\XMLSchema\Exception\SchemaViolationException;

use function array_pop;

/**
 * Class implementing RepublishRequest.
 *
 * @package simplesamlphp/saml2
 */
final class RepublishRequest extends AbstractEmdElement implements
    ArrayizableElementInterface,
    SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;


    /**
     * @param \SimpleSAML\SAML2\XML\emd\RepublishTarget $republishTarget
     */
    public function __construct(
        protected RepublishTarget $republishTarget,
    ) {
    }


    /**
     * Collect the value of the RepublishTarget-property
     *
     * @return \SimpleSAML\SAML2\XML\emd\RepublishTarget
     */
    public function getRepublishTarget(): RepublishTarget
    {
        return $this->republishTarget;
    }


    /**
     * Convert XML into a RepublishRequest
     *
     * @param \DOMElement $xml The XML element we should load
     * @return static
     *
     * @throws \SimpleSAML\XMLSchema\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XMLSchema\Exception\MissingAttributeException
     *   if the supplied element is missing one of the mandatory attributes
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'RepublishRequest', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, RepublishRequest::NS, InvalidDOMElementException::class);

        $republishTarget = RepublishTarget::getChildrenOfClass($xml);
        Assert::count(
            $republishTarget,
            1,
            'A RepublishRequest can contain exactly one RepublishTarget.',
            SchemaViolationException::class,
        );

        return new static(array_pop($republishTarget));
    }


    /**
     * Convert this element to XML.
     *
     * @param \DOMElement|null $parent The element we should append to.
     * @return \DOMElement
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        $this->republishTarget->toXML($e);

        return $e;
    }


    /**
     * Create a class from an array
     *
     * @param array $data
     * @return static
     */
    public static function fromArray(array $data): static
    {
        Assert::keyExists($data, 'RepublishTarget', ArrayValidationException::class);
        Assert::string($data['RepublishTarget'], ArrayValidationException::class);

        return new static(
            new RepublishTarget(
                SAMLAnyURIValue::fromString($data['RepublishTarget']),
            ),
        );
    }


    /**
     * Create an array from this class
     *
     * @return array
     */
    public function toArray(): array
    {
        return ['RepublishTarget' => $this->getRepublishTarget()->getContent()->getValue()];
    }
}
