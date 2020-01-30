<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use DOMElement;
use SAML2\Utils;
use SAML2\XML\Chunk;
use Webmozart\Assert\Assert;

/**
 * Class representing unknown RoleDescriptors.
 *
 * @package SimpleSAMLphp
 */
final class UnknownRoleDescriptor extends AbstractRoleDescriptor
{
    /**
     * This RoleDescriptor as XML
     *
     * @var \SAML2\XML\Chunk
     */
    protected $xml;


    /**
     * Initialize an unknown RoleDescriptor.
     *
     * @param \DOMElement $xml The XML element we should load.
     *
     * @return \SAML2\XML\md\UnknownRoleDescriptor
     *
     * @throws \Exception
     */
    public static function fromXML(DOMElement $xml = null): object
    {
        $validUntil = self::getAttribute($xml, 'validUntil', null);
        $orgs = Organization::getChildrenOfClass($xml);
        Assert::maxCount($orgs, 1, 'More than one Organization found in this descriptor');

        $extensions = Extensions::getChildrenOfClass($xml);
        Assert::maxCount($extensions, 1, 'Only one md:Extensions element is allowed.');

        $object = new self(
            preg_split('/[\s]+/', trim(self::getAttribute($xml, 'protocolSupportEnumeration'))),
            self::getAttribute($xml, 'ID', null),
            $validUntil !== null ? Utils::xsDateTimeToTimestamp($validUntil) : null,
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
     * @return Chunk
     */
    public function getXML(): Chunk
    {
        return $this->xml;
    }


    /**
     * Add this RoleDescriptor to an EntityDescriptor.
     *
     * @param \DOMElement $parent The EntityDescriptor we should append this RoleDescriptor to.
     * @return \DOMElement
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        return $this->xml->toXML($parent);
    }
}
