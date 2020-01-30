<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use DOMElement;
use SAML2\Constants;
use SAML2\Utils;
use SAML2\XML\Chunk;
use SAML2\XML\ds\KeyInfo;
use Webmozart\Assert\Assert;

/**
 * Class representing a KeyDescriptor element.
 *
 * @package SimpleSAMLphp
 */
class KeyDescriptor extends AbstractMdElement
{
    /**
     * What this key can be used for.
     *
     * 'encryption', 'signing' or null.
     *
     * @var string|null
     */
    private $use = null;

    /**
     * The KeyInfo for this key.
     *
     * @var \SAML2\XML\ds\KeyInfo
     */
    private $KeyInfo;

    /**
     * Supported EncryptionMethods.
     *
     * Array of \SAML2\XML\Chunk objects.
     *
     * @var \SAML2\XML\Chunk[]|null
     */
    private $EncryptionMethod = null;


    /**
     * Initialize an KeyDescriptor.
     *
     * @param \SAML2\XML\ds\Keyinfo $keyInfo
     * @param string|null $use Key usage
     * @param \SAML2\XML\Chunk[]|null $encryptionMethod
     * @throws \Exception
     */
    public function __construct(KeyInfo $keyInfo, string $use = null, array $encryptionMethod = null)
    {
        $this->setKeyInfo($keyInfo);
        $this->setUse($use);
        $this->setEncryptionMethod($encryptionMethod);
    }


    /**
     * Collect the value of the use property.
     *
     * @return string|null
     */
    public function getUse(): ?string
    {
        return $this->use;
    }


    /**
     * Set the value of the use property.
     *
     * @param string|null $use
     * @return void
     */
    public function setUse(string $use = null): void
    {
        $this->use = $use;
    }


    /**
     * Collect the value of the KeyInfo property.
     *
     * @return \SAML2\XML\ds\KeyInfo
     *
     * @throws \InvalidArgumentException if assertions are false
     */
    public function getKeyInfo(): KeyInfo
    {
        return $this->KeyInfo;
    }


    /**
     * Set the value of the KeyInfo property.
     *
     * @param \SAML2\XML\ds\KeyInfo $keyInfo
     * @return void
     */
    public function setKeyInfo(KeyInfo $keyInfo): void
    {
        $this->KeyInfo = $keyInfo;
    }


    /**
     * Collect the value of the EncryptionMethod property.
     *
     * @return \SAML2\XML\Chunk[]|null
     */
    public function getEncryptionMethod(): ?array
    {
        return $this->EncryptionMethod;
    }


    /**
     * Set the value of the EncryptionMethod property.
     *
     * @param \SAML2\XML\Chunk[]|null $encryptionMethod
     * @return void
     */
    public function setEncryptionMethod(array $encryptionMethod = null): void
    {
        $this->EncryptionMethod = $encryptionMethod;
    }


    /**
     * Add the value to the EncryptionMethod property.
     *
     * @param \SAML2\XML\Chunk $encryptionMethod
     * @return void
     */
    public function addEncryptionMethod(Chunk $encryptionMethod): void
    {
        $this->EncryptionMethod[] = $encryptionMethod;
    }


    /**
     * Convert XML into a KeyDescriptor
     *
     * @param \DOMElement $xml The XML element we should load
     * @return self
     */
    public static function fromXML(DOMElement $xml): object
    {
        $use = $xml->hasAttribute('use') ? $xml->getAttribute('use') : null;

        $KeyInfo = Utils::xpQuery($xml, './ds:KeyInfo');
        if (count($KeyInfo) > 1) {
            throw new \Exception('More than one ds:KeyInfo in the KeyDescriptor.');
        } elseif (empty($KeyInfo)) {
            throw new \Exception('No ds:KeyInfo in the KeyDescriptor.');
        }

        /** @var \DOMElement $KeyInfo[0] */
        $KeyInfo = KeyInfo::fromXML($KeyInfo[0]);

        $EncryptionMethod = [];
        /** @var \DOMElement $em */
        foreach (Utils::xpQuery($xml, './saml_metadata:EncryptionMethod') as $em) {
            $EncryptionMethod[] = new Chunk($em);
        }

        return new self($KeyInfo, $use, $EncryptionMethod);
    }


    /**
     * Convert this KeyDescriptor to XML.
     *
     * @param \DOMElement|null $parent The element we should append this KeyDescriptor to.
     * @return \DOMElement
     *
     * @throws \InvalidArgumentException if assertions are false
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        // @TODO: Take care of a null parameter

        Assert::notEmpty($this->KeyInfo, 'Cannot convert KeyDescriptor to XML without KeyInfo set.');

        $doc = $parent->ownerDocument;

        $e = $doc->createElementNS(Constants::NS_MD, 'md:KeyDescriptor');
        $parent->appendChild($e);

        $this->KeyInfo->toXML($e);

        if ($this->use !== null) {
            $e->setAttribute('use', $this->use);
        }

        if (!empty($this->EncryptionMethod)) {
            foreach ($this->EncryptionMethod as $em) {
                $em->toXML($e);
            }
        }

        return $e;
    }
}
