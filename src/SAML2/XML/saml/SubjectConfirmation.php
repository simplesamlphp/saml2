<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use DOMElement;
use SAML2\Constants;
use SAML2\Utils;
use SAML2\XML\IdentifiersTrait;
use Webmozart\Assert\Assert;

/**
 * Class representing SAML 2 SubjectConfirmation element.
 *
 * @package SimpleSAMLphp
 */
final class SubjectConfirmation extends AbstractSamlElement
{
    use IdentifiersTrait;

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
     * @param string $Method
     * @param \SAML2\XML\saml\BaseID|null $baseId
     * @param \SAML2\XML\saml\NameID|null $nameId
     * @param \SAML2\XML\saml\EncryptedID|null $encryptedId
     * @param \SAML2\XML\saml\SubjectConfirmationData|null $scd
     */
    public function __construct(
        string $method,
        BaseID $baseId = null,
        NameID $nameId = null,
        EncryptedID $encryptedId = null,
        SubjectConfirmationData $subjectConfirmationData = null
    ) {
        $identifiers = array_diff(
            [$baseId, $nameId, $encryptedId],
            [null]
        );

        Assert::countBetween(
            $identifiers,
            0,
            1,
            'A <saml:Subject> may contain only one of <saml:BaseID>, <saml:NameID> or <saml:EncryptedID>'
        );

        $this->setMethod($method);
        $this->setBaseID($baseId);
        $this->setNameID($nameId);
        $this->setEncryptedID($encryptedId);
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
        $encryptedId = EncryptedID::getChildrenOfClass($xml);

        // We accept only one of BaseID, NameID or EncryptedID
        Assert::countBetween($baseId, 0, 1, 'More than one <saml:BaseID> in <saml:SubjectConfirmation>.');
        Assert::countBetween($nameId, 0, 1, 'More than one <saml:NameID> in <saml:SubjectConfirmation>.');
        Assert::countBetween($encryptedId, 0, 1, 'More than one <saml:EncryptedID> in <saml:SubjectConfirmation>.');

        $subjectConfirmationData = SubjectConfirmationData::getChildrenOfClass($xml);
        Assert::maxCount(
            $subjectConfirmationData,
            1,
            'More than one <saml:SubjectConfirmationData> in <saml:SubjectConfirmation>.'
        );

        return new self(
            $Method,
            empty($baseId) ? null : $baseId[0],
            empty($nameId) ? null : $nameId[0],
            empty($encryptedId) ? null : $encryptedId[0],
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

        if ($this->baseId !== null) {
            $this->baseId->toXML($e);
        }

        if ($this->nameId !== null) {
            $this->nameId->toXML($e);
        }

        if ($this->encryptedId !== null) {
            $this->encryptedId->toXML($e);
        }

        if ($this->SubjectConfirmationData !== null) {
            $this->SubjectConfirmationData->toXML($e);
        }

        return $e;
    }
}
