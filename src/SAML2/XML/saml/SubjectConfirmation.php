<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use DOMElement;
use SAML2\Constants;
use SAML2\Utils;
use SAML2\XML\IdentifierTrait;
use Webmozart\Assert\Assert;

/**
 * Class representing SAML 2 SubjectConfirmation element.
 *
 * @package SimpleSAMLphp
 */
final class SubjectConfirmation extends AbstractSamlElement
{
    use IdentifierTrait;

    /**
     * The method we can use to verify this Subject.
     *
     * @var string
     */
    protected $Method;

    /**
     * SubjectConfirmationData element with extra data for verification of the Subject.
     *
     * @var \SAML2\XML\saml\SubjectConfirmationData|null
     */
    protected $SubjectConfirmationData = null;


    /**
     * Initialize (and parse) a SubjectConfirmation element.
     *
     * @param string $method
     * @param \SAML2\XML\saml\IdentifierInterface|null $identifier
     * @param \SAML2\XML\saml\SubjectConfirmationData|null $subjectConfirmationData
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
     *
     * @throws \InvalidArgumentException if assertions are false
     */
    public function getMethod(): string
    {
        return $this->Method;
    }


    /**
     * Set the value of the Method-property
     *
     * @param string $method
     * @return void
     */
    private function setMethod(string $method): void
    {
        $this->Method = $method;
    }


    /**
     * Collect the value of the SubjectConfirmationData-property
     *
     * @return \SAML2\XML\saml\SubjectConfirmationData|null
     */
    public function getSubjectConfirmationData(): ?SubjectConfirmationData
    {
        return $this->SubjectConfirmationData;
    }


    /**
     * Set the value of the SubjectConfirmationData-property
     *
     * @param \SAML2\XML\saml\SubjectConfirmationData|null $subjectConfirmationData
     * @return void
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
     * @throws \InvalidArgumentException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'SubjectConfirmation');
        Assert::same($xml->namespaceURI, SubjectConfirmation::NS);

        Assert::true($xml->hasAttribute('Method'), 'SubjectConfirmation element without Method attribute.');
        $Method = $xml->getAttribute('Method');

        $baseId = BaseID::getChildrenOfClass($xml);
        $nameId = NameID::getChildrenOfClass($xml);
//        $encryptedId = EncryptedID::getChildrenOfClass($xml);

        // We accept only one of BaseID, NameID or EncryptedID
        Assert::countBetween($baseId, 0, 1, 'More than one <saml:BaseID> in <saml:SubjectConfirmation>.');
        Assert::countBetween($nameId, 0, 1, 'More than one <saml:NameID> in <saml:SubjectConfirmation>.');
//        Assert::countBetween($encryptedId, 0, 1, 'More than one <saml:EncryptedID> in <saml:SubjectConfirmation>.');

        $identifiers = array_merge($baseId, $nameId);
//        $identifiers = array_filter([$baseId, $nameId, $encryptedId]);
        Assert::maxCount($identifiers, 1, 'More than one identifier found in <saml:SubjectConfirmation>.');

        /** @psalm-var \SAML2\XML\saml\IdentifierInterface|null $identifier */
        $identifier = array_pop($identifiers);

        $subjectConfirmationData = SubjectConfirmationData::getChildrenOfClass($xml);
        Assert::maxCount(
            $subjectConfirmationData,
            1,
            'More than one <saml:SubjectConfirmationData> in <saml:SubjectConfirmation>.'
        );

        return new self(
            $Method,
            $identifier,
            empty($subjectConfirmationData) ? null : $subjectConfirmationData[0]
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
