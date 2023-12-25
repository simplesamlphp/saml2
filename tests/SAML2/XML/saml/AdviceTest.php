<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Test\SAML2\XML\saml;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\saml\Advice;
use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\SAML2\XML\saml\AssertionIDRef;
use SimpleSAML\SAML2\XML\saml\AssertionURIRef;
use SimpleSAML\SAML2\XML\saml\EncryptedAssertion;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\saml\AdviceTest
 *
 * @covers \SimpleSAML\SAML2\XML\saml\Advice
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractSamlElement
 *
 * @package simplesamlphp/saml2
 */
final class AdviceTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;

    /** @var \DOMDocument $assertionIDRef */
    private static DOMDocument $assertionIDRef;

    /** @var \DOMDocument $assertionURIRef */
    private static DOMDocument $assertionURIRef;

    /** @var \DOMDocument $assertion */
    private static DOMDocument $assertion;

    /** @var \DOMDocument $encryptedAssertion */
    private static DOMDocument $encryptedAssertion;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$schemaFile = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-assertion-2.0.xsd';

        self::$testedClass = Advice::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/saml_Advice.xml',
        );

        self::$assertionIDRef = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/saml_AssertionIDRef.xml',
        );

        self::$assertionURIRef = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/saml_AssertionURIRef.xml',
        );

        self::$assertion = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/saml_Assertion.xml',
        );

        self::$encryptedAssertion = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/saml_EncryptedAssertion.xml',
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $chunkXml = DOMDocumentFactory::fromString(
            '<ssp:Chunk xmlns:ssp="urn:x-simplesamlphp:namespace">Value</ssp:Chunk>'
        );
        $chunk = Chunk::fromXML($chunkXml->documentElement);

        $advice = new Advice(
            [AssertionIDRef::fromXML(self::$assertionIDRef->documentElement)],
            [AssertionURIRef::fromXML(self::$assertionURIRef->documentElement)],
            [Assertion::fromXML(self::$assertion->documentElement)],
            [EncryptedAssertion::fromXML(self::$encryptedAssertion->documentElement)],
            [$chunk],
        );

        $this->assertFalse($advice->isEmptyElement());

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($advice),
        );
    }


    /**
     */
    public function testMarshallingWithNoContent(): void
    {
        $advice = new Advice();
        $this->assertEquals(
            '<saml:Advice xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"/>',
            strval($advice)
        );
        $this->assertTrue($advice->isEmptyElement());
    }
}
