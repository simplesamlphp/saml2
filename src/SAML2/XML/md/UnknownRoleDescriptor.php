<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XML\Utils as XMLUtils;

/**
 * Class representing unknown RoleDescriptors.
 *
 * @package simplesamlphp/saml2
 */
final class UnknownRoleDescriptor extends AbstractRoleDescriptor
{
    /**
     * This RoleDescriptor as XML
     *
     * @var \SimpleSAML\XML\Chunk
     * @psalm-suppress PropertyNotSetInConstructor
     */
    protected Chunk $xml;


    /**
     * Initialize an unknown RoleDescriptor.
     *
     * @param \DOMElement $xml The XML element we should load.
     * @return \SimpleSAML\SAML2\XML\md\UnknownRoleDescriptor
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingAttributeException if the supplied element is missing one of the mandatory attributes
     * @throws \SimpleSAML\XML\Exception\TooManyElementsException if too many child-elements of a type are specified
     */
    public static function fromXML(DOMElement $xml): object
    {
        $protocols = self::getAttribute($xml, 'protocolSupportEnumeration');

        $validUntil = self::getAttribute($xml, 'validUntil', null);
        $orgs = Organization::getChildrenOfClass($xml);
        Assert::maxCount($orgs, 1, 'More than one Organization found in this descriptor', TooManyElementsException::class);

        $extensions = Extensions::getChildrenOfClass($xml);
        Assert::maxCount($extensions, 1, 'Only one md:Extensions element is allowed.', TooManyElementsException::class);

        $object = new self(
            preg_split('/[\s]+/', trim($protocols)),
            self::getAttribute($xml, 'ID', null),
            $validUntil !== null ? XMLUtils::xsDateTimeToTimestamp($validUntil) : null,
            self::getAttribute($xml, 'cacheDuration', null),
            !empty($extensions) ? $extensions[0] : null,
            self::getAttribute($xml, 'errorURL', null),
            KeyDescriptor::getChildrenOfClass($xml),
            !empty($orgs) ? $orgs[0] : null,
            ContactPerson::getChildrenOfClass($xml)
        );
        $object->xml = new Chunk($xml);
        return $object;
    }


    /**
     * Get the original XML of this descriptor as a Chunk object.
     *
     * @return \SimpleSAML\XML\Chunk
     */
    public function getXML(): Chunk
    {
        return $this->xml;
    }


    /**
     * Add this RoleDescriptor to an EntityDescriptor.
     *
     * @param \DOMElement|null $parent The EntityDescriptor we should append this RoleDescriptor to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        return $this->xml->toXML($parent);
    }
}
