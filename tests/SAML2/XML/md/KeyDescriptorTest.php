<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Type\KeyTypesValue;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\XML\md\AbstractMdElement;
use SimpleSAML\SAML2\XML\md\EncryptionMethod;
use SimpleSAML\SAML2\XML\md\KeyDescriptor;
use SimpleSAML\SAML2\XML\md\KeyTypesEnum;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XMLSchema\Exception\SchemaViolationException;
use SimpleSAML\XMLSecurity\XML\ds\KeyInfo;
use SimpleSAML\XMLSecurity\XML\ds\KeyName;

use function dirname;
use function strval;

/**
 * A set of tests for the md:KeyDescriptor element
 *
 * @package simplesamlphp/saml2
 */
#[Group('md')]
#[CoversClass(KeyDescriptor::class)]
#[CoversClass(AbstractMdElement::class)]
final class KeyDescriptorTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = KeyDescriptor::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/md_KeyDescriptor.xml',
        );
    }


    // test marshalling


    /**
     * Test creating a KeyDescriptor from scratch.
     */
    public function testMarshalling(): void
    {
        $kd = new KeyDescriptor(
            new KeyInfo([
                KeyName::fromString('IdentityProvider.com SSO Key'),
            ]),
            KeyTypesValue::fromEnum(KeyTypesEnum::SIGNING),
            [
                new EncryptionMethod(
                    SAMLAnyURIValue::fromString('http://www.w3.org/2001/04/xmlenc#rsa-1_5'),
                ),
            ],
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($kd),
        );
    }


    /**
     * Test that creating a KeyDescriptor from scratch without any optional argument works.
     */
    public function testMarshallingWithoutOptionalParameters(): void
    {
        $kd = new KeyDescriptor(
            new KeyInfo([
                KeyName::fromString('IdentityProvider.com SSO Key'),
            ]),
        );

        $this->assertNull($kd->getUse());
        $this->assertEmpty($kd->getEncryptionMethod());

        $this->assertEquals(
            <<<XML
<md:KeyDescriptor xmlns:md="urn:oasis:names:tc:SAML:2.0:metadata" xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
  <ds:KeyInfo>
    <ds:KeyName>IdentityProvider.com SSO Key</ds:KeyName>
  </ds:KeyInfo>
</md:KeyDescriptor>
XML
            ,
            strval($kd),
        );
    }


    // test unmarshalling


    /**
     * Test that creating a KeyDescriptor from XML with a wrong use fails.
     */
    public function testUnmarshallingWithWrongUse(): void
    {
        $xmlRepresentation = clone self::$xmlRepresentation;
        $xmlRepresentation->documentElement->setAttribute('use', 'wrong');

        $this->expectException(SchemaViolationException::class);
        $this->expectExceptionMessage('Expected one of: "signing", "encryption". Got: "wrong"');

        KeyDescriptor::fromXML($xmlRepresentation->documentElement);
    }


    /**
     * Test that creating a KeyDescriptor from XML works when no optional elements are present.
     */
    public function testUnmarshallingWithoutOptionalParameters(): void
    {
        $document = DOMDocumentFactory::fromString(
            <<<XML
<md:KeyDescriptor xmlns:md="urn:oasis:names:tc:SAML:2.0:metadata" xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
  <ds:KeyInfo>
    <ds:KeyName>IdentityProvider.com SSO Key</ds:KeyName>
  </ds:KeyInfo>
</md:KeyDescriptor>
XML
            ,
        );

        $kd = KeyDescriptor::fromXML($document->documentElement);
        $this->assertNull($kd->getUse());
        $this->assertEmpty($kd->getEncryptionMethod());
    }
}
