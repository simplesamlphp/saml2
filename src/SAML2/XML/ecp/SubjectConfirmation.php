<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\ecp;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\XML\saml\SubjectConfirmationData;
use SimpleSAML\SOAP\Constants as C;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XML\Exception\SchemaViolationException;

use function boolval;
use function intval;
use function is_null;
use function is_numeric;
use function strval;

/**
 * Class representing the ECP SubjectConfirmation element.
 *
 * @package simplesamlphp/saml2
 */
final class SubjectConfirmation extends AbstractEcpElement
{
    /**
     * Create a ECP SubjectConfirmation element.
     *
     * @param bool $mustUnderstand
     * @param string $method
     * @param \SimpleSAML\SAML2\XML\saml\SubjectConfirmationData|null $subjectConfirmationData
     */
    public function __construct(
        protected bool $mustUnderstand,
        protected string $method,
        protected ?SubjectConfirmationData $subjectConfirmationData = null,
    ) {
        Assert::validURI($method, SchemaViolationException::class);
    }


    /**
     * Collect the value of the mustUnderstand-property
     *
     * @return bool
     */
    public function getMustUnderstand(): bool
    {
        return $this->mustUnderstand;
    }


    /**
     * Collect the value of the method-property
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }


    /**
     * Collect the value of the subjectConfirmationData-property
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
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingAttributeException
     *   if the supplied element is missing any of the mandatory attributes
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'SubjectConfirmation', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, SubjectConfirmation::NS, InvalidDOMElementException::class);

        // Assert required attributes
        Assert::true(
            $xml->hasAttributeNS(C::NS_SOAP_ENV_11, 'actor'),
            'Missing env:actor attribute in <ecp:SubjectConfirmation>.',
            MissingAttributeException::class,
        );
        Assert::true(
            $xml->hasAttributeNS(C::NS_SOAP_ENV_11, 'mustUnderstand'),
            'Missing env:mustUnderstand attribute in <ecp:SubjectConfirmation>.',
            MissingAttributeException::class,
        );

        $mustUnderstand = $xml->getAttributeNS(C::NS_SOAP_ENV_11, 'mustUnderstand');
        $mustUnderstand = ($mustUnderstand === '') ? null : boolval(intval($mustUnderstand));
        Assert::nullOrBoolean(
            $mustUnderstand,
            'Invalid value of env:mustUnderstand attribute in <ecp:SubjectConfirmation>.',
            ProtocolViolationException::class,
        );

        $actor = $xml->getAttributeNS(C::NS_SOAP_ENV_11, 'actor');
        Assert::same(
            $actor,
            C::SOAP_ACTOR_NEXT,
            'Invalid value of env:actor attribute in <ecp:SubjectConfirmation>.',
            ProtocolViolationException::class,
        );

        $subjectConfirmationData = SubjectConfirmationData::getChildrenOfClass($xml);
        Assert::maxCount(
            $subjectConfirmationData,
            1,
            'More than one <saml:SubjectConfirmationData> in <saml:SubjectConfirmation>.',
            TooManyElementsException::class,
        );

        return new static(
            $mustUnderstand,
            self::getAttribute($xml, 'Method'),
            array_pop($subjectConfirmationData),
        );
    }


    /**
     * Convert this ECP SubjectConfirmation to XML.
     *
     * @param \DOMElement|null $parent The element we should append this element to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);
        $e->setAttributeNS(C::NS_SOAP_ENV_11, 'env:mustUnderstand', strval($this->getMustUnderstand()));
        $e->setAttributeNS(C::NS_SOAP_ENV_11, 'env:actor', C::SOAP_ACTOR_NEXT);
        $e->setAttribute('Method', $this->getMethod());

        $this->getSubjectConfirmationData()?->toXML($e);

        return $e;
    }
}
