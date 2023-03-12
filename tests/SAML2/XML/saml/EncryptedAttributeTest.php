<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\SAML2\Compat\MockContainer;
use SimpleSAML\SAML2\XML\saml\Attribute;
use SimpleSAML\SAML2\XML\saml\AttributeValue;
use SimpleSAML\SAML2\XML\saml\EncryptedAttribute;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XMLSecurity\Alg\KeyTransport\KeyTransportAlgorithmFactory;
use SimpleSAML\XMLSecurity\Constants as C;
use SimpleSAML\XMLSecurity\Key\PrivateKey;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;

use function dirname;
use function strval;

/**
 * Class \SAML2\XML\saml\EncryptedAttributeTest
 *
 * @package simplesamlphp/saml2
 * @covers \SimpleSAML\SAML2\XML\saml\EncryptedAttribute
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractSamlElement
 */
final class EncryptedAttributeTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;

    /**
     */
    protected function setUp(): void
    {
        $this->schema = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-assertion-2.0.xsd';

        $this->testedClass = EncryptedAttribute::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 5) . '/resources/xml/saml_EncryptedAttribute.xml',
        );

        $container = new MockContainer();
        $container->setBlacklistedAlgorithms(null);
        ContainerSingleton::setContainer($container);
    }


    // marshalling


    /**
     */
    public function testMarshalling(): void
    {
        $attribute = new Attribute(
            name: 'urn:encrypted:attribute',
            attributeValue: [new AttributeValue('very secret data')],
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
    public function testUnmarshalling(): void
    {
        $encryptedAttribute = EncryptedAttribute::fromXML($this->xmlRepresentation->documentElement);

        $encryptedData = $encryptedAttribute->getEncryptedData();
        $this->assertEquals(C::XMLENC_ELEMENT, $encryptedData->getType());
    }


    /**
     */
    public function testDecryptAttribute(): void
    {
        $encryptedAttribute = EncryptedAttribute::fromXML($this->xmlRepresentation->documentElement);

        /** @psalm-suppress PossiblyNullArgument */
        $decryptor = (new KeyTransportAlgorithmFactory())->getAlgorithm(
            $encryptedAttribute->getEncryptedKey()->getEncryptionMethod()?->getAlgorithm(),
            PEMCertificatesMock::getPrivateKey(PEMCertificatesMock::PRIVATE_KEY),
        );

        $decryptedAttribute = $encryptedAttribute->decrypt($decryptor);
        $this->assertEquals('urn:encrypted:attribute', $decryptedAttribute->getName());
    }
}
