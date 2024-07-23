<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2;

use DateTimeImmutable;
use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Assert\Assert as SAMLAssert;
use SimpleSAML\SAML2\XML\md\AbstractRoleDescriptor;
use SimpleSAML\SAML2\XML\md\ContactPerson;
use SimpleSAML\SAML2\XML\md\Extensions;
use SimpleSAML\SAML2\XML\md\KeyDescriptor;
use SimpleSAML\SAML2\XML\md\Organization;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\MissingElementException;
use SimpleSAML\XML\Exception\TooManyElementsException;

/**
 * Example class to demonstrate how RoleDescriptor can be extended.
 *
 * @package simplesamlphp\saml2
 */
final class CustomRoleDescriptor extends AbstractRoleDescriptor
{
    /** @var string */
    protected const XSI_TYPE_NAME = 'CustomRoleDescriptorType';

    /** @var string */
    protected const XSI_TYPE_NAMESPACE = C::NAMESPACE;

    /** @var string */
    protected const XSI_TYPE_PREFIX = 'ssp';


    /**
     * CustomRoleDescriptor constructor.
     *
     * @param \SimpleSAML\XML\Chunk[] $chunk
     * @param string[] $protocolSupportEnumeration A set of URI specifying the protocols supported.
     * @param string|null $ID The ID for this document. Defaults to null.
     * @param \DateTimeImmutable|null $validUntil Unix time of validity for this document. Defaults to null.
     * @param string|null $cacheDuration Maximum time this document can be cached. Defaults to null.
     * @param \SimpleSAML\SAML2\XML\md\Extensions|null $extensions An Extensions object. Defaults to null.
     * @param string|null $errorURL An URI where to redirect users for support. Defaults to null.
     * @param \SimpleSAML\SAML2\XML\md\KeyDescriptor[] $keyDescriptor An array of KeyDescriptor elements.
     *   Defaults to an empty array.
     * @param \SimpleSAML\SAML2\XML\md\Organization|null $organization
     *   The organization running this entity. Defaults to null.
     * @param \SimpleSAML\SAML2\XML\md\ContactPerson[] $contact
     *   An array of contacts for this entity. Defaults to an empty array.
     * @param list<\SimpleSAML\XML\Attribute> $namespacedAttributes
     */
    public function __construct(
        protected array $chunk,
        array $protocolSupportEnumeration,
        ?string $ID = null,
        ?DateTimeImmutable $validUntil = null,
        ?string $cacheDuration = null,
        ?Extensions $extensions = null,
        ?string $errorURL = null,
        array $keyDescriptor = [],
        ?Organization $organization = null,
        array $contact = [],
        array $namespacedAttributes = [],
    ) {
        Assert::allIsInstanceOf($chunk, Chunk::class);

        parent::__construct(
            self::XSI_TYPE_PREFIX . ':' . self::XSI_TYPE_NAME,
            $protocolSupportEnumeration,
            $ID,
            $validUntil,
            $cacheDuration,
            $extensions,
            $errorURL,
            $keyDescriptor,
            $organization,
            $contact,
            $namespacedAttributes,
        );
    }


    /**
     * Get the value of the chunk-attribute.
     *
     * @return \SimpleSAML\XML\Chunk[]
     */
    public function getChunk(): array
    {
        return $this->chunk;
    }


    /**
     * Convert XML into a RoleDescriptor
     *
     * @param \DOMElement $xml The XML element we should load
     * @return static
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'RoleDescriptor', InvalidDOMElementException::class);
        Assert::notNull($xml->namespaceURI, InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, AbstractRoleDescriptor::NS, InvalidDOMElementException::class);
        Assert::true(
            $xml->hasAttributeNS(C::NS_XSI, 'type'),
            'Missing required xsi:type in <saml:RoleDescriptor> element.',
            InvalidDOMElementException::class,
        );

        $type = $xml->getAttributeNS(C::NS_XSI, 'type');
        Assert::same($type, self::XSI_TYPE_PREFIX . ':' . self::XSI_TYPE_NAME);

        $protocols = self::getAttribute($xml, 'protocolSupportEnumeration');

        $validUntil = self::getOptionalAttribute($xml, 'validUntil', null);
        SAMLAssert::nullOrValidDateTime($validUntil);

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

        $chunk = [];
        foreach ($xml->childNodes as $c) {
            if (!($c instanceof DOMElement)) {
                continue;
            } elseif ($c->namespaceURI === C::NS_MD) {
                continue;
            }

            $chunk[] = new Chunk($c);
        }
        Assert::minCount($chunk, 1, 'At least one ssp:Chunk element must be provided.', MissingElementException::class);

        return new static(
            $chunk,
            preg_split('/[\s]+/', trim($protocols)),
            self::getOptionalAttribute($xml, 'ID', null),
            $validUntil !== null ? new DateTimeImmutable($validUntil) : null,
            self::getOptionalAttribute($xml, 'cacheDuration', null),
            !empty($extensions) ? $extensions[0] : null,
            self::getOptionalAttribute($xml, 'errorURL', null),
            KeyDescriptor::getChildrenOfClass($xml),
            !empty($orgs) ? $orgs[0] : null,
            ContactPerson::getChildrenOfClass($xml),
            self::getAttributesNSFromXML($xml),
        );
    }


    /**
     * Convert this RoleDescriptor to XML.
     *
     * @param \DOMElement $parent The element we are converting to XML.
     * @return \DOMElement The XML element after adding the data corresponding to this RoleDescriptor.
     */
    public function toUnsignedXML(DOMElement $parent = null): DOMElement
    {
        $e = parent::toUnsignedXML($parent);

        foreach ($this->getChunk() as $chunk) {
            $chunk->toXML($e);
        }

        return $e;
    }
}
