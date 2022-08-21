<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\SAML2\Compat\MockContainer;
use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\SAML2\XML\saml\EncryptedAssertion;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\saml\NameID;
use SimpleSAML\SAML2\XML\saml\Subject;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XMLSecurity\Alg\KeyTransport\KeyTransportAlgorithmFactory;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;
use SimpleSAML\XMLSecurity\Constants as C;
use SimpleSAML\XMLSecurity\XML\ds\KeyInfo;
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
 * Class \SAML2\EncryptedAssertionTest
 *
 * @package simplesamlphp/saml2
 * @covers \SimpleSAML\SAML2\XML\saml\EncryptedAssertion
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractSamlElement
 */
final class EncryptedAssertionTest extends TestCase
{
    use SerializableXMLTestTrait;


    /**
     */
    public function setUp(): void
    {
        $this->testedClass = EncryptedAssertion::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/saml_EncryptedAssertion.xml'
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
        $ed = new EncryptedData(
            new CipherData(new CipherValue('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')),
            null,
            C::XMLENC_ELEMENT,
            null,
            null,
            new EncryptionMethod(C::BLOCK_ENC_AES256_GCM),
            new KeyInfo([
                new EncryptedKey(
                    new CipherData(new CipherValue('sNLWjwyj/R0oPwSgNnqowahiOwM0YU3YaH3jsH0t2YUDcHkcgouvW5x6YbNdgvGq0ImsNrkjI//0hrL4HvrOX33+DkhCo2FX5+a7UCdftfBfSjvt0houF8z3Zq/XOm6HxBUbWt5MULYpMKMZ9iAY6+raydxk2tFWgnAyHaBfzvU=')),
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    new EncryptionMethod(C::KEY_TRANSPORT_OAEP_MGF1P),
                ),
                new Chunk(DOMDocumentFactory::fromFile(dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/vendor/simplesamlphp/xml-security/tests/resources/xml/ds_RetrievalMethod.xml')->documentElement)
            ])
        );
        $encryptedAssertion = new EncryptedAssertion($ed);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($encryptedAssertion)
        );
    }


    /**
     * Test encryption / decryption
     */
    public function testEncryption(): void
    {
        $assertion = new Assertion(new Issuer('Test'), null, null, new Subject(new NameID('Someone')));

        $encryptor = (new KeyTransportAlgorithmFactory())->getAlgorithm(
            C::KEY_TRANSPORT_OAEP_MGF1P,
            PEMCertificatesMock::getPublicKey(PEMCertificatesMock::SELFSIGNED_PUBLIC_KEY),
        );

        $encAssertion = new EncryptedAssertion($assertion->encrypt($encryptor));
        $doc = DOMDocumentFactory::fromString(strval($encAssertion));

        $encAssertion = EncryptedAssertion::fromXML($doc->documentElement);
        $decryptor = (new KeyTransportAlgorithmFactory())->getAlgorithm(
            $encAssertion->getEncryptedKey()->getEncryptionMethod()->getAlgorithm(),
            PEMCertificatesMock::getPrivateKey(PEMCertificatesMock::SELFSIGNED_PRIVATE_KEY),
        );

        $this->assertEquals(
            strval($assertion),
            strval($encAssertion->decrypt($decryptor))
        );
    }
}
