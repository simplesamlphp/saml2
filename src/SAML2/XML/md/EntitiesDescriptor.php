<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\ProtocolViolationException;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XML\Utils as XMLUtils;
use SimpleSAML\XMLSecurity\XML\ds\Signature;

/**
 * Class representing SAML 2 EntitiesDescriptor element.
 *
 * @package simplesamlphp/saml2
 */
final class EntitiesDescriptor extends AbstractMetadataDocument
{
    /**
     * The name of this entity collection.
     *
     * @var string|null
     */
    protected ?string $Name = null;

    /** @var \SimpleSAML\SAML2\XML\md\EntityDescriptor[] */
    protected array $entityDescriptors = [];

    /** @var \SimpleSAML\SAML2\XML\md\EntitiesDescriptor[] */
    protected array $entitiesDescriptors = [];


    /**
     * EntitiesDescriptor constructor.
     *
     * @param \SimpleSAML\SAML2\XML\md\EntityDescriptor[] $entityDescriptors
     * @param \SimpleSAML\SAML2\XML\md\EntitiesDescriptor[] $entitiesDescriptors
     * @param string|null $name
     * @param string|null $ID
     * @param int|null $validUntil
     * @param string|null $cacheDuration
     * @param \SimpleSAML\SAML2\XML\md\Extensions|null $extensions
     * @param \DOMAttr[] $namespacedAttributes
     */
    public function __construct(
        array $entityDescriptors = [],
        array $entitiesDescriptors = [],
        ?string $name = null,
        ?string $ID = null,
        ?int $validUntil = null,
        ?string $cacheDuration = null,
        ?Extensions $extensions = null,
        array $namespacedAttributes = []
    ) {
        Assert::true(
            !empty($entitiesDescriptors) || !empty($entityDescriptors),
            'At least one md:EntityDescriptor or md:EntitiesDescriptor element is required.'
        );

        parent::__construct($ID, $validUntil, $cacheDuration, $extensions, $namespacedAttributes);

        $this->setName($name);
        $this->setEntityDescriptors($entityDescriptors);
        $this->setEntitiesDescriptors($entitiesDescriptors);
    }


    /**
     * Initialize an EntitiesDescriptor from an existing XML document.
     *
     * @param \DOMElement $xml The XML element we should load.
     * @return \SimpleSAML\SAML2\XML\md\EntitiesDescriptor
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\TooManyElementsException if too many child-elements of a type are specified
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
            $validUntil !== null ? XMLUtils::xsDateTimeToTimestamp($validUntil) : null,
            self::getAttribute($xml, 'cacheDuration', null),
            !empty($extensions) ? $extensions[0] : null,
            self::getAttributesNSFromXML($xml)
        );

        if (!empty($signature)) {
            $entities->setSignature($signature[0]);
        }

        return $entities;
    }


    /**
     * Get the EntitiesDescriptor children objects
     *
     * @return \SimpleSAML\SAML2\XML\md\EntitiesDescriptor[]
     */
    public function getEntitiesDescriptors(): array
    {
        return $this->entitiesDescriptors;
    }


    /**
     * Set the EntitiesDescriptor children objects
     *
     * @param \SimpleSAML\SAML2\XML\md\EntitiesDescriptor[] $entitiesDescriptors
     */
    protected function setEntitiesDescriptors(array $entitiesDescriptors): void
    {
        Assert::allIsInstanceOf($entitiesDescriptors, EntitiesDescriptor::class);
        $this->entitiesDescriptors = $entitiesDescriptors;
    }


    /**
     * Get the EntityDescriptor children objects
     *
     * @return \SimpleSAML\SAML2\XML\md\EntityDescriptor[]
     */
    public function getEntityDescriptors(): array
    {
        return $this->entityDescriptors;
    }



    /**
     * Set the EntityDescriptor children objects
     *
     * @param \SimpleSAML\SAML2\XML\md\EntityDescriptor[] $entityDescriptors
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

        foreach ($this->getAttributesNS() as $attr) {
            $e->setAttributeNS($attr['namespaceURI'], $attr['qualifiedName'], $attr['value']);
        }

        if ($this->Name !== null) {
            $e->setAttribute('Name', $this->Name);
        }

        foreach ($this->entitiesDescriptors as $entitiesDescriptor) {
            $entitiesDescriptor->toXML($e);
        }

        foreach ($this->entityDescriptors as $entityDescriptor) {
            $entityDescriptor->toXML($e);
        }

        if ($this->signer !== null) {
            $signedXML = $this->doSign($e);
            $signedXML->insertBefore($this->signature->toXML($signedXML), $signedXML->firstChild);
            return $signedXML;
        }

        return $e;
    }
}
