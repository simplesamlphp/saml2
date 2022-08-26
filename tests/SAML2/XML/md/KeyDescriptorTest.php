<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\XML\md\EncryptionMethod;
use SimpleSAML\SAML2\XML\md\KeyDescriptor;
use SimpleSAML\Test\XML\SchemaValidationTestTrait;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XMLSecurity\XML\ds\AbstractDsElement;
use SimpleSAML\XMLSecurity\XML\ds\KeyInfo;
use SimpleSAML\XMLSecurity\XML\ds\KeyName;

use function dirname;
use function strval;

/**
 * A set of tests for the md:KeyDescriptor element
 *
 * @covers \SimpleSAML\SAML2\XML\md\KeyDescriptor
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMdElement
 * @package simplesamlphp/saml2
 */
final class KeyDescriptorTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableXMLTestTrait;


    /**
     */
    protected function setUp(): void
    {
        $this->schema = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/schemas/saml-schema-metadata-2.0.xsd';

        $this->testedClass = KeyDescriptor::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/md_KeyDescriptor.xml'
        );
    }


    // test marshalling


    /**
     * Test creating a KeyDescriptor from scratch.
     */
    public function testMarshalling(): void
    {
        $kd = new KeyDescriptor(
            new KeyInfo([new KeyName('IdentityProvider.com SSO Key')]),
            'signing',
            [new EncryptionMethod('http://www.w3.org/2001/04/xmlenc#rsa-1_5')]
        );

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($kd)
        );
    }


    /**
     * Test that creating a KeyDescriptor from scratch with a wrong use fails.
     */
    public function testMarshallingWrongUse(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('The "use" attribute of a KeyDescriptor can only be "encryption" or "signing".');

        new KeyDescriptor(
            new KeyInfo([new KeyName('IdentityProvider.com SSO Key')]),
            'wrong'
        );
    }


    /**
     * Test that creating a KeyDescriptor from scratch without any optional argument works.
     */
    public function testMarshallingWithoutOptionalParameters(): void
    {
        $kd = new KeyDescriptor(new KeyInfo([new KeyName('IdentityProvider.com SSO Key')]));

        $this->assertNull($kd->getUse());
        $this->assertEmpty($kd->getEncryptionMethods());

        $this->assertEquals(
            <<<XML
<md:KeyDescriptor xmlns:md="urn:oasis:names:tc:SAML:2.0:metadata">
  <ds:KeyInfo xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
    <ds:KeyName>IdentityProvider.com SSO Key</ds:KeyName>
  </ds:KeyInfo>
</md:KeyDescriptor>
XML
            ,
            strval($kd)
        );
    }


    // test unmarshalling


    /**
     * Test creating a KeyDescriptor from XML.
     */
    public function testUnmarshalling(): void
    {
        $kd = KeyDescriptor::fromXML($this->xmlRepresentation->documentElement);
        $this->assertEquals('signing', $kd->getUse());

        $knfo = $kd->getKeyInfo();
        $info = $knfo->getInfo();
        $this->assertCount(1, $info);
        $this->assertCount(1, $info);
        $this->assertInstanceOf(KeyName::class, $info[0]);
        $this->assertCount(1, $kd->getEncryptionMethods());
        $this->assertEquals('http://www.w3.org/2001/04/xmlenc#rsa-1_5', $kd->getEncryptionMethods()[0]->getAlgorithm());
    }


    /**
     * Test that creating a KeyDescriptor from XML with a wrong use fails.
     */
    public function testUnmarshallingWithWrongUse(): void
    {
        $this->xmlRepresentation->documentElement->setAttribute('use', 'wrong');

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('The "use" attribute of a KeyDescriptor can only be "encryption" or "signing".');

        KeyDescriptor::fromXML($this->xmlRepresentation->documentElement);
    }


    /**
     * Test that creating a KeyDescriptor from XML works when no optional elements are present.
     */
    public function testUnmarshallingWithoutOptionalParameters(): void
    {
        $document = DOMDocumentFactory::fromString(<<<XML
<md:KeyDescriptor xmlns:md="urn:oasis:names:tc:SAML:2.0:metadata">
  <ds:KeyInfo xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
    <ds:KeyName>IdentityProvider.com SSO Key</ds:KeyName>
  </ds:KeyInfo>
</md:KeyDescriptor>
XML
        );

        $kd = KeyDescriptor::fromXML($document->documentElement);
        $this->assertNull($kd->getUse());
        $this->assertEmpty($kd->getEncryptionMethods());
    }
}
