<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\SignedElementHelper;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XML\Exception\MissingElementException;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XML\Utils as XMLUtils;

use function array_pop;
use function gmdate;

/**
 * Class representing SAML 2 AffiliationDescriptor element.
 *
 * @package SimpleSAMLphp
 */
class AffiliationDescriptor extends SignedElementHelper
{
    /**
     * The affiliationOwnerID.
     *
     * @var string
     */
    public string $affiliationOwnerID = '';

    /**
     * The ID of this element.
     *
     * @var string|null
     */
    private ?string $ID = null;

    /**
     * Extensions on this element.
     *
     * Array of extension elements.
     *
     * @var \SimpleSAML\SAML2\XML\md\Extensions|null
     */
    private ?Extensions $Extensions = null;

    /**
     * The AffiliateMember(s).
     *
     * Array of AffiliateMember objects.
     *
     * @var \SimpleSAML\SAML2\XML\md\AffiliateMember[]
     */
    private array $AffiliateMember = [];

    /**
     * KeyDescriptor elements.
     *
     * Array of \SimpleSAML\SAML2\XML\md\KeyDescriptor elements.
     *
     * @var \SimpleSAML\SAML2\XML\md\KeyDescriptor[]
     */
    private array $KeyDescriptor = [];


    /**
     * Initialize a AffiliationDescriptor.
     *
     * @param \DOMElement|null $xml The XML element we should load.
     * @throws \Exception
     */
    public function __construct(DOMElement $xml = null)
    {
        parent::__construct($xml);

        if ($xml === null) {
            return;
        }

        if (!$xml->hasAttribute('affiliationOwnerID')) {
            throw new MissingAttributeException('Missing affiliationOwnerID on AffiliationDescriptor.');
        }
        $this->setAffiliationOwnerID($xml->getAttribute('affiliationOwnerID'));

        if ($xml->hasAttribute('ID')) {
            $this->setID($xml->getAttribute('ID'));
        }

        if ($xml->hasAttribute('validUntil')) {
            $this->setValidUntil(XMLUtils::xsDateTimeToTimestamp($xml->getAttribute('validUntil')));
        }

        if ($xml->hasAttribute('cacheDuration')) {
            $this->setCacheDuration($xml->getAttribute('cacheDuration'));
        }

        $extensions = Extensions::getChildrenOfClass($xml);
        Assert::maxCount(
            $extensions,
            1,
            'Only one md:Extensions element is allowed.',
            TooManyElementsException::class,
        );
        $this->Extensions = array_pop($extensions);

        $this->setAffiliateMember(AffiliateMember::getChildrenOfClass($xml));
        if (empty($this->AffiliateMember)) {
            throw new MissingElementException('Missing AffiliateMember in AffiliationDescriptor.');
        }

        foreach (KeyDescriptor::getChildrenOfClass($xml) as $kd) {
            $this->addKeyDescriptor($kd);
        }
    }


    /**
     * Collect the value of the affiliationOwnerId-property
     *
     * @return string
     */
    public function getAffiliationOwnerID(): string
    {
        return $this->affiliationOwnerID;
    }


    /**
     * Set the value of the affiliationOwnerId-property
     *
     * @param string $affiliationOwnerId
     * @return void
     */
    public function setAffiliationOwnerID(string $affiliationOwnerId): void
    {
        $this->affiliationOwnerID = $affiliationOwnerId;
    }


    /**
     * Collect the value of the ID-property
     *
     * @return string|null
     */
    public function getID(): ?string
    {
        return $this->ID;
    }


    /**
     * Set the value of the ID-property
     *
     * @param string|null $Id
     * @return void
     */
    public function setID(string $Id = null): void
    {
        $this->ID = $Id;
    }


    /**
     * Collect the value of the Extensions property.
     *
     * @return \SimpleSAML\SAML2\XML\md\Extensions|null
     */
    public function getExtensions(): ?Extensions
    {
        return $this->Extensions;
    }


    /**
     * Set the value of the Extensions property.
     *
     * @param \SimpleSAML\SAML2\XML\md\Extensions|null $extensions
     * @return void
     */
    public function setExtensions(?Extensions $extensions): void
    {
        $this->Extensions = $extensions;
    }


    /**
     * Collect the value of the AffiliateMember-property
     *
     * @return \SimpleSAML\SAML2\XML\md\AffiliateMember[]
     */
    public function getAffiliateMember(): array
    {
        return $this->AffiliateMember;
    }


    /**
     * Set the value of the AffiliateMember-property
     *
     * @param \SimpleSAML\SAML2\XML\md\AffiliateMember $affiliateMember
     * @return void
     */
    public function setAffiliateMember(array $affiliateMember): void
    {
        Assert::allIsInstanceOf($affiliateMember, AffiliateMember::class);
        $this->AffiliateMember = $affiliateMember;
    }


    /**
     * Collect the value of the KeyDescriptor-property
     *
     * @return \SimpleSAML\SAML2\XML\md\KeyDescriptor[]
     */
    public function getKeyDescriptor(): array
    {
        return $this->KeyDescriptor;
    }


    /**
     * Set the value of the KeyDescriptor-property
     *
     * @param \SimpleSAML\SAML2\XML\md\KeyDescriptor[] $keyDescriptor
     * @return void
     */
    public function setKeyDescriptor(array $keyDescriptor): void
    {
        $this->KeyDescriptor = $keyDescriptor;
    }


    /**
     * Add the value to the KeyDescriptor-property
     *
     * @param \SimpleSAML\SAML2\XML\md\KeyDescriptor $keyDescriptor
     * @return void
     */
    public function addKeyDescriptor(KeyDescriptor $keyDescriptor): void
    {
        $this->KeyDescriptor[] = $keyDescriptor;
    }


    /**
     * Add this AffiliationDescriptor to an EntityDescriptor.
     *
     * @param \DOMElement $parent The EntityDescriptor we should append this endpoint to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent): DOMElement
    {
        Assert::notEmpty($this->affiliationOwnerID);

        $e = $parent->ownerDocument->createElementNS(C::NS_MD, 'md:AffiliationDescriptor');
        $parent->appendChild($e);

        $e->setAttribute('affiliationOwnerID', $this->affiliationOwnerID);

        if ($this->ID !== null) {
            $e->setAttribute('ID', $this->ID);
        }

        if ($this->validUntil !== null) {
            $e->setAttribute('validUntil', gmdate('Y-m-d\TH:i:s\Z', $this->validUntil));
        }

        if ($this->cacheDuration !== null) {
            $e->setAttribute('cacheDuration', $this->cacheDuration);
        }

        $this->Extensions?->toXML($e);

        foreach ($this->AffiliateMember as $am) {
            $am->toXML($e);
        }

        foreach ($this->KeyDescriptor as $kd) {
            $kd->toXML($e);
        }

        $this->signElement($e, $e->firstChild);

        return $e;
    }
}
