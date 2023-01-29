<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Configuration;
use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\SAML2\Compat\MockContainer;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\saml\Attribute;
use SimpleSAML\SAML2\XML\saml\AbstractBaseID;
use SimpleSAML\SAML2\XML\saml\Audience;
use SimpleSAML\SAML2\XML\saml\EncryptedID;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\saml\NameID;
use SimpleSAML\SAML2\XML\saml\UnknownID;
use SimpleSAML\Test\SAML2\CustomBaseID;
use SimpleSAML\Test\XML\SchemaValidationTestTrait;
use SimpleSAML\Test\XML\SerializableElementTestTrait;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
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
 * Class EncryptedIDTest
 *
 * @covers \SimpleSAML\SAML2\XML\saml\EncryptedID
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractSamlElement
 * @package simplesamlphp/saml2
 */
final class EncryptedIDTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;

    /**
     */
    public function setup(): void
    {
        $this->schema = dirname(__FILE__, 5) . '/schemas/saml-schema-assertion-2.0.xsd';

        $this->testedClass = EncryptedID::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/saml_EncryptedID.xml',
        );

        $container = new MockContainer();
        $container->setBlacklistedAlgorithms(null);
        $container->registerExtensionHandler(CustomBaseID::class);
        ContainerSingleton::setContainer($container);
    }


    // marshalling


    /**
     */
    public function testMarshalling(): void
    {
        $ed = new EncryptedData(
            cipherData: new CipherData(
                new CipherValue('720FAxwOXcv8ast9YvQutUoue+YA2FgLLNaD/FZrWiNexTkPyZ8CWrcf2zZj2zrOwTjQ9KJvzvCuzq4fM51sU1boOakLpz05NonDdMgeWW/eWcOJJfOZs0tYvYc5qZ/R+BzRnJsGG6w2ZmipEi88X/8uA85c'),
            ),
            type: C::XMLENC_ELEMENT,
            encryptionMethod: new EncryptionMethod('http://www.w3.org/2009xmlenc11#aes256-gcm'),
            keyInfo: new KeyInfo([
                new EncryptedKey(
                    cipherData: new CipherData(
                        new CipherValue('he5ZBjtfp/1/Y3PgE/CWspDPADig9vuZ7yZyYXDQ1wA/HBTPCldtL/p6UT5RCAFYUwN6kp3jnHkhK1yMjrI1SMw0n5NEc2wO9N5inQIeQOZ8XD9yD9M5fHvWz2ByNMGlB35RWMnBRHzDi1PRV7Irwcs9WoiODh3i6j2vYXP7cAo='),
                    ),
                    encryptionMethod: new EncryptionMethod('http://www.w3.org/2009/xmlenc11#rsa-oaep'),
                )
            ]),
        );
        $ek = new EncryptedKey(
            cipherData: new CipherData(
                new CipherValue('he5ZBjtfp/1/Y3PgE/CWspDPADig9vuZ7yZyYXDQ1wA/HBTPCldtL/p6UT5RCAFYUwN6kp3jnHkhK1yMjrI1SMw0n5NEc2wO9N5inQIeQOZ8XD9yD9M5fHvWz2ByNMGlB35RWMnBRHzDi1PRV7Irwcs9WoiODh3i6j2vYXP7cAo='),
            ),
            id: 'Encrypted_KEY_ID',
            recipient: 'some_ENTITY_ID',
            carriedKeyName: new CarriedKeyName('Name of the key'),
            encryptionMethod: new EncryptionMethod('http://www.w3.org/2009/xmlenc11#rsa-oaep'),
            referenceList: new ReferenceList(
                [new DataReference('#Encrypted_DATA_ID')],
            ),
        );
        $eid = new EncryptedID($ed);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($eid),
        );
    }


    /**
     */
    public function testMarshallingElementOrdering(): void
    {
        $ed = new EncryptedData(
            cipherData: new CipherData(
                new CipherValue('iFz/8KASJCLCAHqaAKhZXWOG/TPZlgTxcQ25lTGxdSdEsGYz7cg5lfZAbcN3UITCP9MkJsyjMlRsQouIqBkoPCGZz8NXibDkQ8OUeE7JdkFgKvgUMXawp+uDL4gHR8L7l6SPAmWZU3Hx/Wg9pTJBOpTjwoS0'),
            ),
            encryptionMethod: new EncryptionMethod('http://www.w3.org/2001/04/xmlenc#aes128-cbc'),
            keyInfo: new KeyInfo([
                new EncryptedKey(
                    cipherData: new CipherData(
                        new CipherValue('GMhpk09X+quNC/SsnxcDglZU/DCLAu9bMJ5bPcgaBK4s3F1eXciU8hlOYNaskSwP86HmA704NbzSDOHAgN6ckR+iCssxA7XCBjz0hltsgfn5p9Rh8qKtKltiXvxo/xXTcSXXZXNcE0R2KTya0P4DjZvYYgbIls/AH8ZyDV07ntI='),
                    ),
                    encryptionMethod: new EncryptionMethod('http://www.w3.org/2009/xmlenc11#rsa-oaep'),
                ),
            ]),
        );
        $ek = new EncryptedKey(
            cipherData: new CipherData(
                new CipherValue('GMhpk09X+quNC/SsnxcDglZU/DCLAu9bMJ5bPcgaBK4s3F1eXciU8hlOYNaskSwP86HmA704NbzSDOHAgN6ckR+iCssxA7XCBjz0hltsgfn5p9Rh8qKtKltiXvxo/xXTcSXXZXNcE0R2KTya0P4DjZvYYgbIls/AH8ZyDV07ntI='),
            ),
            id: 'Encrypted_KEY_ID',
            recipient: 'some_ENTITY_ID',
            carriedKeyName: new CarriedKeyName('Name of the key'),
            encryptionMethod: new EncryptionMethod('http://www.w3.org/2001/04/xmlenc#rsa-1_5'),
            referenceList: new ReferenceList(
                [new DataReference('#Encrypted_DATA_ID')],
            ),
        );
        $eid = new EncryptedID($ed);
        $eidElement = $eid->toXML();

        // Test for an EncryptedID
        $xpCache = XPath::getXPath($eidElement);
        $eidElements = XPath::xpQuery($eidElement, './xenc:EncryptedData', $xpCache);
        $this->assertCount(1, $eidElements);

        // Note: this cannot be tested as long as we don't include EncryptedKey in the structure
        // (see simplesamlphp/xml-security issue #30)
        //
        // Test ordering of EncryptedID contents
        /** @psalm-var \DOMElement[] $eidElements */
        //$eidElements = XPath::xpQuery($eidElement, './xenc:EncryptedData/following-sibling::*', $xpCache);
        //$this->assertCount(1, $eidElements);
        //$this->assertEquals('xenc:EncryptedKey', $eidElements[0]->tagName);
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
        $nameid = new NameID('value', 'name_qualifier');
        $encid = new EncryptedID($nameid->encrypt($encryptor));
        /** @psalm-suppress ArgumentTypeCoercion */
        $doc = DOMDocumentFactory::fromString(strval($encid));

        $encid = EncryptedID::fromXML($doc->documentElement);
        /** @psalm-suppress PossiblyNullArgument */
        $decryptor = (new KeyTransportAlgorithmFactory())->getAlgorithm(
            $encid->getEncryptedKey()->getEncryptionMethod()?->getAlgorithm(),
            $privKey,
        );
        $id = $encid->decrypt($decryptor);
        /** @psalm-suppress ArgumentTypeCoercion */
        $this->assertEquals(strval($nameid), strval($id));

        // test a custom BaseID that's registered
        $customId = new CustomBaseID(
            [new Audience('urn:some:audience')],
            'TheNameQualifier',
            'TheSPNameQualifier',
        );

        $encid = new EncryptedID($customId->encrypt($encryptor));
        /** @psalm-suppress ArgumentTypeCoercion */
        $doc = DOMDocumentFactory::fromString(strval($encid));

        $encid = EncryptedID::fromXML($doc->documentElement);
        /** @psalm-suppress PossiblyNullArgument */
        $decryptor = (new KeyTransportAlgorithmFactory())->getAlgorithm(
            $encid->getEncryptedKey()->getEncryptionMethod()?->getAlgorithm(),
            $privKey,
        );
        $id = $encid->decrypt($decryptor);
        $this->assertInstanceOf(CustomBaseID::class, $id);
        /** @psalm-suppress ArgumentTypeCoercion */
        $this->assertEquals(strval($customId), strval($id));

        // Remove registration by using a clean container
        $container = new MockContainer();
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
            $encid->getEncryptedKey()->getEncryptionMethod()?->getAlgorithm(),
            $privKey,
        );
        $id = $encid->decrypt($decryptor);
        $this->assertInstanceOf(UnknownID::class, $id);
        /** @psalm-suppress ArgumentTypeCoercion */
        $this->assertEquals(strval($unknownId), strval($id));

        // test with unsupported ID
        $attr = new Attribute('name');
        $encid = new EncryptedID($attr->encrypt($encryptor));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown or unsupported encrypted identifier.');
        $encid->decrypt($decryptor);
    }
}
