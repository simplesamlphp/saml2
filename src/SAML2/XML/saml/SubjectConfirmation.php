<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use DOMElement;
use SAML2\Constants;
use SAML2\Utils;
use Webmozart\Assert\Assert;

/**
 * Class representing SAML 2 SubjectConfirmation element.
 *
 * @package SimpleSAMLphp
 */
final class SubjectConfirmation extends AbstractSamlElement
{
    /**
     * The method we can use to verify this Subject.
     *
     * @var string
     */
    protected $Method;

    /**
     * The NameID of the entity that can use this element to verify the Subject.
     *
     * @var \SAML2\XML\saml\NameID|null
     */
    protected $NameID = null;

    /**
     * SubjectConfirmationData element with extra data for verification of the Subject.
     *
     * @var \SAML2\XML\saml\SubjectConfirmationData|null
     */
    protected $SubjectConfirmationData = null;


    /**
     * Initialize (and parse? a SubjectConfirmation element.
     *
     * @param string $Method
     * @param \SAML2\XML\saml\NameID|null $nid
     * @param \SAML2\XML\saml\SubjectConfirmationData|null $scd
     */
    public function __construct(string $method, NameID $nid = null, SubjectConfirmationData $scd = null)
    {
        $this->setMethod($method);
        $this->setNameID($nid);
        $this->setSubjectConfirmationData($scd);
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
        Assert::notEmpty($this->Method);

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
     * Collect the value of the NameID-property
     *
     * @return \SAML2\XML\saml\NameID|null
     */
    public function getNameID(): ?NameID
    {
        return $this->NameID;
    }


    /**
     * Set the value of the NameID-property
     *
     * @param \SAML2\XML\saml\NameID $nameId
     * @return void
     */
    private function setNameID(?NameID $nameId): void
    {
        $this->NameID = $nameId;
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
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'SubjectConfirmation');
        Assert::same($xml->namespaceURI, SubjectConfirmation::NS);

        Assert::true($xml->hasAttribute('Method'), 'SubjectConfirmation element without Method attribute.');
        $Method = $xml->getAttribute('Method');

        /** @var \DOMElement[] $nid */
        $nid = Utils::xpQuery($xml, './saml_assertion:NameID');
        Assert::maxCount($nid, 1, 'More than one NameID in a SubjectConfirmation element.');

        /** @var \DOMElement[] $scd */
        $scd = Utils::xpQuery($xml, './saml_assertion:SubjectConfirmationData');
        Assert::maxCount($scd, 1, 'More than one SubjectConfirmationData child in a SubjectConfirmation element.');

        return new self(
            $Method,
            empty($nid) ? null : NameID::fromXML($nid[0]),
            empty($scd) ? null : SubjectConfirmationData::fromXML($scd[0])
        );
    }


    /**
     * Convert this element to XML.
     *
     * @param  \DOMElement|null $parent The parent element we should append this element to.
     * @return \DOMElement This element, as XML.
     *
     * @throws \InvalidArgumentException if assertions are false
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);
        $e->setAttribute('Method', $this->Method);
        if ($this->NameID !== null) {
            $this->NameID->toXML($e);
        }
        if ($this->SubjectConfirmationData !== null) {
            $this->SubjectConfirmationData->toXML($e);
        }

        return $e;
    }
}
