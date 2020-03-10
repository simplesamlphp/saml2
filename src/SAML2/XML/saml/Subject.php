<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use DOMElement;
use SAML2\Constants;
use SAML2\Utils;
use SAML2\XML\IdentifierTrait;
use Webmozart\Assert\Assert;

/**
 * Class representing SAML 2 Subject element.
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package SimpleSAMLphp
 */
final class Subject extends AbstractSamlElement
{
    use IdentifierTrait;

    /**
     * SubjectConfirmation element with extra data for verification of the Subject.
     *
     * @var \SAML2\XML\saml\SubjectConfirmation[]
     */
    protected $SubjectConfirmation;


    /**
     * Initialize a Subject element.
     *
     * @param \SAML2\XML\saml\IdentifierInterface|null $identifier
     * @param \SAML2\XML\saml\SubjectConfirmation[] $SubjectConfirmation
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
     * @return \SAML2\XML\saml\SubjectConfirmation[]
     */
    public function getSubjectConfirmation(): array
    {
        return $this->SubjectConfirmation;
    }


    /**
     * Set the value of the SubjectConfirmation-property
     *
     * @param \SAML2\XML\saml\SubjectConfirmation[] $subjectConfirmation
     * @return void
     */
    private function setSubjectConfirmation(array $subjectConfirmation): void
    {
        $this->SubjectConfirmation = $subjectConfirmation;
    }


    /**
     * Convert XML into a Subject
     *
     * @param \DOMElement $xml The XML element we should load
     * @return self
     * @throws \InvalidArgumentException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'Subject');
        Assert::same($xml->namespaceURI, Subject::NS);

        $baseId = BaseID::getChildrenOfClass($xml);
        $nameId = NameID::getChildrenOfClass($xml);
        $encryptedId = EncryptedID::getChildrenOfClass($xml);

        // We accept only one of BaseID, NameID or EncryptedID
        Assert::countBetween($baseId, 0, 1, 'More than one <saml:BaseID> in <saml:Subject>.');
        Assert::countBetween($nameId, 0, 1, 'More than one <saml:NameID> in <saml:Subject>.');
        Assert::countBetween($encryptedId, 0, 1, 'More than one <saml:EncryptedID> in <saml:Subject>.');

        $subjectConfirmation = SubjectConfirmation::getChildrenOfClass($xml);

        $identifiers = array_merge($baseId, $nameId, $encryptedId);
        Assert::countBetween(
            $identifiers,
            0,
            1,
            'A <saml:Subject> can contain exactly one of <saml:BaseID>, <saml:NameID> or <saml:EncryptedID>.'
        );

        $identifier = empty($identifiers) ? null : $identifier[0];

        return new self(
            empty($identifier),
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

        $this->getIdentifier()->toXML($e);

        foreach ($this->SubjectConfirmation as $sc) {
            $sc->toXML($e);
        }

        return $e;
    }
}
