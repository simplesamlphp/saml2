<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\{CoversClass, Group};
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Compat\{AbstractContainer, ContainerSingleton};
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Type\{SAMLAnyURIValue, SAMLStringValue};
use SimpleSAML\SAML2\XML\saml\{AbstractSamlElement, Attribute, AttributeValue, EncryptedAttribute};
use SimpleSAML\XML\Attribute as XMLAttribute;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XML\TestUtils\{SchemaValidationTestTrait, SerializableElementTestTrait};
use SimpleSAML\XML\Type\StringValue;
use SimpleSAML\XMLSecurity\Alg\KeyTransport\KeyTransportAlgorithmFactory;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\saml\AttributeTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('saml')]
#[CoversClass(Attribute::class)]
#[CoversClass(AttributeValue::class)]
#[CoversClass(AbstractSamlElement::class)]
final class AttributeTest extends TestCase
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

        self::$testedClass = Attribute::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/saml_Attribute.xml',
        );

        $container = clone self::$containerBackup;
        $container->setBlacklistedAlgorithms(null);
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
     * Test creating an Attribute from scratch.
     */
    public function testMarshalling(): void
    {
        $attr1 = new XMLAttribute('urn:test:something', 'test', 'attr1', StringValue::fromString('testval1'));
        $attr2 = new XMLAttribute('urn:test:something', 'test', 'attr2', StringValue::fromString('testval2'));

        $attribute = new Attribute(
            SAMLStringValue::fromString('TheName'),
            SAMLAnyURIValue::fromString(C::NAMEFORMAT_BASIC),
            SAMLStringValue::fromString('TheFriendlyName'),
            [
                new AttributeValue('FirstValue'),
                new AttributeValue('SecondValue'),
                new AttributeValue(3),
                new AttributeValue(new DateTimeImmutable('2024-04-04T04:44:44Z')),
                new AttributeValue(null),
            ],
            [$attr1, $attr2],
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($attribute),
        );
    }


    // unmarshalling


    /**
     * Test that creating an Attribute from XML fails if no Name is provided.
     */
    public function testUnmarshallingWithoutName(): void
    {
        $document = clone self::$xmlRepresentation;
        $document->documentElement->removeAttribute('Name');

        $this->expectException(MissingAttributeException::class);

        Attribute::fromXML($document->documentElement);
    }


    /**
     * Test encryption / decryption
     */
    public function testEncryption(): void
    {
        $attribute = Attribute::fromXML(self::$xmlRepresentation->documentElement);

        $encryptor = (new KeyTransportAlgorithmFactory())->getAlgorithm(
            C::KEY_TRANSPORT_OAEP,
            PEMCertificatesMock::getPublicKey(PEMCertificatesMock::PUBLIC_KEY),
        );

        $encattr = new EncryptedAttribute($attribute->encrypt($encryptor));
        $str = strval($encattr);
        $doc = DOMDocumentFactory::fromString($str);
        $encattr = EncryptedAttribute::fromXML($doc->documentElement);

        $decryptor = (new KeyTransportAlgorithmFactory())->getAlgorithm(
            $encattr->getEncryptedKey()->getEncryptionMethod()?->getAlgorithm()->getValue(),
            PEMCertificatesMock::getPrivateKey(PEMCertificatesMock::PRIVATE_KEY),
        );

        $attr = $encattr->decrypt($decryptor);
        $this->assertEquals(strval($attribute), strval($attr));
    }
}
