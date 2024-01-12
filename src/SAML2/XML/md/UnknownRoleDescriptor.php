<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DateTimeImmutable;
use DOMElement;
use SimpleSAML\XML\Chunk;

/**
 * Class representing unknown RoleDescriptors.
 *
 * @package simplesamlphp/saml2
 */
final class UnknownRoleDescriptor extends AbstractRoleDescriptor
{
    /**
     * Initialize an unknown RoleDescriptor.
     *
     * @param \SimpleSAML\XML\Chunk $chunk The whole RoleDescriptor element as a chunk object.
     * @param string $type
     * @param string[] $protocolSupportEnumeration A set of URI specifying the protocols supported.
     * @param string|null $ID The ID for this document. Defaults to null.
     * @param \DateTimeImmutable|null $validUntil Unix time of validity for this document. Defaults to null.
     * @param string|null $cacheDuration Maximum time this document can be cached. Defaults to null.
     * @param \SimpleSAML\SAML2\XML\md\Extensions|null $extensions An Extensions object. Defaults to null.
     * @param string|null $errorURL An URI where to redirect users for support. Defaults to null.
     * @param \SimpleSAML\SAML2\XML\md\KeyDescriptor[] $keyDescriptors An array of KeyDescriptor elements.
     *   Defaults to an empty array.
     * @param \SimpleSAML\SAML2\XML\md\Organization|null $organization
     *   The organization running this entity. Defaults to null.
     * @param \SimpleSAML\SAML2\XML\md\ContactPerson[] $contacts
     *   An array of contacts for this entity. Defaults to an empty array.
     * @param list<\SimpleSAML\XML\Attribute> $namespacedAttributes
     */
    protected function __construct(
        protected Chunk $chunk,
        string $type,
        array $protocolSupportEnumeration,
        ?string $ID = null,
        ?DateTimeImmutable $validUntil = null,
        ?string $cacheDuration = null,
        ?Extensions $extensions = null,
        ?string $errorURL = null,
        array $keyDescriptors = [],
        ?Organization $organization = null,
        array $contacts = [],
        array $namespacedAttributes = []
    ) {
        parent::__construct(
            $type,
            $protocolSupportEnumeration,
            $ID,
            $validUntil,
            $cacheDuration,
            $extensions,
            $errorURL,
            $keyDescriptors,
            $organization,
            $contacts,
            $namespacedAttributes,
        );
    }


    /**
     * Get the raw version of this RoleDescriptor as a Chunk.
     *
     * @return \SimpleSAML\XML\Chunk
     */
    public function getRawRoleDescriptor(): Chunk
    {
        return $this->chunk;
    }


    /**
     * Convert this RoleDescriptor to XML.
     *
     * @param \DOMElement|null $parent The element we are converting to XML.
     * @return \DOMElement The XML element after adding the data corresponding to this unknown RoleDescriptor.
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        return $this->getRawRoleDescriptor()->toXML($parent);
    }
}
