<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\XML\IdentifierTrait;

/**
 * Class representing SAML 2 SubjectConfirmation element.
 *
 * @package simplesamlphp/saml2
 */
final class SubjectConfirmation extends AbstractSamlElement
{
    use IdentifierTrait;

    /**
     * The method we can use to verify this Subject.
     *
     * @var string
     */
    protected string $Method;

    /**
     * SubjectConfirmationData element with extra data for verification of the Subject.
     *
     * @var \SimpleSAML\SAML2\XML\saml\SubjectConfirmationData|null
     */
    protected ?SubjectConfirmationData $SubjectConfirmationData = null;


    /**
     * Initialize (and parse) a SubjectConfirmation element.
     *
     * @param string $method
     * @param \SimpleSAML\SAML2\XML\saml\IdentifierInterface|null $identifier
     * @param \SimpleSAML\SAML2\XML\saml\SubjectConfirmationData|null $subjectConfirmationData
     */
    public function __construct(
        string $method,
        ?IdentifierInterface $identifier = null,
        SubjectConfirmationData $subjectConfirmationData = null
    ) {
        $this->setMethod($method);
        $this->setIdentifier($identifier);
        $this->setSubjectConfirmationData($subjectConfirmationData);
    }


    /**
     * Collect the value of the Method-property
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->Method;
    }


    /**
     * Set the value of the Method-property
     *
     * @param string $method
     */
    private function setMethod(string $method): void
    {
        $this->Method = $method;
    }


    /**
     * Collect the value of the SubjectConfirmationData-property
     *
     * @return \SimpleSAML\SAML2\XML\saml\SubjectConfirmationData|null
     */
    public function getSubjectConfirmationData(): ?SubjectConfirmationData
    {
        return $this->SubjectConfirmationData;
    }


    /**
     * Set the value of the SubjectConfirmationData-property
     *
     * @param \SimpleSAML\SAML2\XML\saml\SubjectConfirmationData|null $subjectConfirmationData
     */
    private function setSubjectConfirmationData(?SubjectConfirmationData $subjectConfirmationData): void
    {
        $this->SubjectConfirmationData = $subjectConfirmationData;
    }


    /**
     * Convert XML into a SubjectConfirmation
     *
     * @param \DOMElement $xml The XML element we should load
     * @return self
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingAttributeException if the supplied element is missing one of the mandatory attributes
     * @throws \SimpleSAML\XML\Exception\TooManyElementsException if too many child-elements of a type are specified
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'SubjectConfirmation', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, SubjectConfirmation::NS, InvalidDOMElementException::class);

        $Method = self::getAttribute($xml, 'Method');
        $identifier = self::getIdentifierFromXML($xml);
        $subjectConfirmationData = SubjectConfirmationData::getChildrenOfClass($xml);

        Assert::maxCount(
            $subjectConfirmationData,
            1,
            'More than one <saml:SubjectConfirmationData> in <saml:SubjectConfirmation>.',
            TooManyElementsException::class
        );

        return new self(
            $Method,
            $identifier,
            array_pop($subjectConfirmationData)
        );
    }


    /**
     * Convert this element to XML.
     *
     * @param  \DOMElement|null $parent The parent element we should append this element to.
     * @return \DOMElement This element, as XML.
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);
        $e->setAttribute('Method', $this->Method);

        if ($this->identifier !== null) {
            $this->identifier->toXML($e);
        }

        if ($this->SubjectConfirmationData !== null) {
            $this->SubjectConfirmationData->toXML($e);
        }

        return $e;
    }
}
