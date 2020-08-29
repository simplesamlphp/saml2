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
 * Class representing SAML 2 Subject element.
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
final class Subject extends AbstractSamlElement
{
    use IdentifierTrait;

    /**
     * SubjectConfirmation element with extra data for verification of the Subject.
     *
     * @var \SimpleSAML\SAML2\XML\saml\SubjectConfirmation[]
     */
    protected $SubjectConfirmation;


    /**
     * Initialize a Subject element.
     *
     * @param \SimpleSAML\SAML2\XML\saml\IdentifierInterface|null $identifier
     * @param \SimpleSAML\SAML2\XML\saml\SubjectConfirmation[] $SubjectConfirmation
     */
    public function __construct(
        ?IdentifierInterface $identifier,
        array $SubjectConfirmation = []
    ) {
        if (empty($SubjectConfirmation)) {
            Assert::notNull(
                $identifier,
                'A <saml:Subject> not containing <saml:SubjectConfirmation> should provide exactly one of '
                    . '<saml:BaseID>, <saml:NameID> or <saml:EncryptedID>'
            );
        }

        $this->setIdentifier($identifier);
        $this->setSubjectConfirmation($SubjectConfirmation);
    }


    /**
     * Collect the value of the SubjectConfirmation-property
     *
     * @return \SimpleSAML\SAML2\XML\saml\SubjectConfirmation[]
     */
    public function getSubjectConfirmation(): array
    {
        return $this->SubjectConfirmation;
    }


    /**
     * Set the value of the SubjectConfirmation-property
     *
     * @param \SimpleSAML\SAML2\XML\saml\SubjectConfirmation[] $subjectConfirmation
     * @return void
     */
    private function setSubjectConfirmation(array $subjectConfirmation): void
    {
        Assert::allIsInstanceOf($subjectConfirmation, SubjectConfirmation::class);

        $this->SubjectConfirmation = $subjectConfirmation;
    }


    /**
     * Convert XML into a Subject
     *
     * @param \DOMElement $xml The XML element we should load
     * @return self
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
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
                TooManyElementsException::class
            );
        }

        return new self(
            $identifier,
            $subjectConfirmation
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

        if ($this->identifier !== null) {
            $this->identifier->toXML($e);
        }

        foreach ($this->SubjectConfirmation as $sc) {
            $sc->toXML($e);
        }

        return $e;
    }
}
