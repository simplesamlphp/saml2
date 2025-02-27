<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\{CoversClass, Group};
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Compat\{AbstractContainer, ContainerSingleton};
use SimpleSAML\SAML2\Type\{SAMLAnyURIValue, SAMLStringValue};
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\saml\{
    AbstractSamlElement,
    Attribute,
    Audience,
    EncryptedID,
    NameID,
    UnknownID,
};
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\Test\SAML2\CustomBaseID;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\{SchemaValidationTestTrait, SerializableElementTestTrait};
use SimpleSAML\XML\Type\{AnyURIValue, Base64BinaryValue, IDValue, StringValue};
use SimpleSAML\XMLSecurity\Alg\KeyTransport\KeyTransportAlgorithmFactory;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;
use SimpleSAML\XMLSecurity\XML\ds\KeyInfo;
use SimpleSAML\XMLSecurity\XML\xenc\{
    CarriedKeyName,
    CipherData,
    CipherValue,
    DataReference,
    EncryptedData,
    EncryptedKey,
    EncryptionMethod,
    ReferenceList,
};

use function dirname;
use function strval;

/**
 * Class EncryptedIDTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('saml')]
#[CoversClass(EncryptedID::class)]
#[CoversClass(AbstractSamlElement::class)]
final class EncryptedIDTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;

    /** @var \SimpleSAML\SAML2\Compat\AbstractContainer */
    private static AbstractContainer $containerBackup;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$containerBackup = ContainerSingleton::getInstance();

        self::$testedClass = EncryptedID::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/saml_EncryptedID.xml',
        );

        $container = clone self::$containerBackup;
        $container->setBlacklistedAlgorithms(null);
        $container->registerExtensionHandler(CustomBaseID::class);
        ContainerSingleton::setContainer($container);
    }


    /**
     */
    public static function tearDownAfterClass(): void
    {
        ContainerSingleton::setContainer(self::$containerBackup);
    }


    // marshalling


    /**
     */
    public function testMarshalling(): void
    {
        $ed = new EncryptedData(
            cipherData: new CipherData(
                new CipherValue(
                    Base64BinaryValue::fromString('720FAxwOXcv8ast9YvQutUoue+YA2FgLLNaD/FZrWiNexTkPyZ8CWrcf2zZj2zrOwTjQ9KJvzvCuzq4fM51sU1boOakLpz05NonDdMgeWW/eWcOJJfOZs0tYvYc5qZ/R+BzRnJsGG6w2ZmipEi88X/8uA85c'),
                ),
            ),
            type: AnyURIValue::fromString(C::XMLENC_ELEMENT),
            encryptionMethod: new EncryptionMethod(
                AnyURIValue::fromString('http://www.w3.org/2009xmlenc11#aes256-gcm'),
            ),
            keyInfo: new KeyInfo([
                new EncryptedKey(
                    cipherData: new CipherData(
                        new CipherValue(
                            Base64BinaryValue::fromString('he5ZBjtfp/1/Y3PgE/CWspDPADig9vuZ7yZyYXDQ1wA/HBTPCldtL/p6UT5RCAFYUwN6kp3jnHkhK1yMjrI1SMw0n5NEc2wO9N5inQIeQOZ8XD9yD9M5fHvWz2ByNMGlB35RWMnBRHzDi1PRV7Irwcs9WoiODh3i6j2vYXP7cAo='),
                        ),
                    ),
                    encryptionMethod: new EncryptionMethod(
                        AnyURIValue::fromString('http://www.w3.org/2009/xmlenc11#rsa-oaep'),
                    ),
                ),
            ]),
        );
        $ek = new EncryptedKey(
            cipherData: new CipherData(
                new CipherValue(
                    Base64BinaryValue::fromString('he5ZBjtfp/1/Y3PgE/CWspDPADig9vuZ7yZyYXDQ1wA/HBTPCldtL/p6UT5RCAFYUwN6kp3jnHkhK1yMjrI1SMw0n5NEc2wO9N5inQIeQOZ8XD9yD9M5fHvWz2ByNMGlB35RWMnBRHzDi1PRV7Irwcs9WoiODh3i6j2vYXP7cAo='),
                ),
            ),
            id: IDValue::fromString('Encrypted_KEY_ID'),
            recipient: StringValue::fromString(C::ENTITY_SP),
            carriedKeyName: new CarriedKeyName(
                StringValue::fromString('Name of the key'),
            ),
            encryptionMethod: new EncryptionMethod(
                AnyURIValue::fromString('http://www.w3.org/2009/xmlenc11#rsa-oaep'),
            ),
            referenceList: new ReferenceList([
                new DataReference(
                    AnyURIValue::fromString('#Encrypted_DATA_ID'),
                ),
            ]),
        );
        $eid = new EncryptedID($ed, [$ek]);

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($eid),
        );
    }


    /**
     */
    public function testMarshallingElementOrdering(): void
    {
        $ed = new EncryptedData(
            cipherData: new CipherData(
                new CipherValue(
                    Base64BinaryValue::fromString('iFz/8KASJCLCAHqaAKhZXWOG/TPZlgTxcQ25lTGxdSdEsGYz7cg5lfZAbcN3UITCP9MkJsyjMlRsQouIqBkoPCGZz8NXibDkQ8OUeE7JdkFgKvgUMXawp+uDL4gHR8L7l6SPAmWZU3Hx/Wg9pTJBOpTjwoS0'),
                ),
            ),
            encryptionMethod: new EncryptionMethod(
                AnyURIValue::fromString('http://www.w3.org/2001/04/xmlenc#aes128-cbc'),
            ),
            type: AnyURIValue::fromString(C::XMLENC_ELEMENT),
            keyInfo: new KeyInfo([
                new EncryptedKey(
                    cipherData: new CipherData(
                        new CipherValue(
                            Base64BinaryValue::fromString('GMhpk09X+quNC/SsnxcDglZU/DCLAu9bMJ5bPcgaBK4s3F1eXciU8hlOYNaskSwP86HmA704NbzSDOHAgN6ckR+iCssxA7XCBjz0hltsgfn5p9Rh8qKtKltiXvxo/xXTcSXXZXNcE0R2KTya0P4DjZvYYgbIls/AH8ZyDV07ntI='),
                        ),
                    ),
                    encryptionMethod: new EncryptionMethod(
                        AnyURIValue::fromString('http://www.w3.org/2009/xmlenc11#rsa-oaep'),
                    ),
                ),
            ]),
        );
        $ek = new EncryptedKey(
            cipherData: new CipherData(
                new CipherValue(
                    Base64BinaryValue::fromString('GMhpk09X+quNC/SsnxcDglZU/DCLAu9bMJ5bPcgaBK4s3F1eXciU8hlOYNaskSwP86HmA704NbzSDOHAgN6ckR+iCssxA7XCBjz0hltsgfn5p9Rh8qKtKltiXvxo/xXTcSXXZXNcE0R2KTya0P4DjZvYYgbIls/AH8ZyDV07ntI='),
                ),
            ),
            id: IDValue::fromString('Encrypted_KEY_ID'),
            recipient: StringValue::fromString(C::ENTITY_SP),
            carriedKeyName: new CarriedKeyName(
                StringValue::fromString('Name of the key'),
            ),
            encryptionMethod: new EncryptionMethod(
                AnyURIValue::fromString('http://www.w3.org/2001/04/xmlenc#rsa-1_5'),
            ),
            referenceList: new ReferenceList([
                new DataReference(
                    AnyURIValue::fromString('#Encrypted_DATA_ID'),
                ),
            ]),
        );
        $eid = new EncryptedID($ed, [$ek]);
        $eidElement = $eid->toXML();

        // Test for an EncryptedID
        $xpCache = XPath::getXPath($eidElement);
        $eidElements = XPath::xpQuery($eidElement, './xenc:EncryptedData', $xpCache);
        $this->assertCount(1, $eidElements);

        // Test ordering of EncryptedID contents
        /** @psalm-var \DOMElement[] $eidElements */
        $eidElements = XPath::xpQuery($eidElement, './xenc:EncryptedData/following-sibling::*', $xpCache);
        $this->assertCount(1, $eidElements);
        $this->assertEquals('xenc:EncryptedKey', $eidElements[0]->tagName);
    }


    /**
     * Test encryption / decryption
     */
    public function testEncryption(): void
    {
        // Create keys
        $privKey = PEMCertificatesMock::getPrivateKey(PEMCertificatesMock::SELFSIGNED_PRIVATE_KEY);
        $pubKey = PEMCertificatesMock::getPublicKey(PEMCertificatesMock::SELFSIGNED_PUBLIC_KEY);

        // Create encryptor
        $encryptor = (new KeyTransportAlgorithmFactory())->getAlgorithm(
            C::KEY_TRANSPORT_OAEP,
            $pubKey,
        );

        // test with a NameID
        $nameid = new NameID(
            SAMLStringValue::fromString('value'),
            SAMLStringValue::fromString('urn:x-simplesamlphp:namequalifier'),
        );

        $encid = new EncryptedID($nameid->encrypt($encryptor));
        /** @psalm-suppress ArgumentTypeCoercion */
        $doc = DOMDocumentFactory::fromString(strval($encid));

        $encid = EncryptedID::fromXML($doc->documentElement);
        /** @psalm-suppress PossiblyNullArgument */
        $decryptor = (new KeyTransportAlgorithmFactory())->getAlgorithm(
            $encid->getEncryptedKey()->getEncryptionMethod()?->getAlgorithm()->getValue(),
            $privKey,
        );
        $id = $encid->decrypt($decryptor);
        /** @psalm-suppress ArgumentTypeCoercion */
        $this->assertEquals(strval($nameid), strval($id));

        // test a custom BaseID that's registered
        $customId = new CustomBaseID(
            [
                new Audience(
                    SAMLAnyURIValue::fromString('urn:some:audience'),
                ),
            ],
            SAMLStringValue::fromString('urn:x-simplesamlphp:namequalifier'),
            SAMLStringValue::fromString('urn:x-simplesamlphp:spnamequalifier'),
        );

        $encid = new EncryptedID($customId->encrypt($encryptor));
        /** @psalm-suppress ArgumentTypeCoercion */
        $doc = DOMDocumentFactory::fromString(strval($encid));

        $encid = EncryptedID::fromXML($doc->documentElement);
        /** @psalm-suppress PossiblyNullArgument */
        $decryptor = (new KeyTransportAlgorithmFactory())->getAlgorithm(
            $encid->getEncryptedKey()->getEncryptionMethod()?->getAlgorithm()->getValue(),
            $privKey,
        );
        $id = $encid->decrypt($decryptor);
        $this->assertInstanceOf(CustomBaseID::class, $id);
        /** @psalm-suppress ArgumentTypeCoercion */
        $this->assertEquals(strval($customId), strval($id));

        // Remove registration by using a clean container
        $container = clone self::$containerBackup;
        $container->setBlacklistedAlgorithms(null);
        ContainerSingleton::setContainer($container);

        // test a custom BaseID that's unregistered
        $unknownId = $customId;

        $encid = new EncryptedID($unknownId->encrypt($encryptor));
        /** @psalm-suppress ArgumentTypeCoercion */
        $doc = DOMDocumentFactory::fromString(strval($encid));

        $encid = EncryptedID::fromXML($doc->documentElement);
        /** @psalm-suppress PossiblyNullArgument */
        $decryptor = (new KeyTransportAlgorithmFactory())->getAlgorithm(
            $encid->getEncryptedKey()->getEncryptionMethod()?->getAlgorithm()->getValue(),
            $privKey,
        );
        $id = $encid->decrypt($decryptor);
        $this->assertInstanceOf(UnknownID::class, $id);
        /** @psalm-suppress ArgumentTypeCoercion */
        $this->assertEquals(strval($unknownId), strval($id));

        // test with unsupported ID
        $attr = new Attribute(
            SAMLStringValue::fromString('name'),
        );
        $encid = new EncryptedID($attr->encrypt($encryptor));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown or unsupported encrypted identifier.');
        $encid->decrypt($decryptor);
    }
}
