<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XML\Exception\TooManyElementsException;

use function count;

/**
 * Class representing SAML 2 SubjectConfirmation element.
 *
 * @package SimpleSAMLphp
 */
class SubjectConfirmation
{
    /**
     * The method we can use to verify this Subject.
     *
     * @var string|null
     */
    private ?string $Method = null;

    /**
     * The NameID of the entity that can use this element to verify the Subject.
     *
     * @var \SimpleSAML\SAML2\XML\saml\NameID|null
     */
    private ?NameID $NameID = null;

    /**
     * SubjectConfirmationData element with extra data for verification of the Subject.
     *
     * @var \SimpleSAML\SAML2\XML\saml\SubjectConfirmationData|null
     */
    private ?SubjectConfirmationData $SubjectConfirmationData = null;


    /**
     * Initialize (and parse? a SubjectConfirmation element.
     *
     * @param \DOMElement|null $xml The XML element we should load.
     * @throws \Exception
     */
    public function __construct(DOMElement $xml = null)
    {
        if ($xml === null) {
            return;
        }

        if (!$xml->hasAttribute('Method')) {
            throw new MissingAttributeException('SubjectConfirmation element without Method attribute.');
        }
        $this->Method = $xml->getAttribute('Method');

        $xpCache = XPath::getXPath($xml);
        /** @var \DOMElement[] $nid */
        $nid = XPath::xpQuery($xml, './saml_assertion:NameID', $xpCache);
        if (count($nid) > 1) {
            throw new TooManyElementsException('More than one NameID in a SubjectConfirmation element.');
        } elseif (!empty($nid)) {
            $this->NameID = new NameID($nid[0]);
        }

        /** @var \DOMElement[] $scd */
        $scd = XPath::xpQuery($xml, './saml_assertion:SubjectConfirmationData', $xpCache);
        if (count($scd) > 1) {
            throw new TooManyElementsException(
                'More than one SubjectConfirmationData child in a SubjectConfirmation element.',
            );
        } elseif (!empty($scd)) {
            $this->SubjectConfirmationData = new SubjectConfirmationData($scd[0]);
        }
    }


    /**
     * Collect the value of the Method-property
     *
     * @return string|null
     */
    public function getMethod(): ?string
    {
        return $this->Method;
    }


    /**
     * Set the value of the Method-property
     *
     * @param string $method
     * @return void
     */
    public function setMethod(string $method): void
    {
        $this->Method = $method;
    }


    /**
     * Collect the value of the NameID-property
     *
     * @return \SimpleSAML\SAML2\XML\saml\NameID|null
     */
    public function getNameID(): ?NameID
    {
        return $this->NameID;
    }


    /**
     * Set the value of the NameID-property
     *
     * @param \SimpleSAML\SAML2\XML\saml\NameID $nameId
     * @return void
     */
    public function setNameID(NameID $nameId = null): void
    {
        $this->NameID = $nameId;
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
     * @return void
     */
    public function setSubjectConfirmationData(SubjectConfirmationData $subjectConfirmationData = null): void
    {
        $this->SubjectConfirmationData = $subjectConfirmationData;
    }


    /**
     * Convert this element to XML.
     *
     * @param \DOMElement $parent The parent element we should append this element to.
     * @return \DOMElement This element, as XML.
     */
    public function toXML(DOMElement $parent): DOMElement
    {
        Assert::notNull($this->Method, "Cannot convert SubjectConfirmation to XML without a Method set.");

        $e = $parent->ownerDocument->createElementNS(C::NS_SAML, 'saml:SubjectConfirmation');
        $parent->appendChild($e);

        /** @psalm-suppress PossiblyNullArgument */
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
