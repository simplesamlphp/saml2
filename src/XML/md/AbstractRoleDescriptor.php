<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Type\AnyURIListValue;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\Type\SAMLDateTimeValue;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\XML\ExtensionPointInterface;
use SimpleSAML\SAML2\XML\ExtensionPointTrait;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XMLSchema\Constants as C_XSI;
use SimpleSAML\XMLSchema\Exception\InvalidDOMElementException;
use SimpleSAML\XMLSchema\Exception\SchemaViolationException;
use SimpleSAML\XMLSchema\Exception\TooManyElementsException;
use SimpleSAML\XMLSchema\Type\DurationValue;
use SimpleSAML\XMLSchema\Type\IDValue;
use SimpleSAML\XMLSchema\Type\QNameValue;

use function array_pop;

/**
 * Class representing a SAML2 RoleDescriptor element.
 *
 * @package simplesamlphp/saml2
 */
abstract class AbstractRoleDescriptor extends AbstractRoleDescriptorType implements
    ExtensionPointInterface,
    SchemaValidatableElementInterface
{
    use ExtensionPointTrait;
    use SchemaValidatableElementTrait;


    /** @var string */
    public const LOCALNAME = 'RoleDescriptor';


    /**
     * Initialize a md:RoleDescriptor from scratch.
     *
     * @param \SimpleSAML\XMLSchema\Type\QNameValue $type
     * @param \SimpleSAML\SAML2\Type\AnyURIListValue $protocolSupportEnumeration
     *   A set of URI specifying the protocols supported.
     * @param \SimpleSAML\XMLSchema\Type\IDValue|null $ID The ID for this document. Defaults to null.
     * @param \SimpleSAML\SAML2\Type\SAMLDateTimeValue|null $validUntil Unix time of validity for this document.
     *   Defaults to null.
     * @param \SimpleSAML\XMLSchema\Type\DurationValue|null $cacheDuration Maximum time this document can be cached.
     *   Defaults to null.
     * @param \SimpleSAML\SAML2\XML\md\Extensions|null $extensions An Extensions object. Defaults to null.
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue|null $errorURL An URI where to redirect users for support.
     *   Defaults to null.
     * @param \SimpleSAML\SAML2\XML\md\KeyDescriptor[] $keyDescriptor
     *   An array of KeyDescriptor elements. Defaults to an empty array.
     * @param \SimpleSAML\SAML2\XML\md\Organization|null $organization
     *   The organization running this entity. Defaults to null.
     * @param \SimpleSAML\SAML2\XML\md\ContactPerson[] $contactPerson
     *   An array of contacts for this entity. Defaults to an empty array.
     * @param list<\SimpleSAML\XML\Attribute> $namespacedAttributes
     */
    public function __construct(
        protected QNameValue $type,
        AnyURIListValue $protocolSupportEnumeration,
        ?IDValue $ID = null,
        ?SAMLDateTimeValue $validUntil = null,
        ?DurationValue $cacheDuration = null,
        ?Extensions $extensions = null,
        ?SAMLAnyURIValue $errorURL = null,
        array $keyDescriptor = [],
        ?Organization $organization = null,
        array $contactPerson = [],
        array $namespacedAttributes = [],
    ) {
        parent::__construct(
            $protocolSupportEnumeration,
            $ID,
            $validUntil,
            $cacheDuration,
            $extensions,
            $errorURL,
            $keyDescriptor,
            $organization,
            $contactPerson,
            $namespacedAttributes,
        );
    }


    /**
     * Return the xsi:type value corresponding this element.
     *
     * @return \SimpleSAML\XMLSchema\Type\QNameValue
     */
    public function getXsiType(): QNameValue
    {
        return $this->type;
    }


    /**
     * Convert XML into an RoleDescriptor
     * @param \DOMElement $xml The XML element we should load
     * @return static
     *
     * @throws \SimpleSAML\XMLSchema\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'RoleDescriptor', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, C::NS_MD, InvalidDOMElementException::class);
        Assert::true(
            $xml->hasAttributeNS(C_XSI::NS_XSI, 'type'),
            'Missing required xsi:type in <md:RoleDescriptor> element.',
            SchemaViolationException::class,
        );

        $type = QNameValue::fromDocument($xml->getAttributeNS(C_XSI::NS_XSI, 'type'), $xml);

        // now check if we have a handler registered for it
        $handler = Utils::getContainer()->getExtensionHandler($type);
        if ($handler === null) {
            // we don't have a handler, proceed with unknown RoleDescriptor
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

            return new UnknownRoleDescriptor(
                new Chunk($xml),
                $type,
                self::getAttribute($xml, 'protocolSupportEnumeration', AnyURIListValue::class),
                self::getOptionalAttribute($xml, 'ID', IDValue::class, null),
                self::getOptionalAttribute($xml, 'validUntil', SAMLDateTimeValue::class, null),
                self::getOptionalAttribute($xml, 'cacheDuration', DurationValue::class, null),
                array_pop($extensions),
                self::getOptionalAttribute($xml, 'errorURL', SAMLAnyURIValue::class, null),
                KeyDescriptor::getChildrenOfClass($xml),
                array_pop($orgs),
                ContactPerson::getChildrenOfClass($xml),
                self::getAttributesNSFromXML($xml),
            );
        }

        Assert::subclassOf(
            $handler,
            AbstractRoleDescriptor::class,
            'Elements implementing RoleDescriptor must extend \SimpleSAML\SAML2\XML\saml\AbstractRoleDescriptor.',
        );

        return $handler::fromXML($xml);
    }
}
