<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Compat\AbstractContainer;
use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\SAML2\XML\saml\AbstractSamlElement;
use SimpleSAML\SAML2\XML\saml\Attribute;
use SimpleSAML\SAML2\XML\saml\AttributeValue;
use SimpleSAML\SAML2\XML\saml\EncryptedAttribute;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XMLSchema\Type\StringValue;
use SimpleSAML\XMLSecurity\Alg\KeyTransport\KeyTransportAlgorithmFactory;
use SimpleSAML\XMLSecurity\Constants as C;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;

use function dirname;

/**
 * Class \SimpleSAML\SAML2\XML\saml\EncryptedAttributeTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('saml')]
#[CoversClass(EncryptedAttribute::class)]
#[CoversClass(AbstractSamlElement::class)]
final class EncryptedAttributeTest extends TestCase
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

        self::$testedClass = EncryptedAttribute::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/saml_EncryptedAttribute.xml',
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
     */
    public function testMarshalling(): void
    {
        $attribute = new Attribute(
            name: SAMLStringValue::fromString('urn:encrypted:attribute'),
            attributeValue: [
                new AttributeValue(StringValue::fromString('very secret data')),
            ],
        );

        $encryptor = (new KeyTransportAlgorithmFactory())->getAlgorithm(
            C::KEY_TRANSPORT_OAEP_MGF1P,
            PEMCertificatesMock::getPublicKey(PEMCertificatesMock::PUBLIC_KEY),
        );

        $encryptedAttribute = new EncryptedAttribute($attribute->encrypt($encryptor));
        $encryptedData = $encryptedAttribute->getEncryptedData();

        $this->assertEquals(C::XMLENC_ELEMENT, $encryptedData->getType());
    }


    // unmarshalling


    /**
     */
    public function testDecryptAttribute(): void
    {
        $encryptedAttribute = EncryptedAttribute::fromXML(self::$xmlRepresentation->documentElement);

        $decryptor = (new KeyTransportAlgorithmFactory())->getAlgorithm(
            $encryptedAttribute->getEncryptedKeys()[0]->getEncryptionMethod()?->getAlgorithm()->getValue(),
            PEMCertificatesMock::getPrivateKey(PEMCertificatesMock::PRIVATE_KEY),
        );

        $decryptedAttribute = $encryptedAttribute->decrypt($decryptor);
        $this->assertEquals('urn:encrypted:attribute', $decryptedAttribute->getName());
    }
}
