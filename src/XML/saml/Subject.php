<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\XML\IdentifierTrait;
use SimpleSAML\XML\Constants as C;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;

/**
 * Class representing SAML 2 Subject element.
 *
 * @package simplesamlphp/saml2
 */
final class Subject extends AbstractSamlElement implements SchemaValidatableElementInterface
{
    use IdentifierTrait;
    use SchemaValidatableElementTrait;


    /**
     * Initialize a Subject element.
     *
     * @param \SimpleSAML\SAML2\XML\saml\IdentifierInterface|null $identifier
     * @param \SimpleSAML\SAML2\XML\saml\SubjectConfirmation[] $subjectConfirmation
     */
    public function __construct(
        ?IdentifierInterface $identifier,
        protected array $subjectConfirmation = [],
    ) {
        if (empty($subjectConfirmation)) {
            Assert::notNull(
                $identifier,
                'A <saml:Subject> not containing <saml:SubjectConfirmation> should provide exactly one of '
                    . '<saml:BaseID>, <saml:NameID> or <saml:EncryptedID>',
            );
        }
        Assert::maxCount($subjectConfirmation, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOf($subjectConfirmation, SubjectConfirmation::class);

        $this->setIdentifier($identifier);
    }


    /**
     * Collect the value of the SubjectConfirmation-property
     *
     * @return \SimpleSAML\SAML2\XML\saml\SubjectConfirmation[]
     */
    public function getSubjectConfirmation(): array
    {
        return $this->subjectConfirmation;
    }


    /**
     * Convert XML into a Subject
     *
     * @param \DOMElement $xml The XML element we should load
     * @return static
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'Subject', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, Subject::NS, InvalidDOMElementException::class);

        $identifier = self::getIdentifierFromXML($xml);
        $subjectConfirmation = SubjectConfirmation::getChildrenOfClass($xml);

        if (empty($subjectConfirmation)) {
            Assert::notNull(
                $identifier,
                'A <saml:Subject> not containing <saml:SubjectConfirmation> should provide' .
                ' exactly one of <saml:BaseID>, <saml:NameID> or <saml:EncryptedID>',
                TooManyElementsException::class,
            );
        }

        return new static(
            $identifier,
            $subjectConfirmation,
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

        /** @var \SimpleSAML\XML\SerializableElementInterface|null $identifier */
        $identifier = $this->getIdentifier();
        $identifier?->toXML($e);

        foreach ($this->getSubjectConfirmation() as $sc) {
            $sc->toXML($e);
        }

        return $e;
    }
}
