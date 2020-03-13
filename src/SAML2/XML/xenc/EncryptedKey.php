<?php

declare(strict_types=1);

namespace SAML2\XML\xenc;

use DOMElement;
use SAML2\Utils;
use SAML2\XML\ds\KeyInfo;
use Webmozart\Assert\Assert;

/**
 * Class representing an encrypted key.
 *
 * @package simplesamlphp/saml2
 */
class EncryptedKey extends EncryptedData
{
    /** @var string */
    protected $carriedKeyName;

    /** @var string */
    protected $recipient;

    /** @var ReferenceList */
    protected $referenceList;


    /**
     * EncryptedKey constructor.
     *
     * @param CipherData $cipherData The CipherData object of this EncryptedData.
     * @param string|null $id The Id attribute of this object. Optional.
     * @param string|null $type The Type attribute of this object. Optional.
     * @param string|null $mimeType The MimeType attribute of this object. Optional.
     * @param string|null $encoding The Encoding attribute of this object. Optional.
     * @param string|null $recipient The Recipient attribute of this object. Optional.
     * @param string|null $carriedKeyName The value of the CarriedKeyName element of this EncryptedData.
     * @param EncryptionMethod|null $encryptionMethod The EncryptionMethod object of this EncryptedData. Optional.
     * @param KeyInfo|null $keyInfo The KeyInfo object of this EncryptedData. Optional.
     * @param ReferenceList|null $referenceList The ReferenceList object of this EncryptedData. Optional.
     */
    public function __construct(
        CipherData $cipherData,
        ?string $id = null,
        ?string $type = null,
        ?string $mimeType = null,
        ?string $encoding = null,
        ?string $recipient = null,
        ?string $carriedKeyName = null,
        ?EncryptionMethod $encryptionMethod = null,
        ?KeyInfo $keyInfo = null,
        ?ReferenceList $referenceList = null
    ) {
        parent::__construct($cipherData, $id, $type, $mimeType, $encoding, $encryptionMethod, $keyInfo);
        $this->setRecipient($recipient);
        $this->setReferenceList($referenceList);
        $this->setCarriedKeyName($carriedKeyName);
    }


    /**
     * Get the value of the CarriedKeyName element.
     *
     * @return string|null
     */
    public function getCarriedKeyName(): ?string
    {
        return $this->carriedKeyName;
    }


    /**
     * @param string|null $carriedKeyName
     */
    protected function setCarriedKeyName(?string $carriedKeyName): void
    {
        $this->carriedKeyName = $carriedKeyName;
    }


    /**
     * Get the value of the Recipient attribute.
     *
     * @return string|null
     */
    public function getRecipient(): ?string
    {
        return $this->recipient;
    }


    /**
     * @param string|null $recipient
     */
    protected function setRecipient(?string $recipient): void
    {
        $this->recipient = $recipient;
    }


    /**
     * Get the ReferenceList object.
     *
     * @return ReferenceList|null
     */
    public function getReferenceList(): ?ReferenceList
    {
        return $this->referenceList;
    }


    /**
     * @param ReferenceList|null $referenceList
     */
    protected function setReferenceList(?ReferenceList $referenceList): void
    {
        $this->referenceList = $referenceList;
    }


    /**
     * @inheritDoc
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'EncryptedKey');
        Assert::same($xml->namespaceURI, EncryptedKey::NS);

        $cipherData = CipherData::getChildrenOfClass($xml);
        Assert::count($cipherData, 1, 'No or more than one CipherData element found in <xenc:EncryptedKey>.');

        $encryptionMethod = EncryptionMethod::getChildrenOfClass($xml);
        Assert::maxCount(
            $encryptionMethod,
            1,
            'No more than one EncryptionMethod element allowed in <xenc:EncryptedKey>.'
        );

        $keyInfo = KeyInfo::getChildrenOfClass($xml);
        Assert::maxCount($keyInfo, 1, 'No more than one KeyInfo element allowed in <xenc:EncryptedKey>.');

        $referenceLists = ReferenceList::getChildrenOfClass($xml);
        Assert::maxCount($keyInfo, 1, 'Only one ReferenceList element allowed in <xenc:EncryptedKey>.');

        $carriedKeyNames = Utils::xpQuery($xml, './xenc:CarriedKeyName');
        Assert::maxCount($carriedKeyNames, 1, 'Only one CarriedKeyName element allowed in <xenc:EncryptedKey>.');

        return new self(
            $cipherData[0],
            self::getAttribute($xml, 'Id', null),
            self::getAttribute($xml, 'Type', null),
            self::getAttribute($xml, 'MimeType', null),
            self::getAttribute($xml, 'Type', null),
            self::getAttribute($xml, 'Recipient', null),
            count($carriedKeyNames) === 1 ? $carriedKeyNames[0]->textContent : null,
            count($encryptionMethod) === 1 ? $encryptionMethod[0] : null,
            count($keyInfo) === 1 ? $keyInfo[0] : null,
            count($referenceLists) === 1 ? $referenceLists[0] : null
        );
    }


    /**
     * @inheritDoc
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = parent::toXML($parent);

        if ($this->referenceList !== null) {
            $this->referenceList->toXML($e);
        }

        if ($this->carriedKeyName !== null) {
            $ckn = $e->ownerDocument->createElementNS(self::NS, self::NS_PREFIX . ':CarriedKeyName');
            $ckn->textContent = $this->carriedKeyName;
            $e->appendChild($ckn);
        }

        if ($this->recipient !== null) {
            $e->setAttribute('Recipient', $this->recipient);
        }

        return $e;
    }
}
