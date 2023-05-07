<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use Exception;
use InvalidArgumentException;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\SignedElementHelper;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\Utils as XMLUtils;

use function count;
use function filter_var;
use function gmdate;
use function implode;
use function is_null;

/**
 * Class representing SAML 2 RoleDescriptor element.
 *
 * @package SimpleSAMLphp
 */
class RoleDescriptor extends SignedElementHelper
{
    /**
     * The name of this descriptor element.
     *
     * @var string
     */
    private string $elementName;

    /**
     * The ID of this element.
     *
     * @var string|null
     */
    private ?string $ID = null;

    /**
     * List of supported protocols.
     *
     * @var array
     */
    private array $protocolSupportEnumeration = [];

    /**
     * Error URL for this role.
     *
     * @var string|null
     */
    private ?string $errorURL = null;

    /**
     * Extensions on this element.
     *
     * Array of extension elements.
     *
     * @var array
     */
    private array $Extensions = [];

    /**
     * KeyDescriptor elements.
     *
     * Array of \SimpleSAML\SAML2\XML\md\KeyDescriptor elements.
     *
     * @var \SimpleSAML\SAML2\XML\md\KeyDescriptor[]
     */
    private array $KeyDescriptor = [];

    /**
     * Organization of this role.
     *
     * @var \SimpleSAML\SAML2\XML\md\Organization|null
     */
    private ?Organization $Organization = null;

    /**
     * ContactPerson elements for this role.
     *
     * Array of \SimpleSAML\SAML2\XML\md\ContactPerson objects.
     *
     * @var \SimpleSAML\SAML2\XML\md\ContactPerson[]
     */
    private array $ContactPerson = [];


    /**
     * Initialize a RoleDescriptor.
     *
     * @param string $elementName The name of this element.
     * @param \DOMElement|null $xml The XML element we should load.
     * @throws \Exception
     */
    protected function __construct(string $elementName, DOMElement $xml = null)
    {
        parent::__construct($xml);
        $this->elementName = $elementName;

        if ($xml === null) {
            return;
        }

        if ($xml->hasAttribute('ID')) {
            $this->ID = $xml->getAttribute('ID');
        }
        if ($xml->hasAttribute('validUntil')) {
            $this->validUntil = XMLUtils::xsDateTimeToTimestamp($xml->getAttribute('validUntil'));
        }
        if ($xml->hasAttribute('cacheDuration')) {
            $this->cacheDuration = $xml->getAttribute('cacheDuration');
        }

        if (!$xml->hasAttribute('protocolSupportEnumeration')) {
            throw new MissingAttributeException('Missing protocolSupportEnumeration attribute on ' . $xml->localName);
        }
        $this->protocolSupportEnumeration = preg_split('/[\s]+/', $xml->getAttribute('protocolSupportEnumeration'));

        if ($xml->hasAttribute('errorURL')) {
            $this->errorURL = $xml->getAttribute('errorURL');
        }

        $this->Extensions = Extensions::getList($xml);
        $xpCache = XPath::getXPath($xml);
        foreach (XPath::xpQuery($xml, './saml_metadata:KeyDescriptor', $xpCache) as $kd) {
            /** @var \DOMElement $kd */
            $this->KeyDescriptor[] = new KeyDescriptor($kd);
        }

        $organization = XPath::xpQuery($xml, './saml_metadata:Organization', $xpCache);
        if (count($organization) > 1) {
            throw new Exception('More than one Organization in the entity.');
        } elseif (!empty($organization)) {
            /** @var \DOMElement $organization[0] */
            $this->Organization = new Organization($organization[0]);
        }

        foreach (XPath::xpQuery($xml, './saml_metadata:ContactPerson', $xpCache) as $cp) {
            /** @var \DOMElement $cp */
            $this->ContactPerson[] = new ContactPerson($cp);
        }
    }


    /**
     * Collect the value of the ID property.
     *
     * @return string|null
     */
    public function getID(): ?string
    {
        return $this->ID;
    }


    /**
     * Set the value of the ID property.
     *
     * @param string|null $Id
     * @return void
     */
    public function setID(string $Id = null): void
    {
        $this->ID = $Id;
    }


    /**
     * Collect the value of the validUntil-property
     * @return int|null
     */
    public function getValidUntil(): ?int
    {
        return $this->validUntil;
    }


    /**
     * Set the value of the validUntil-property
     * @param int|null $validUntil
     * @return void
     */
    public function setValidUntil(int $validUntil = null): void
    {
        $this->validUntil = $validUntil;
    }


    /**
     * Collect the value of the cacheDuration-property
     * @return string|null
     */
    public function getCacheDuration(): ?string
    {
        return $this->cacheDuration;
    }


    /**
     * Set the value of the cacheDuration-property
     * @param string|null $cacheDuration
     * @return void
     */
    public function setCacheDuration(string $cacheDuration = null): void
    {
        $this->cacheDuration = $cacheDuration;
    }


    /**
     * Collect the value of the Extensions property.
     *
     * @return \SimpleSAML\XML\Chunk[]
     */
    public function getExtensions(): array
    {
        return $this->Extensions;
    }


    /**
     * Set the value of the Extensions property.
     *
     * @param array $extensions
     * @return void
     */
    public function setExtensions(array $extensions): void
    {
        $this->Extensions = $extensions;
    }


    /**
     * Add an Extension.
     *
     * @param \SimpleSAML\XML\Chunk $extensions The Extensions
     * @return void
     */
    public function addExtension(Extensions $extension): void
    {
        $this->Extensions[] = $extension;
    }


    /**
     * Set the value of the errorURL property.
     *
     * @param string|null $errorURL
     * @return void
     */
    public function setErrorURL(string $errorURL = null): void
    {
        if (!is_null($errorURL) && !filter_var($errorURL, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('RoleDescriptor errorURL is not a valid URL.');
        }
        $this->errorURL = $errorURL;
    }


    /**
     * Collect the value of the errorURL property.
     *
     * @return string|null
     */
    public function getErrorURL(): ?string
    {
        return $this->errorURL;
    }


    /**
     * Collect the value of the ProtocolSupportEnumeration property.
     *
     * @return string[]
     */
    public function getProtocolSupportEnumeration(): array
    {
        return $this->protocolSupportEnumeration;
    }


    /**
     * Set the value of the ProtocolSupportEnumeration property.
     *
     * @param array $protocols
     * @return void
     */
    public function setProtocolSupportEnumeration(array $protocols): void
    {
        $this->protocolSupportEnumeration = $protocols;
    }


    /**
     * Add the value to the ProtocolSupportEnumeration property.
     *
     * @param string $protocol
     * @return void
     */
    public function addProtocolSupportEnumeration(string $protocol): void
    {
        $this->protocolSupportEnumeration[] = $protocol;
    }


    /**
     * Collect the value of the Organization property.
     *
     * @return \SimpleSAML\SAML2\XML\md\Organization|null
     */
    public function getOrganization(): ?Organization
    {
        return $this->Organization;
    }


    /**
     * Set the value of the Organization property.
     *
     * @param \SimpleSAML\SAML2\XML\md\Organization|null $organization
     * @return void
     */
    public function setOrganization(Organization $organization = null): void
    {
        $this->Organization = $organization;
    }


    /**
     * Collect the value of the ContactPerson property.
     *
     * @return \SimpleSAML\SAML2\XML\md\ContactPerson[]
     */
    public function getContactPerson(): array
    {
        return $this->ContactPerson;
    }


    /**
     * Set the value of the ContactPerson property.
     *
     * @param \SimpleSAML\SAML2\XML\md\ContactPerson[] $contactPerson
     * @return void
     */
    public function setContactPerson(array $contactPerson): void
    {
        $this->ContactPerson = $contactPerson;
    }


    /**
     * Add the value to the ContactPerson property.
     *
     * @param \SimpleSAML\SAML2\XML\md\ContactPerson $contactPerson
     * @return void
     */
    public function addContactPerson(ContactPerson $contactPerson): void
    {
        $this->ContactPerson[] = $contactPerson;
    }


    /**
     * Collect the value of the KeyDescriptor property.
     *
     * @return \SimpleSAML\SAML2\XML\md\KeyDescriptor[]
     */
    public function getKeyDescriptor(): array
    {
        return $this->KeyDescriptor;
    }


    /**
     * Set the value of the KeyDescriptor property.
     *
     * @param \SimpleSAML\SAML2\XML\md\KeyDescriptor[] $keyDescriptor
     * @return void
     */
    public function setKeyDescriptor(array $keyDescriptor): void
    {
        $this->KeyDescriptor = $keyDescriptor;
    }


    /**
     * Add the value to the KeyDescriptor property.
     *
     * @param \SimpleSAML\SAML2\XML\md\KeyDescriptor $keyDescriptor
     * @return void
     */
    public function addKeyDescriptor(KeyDescriptor $keyDescriptor): void
    {
        $this->KeyDescriptor[] = $keyDescriptor;
    }


    /**
     * Add this RoleDescriptor to an EntityDescriptor.
     *
     * @param \DOMElement $parent The EntityDescriptor we should append this endpoint to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent): DOMElement
    {
        $e = $parent->ownerDocument->createElementNS(C::NS_MD, $this->elementName);
        $parent->appendChild($e);

        if ($this->ID !== null) {
            $e->setAttribute('ID', $this->ID);
        }

        if ($this->validUntil !== null) {
            $e->setAttribute('validUntil', gmdate('Y-m-d\TH:i:s\Z', $this->validUntil));
        }

        if ($this->cacheDuration !== null) {
            $e->setAttribute('cacheDuration', $this->cacheDuration);
        }

        $e->setAttribute('protocolSupportEnumeration', implode(' ', $this->protocolSupportEnumeration));

        if ($this->errorURL !== null) {
            $e->setAttribute('errorURL', $this->errorURL);
        }

        Extensions::addList($e, $this->Extensions);

        foreach ($this->KeyDescriptor as $kd) {
            $kd->toXML($e);
        }

        if ($this->Organization !== null) {
            $this->Organization->toXML($e);
        }

        foreach ($this->ContactPerson as $cp) {
            $cp->toXML($e);
        }

        return $e;
    }
}
