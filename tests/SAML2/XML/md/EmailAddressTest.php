<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\XML\md\EmailAddress;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\ArrayizableElementTestTrait;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

/**
 * Tests for EmailAddress.
 *
 * @covers \SimpleSAML\SAML2\XML\md\EmailAddress
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMdElement
 * @package simplesamlphp/saml2
 */
final class EmailAddressTest extends TestCase
{
    use ArrayizableElementTestTrait;
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$schemaFile = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-metadata-2.0.xsd';

        self::$testedClass = EmailAddress::class;

        self::$arrayRepresentation = ['mailto:john.doe@example.org'];

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/md_EmailAddress.xml',
        );
    }


    // test marshalling


    /**
     * Test creating an EmailAddress object from scratch.
     */
    public function testMarshalling(): void
    {
        $name = new EmailAddress('john.doe@example.org');

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($name),
        );
    }


    /**
     */
    public function testInvalidEmailThrowsException(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Expected a value to be a valid e-mail address. Got: "not so valid"');
        new EmailAddress('not so valid');
    }


    // test unmarshalling


    /**
     * Test creating a EmailAddress from XML.
     */
    public function testUnmarshalling(): void
    {
        $name = EmailAddress::fromXML(self::$xmlRepresentation->documentElement);

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($name),
        );
    }


    /**
     * Test that creating an EmailAddress from XML fails when an invalid email address is found.
     */
    public function testUnmarshallingWithInvalidEmail(): void
    {
        $document = clone self::$xmlRepresentation;
        $document->documentElement->textContent = 'not so valid';

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Expected a value to be a valid e-mail address. Got: "not so valid"');

        EmailAddress::fromXML($document->documentElement);
    }
}
