<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\shibmd;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\XML\Constants as C;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\ExtendableAttributesTrait;
use SimpleSAML\XMLSecurity\XML\ds\KeyInfo;

use function strval;

/**
 * Class which represents the KeyAuthority element found in Shibboleth metadata.
 *
 * @link https://wiki.shibboleth.net/confluence/display/SC/ShibMetaExt+V1.0
 * @package simplesamlphp/saml2
 */
final class KeyAuthority extends AbstractShibmdElement
{
    use ExtendableAttributesTrait;

    /** The namespace-attribute for the xs:anyAttribute element */
    public const XS_ANY_ATTR_NAMESPACE = C::XS_ANY_NS_OTHER;


    /**
     * Create a KeyAuthority.
     *
     * @param \SimpleSAML\XMLSecurity\XML\ds\KeyInfo[] $keys
     * @param int|null $verifyDepth
     * @param list<\SimpleSAML\XML\Attribute> $namespacedAttributes
     */
    public function __construct(
        protected array $keys,
        protected ?int $VerifyDepth = null,
        array $namespacedAttributes = [],
    ) {
        Assert::maxCount($keys, C::UNBOUNDED_LIMIT);
        Assert::nullOrRange($VerifyDepth, 0, 255);

        $this->setAttributesNS($namespacedAttributes);
    }


    /**
     * Collect the value of the VerifyDepth-property
     *
     * @return int|null
     */
    public function getVerifyDepth(): ?int
    {
        return $this->VerifyDepth;
    }


    /**
     * Collect the value of the keys-property
     *
     * @return \SimpleSAML\XMLSecurity\XML\ds\KeyInfo[]
     */
    public function getKeys(): array
    {
        return $this->keys;
    }


    /**
     * Convert XML into a KeyAuthority
     *
     * @param \DOMElement $xml The XML element we should load
     * @return static
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'KeyAuthority', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, KeyAuthority::NS, InvalidDOMElementException::class);

        $verifyDepth = self::getOptionalIntegerAttribute($xml, 'VerifyDepth', 1);
        Assert::natural($verifyDepth);

        $keys = KeyInfo::getChildrenOfClass($xml);
        Assert::minCount($keys, 1);

        return new static($keys, $verifyDepth, self::getAttributesNSFromXML($xml));
    }


    /**
     * Convert this Scope to XML.
     *
     * @param \DOMElement|null $parent The element we should append this Scope to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        /** @psalm-var \DOMDocument $e->ownerDocument */
        $e = $this->instantiateParentElement($parent);

        foreach ($this->getAttributesNS() as $attr) {
            $attr->toXML($e);
        }

        if ($this->getVerifyDepth() !== null) {
            $e->setAttribute('VerifyDepth', strval($this->getVerifyDepth()));
        }

        foreach ($this->getKeys() as $key) {
            $key->toXML($e);
        }

        return $e;
    }
}
