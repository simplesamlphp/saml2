<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Compat\AbstractContainer;
use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\saml\AbstractEncryptedElement;
use SimpleSAML\SAML2\XML\saml\Attribute;
use SimpleSAML\SAML2\XML\saml\Audience;
use SimpleSAML\SAML2\XML\saml\NameID;
use SimpleSAML\SAML2\XML\saml\UnknownID;
use SimpleSAML\SAML2\XML\samlp\NewEncryptedID;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\Test\SAML2\CustomBaseID;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XMLSchema\Type\AnyURIValue;
use SimpleSAML\XMLSchema\Type\Base64BinaryValue;
use SimpleSAML\XMLSchema\Type\IDValue;
use SimpleSAML\XMLSchema\Type\StringValue;
use SimpleSAML\XMLSecurity\Alg\KeyTransport\KeyTransportAlgorithmFactory;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;
use SimpleSAML\XMLSecurity\XML\ds\KeyInfo;
use SimpleSAML\XMLSecurity\XML\xenc\CarriedKeyName;
use SimpleSAML\XMLSecurity\XML\xenc\CipherData;
use SimpleSAML\XMLSecurity\XML\xenc\CipherValue;
use SimpleSAML\XMLSecurity\XML\xenc\DataReference;
use SimpleSAML\XMLSecurity\XML\xenc\EncryptedData;
use SimpleSAML\XMLSecurity\XML\xenc\EncryptedKey;
use SimpleSAML\XMLSecurity\XML\xenc\EncryptionMethod;
use SimpleSAML\XMLSecurity\XML\xenc\ReferenceList;

use function dirname;
use function strval;

/**
 * Class NewEncryptedIDTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('saml')]
#[CoversClass(NewEncryptedID::class)]
#[CoversClass(AbstractEncryptedElement::class)]
final class NewEncryptedIDTest extends TestCase
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

        self::$testedClass = NewEncryptedID::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/samlp_NewEncryptedID.xml',
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
        $eid = new NewEncryptedID($ed, [$ek]);

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
        $eid = new NewEncryptedID($ed, [$ek]);
        $eidElement = $eid->toXML();

        // Test for an NewEncryptedID
        $xpCache = XPath::getXPath($eidElement);
        $eidElements = XPath::xpQuery($eidElement, './xenc:EncryptedData', $xpCache);
        $this->assertCount(1, $eidElements);

        // Test ordering of NewEncryptedID contents
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

        $encid = new NewEncryptedID($nameid->encrypt($encryptor));
        /** @psalm-suppress ArgumentTypeCoercion */
        $doc = DOMDocumentFactory::fromString(strval($encid));

        $encid = NewEncryptedID::fromXML($doc->documentElement);
        /** @psalm-suppress PossiblyNullArgument */
        $decryptor = (new KeyTransportAlgorithmFactory())->getAlgorithm(
            $encid->getEncryptedKeys()[0]->getEncryptionMethod()?->getAlgorithm()->getValue(),
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

        $encid = new NewEncryptedID($customId->encrypt($encryptor));
        /** @psalm-suppress ArgumentTypeCoercion */
        $doc = DOMDocumentFactory::fromString(strval($encid));

        $encid = NewEncryptedID::fromXML($doc->documentElement);
        /** @psalm-suppress PossiblyNullArgument */
        $decryptor = (new KeyTransportAlgorithmFactory())->getAlgorithm(
            $encid->getEncryptedKeys()[0]->getEncryptionMethod()?->getAlgorithm()->getValue(),
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

        $encid = new NewEncryptedID($unknownId->encrypt($encryptor));
        /** @psalm-suppress ArgumentTypeCoercion */
        $doc = DOMDocumentFactory::fromString(strval($encid));

        $encid = NewEncryptedID::fromXML($doc->documentElement);
        /** @psalm-suppress PossiblyNullArgument */
        $decryptor = (new KeyTransportAlgorithmFactory())->getAlgorithm(
            $encid->getEncryptedKeys()[0]->getEncryptionMethod()?->getAlgorithm()->getValue(),
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
        $encid = new NewEncryptedID($attr->encrypt($encryptor));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown or unsupported encrypted identifier.');
        $encid->decrypt($decryptor);
    }
}
