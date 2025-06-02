<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use PHPUnit\Framework\Attributes\{CoversClass, Group};
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\XML\md\{AbstractMdElement, EmailAddress};
use SimpleSAML\SAML2\Type\EmailAddressValue;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\{ArrayizableElementTestTrait, SchemaValidationTestTrait, SerializableElementTestTrait};

/**
 * Tests for EmailAddress.
 *
 * @package simplesamlphp/saml2
 */
#[Group('md')]
#[CoversClass(EmailAddress::class)]
#[CoversClass(AbstractMdElement::class)]
final class EmailAddressTest extends TestCase
{
    use ArrayizableElementTestTrait;
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
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
        $email = new EmailAddress(
            EmailAddressValue::fromString('john.doe@example.org'),
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($email),
        );
    }


    /**
     */
    public function testInvalidEmailThrowsException(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Expected a value to be a valid e-mail address. Got: "not so valid"');
        new EmailAddress(
            EmailAddressValue::fromString('not so valid'),
        );
    }


    // test unmarshalling


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


    /**
     * Test that creating an EmailAddress from XML succeeds when multiple mailto: prefixes are in place.
     */
    public function testUnmarshallingWithMultipleMailtoUri(): void
    {
        $document = clone self::$xmlRepresentation;
        $document->documentElement->textContent = 'mailto:mailto:mailto:john.doe@example.org';

        $email = EmailAddress::fromXML($document->documentElement);
        $this->assertEquals('mailto:john.doe@example.org', $email->getContent());
    }
}
