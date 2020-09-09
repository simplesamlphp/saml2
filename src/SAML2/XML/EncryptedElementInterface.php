<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML;

use SimpleSAML\XML\AbstractXMLElement;
use SimpleSAML\XMLSecurity\XML\xenc\EncryptedData;
use SimpleSAML\XMLSecurity\XMLSecurityKey;

/**
 * Interface for encrypted elements.
 *
 * @package simplesamlphp/saml2
 */
interface EncryptedElementInterface
{
    /**
     * Constructor for encrypted elements.
     *
     * @param \SimpleSAML\XMLSecurity\XML\xenc\EncryptedData $encryptedData The EncryptedData object.
     * @param \SimpleSAML\XMLSecurity\XML\xenc\EncryptedKey[] $encryptedKeys An array of zero or more EncryptedKey objects.
     */
    public function __construct(EncryptedData $encryptedData, array $encryptedKeys);


    /**
     * @param \SimpleSAML\XMLSecurity\XMLSecurityKey $key The key we should use to decrypt the element.
     * @param string[] $blacklist List of blacklisted encryption algorithms.
     *
     * @return \SimpleSAML\XML\AbstractXMLElement The decrypted element.
     */
    public function decrypt(XMLSecurityKey $key, array $blacklist = []): AbstractXMLElement;


    /**
     * Get the EncryptedData object.
     *
     * @return \SimpleSAML\XMLSecurity\XML\xenc\EncryptedData
     */
    public function getEncryptedData(): EncryptedData;


    /**
     * Get the array of EncryptedKey objects
     *
     * @return \SimpleSAML\XMLSecurity\XML\xenc\EncryptedKey[]
     */
    public function getEncryptedKeys(): array;
}
