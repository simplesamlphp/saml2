<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XMLSecurity\Backend\EncryptionBackend;
use SimpleSAML\XMLSecurity\Constants as C;
use SimpleSAML\XMLSecurity\XML\EncryptableElementInterface;
use SimpleSAML\XMLSecurity\XML\EncryptableElementTrait;

/**
 * Class representing the saml:NameID element.
 *
 * @package simplesamlphp/saml2
 */
final class NameID extends NameIDType implements EncryptableElementInterface
{
    use EncryptableElementTrait;

    /**
     * Initialize a saml:NameID
     *
     * @param string $value
     * @param string|null $NameQualifier
     * @param string|null $SPNameQualifier
     * @param string|null $Format
     * @param string|null $SPProvidedID
     */
    public function __construct(
        string $value,
        ?string $NameQualifier = null,
        ?string $SPNameQualifier = null,
        ?string $Format = null,
        ?string $SPProvidedID = null,
    ) {
        parent::__construct($value, $NameQualifier, $SPNameQualifier, $Format, $SPProvidedID);

        $this->dataType = C::XMLENC_ELEMENT;
    }


    /**
     * Convert XML into an NameID
     *
     * @param \DOMElement $xml The XML element we should load
     * @return static
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'NameID', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, NameID::NS, InvalidDOMElementException::class);

        $NameQualifier = self::getOptionalAttribute($xml, 'NameQualifier', null);
        $SPNameQualifier = self::getOptionalAttribute($xml, 'SPNameQualifier', null);
        $Format = self::getOptionalAttribute($xml, 'Format', null);
        $SPProvidedID = self::getOptionalAttribute($xml, 'SPProvidedID', null);

        return new static($xml->textContent, $NameQualifier, $SPNameQualifier, $Format, $SPProvidedID);
    }


    public function getBlacklistedAlgorithms(): ?array
    {
        $container = ContainerSingleton::getInstance();
        return $container->getBlacklistedEncryptionAlgorithms();
    }


    public function getEncryptionBackend(): ?EncryptionBackend
    {
        // return the encryption backend you want to use,
        // or null if you are fine with the default
        return null;
    }
}
