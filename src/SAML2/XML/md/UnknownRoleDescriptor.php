<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\XML\AbstractElement;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XML\Utils as XMLUtils;
use SimpleSAML\XMLSecurity\XML\ds\Signature;

use function array_pop;
use function preg_split;

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
     * @param \DOMElement $xml The XML element we should load.
     * @return \SimpleSAML\SAML2\XML\md\UnknownRoleDescriptor
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingAttributeException
     *   if the supplied element is missing one of the mandatory attributes
     * @throws \SimpleSAML\XML\Exception\TooManyElementsException
     *   if too many child-elements of a type are specified
     */
    public static function fromXML(DOMElement $xml): static
    {
        $protocols = self::getAttribute($xml, 'protocolSupportEnumeration');

        $validUntil = self::getAttribute($xml, 'validUntil', null);
        $orgs = Organization::getChildrenOfClass($xml);
        Assert::maxCount(
            $orgs,
            1,
            'More than one Organization found in this descriptor',
            TooManyElementsException::class,
        );

        $signature = Signature::getChildrenOfClass($xml);
        Assert::maxCount($signature, 1, 'Only one ds:Signature element is allowed.', TooManyElementsException::class);

        $extensions = Extensions::getChildrenOfClass($xml);
        Assert::maxCount(
            $extensions,
            1,
            'Only one md:Extensions element is allowed.',
            TooManyElementsException::class,
        );

        $descriptor = new static(
            preg_split('/[\s]+/', trim($protocols)),
            self::getAttribute($xml, 'ID', null),
            $validUntil !== null ? XMLUtils::xsDateTimeToTimestamp($validUntil) : null,
            self::getAttribute($xml, 'cacheDuration', null),
            !empty($extensions) ? $extensions[0] : null,
            self::getAttribute($xml, 'errorURL', null),
            KeyDescriptor::getChildrenOfClass($xml),
            !empty($orgs) ? $orgs[0] : null,
            ContactPerson::getChildrenOfClass($xml),
        );

        if (!empty($signature)) {
            $descriptor->setSignature($signature[0]);
        }

        $descriptor->setXML($xml);
        return $descriptor;
    }


    /**
     * Add this RoleDescriptor to an EntityDescriptor.
     *
     * @param \DOMElement|null $parent The EntityDescriptor we should append this RoleDescriptor to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        if ($this->isSigned() === true && $this->signer === null) {
            // We already have a signed document and no signer was set to re-sign it
            if ($parent === null) {
                return $this->xml;
            }

            $node = $parent->ownerDocument?->importNode($this->getXML(), true);
            $parent->appendChild($node);
            return $parent;
        }

        $e = (new Chunk($this->xml))->toXML($parent);

        if ($this->signer !== null) {
            $signedXML = $this->doSign($e);
            $signedXML->insertBefore($this->signature?->toXML($signedXML), $signedXML->firstChild);
            return $signedXML;
        }

        return $e;
    }
}
