<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
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
     * EntitiesDescriptor constructor.
     *
     * @param \SimpleSAML\SAML2\XML\md\EntityDescriptor[] $entityDescriptors
     * @param \SimpleSAML\SAML2\XML\md\EntitiesDescriptor[] $entitiesDescriptors
     * @param string|null $Name
     * @param string|null $ID
     * @param int|null $validUntil
     * @param string|null $cacheDuration
     * @param \SimpleSAML\SAML2\XML\md\Extensions|null $extensions
     */
    public function __construct(
        protected array $entityDescriptors = [],
        protected array $entitiesDescriptors = [],
        protected ?string $Name = null,
        ?string $ID = null,
        ?int $validUntil = null,
        ?string $cacheDuration = null,
        ?Extensions $extensions = null,
    ) {
        Assert::true(
            !empty($entitiesDescriptors) || !empty($entityDescriptors),
            'At least one md:EntityDescriptor or md:EntitiesDescriptor element is required.',
            ProtocolViolationException::class,
        );
        Assert::allIsInstanceOf($entitiesDescriptors, EntitiesDescriptor::class);
        Assert::allIsInstanceOf($entityDescriptors, EntityDescriptor::class);

        parent::__construct($ID, $validUntil, $cacheDuration, $extensions);
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
     * Get the EntityDescriptor children objects
     *
     * @return \SimpleSAML\SAML2\XML\md\EntityDescriptor[]
     */
    public function getEntityDescriptors(): array
    {
        return $this->entityDescriptors;
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
     * Initialize an EntitiesDescriptor from an existing XML document.
     *
     * @param \DOMElement $xml The XML element we should load.
     * @return static
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\TooManyElementsException
     *   if too many child-elements of a type are specified
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'EntitiesDescriptor', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, EntitiesDescriptor::NS, InvalidDOMElementException::class);

        $validUntil = self::getOptionalAttribute($xml, 'validUntil', null);
        $orgs = Organization::getChildrenOfClass($xml);
        Assert::maxCount(
            $orgs,
            1,
            'More than one Organization found in this descriptor',
            TooManyElementsException::class,
        );

        $extensions = Extensions::getChildrenOfClass($xml);
        Assert::maxCount(
            $extensions,
            1,
            'Only one md:Extensions element is allowed.',
            TooManyElementsException::class,
        );

        $signature = Signature::getChildrenOfClass($xml);
        Assert::maxCount(
            $signature,
            1,
            'Only one ds:Signature element is allowed.',
            TooManyElementsException::class,
        );

        $entities = new static(
            EntityDescriptor::getChildrenOfClass($xml),
            EntitiesDescriptor::getChildrenOfClass($xml),
            self::getOptionalAttribute($xml, 'Name', null),
            self::getOptionalAttribute($xml, 'ID', null),
            $validUntil !== null ? XMLUtils::xsDateTimeToTimestamp($validUntil) : null,
            self::getOptionalAttribute($xml, 'cacheDuration', null),
            !empty($extensions) ? $extensions[0] : null,
        );

        if (!empty($signature)) {
            $entities->setSignature($signature[0]);
            $entities->setXML($xml);
        }

        return $entities;
    }


    /**
     * Convert this assertion to an unsigned XML document.
     * This method does not sign the resulting XML document.
     *
     * @return \DOMElement The root element of the DOM tree
     */
    public function toUnsignedXML(?DOMElement $parent = null): DOMElement
    {
        $e = parent::toUnsignedXML($parent);

        if ($this->Name !== null) {
            $e->setAttribute('Name', $this->Name);
        }

        foreach ($this->getEntitiesDescriptors() as $entitiesDescriptor) {
            $entitiesDescriptor->toXML($e);
        }

        foreach ($this->getEntityDescriptors() as $entityDescriptor) {
            $entityDescriptor->toXML($e);
        }

        return $e;
    }
}
