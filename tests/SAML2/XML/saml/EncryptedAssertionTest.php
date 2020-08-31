<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use Phpunit\Framework\TestCase;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SimpleSAML\SAML2\DOMDocumentFactory;
use SimpleSAML\SAML2\XML\Chunk;
use SimpleSAML\SAML2\XML\ds\KeyInfo;
use SimpleSAML\SAML2\XML\xenc\CipherData;
use SimpleSAML\SAML2\XML\xenc\DataReference;
use SimpleSAML\SAML2\XML\xenc\EncryptedData;
use SimpleSAML\SAML2\XML\xenc\EncryptedKey;
use SimpleSAML\SAML2\XML\xenc\EncryptionMethod;
use SimpleSAML\SAML2\XML\xenc\ReferenceList;
use SimpleSAML\TestUtils\PEMCertificatesMock;

/**
 * Class \SAML2\EncryptedAssertionTest
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @covers \SimpleSAML\SAML2\XML\saml\EncryptedAssertion
 * @package simplesamlphp/saml2
 */
final class EncryptedAssertionTest extends TestCase
{
    /** @var \DOMDocument */
    private $document;


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
