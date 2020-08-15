<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use PHPUnit\Framework\TestCase;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\TestUtils\PEMCertificatesMock;

/**
 * Class \SAML2\XML\saml\AttributeStatementTest
 */
final class AttributeStatementTest extends TestCase
{
    /** @var \DOMDocument */
    private $document;

    /** @var \DOMDocument */
    private $attributeXML;

    /** @var \DOMDocument */
    private $encryptedAttributeXML;

    /** @var \SAML2\XML\saml\EncryptedAttribute */
    private $encryptedAttribute;


    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->document = DOMDocumentFactory::fromString(<<<XML
<saml:AttributeStatement xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">
</saml:AttributeStatement>
XML
        );

        $this->attributeXML = DOMDocumentFactory::fromString(<<<XML
<saml:Attribute Name="urn:ServiceID" xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">
  <saml:AttributeValue>1</saml:AttributeValue>
</saml:Attribute>
XML
        );

        $this->encryptedAttributeXML = DOMDocumentFactory::fromString(<<<XML
<saml:EncryptedAttribute xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">
  <xenc:EncryptedData xmlns:xenc="http://www.w3.org/2001/04/xmlenc#" Type="http://www.w3.org/2001/04/xmlenc#Element">
    <xenc:EncryptionMethod Algorithm="http://www.w3.org/2001/04/xmlenc#aes128-cbc"/>
    <ds:KeyInfo xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
      <xenc:EncryptedKey xmlns:dsig="http://www.w3.org/2000/09/xmldsig#">
        <xenc:EncryptionMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"/>
        <xenc:CipherData>
          <xenc:CipherValue>nxf0bJ/1UECkfZBkKrEKU0phPpwUi6sS3bP5SovqVg/ohvgnnmpePNB6/CYyXbnatyhZ8bdgoKcpOJwYiQU5fKkY8ONFHnqWu13S8A8bBIL9ye6ceCKjHtCPiBzYwwOSjSsb+xJHSDzrfFbXDNzYBuyhuW1+uTqcmpoe+PdYyv4=</xenc:CipherValue>
        </xenc:CipherData>
      </xenc:EncryptedKey>
    </ds:KeyInfo>
    <xenc:CipherData>
      <xenc:CipherValue>iaDc7XVor2+y5PtFsASE1FKbC/l6BJd33JhA0EufLA2AMnijQNIZgWmXzP2CUI6dPYSmqECBQbuGgelQlEes8REAgxDJrqpqmzHDEXZ+XjWsD1pHBP2mQrG0hOlWc2YQOgHjh4pKc1IZJ45s85aFsiGU3kLiBMhR15vxHyHjOXOef9RXbZe3RChgB/st75iHxDFY6l/ojC6OmT0Vn1IBPGbMynAPP5g/REPHzhSDoHJBSqIzSVJb7wZlRmDd3itk</xenc:CipherValue>
    </xenc:CipherData>
  </xenc:EncryptedData>
</saml:EncryptedAttribute>
XML
        );

        $pubkey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'public']);
        $pubkey->loadKey(PEMCertificatesMock::getPlainPublicKey(PEMCertificatesMock::PUBLIC_KEY));

        /** @psalm-var \SAML2\XML\saml\EncryptedAttribute $encryptedAttribute */
        $encryptedAttribute = EncryptedAttribute::fromUnencryptedElement(
            new Attribute('urn:encrypted:attribute', null, null, [new AttributeValue('very secret data')]),
            $pubkey
        );

        $this->encryptedAttribute = $encryptedAttribute;
    }


    // marshalling


    /**
     * @return void
     */
    public function testMarshallingAttributes(): void
    {
        $attrStatement = new AttributeStatement(
            [
                new Attribute('urn:ServiceID', null, null, [new AttributeValue('1')])
            ]
        );

        $attributes = $attrStatement->getAttributes();
        $this->assertCount(1, $attributes);

        $this->assertEquals('urn:ServiceID', $attributes[0]->getName());

        $this->assertEmpty($attrStatement->getEncryptedAttributes());
        $this->assertFalse($attrStatement->hasEncryptedAttributes());
    }


    /**
     * @return void
     */
    public function testMarshallingEncryptedAttributes(): void
    {
        $attrStatement = new AttributeStatement(
            [
                new Attribute('urn:ServiceID', null, null, [new AttributeValue('1')])
            ],
            [
                $this->encryptedAttribute
            ]
        );

        $attributes = $attrStatement->getAttributes();
        $this->assertCount(1, $attributes);

        $this->assertEquals('urn:ServiceID', $attributes[0]->getName());

        $encryptedAttributes = $attrStatement->getEncryptedAttributes();
        $this->assertCount(1, $encryptedAttributes);
        $this->assertTrue($attrStatement->hasEncryptedAttributes());
    }


    /**
     * @return void
     */
    public function testMarshallingMissingAttributesThrowsException(): void
    {
        $this->expectException(AssertionFailedException::class);

        new AttributeStatement([], []);
    }


    // unmarshalling


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $document = $this->document;
        $document->documentElement->appendChild($document->importNode($this->attributeXML->documentElement, true));

        $attrStatement = AttributeStatement::fromXML($document->documentElement);

        $attributes = $attrStatement->getAttributes();
        $this->assertCount(1, $attributes);
        $this->assertEquals('urn:ServiceID', $attributes[0]->getName());

        $this->assertEmpty($attrStatement->getEncryptedAttributes());
        $this->assertFalse($attrStatement->hasEncryptedAttributes());
    }


    /**
     * @return void
     */
    public function testUnmarshallingEncryptedAttribute(): void
    {
        $document = $this->document;
        $document->documentElement->appendChild($document->importNode($this->attributeXML->documentElement, true));
        $document->documentElement->appendChild($document->importNode($this->encryptedAttributeXML->documentElement, true));

        $attrStatement = AttributeStatement::fromXML($document->documentElement);

        $attributes = $attrStatement->getAttributes();
        $this->assertCount(1, $attributes);
        $this->assertEquals('urn:ServiceID', $attributes[0]->getName());

        $encryptedAttributes = $attrStatement->getEncryptedAttributes();
        $this->assertCount(1, $encryptedAttributes);
        $this->assertTrue($attrStatement->hasEncryptedAttributes());
    }


    /**
     * @return void
     */
    public function testUnmarshallingMissingAttributesThrowsException(): void
    {
        $this->expectException(AssertionFailedException::class);
        AttributeStatement::fromXML($this->document->documentElement);
    }


    /**
     * @return void
     */
    public function testDecryptAttributes(): void
    {
        $attrStatement = new AttributeStatement(
            [
                new Attribute('urn:ServiceID', null, null, [new AttributeValue('1')])
            ],
            [
                $this->encryptedAttribute
            ]
        );

        $privkey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'private']);
        $privkey->loadKey(PEMCertificatesMock::getPlainPrivateKey(PEMCertificatesMock::PRIVATE_KEY));
        $attrStatement->decryptAttributes($privkey, []);

        $attributes = $attrStatement->getAttributes();
        $this->assertCount(2, $attributes);

        $this->assertEquals('urn:ServiceID', $attributes[0]->getName());
        $this->assertEquals('urn:encrypted:attribute', $attributes[1]->getName());
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $document = DOMDocumentFactory::fromString(<<<XML
<saml:AttributeStatement xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion">
  <saml:Attribute Name="urn:ServiceID">
    <saml:AttributeValue>1</saml:AttributeValue>
  </saml:Attribute>
  <saml:EncryptedAttribute>
    <xenc:EncryptedData xmlns:xenc="http://www.w3.org/2001/04/xmlenc#" Type="http://www.w3.org/2001/04/xmlenc#Element">
      <xenc:EncryptionMethod Algorithm="http://www.w3.org/2001/04/xmlenc#aes128-cbc"/>
      <ds:KeyInfo xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
        <xenc:EncryptedKey xmlns:dsig="http://www.w3.org/2000/09/xmldsig#">
          <xenc:EncryptionMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"/>
          <xenc:CipherData>
            <xenc:CipherValue>nxf0bJ/1UECkfZBkKrEKU0phPpwUi6sS3bP5SovqVg/ohvgnnmpePNB6/CYyXbnatyhZ8bdgoKcpOJwYiQU5fKkY8ONFHnqWu13S8A8bBIL9ye6ceCKjHtCPiBzYwwOSjSsb+xJHSDzrfFbXDNzYBuyhuW1+uTqcmpoe+PdYyv4=</xenc:CipherValue>
          </xenc:CipherData>
        </xenc:EncryptedKey>
      </ds:KeyInfo>
      <xenc:CipherData>
        <xenc:CipherValue>iaDc7XVor2+y5PtFsASE1FKbC/l6BJd33JhA0EufLA2AMnijQNIZgWmXzP2CUI6dPYSmqECBQbuGgelQlEes8REAgxDJrqpqmzHDEXZ+XjWsD1pHBP2mQrG0hOlWc2YQOgHjh4pKc1IZJ45s85aFsiGU3kLiBMhR15vxHyHjOXOef9RXbZe3RChgB/st75iHxDFY6l/ojC6OmT0Vn1IBPGbMynAPP5g/REPHzhSDoHJBSqIzSVJb7wZlRmDd3itk</xenc:CipherValue>
      </xenc:CipherData>
    </xenc:EncryptedData>
  </saml:EncryptedAttribute>
</saml:AttributeStatement>
XML
        );

        $this->assertEquals(
            $document->saveXML($document->documentElement),
            strval(unserialize(serialize(AttributeStatement::fromXML($document->documentElement))))
        );
    }
}
