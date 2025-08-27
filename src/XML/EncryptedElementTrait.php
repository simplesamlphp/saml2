<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\XML\AbstractElement;
use SimpleSAML\XMLSchema\Exception\{InvalidDOMElementException, TooManyElementsException};
use SimpleSAML\XMLSecurity\Backend\EncryptionBackend;
use SimpleSAML\XMLSecurity\Constants as C;
use SimpleSAML\XMLSecurity\XML\EncryptedElementTrait as ParentEncryptedElementTrait;
use SimpleSAML\XMLSecurity\XML\xenc\EncryptedData;
use SimpleSAML\XMLSecurity\XML\xenc\EncryptedKey;

/**
 * Trait aggregating functionality for elements that are encrypted.
 *
 * @package simplesamlphp/saml2
 */
trait EncryptedElementTrait
{
    use ParentEncryptedElementTrait;


    /**
     * Constructor for encrypted elements.
     *
     * @param \SimpleSAML\XMLSecurity\XML\xenc\EncryptedData $encryptedData The EncryptedData object.
     * @param \SimpleSAML\XMLSecurity\XML\xenc\EncryptedKey[] $decryptionKeys The EncryptedKey objects.
     */
    public function __construct(
        protected EncryptedData $encryptedData,
        protected array $decryptionKeys = [],
    ) {
        Assert::allIsInstanceOf($decryptionKeys, EncryptedKey::class, ProtocolViolationException::class);

        /**
         * 6.2: The <EncryptedData> element's Type attribute SHOULD be used and, if it is
         * present, MUST have the value http://www.w3.org/2001/04/xmlenc#Element.
         */
        Assert::nullOrSame($encryptedData->getType()->getValue(), C::XMLENC_ELEMENT);

        $keyInfo = $this->encryptedData->getKeyInfo();
        if ($keyInfo === null) {
            return;
        }

        foreach ($keyInfo->getInfo() as $info) {
            if ($info instanceof EncryptedKey) {
                $this->encryptedKey = [$info];
                break;
            }
        }
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


    public function getDecryptionKeys(): array
    {
        return $this->decryptionKeys;
    }


    /**
     * @inheritDoc
     *
     * @throws \SimpleSAML\XMLSchema\Exception\InvalidDOMElementException
     *   If the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same(
            $xml->localName,
            AbstractElement::getClassName(static::class),
            InvalidDOMElementException::class,
        );
        Assert::same($xml->namespaceURI, static::NS, InvalidDOMElementException::class);

        $ed = EncryptedData::getChildrenOfClass($xml);
        Assert::count(
            $ed,
            1,
            sprintf(
                'No more or less than one EncryptedData element allowed in %s.',
                AbstractElement::getClassName(static::class),
            ),
            TooManyElementsException::class,
        );

        $ek = EncryptedKey::getChildrenOfClass($xml);
        return new static($ed[0], $ek);
    }


    /**
     * @inheritDoc
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);
        $this->encryptedData->toXML($e);
        foreach ($this->getDecryptionKeys() as $key) {
            $key->toXML($e);
        }
        return $e;
    }
}
