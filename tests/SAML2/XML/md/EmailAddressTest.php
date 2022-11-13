<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\XML\md\EmailAddress;
use SimpleSAML\Test\XML\ArrayizableElementTestTrait;
use SimpleSAML\Test\XML\SchemaValidationTestTrait;
use SimpleSAML\Test\XML\SerializableElementTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;

/**
 * Tests for SurName.
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
    protected function setUp(): void
    {
        $this->schema = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/schemas/saml-schema-metadata-2.0.xsd';

        $this->testedClass = EmailAddress::class;

        $this->arrayRepresentation = ['mailto:john.doe@example.org'];

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/md_EmailAddress.xml'
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
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($name)
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
        $name = EmailAddress::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($name)
        );
    }


    /**
     * Test that creating an EmailAddress from XML fails when an invalid email address is found.
     */
    public function testUnmarshallingWithInvalidEmail(): void
    {
        $document = $this->xmlRepresentation;
        $document->documentElement->textContent = 'not so valid';

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Expected a value to be a valid e-mail address. Got: "not so valid"');

        EmailAddress::fromXML($document->documentElement);
    }
}
