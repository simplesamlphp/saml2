<?php

namespace SAML2\XML;

/**
 * SAML EncryptedElementType data type.
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
abstract class EncryptedElementType extends AbstractXMLElement implements EncryptedElementInterface
{
    /**
     * The encrypted element.
     *
     * @var \DOMElement[]
     */
    protected $encryptedData;


    /**
     * The keys to be used for decryption.
     *
     * @var \DOMElement[]
     */
    protected $encryptionKey = [];
}
