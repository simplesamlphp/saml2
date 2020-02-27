<?php

namespace SAML2\XML\saml;

use DOMElement;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SAML2\Utils;
use SAML2\XML\EncryptedElementType;
use Webmozart\Assert\Assert;

/**
 * SAML EncryptedID data type.
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
class EncryptedID extends EncryptedElementType
{
    /**
     * Create an EncryptedID from XML
     *
     * @param \DOMElement $xml
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'EncryptedID');
        Assert::same($xml->namespaceURI, EncryptedID::NS);

        // TODO: Create xenc/EncryptedData class (perhaps in xmlsec)

        /** @var \DOMElement[] $data */
        $data = Utils::xpQuery($xml, './xenc:EncryptedData');
        if (empty($data)) {
            throw new \Exception('Missing encrypted data in <saml:EncryptedID>.');
        } elseif (count($data) > 1) {
            throw new \Exception('More than one encrypted data element in <saml:EncryptedID>.');
        }

        // TODO: We can't always use the first key.. Also, the encrypted value may be an EncryptedAssertion!
        $decryptionKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'private']);
        return self::decryptElement($data[0], $decryptionKey);
    }


    /**
     * Create XML from this class
     *
     * @param \DOMElement|null $parent
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        $this->encryptElement($e, $this->encryptionKey);

        $e->appendChild($this->encryptedData);
        return $e;
    }
}
