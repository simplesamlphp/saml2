<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\Type\SAMLDateTimeValue;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\SAML2\XML\mdrpi\PublicationInfo;
use SimpleSAML\SAML2\XML\mdrpi\PublicationPath;
use SimpleSAML\SAML2\XML\mdrpi\RegistrationInfo;
use SimpleSAML\XML\Constants as C;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XMLSchema\Exception\InvalidDOMElementException;
use SimpleSAML\XMLSchema\Exception\TooManyElementsException;
use SimpleSAML\XMLSchema\Type\DurationValue;
use SimpleSAML\XMLSchema\Type\IDValue;
use SimpleSAML\XMLSecurity\XML\ds\Signature;

use function array_filter;
use function array_merge;
use function array_values;
use function count;

/**
 * Class representing SAML 2 EntitiesDescriptor element.
 *
 * @package simplesamlphp/saml2
 */
final class EntitiesDescriptor extends AbstractMetadataDocument implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;


    /**
     * EntitiesDescriptor constructor.
     *
     * @param \SimpleSAML\SAML2\XML\md\EntityDescriptor[] $entityDescriptors
     * @param \SimpleSAML\SAML2\XML\md\EntitiesDescriptor[] $entitiesDescriptors
     * @param \SimpleSAML\SAML2\Type\SAMLStringValue|null $Name
     * @param \SimpleSAML\XMLSchema\Type\IDValue|null $ID
     * @param \SimpleSAML\SAML2\Type\SAMLDateTimeValue|null $validUntil
     * @param \SimpleSAML\XMLSchema\Type\DurationValue|null $cacheDuration
     * @param \SimpleSAML\SAML2\XML\md\Extensions|null $extensions
     */
    public function __construct(
        protected array $entityDescriptors = [],
        protected array $entitiesDescriptors = [],
        protected ?SAMLStringValue $Name = null,
        ?IDValue $ID = null,
        ?SAMLDateTimeValue $validUntil = null,
        ?DurationValue $cacheDuration = null,
        ?Extensions $extensions = null,
    ) {
        Assert::true(
            !empty($entitiesDescriptors) || !empty($entityDescriptors),
            'At least one md:EntityDescriptor or md:EntitiesDescriptor element is required.',
            ProtocolViolationException::class,
        );
        Assert::maxCount($entitiesDescriptors, C::UNBOUNDED_LIMIT);
        Assert::maxCount($entityDescriptors, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOf($entitiesDescriptors, EntitiesDescriptor::class);
        Assert::allIsInstanceOf($entityDescriptors, EntityDescriptor::class);

        if ($extensions !== null) {
            /**
             * When a <mdrpi:RegistrationInfo> element appears in the <md:Extensions> element of a
             * <md:EntitiesDescriptor> element it applies to all descendant <md:EntitiesDescriptor> and
             * <md:EntityDescriptor> elements. That is to say, this is equivalent to putting an identical
             * <mdrpi:RegistrationInfo> on every descendant <md:EntityDescriptor>. When used in this
             * manner, descendant <md:EntitiesDescriptor> and <md:EntityDescriptor> elements MUST
             * NOT contain a <mdrpi:RegistrationInfo> element in their <md:Extensions> element.
             */
            $toplevel_regInfo = array_values(array_filter($extensions->getList(), function ($ext) {
                return $ext instanceof RegistrationInfo;
            }));

            /**
             * The <mdrpi:PublicationInfo> element SHOULD only be used on the root element of a metadata document.
             */
            $toplevel_pubInfo = array_values(array_filter($extensions->getList(), function ($ext) {
                return $ext instanceof PublicationInfo;
            }));

            /**
             * When a <mdrpi:PublicationPath> element appears in the <md:Extensions> element of a
             * <md:EntitiesDescriptor> element it applies to all descendant <md:EntitiesDescriptor> and
             * <md:EntityDescriptor> elements. That is to say, this is equivalent to putting an identical
             * <mdrpi:PublicationPath> on every descendant <md:EntitiesDescriptor> and <md:EntityDescriptor>.
             * When used in this manner, descendant <md:EntitiesDescriptor> and <md:EntityDescriptor>
             * elements MUST NOT contain a <mdrpi:PublicationPath> element in their <md:Extensions> element.
             */
            $toplevel_pubPath = array_values(array_filter($extensions->getList(), function ($ext) {
                return $ext instanceof PublicationPath;
            }));

            if (count($toplevel_regInfo) > 0 || count($toplevel_pubInfo) > 0 || count($toplevel_pubPath)) {
                $nestedExtensions = [];
                foreach (array_merge($entityDescriptors, $entitiesDescriptors) as $ed) {
                    $nestedExtensions = array_merge($nestedExtensions, $this->getRecursiveExtensions($ed));
                }

                if (count($toplevel_regInfo) > 0) {
                    $nested_regInfo = array_values(array_filter($nestedExtensions, function ($ext) {
                        return $ext instanceof RegistrationInfo;
                    }));

                    Assert::count(
                        $nested_regInfo,
                        0,
                        "<mdrpi:RegistrationInfo> already set at top-level.",
                        ProtocolViolationException::class,
                    );
                }

                if (count($toplevel_pubInfo) > 0) {
                    $nested_pubInfo = array_values(array_filter($nestedExtensions, function ($ext) {
                        return $ext instanceof PublicationInfo;
                    }));

                    Assert::count(
                        $nested_pubInfo,
                        0,
                        "<mdrpi:PublicationInfo> already set at top-level.",
                        ProtocolViolationException::class,
                    );
                }

                if (count($toplevel_pubPath) > 0) {
                    $nested_pubPath = array_values(array_filter($nestedExtensions, function ($ext) {
                        return $ext instanceof PublicationPath;
                    }));

                    Assert::count(
                        $nested_pubPath,
                        0,
                        "<mdrpi:PublicationPath> already set at top-level.",
                        ProtocolViolationException::class,
                    );
                }
            }
        }

        parent::__construct($ID, $validUntil, $cacheDuration, $extensions);
    }


    /**
     * Get all extensions from all nested entity/entities descriptors
     */
    private function getRecursiveExtensions(EntityDescriptor|EntitiesDescriptor $descriptor): array
    {
        $extensions = [];
        if ($descriptor->getExtensions() !== null) {
            $extensions = $descriptor->getExtensions()->getList();

            if ($descriptor instanceof EntitiesDescriptor) {
                $eds = array_merge($descriptor->getEntitiesDescriptors(), $descriptor->getEntityDescriptors());
                foreach ($eds as $ed) {
                    $extensions = array_merge($extensions, $descriptor->getRecursiveExtensions($ed));
                }
            }
        }

        return $extensions;
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
     * @return \SimpleSAML\SAML2\Type\SAMLStringValue|null
     */
    public function getName(): ?SAMLStringValue
    {
        return $this->Name;
    }


    /**
     * Initialize an EntitiesDescriptor from an existing XML document.
     *
     * @throws \SimpleSAML\XMLSchema\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XMLSchema\Exception\TooManyElementsException
     *   if too many child-elements of a type are specified
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'EntitiesDescriptor', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, EntitiesDescriptor::NS, InvalidDOMElementException::class);

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
            self::getOptionalAttribute($xml, 'Name', SAMLStringValue::class, null),
            self::getOptionalAttribute($xml, 'ID', IDValue::class, null),
            self::getOptionalAttribute($xml, 'validUntil', SAMLDateTimeValue::class, null),
            self::getOptionalAttribute($xml, 'cacheDuration', DurationValue::class, null),
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
     */
    public function toUnsignedXML(?DOMElement $parent = null): DOMElement
    {
        $e = parent::toUnsignedXML($parent);

        if ($this->getName() !== null) {
            $e->setAttribute('Name', $this->getName()->getValue());
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
