<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use DOMElement;
use SAML2\Exception\InvalidDOMElementException;
use SAML2\Exception\TooManyElementsException;
use SAML2\Utils;
use SAML2\XML\ds\Signature;
use SimpleSAML\Assert\Assert;

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
     * @param \SAML2\XML\md\EntityDescriptor[] $entityDescriptors
     * @param \SAML2\XML\md\EntitiesDescriptor[] $entitiesDescriptors
     * @param string|null $name
     * @param string|null $ID
     * @param int|null $validUntil
     * @param string|null $cacheDuration
     * @param \SAML2\XML\md\Extensions|null $extensions
     */
    public function __construct(
        array $entityDescriptors = [],
        array $entitiesDescriptors = [],
        ?string $name = null,
        ?string $ID = null,
        ?int $validUntil = null,
        ?string $cacheDuration = null,
        ?Extensions $extensions = null
    ) {
        Assert::true(
            !empty($entitiesDescriptors) || !empty($entityDescriptors),
            'At least one md:EntityDescriptor or md:EntitiesDescriptor element is required.'
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
     *
     * @throws \SAML2\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     * @throws \SAML2\Exception\TooManyElementsException if too many child-elements of a type are specified
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'EntitiesDescriptor', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, EntitiesDescriptor::NS, InvalidDOMElementException::class);

        $validUntil = self::getAttribute($xml, 'validUntil', null);
        $orgs = Organization::getChildrenOfClass($xml);
        Assert::maxCount($orgs, 1, 'More than one Organization found in this descriptor', TooManyElementsException::class);

        $extensions = Extensions::getChildrenOfClass($xml);
        Assert::maxCount($extensions, 1, 'Only one md:Extensions element is allowed.', TooManyElementsException::class);

        $signature = Signature::getChildrenOfClass($xml);
        Assert::maxCount($signature, 1, 'Only one ds:Signature element is allowed.', TooManyElementsException::class);

        $entities = new self(
            EntityDescriptor::getChildrenOfClass($xml),
            EntitiesDescriptor::getChildrenOfClass($xml),
            self::getAttribute($xml, 'Name', null),
            self::getAttribute($xml, 'ID', null),
            $validUntil !== null ? Utils::xsDateTimeToTimestamp($validUntil) : null,
            self::getAttribute($xml, 'cacheDuration', null),
            !empty($extensions) ? $extensions[0] : null
        );
        if (!empty($signature)) {
            $entities->setSignature($signature[0]);
        }
        return $entities;
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
     * @param \SAML2\XML\md\EntitiesDescriptor[] $entitiesDescriptors
     */
    protected function setEntitiesDescriptors(array $entitiesDescriptors): void
    {
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
     * @param \SAML2\XML\md\EntityDescriptor[] $entityDescriptors
     */
    protected function setEntityDescriptors(array $entityDescriptors): void
    {
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
        $this->Name = $name;
    }


    /**
     * Convert this EntitiesDescriptor to XML.
     *
     * @param \DOMElement|null $parent The EntitiesDescriptor we should append this EntitiesDescriptor to.
     * @return \DOMElement
     * @throws \Exception
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

        return $this->signElement($e);
    }
}
