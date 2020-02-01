<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use DOMElement;
use SAML2\Utils;
use Webmozart\Assert\Assert;

/**
 * Class representing SAML 2 EntitiesDescriptor element.
 *
 * @package SimpleSAMLphp
 */
final class EntitiesDescriptor extends AbstractMetadataDocument
{
    /**
     * The name of this entity collection.
     *
     * @var string|null
     */
    protected $Name = null;

    /** @var \SAML2\XML\md\EntityDescriptor[] */
    protected $entityDescriptors = [];

    /** @var \SAML2\XML\md\EntitiesDescriptor[] */
    protected $entitiesDescriptors = [];


    /**
     * EntitiesDescriptor constructor.
     *
     * @param string|null $name
     * @param \SAML2\XML\md\EntityDescriptor[]|null $entityDescriptors
     * @param \SAML2\XML\md\EntitiesDescriptor[]|null $entitiesDescriptors
     * @param string|null $ID
     * @param int|null $validUntil
     * @param string|null $cacheDuration
     * @param \SAML2\XML\md\Extensions|null $extensions
     */
    public function __construct(
        ?string $name = null,
        ?array $entityDescriptors = null,
        ?array $entitiesDescriptors = null,
        ?string $ID = null,
        ?int $validUntil = null,
        ?string $cacheDuration = null,
        ?Extensions $extensions = null
    ) {
        Assert::true(
            !empty($entitiesDescriptors) || !empty($entityDescriptors),
            'At least one md:EntityDescriptor or md:EntitiesDescriptor element is required.'
        );
        Assert::false(
            is_null($validUntil) && is_null($cacheDuration),
            'You need either validUntil or cacheDuration set'
        );

        parent::__construct($ID, $validUntil, $cacheDuration, $extensions);

        $this->setName($name);
        $this->setEntityDescriptors($entityDescriptors);
        $this->setEntitiesDescriptors($entitiesDescriptors);
    }


    /**
     * Initialize an EntitiesDescriptor from an existing XML document.
     *
     * @param \DOMElement $xml The XML element we should load.
     * @return \SAML2\XML\md\EntitiesDescriptor
     * @throws \Exception
     */
    public static function fromXML(DOMElement $xml): object
    {
        $validUntil = self::getAttribute($xml, 'validUntil', null);
        $orgs = Organization::getChildrenOfClass($xml);
        Assert::maxCount($orgs, 1, 'More than one Organization found in this descriptor');

        $extensions = Extensions::getChildrenOfClass($xml);
        Assert::maxCount($extensions, 1, 'Only one md:Extensions element is allowed.');

        return new self(
            self::getAttribute($xml, 'Name'),
            EntityDescriptor::getChildrenOfClass($xml),
            EntitiesDescriptor::getChildrenOfClass($xml),
            self::getAttribute($xml, 'ID', null),
            $validUntil !== null ? Utils::xsDateTimeToTimestamp($validUntil) : null,
            self::getAttribute($xml, 'cacheDuration', null),
            !empty($extensions) ? $extensions[0] : null
        );
    }


    /**
     * Get the EntitiesDescriptor children objects
     *
     * @return \SAML2\XML\md\EntitiesDescriptor[]
     */
    public function getEntitiesDescriptors(): array
    {
        return $this->entitiesDescriptors;
    }


    /**
     * Set the EntitiesDescriptor children objects
     *
     * @param \SAML2\XML\md\EntitiesDescriptor[]|null $entitiesDescriptors
     */
    protected function setEntitiesDescriptors(?array $entitiesDescriptors): void
    {
        if ($entitiesDescriptors === null) {
            return;
        }
        Assert::allIsInstanceOf($entitiesDescriptors, EntitiesDescriptor::class);
        $this->entitiesDescriptors = $entitiesDescriptors;
    }


    /**
     * Get the EntityDescriptor children objects
     *
     * @return \SAML2\XML\md\EntityDescriptor[]
     */
    public function getEntityDescriptors(): array
    {
        return $this->entityDescriptors;
    }


    /**
     * Set the EntityDescriptor children objects
     *
     * @param \SAML2\XML\md\EntityDescriptor[]|null $entityDescriptors
     */
    protected function setEntityDescriptors(?array $entityDescriptors): void
    {
        if ($entityDescriptors === null) {
            return;
        }
        Assert::allIsInstanceOf($entityDescriptors, EntityDescriptor::class);
        $this->entityDescriptors = $entityDescriptors;
    }



    /**
     * Collect the value of the Name property.
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->Name;
    }


    /**
     * Set the value of the Name property.
     *
     * @param string|null $name
     */
    protected function setName(?string $name = null): void
    {
        if ($name === null) {
            return;
        }
        Assert::notEmpty($name, 'Cannot assign an empty name to an md:EntitiesDescriptor.');
        $this->Name = $name;
    }




    /**
     * Convert this EntitiesDescriptor to XML.
     *
     * @param \DOMElement|null $parent The EntitiesDescriptor we should append this EntitiesDescriptor to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = parent::toXML($parent);

        if ($this->Name !== null) {
            $e->setAttribute('Name', $this->Name);
        }

        foreach ($this->entitiesDescriptors as $entitiesDescriptor) {
            $entitiesDescriptor->toXML($e);
        }

        foreach ($this->entityDescriptors as $entityDescriptor) {
            $entityDescriptor->toXML($e);
        }

        $this->signElement($e, $e->firstChild);

        return $e;
    }
}
