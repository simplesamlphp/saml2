<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\XML\IdentifierTrait;
use SimpleSAML\XML\{SchemaValidatableElementInterface, SchemaValidatableElementTrait};
use SimpleSAML\XMLSchema\Exception\{InvalidDOMElementException, TooManyElementsException};

use function array_pop;

/**
 * Class representing SAML 2 SubjectConfirmation element.
 *
 * @package simplesamlphp/saml2
 */
final class SubjectConfirmation extends AbstractSamlElement implements SchemaValidatableElementInterface
{
    use IdentifierTrait;
    use SchemaValidatableElementTrait;


    /**
     * Initialize (and parse) a SubjectConfirmation element.
     *
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue $method
     * @param \SimpleSAML\SAML2\XML\saml\IdentifierInterface|null $identifier
     * @param \SimpleSAML\SAML2\XML\saml\SubjectConfirmationData|null $subjectConfirmationData
     */
    public function __construct(
        protected SAMLAnyURIValue $method,
        ?IdentifierInterface $identifier = null,
        protected ?SubjectConfirmationData $subjectConfirmationData = null,
    ) {
        $this->setIdentifier($identifier);
    }


    /**
     * Collect the value of the Method-property
     *
     * @return \SimpleSAML\SAML2\Type\SAMLAnyURIValue
     */
    public function getMethod(): SAMLAnyURIValue
    {
        return $this->method;
    }


    /**
     * Collect the value of the SubjectConfirmationData-property
     *
     * @return \SimpleSAML\SAML2\XML\saml\SubjectConfirmationData|null
     */
    public function getSubjectConfirmationData(): ?SubjectConfirmationData
    {
        return $this->subjectConfirmationData;
    }


    /**
     * Convert XML into a SubjectConfirmation
     *
     * @param \DOMElement $xml The XML element we should load
     * @return static
     *
     * @throws \SimpleSAML\XMLSchema\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XMLSchema\Exception\MissingAttributeException
     *   if the supplied element is missing one of the mandatory attributes
     * @throws \SimpleSAML\XMLSchema\Exception\TooManyElementsException
     *   if too many child-elements of a type are specified
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'SubjectConfirmation', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, SubjectConfirmation::NS, InvalidDOMElementException::class);

        $subjectConfirmationData = SubjectConfirmationData::getChildrenOfClass($xml);
        Assert::maxCount(
            $subjectConfirmationData,
            1,
            'More than one <saml:SubjectConfirmationData> in <saml:SubjectConfirmation>.',
            TooManyElementsException::class,
        );

        return new static(
            self::getAttribute($xml, 'Method', SAMLAnyURIValue::class),
            self::getIdentifierFromXML($xml),
            array_pop($subjectConfirmationData),
        );
    }


    /**
     * Convert this element to XML.
     *
     * @param  \DOMElement|null $parent The parent element we should append this element to.
     * @return \DOMElement This element, as XML.
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);
        $e->setAttribute('Method', $this->getMethod()->getValue());

        /** @var \SimpleSAML\XML\SerializableElementInterface|null $identifier */
        $identifier = $this->getIdentifier();
        $identifier?->toXML($e);

        if ($this->getSubjectConfirmationData() !== null && !$this->getSubjectConfirmationData()->isEmptyElement()) {
            $this->getSubjectConfirmationData()->toXML($e);
        }

        return $e;
    }
}
