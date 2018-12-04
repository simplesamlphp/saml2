<?php

namespace SAML2\XML\md;

use SAML2\Constants;
use SAML2\SignedElementHelper;
use SAML2\Utils;

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
    public $affiliationOwnerID;

    /**
     * The ID of this element.
     *
     * @var string|null
     */
    public $ID;

    /**
     * How long this element is valid, as a unix timestamp.
     *
     * @var int|null
     */
    public $validUntil;

    /**
     * The length of time this element can be cached, as string.
     *
     * @var string|null
     */
    public $cacheDuration;

    /**
     * Extensions on this element.
     *
     * Array of extension elements.
     *
     * @var \SAML2\XML\Chunk[]
     */
    public $Extensions = [];

    /**
     * The AffiliateMember(s).
     *
     * Array of entity ID strings.
     *
     * @var array
     */
    public $AffiliateMember = [];

    /**
     * KeyDescriptor elements.
     *
     * Array of \SAML2\XML\md\KeyDescriptor elements.
     *
     * @var \SAML2\XML\md\KeyDescriptor[]
     */
    public $KeyDescriptor = [];

    /**
     * Initialize a AffiliationDescriptor.
     *
     * @param \DOMElement|null $xml The XML element we should load.
     * @throws \Exception
     */
    public function __construct(\DOMElement $xml = null)
    {
        parent::__construct($xml);

        if ($xml === null) {
            return;
        }

        if (!$xml->hasAttribute('affiliationOwnerID')) {
            throw new \Exception('Missing affiliationOwnerID on AffiliationDescriptor.');
        }
        $this->setAffiliationOwnerID($xml->getAttribute('affiliationOwnerID'));

        if ($xml->hasAttribute('ID')) {
            $this->setID($xml->getAttribute('ID'));
        }

        if ($xml->hasAttribute('validUntil')) {
            $this->setValidUntil(Utils::xsDateTimeToTimestamp($xml->getAttribute('validUntil')));
        }

        if ($xml->hasAttribute('cacheDuration')) {
            $this->setCacheDuration($xml->getAttribute('cacheDuration'));
        }

        $this->setExtensions(Extensions::getList($xml));

        $this->setAffiliateMember(Utils::extractStrings($xml, Constants::NS_MD, 'AffiliateMember'));
        if (empty($this->AffiliateMember)) {
            throw new \Exception('Missing AffiliateMember in AffiliationDescriptor.');
        }

        foreach (Utils::xpQuery($xml, './saml_metadata:KeyDescriptor') as $kd) {
            $this->addKeyDescriptor(new KeyDescriptor($kd));
        }
    }

    /**
     * Collect the value of the affiliationOwnerId-property
     * @return string
     */
    public function getAffiliationOwnerID()
    {
        return $this->affiliationOwnerID;
    }

    /**
     * Set the value of the affiliationOwnerId-property
     * @param string $affiliationOwnerId
     */
    public function setAffiliationOwnerID($affiliationOwnerId)
    {
        assert(is_string($affiliationOwnerId));
        $this->affiliationOwnerID = $affiliationOwnerId;
    }

    /**
     * Collect the value of the ID-property
     * @return string|null
     */
    public function getID()
    {
        return $this->ID;
    }

    /**
     * Set the value of the ID-property
     * @param string|null $Id
     */
    public function setID($Id = null)
    {
        assert(is_string($Id) || is_null($Id));
        $this->ID = $Id;
    }

    /**
     * Collect the value of the validUntil-property
     * @return int|null
     */
    public function getValidUntil()
    {
        return $this->validUntil;
    }

    /**
     * Set the value of the validUntil-property
     * @param int|null $validUntil
     */
    public function setValidUntil($validUntil = null)
    {
        assert(is_int($validUntil) || is_null($validUntil));
        $this->validUntil = $validUntil;
    }

    /**
     * Collect the value of the cacheDuration-property
     * @return string|null
     */
    public function getCacheDuration()
    {
        return $this->cacheDuration;
    }

    /**
     * Set the value of the cacheDuration-property
     * @param string|null $cacheDuration
     */
    public function setCacheDuration($cacheDuration = null)
    {
        assert(is_string($cacheDuration) || is_null($cacheDuration));
        $this->cacheDuration = $cacheDuration;
    }

    /**
     * Collect the value of the Extensions-property
     * @return \SAML2\XML\Chunk[]
     */
    public function getExtensions()
    {
        return $this->Extensions;
    }

    /**
     * Set the value of the Extensions-property
     * @param array $extensions
     */
    public function setExtensions(array $extensions)
    {
        $this->Extensions = $extensions;
    }

    /**
     * Add an Extension.
     *
     * @param \SAML2\XML\Chunk $extensions The Extensions
     */
    public function addExtension(Extensions $extension)
    {
        $this->Extensions[] = $extension;
    }

    /**
     * Collect the value of the AffiliateMember-property
     * @return array
     */
    public function getAffiliateMember()
    {
        return $this->AffiliateMember;
    }

    /**
     * Set the value of the AffiliateMember-property
     * @param array $affiliateMember
     */
    public function setAffiliateMember(array $affiliateMember)
    {
        $this->AffiliateMember = $affiliateMember;
    }

    /**
     * Collect the value of the KeyDescriptor-property
     * @return \SAML2\XML\md\KeyDescriptor[]
     */
    public function getKeyDescriptor()
    {
        return $this->KeyDescriptor;
    }

    /**
     * Set the value of the KeyDescriptor-property
     * @param array $keyDescriptor
     */
    public function setKeyDescriptor(array $keyDescriptor)
    {
        $this->KeyDescriptor = $keyDescriptor;
    }

    /**
     * Add the value to the KeyDescriptor-property
     * @param \SAML2\XML\md\KeyDescriptor $keyDescriptor
     */
    public function addKeyDescriptor(KeyDescriptor $keyDescriptor)
    {
        $this->KeyDescriptor[] = $keyDescriptor;
    }

    /**
     * Add this AffiliationDescriptor to an EntityDescriptor.
     *
     * @param \DOMElement $parent The EntityDescriptor we should append this endpoint to.
     * @return \DOMElement
     */
    public function toXML(\DOMElement $parent)
    {
        assert(is_string($this->getAffiliationOwnerID()));
        assert(is_null($this->getID()) || is_string($this->getID()));
        assert(is_null($this->getValidUntil()) || is_int($this->getValidUntil()));
        assert(is_null($this->getCacheDuration()) || is_string($this->getCacheDuration()));
        assert(is_array($this->getExtensions()));
        assert(is_array($affiliateMember = $this->getAffiliateMember()));
        assert(!empty($affiliateMember));
        assert(is_array($this->getKeyDescriptor()));

        $e = $parent->ownerDocument->createElementNS(Constants::NS_MD, 'md:AffiliationDescriptor');
        $parent->appendChild($e);

        $e->setAttribute('affiliationOwnerID', $this->getAffiliationOwnerID());

        if ($this->getID() !== null) {
            $e->setAttribute('ID', $this->getID());
        }

        if ($this->getValidUntil() !== null) {
            $e->setAttribute('validUntil', gmdate('Y-m-d\TH:i:s\Z', $this->getValidUntil()));
        }

        if ($this->getCacheDuration() !== null) {
            $e->setAttribute('cacheDuration', $this->getCacheDuration());
        }

        Extensions::addList($e, $this->getExtensions());

        Utils::addStrings($e, Constants::NS_MD, 'md:AffiliateMember', false, $this->getAffiliateMember());

        foreach ($this->getKeyDescriptor() as $kd) {
            $kd->toXML($e);
        }

        $this->signElement($e, $e->firstChild);

        return $e;
    }
}
