<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\shibmd;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\ExtendableAttributesTrait;
use SimpleSAML\XMLSecurity\XML\ds\KeyInfo;

/**
 * Class which represents the KeyAuthority element found in Shibboleth metadata.
 *
 * @link https://wiki.shibboleth.net/confluence/display/SC/ShibMetaExt+V1.0
 * @package simplesamlphp/saml2
 */
final class KeyAuthority extends AbstractShibmdElement
{
    use ExtendableAttributesTrait;

    /**
     * @var \SimpleSAML\XMLSecurity\XML\ds\KeyInfo[]
     */
    protected array $keys;

    /**
     * @var int|null
     */
    protected ?int $VerifyDepth;


    /**
     * Create a KeyAuthority.
     *
     * @param \SimpleSAML\XMLSecurity\XML\ds\KeyInfo[] $keys
     * @param int|null $verifyDepth
     * @param \DOMAttr[] $namespacedAttributes
     */
    public function __construct(array $keys, ?int $verifyDepth = null, array $namespacedAttributes = [])
    {
        $this->setKeys($keys);
        $this->setVerifyDepth($verifyDepth);
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
     * Set the value of the VerifyDepth-property
     *
     * @param int|null $verifyDepth
     */
    private function setVerifyDepth(?int $verifyDepth): void
    {
        $this->VerifyDepth = $verifyDepth;
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
     * Set the value of the keys-property
     *
     * @param \SimpleSAML\XMLSecurity\XML\ds\KeyInfo[] $keys
     */
    private function setKeys(array $keys): void
    {
        $this->keys = $keys;
    }


    /**
     * Convert XML into a KeyAuthority
     *
     * @param \DOMElement $xml The XML element we should load
     * @return self
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'KeyAuthority', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, KeyAuthority::NS, InvalidDOMElementException::class);

        $verifyDepth = self::getIntegerAttribute($xml, 'VerifyDepth', null);
        Assert::natural($verifyDepth);

        $keys = KeyInfo::getChildrenOfClass($xml);
        Assert::minCount($keys, 1);

        return new self($keys, $verifyDepth, self::getAttributesNSFromXML($xml));
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
            $e->setAttributeNS($attr['namespaceURI'], $attr['qualifiedName'], $attr['value']);
        }

        if ($this->VerifyDepth !== null) {
            $e->setAttribute('VerifyDepth', strval($this->VerifyDepth));
        }

        foreach ($this->keys as $key) {
            $key->toXML($e);
        }

        return $e;
    }
}
