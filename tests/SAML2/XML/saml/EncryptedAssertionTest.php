<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\SAML2\XML\saml\EncryptedAssertion;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;
use SimpleSAML\XMLSecurity\XML\ds\KeyInfo;
use SimpleSAML\XMLSecurity\XML\xenc\CipherData;
use SimpleSAML\XMLSecurity\XML\xenc\CipherValue;
use SimpleSAML\XMLSecurity\XML\xenc\DataReference;
use SimpleSAML\XMLSecurity\XML\xenc\EncryptedData;
use SimpleSAML\XMLSecurity\XML\xenc\EncryptedKey;
use SimpleSAML\XMLSecurity\XML\xenc\EncryptionMethod;
use SimpleSAML\XMLSecurity\XML\xenc\ReferenceList;
use SimpleSAML\XMLSecurity\XMLSecurityKey;

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
    }


    // marshalling


    /**
     */
    public function testMarshalling(): void
    {
        $ed = new EncryptedData(
            new CipherData(new CipherValue('/CTj03d1DB5e2t7CTo9BEzCf5S9NRzwnBgZRlm32REI=')),
            null,
            'http://www.w3.org/2001/04/xmlenc#Element',
            null,
            null,
            new EncryptionMethod('http://www.w3.org/2001/04/xmlenc#aes128-cbc'),
            new KeyInfo(
                [new Chunk(DOMDocumentFactory::fromFile(dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/vendor/simplesamlphp/xml-security/tests/resources/xml/ds_RetrievalMethod.xml')->documentElement)]
            )
        );
        $encryptedAssertion = new EncryptedAssertion($ed, []);

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
        $this->markTestSkipped('This test can be enabled as soon as the rewrite-assertion branch has been merged');

        $pubkey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'public']);
        $pubkey->loadKey(PEMCertificatesMock::getPlainPublicKey(PEMCertificatesMock::PUBLIC_KEY));

        $assertion = new Assertion(new Issuer('Test'));

        $encAssertion = EncryptedAssertion::fromUnencryptedElement($assertion, $pubkey);
        $doc = DOMDocumentFactory::fromString(strval($encAssertion));

        /** @psalm-var \SimpleSAML\XMLSecurity\XML\EncryptedElementInterface $encAssertion */
        $encAssertion = Assertion::fromXML($doc->documentElement);
        $privkey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'private']);
        $privkey->loadKey(PEMCertificatesMock::getPlainPrivateKey(PEMCertificatesMock::PRIVATE_KEY));

        $this->assertEquals(strval($assertion), strval($encAssertion->decrypt($privkey)));
    }
}
