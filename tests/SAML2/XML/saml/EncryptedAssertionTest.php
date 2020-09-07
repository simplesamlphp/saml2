<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMDocument;
use Phpunit\Framework\TestCase;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SimpleSAML\SAML2\XML\ds\KeyInfo;
use SimpleSAML\SAML2\XML\xenc\CipherData;
use SimpleSAML\SAML2\XML\xenc\DataReference;
use SimpleSAML\SAML2\XML\xenc\EncryptedData;
use SimpleSAML\SAML2\XML\xenc\EncryptedKey;
use SimpleSAML\SAML2\XML\xenc\EncryptionMethod;
use SimpleSAML\SAML2\XML\xenc\ReferenceList;
use SimpleSAML\TestUtils\PEMCertificatesMock;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Chunk;

/**
 * Class \SAML2\EncryptedAssertionTest
 *
 * @package simplesamlphp/saml2
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @covers \SimpleSAML\SAML2\XML\saml\EncryptedAssertion
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractSamlElement
 */
final class EncryptedAssertionTest extends TestCase
{
    /** @var \DOMDocument */
    private DOMDocument $document;


    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->document = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/saml_EncryptedAssertion.xml'
        );
    }


    // marshalling


    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $ed = new EncryptedData(
            new CipherData('GaYev...'),
            null,
            'http://www.w3.org/2001/04/xmlenc#Element',
            null,
            null,
            new EncryptionMethod('http://www.w3.org/2001/04/xmlenc#aes128-cbc'),
            new KeyInfo([new Chunk(DOMDocumentFactory::fromFile(dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/ds_RetrievalMethod.xml')->documentElement)])
        );
        $encryptedAssertion = new EncryptedAssertion($ed, []);

        $ed = $encryptedAssertion->getEncryptedData();
        $this->assertEquals('GaYev...', $ed->getCipherData()->getCipherValue());
        $this->assertEquals('http://www.w3.org/2001/04/xmlenc#Element', $ed->getType());
        $encMethod = $ed->getEncryptionMethod();
        $this->assertInstanceOf(EncryptionMethod::class, $encMethod);
        $this->assertEquals('http://www.w3.org/2001/04/xmlenc#aes128-cbc', $encMethod->getAlgorithm());
        $this->assertInstanceOf(KeyInfo::class, $ed->getKeyInfo());

        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($encryptedAssertion)
        );
    }


    /**
     * Test encryption / decryption
     */
    public function testEncryption(): void
    {
        $this->markTestSkipped('This test can be enabled as soon as the rewrite-assertion branch has been merged');

        $pubkey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'public']);
        $pubkey->loadKey(PEMCertificatesMock::getPlainPublicKey(PEMCertificatesMock::PUBLIC_KEY));

        $assertion = new Assertion(new Issuer('Test'));

        /** \SAML2\XML\saml\AbstractSamlElement $encAssertion */
        $encAssertion = EncryptedAssertion::fromUnencryptedElement($assertion, $pubkey);
        $doc = DOMDocumentFactory::fromString(strval($encAssertion));

        /** \SAML2\XML\EncryptedElementInterface $encid */
        $encAssertion = Assertion::fromXML($doc->documentElement);
        $privkey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'private']);
        $privkey->loadKey(PEMCertificatesMock::getPlainPrivateKey(PEMCertificatesMock::PRIVATE_KEY));

        $this->assertEquals(strval($assertion), strval($encAssertion->decrypt($privkey)));
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(EncryptedAssertion::fromXML($this->document->documentElement))))
        );
    }
}
