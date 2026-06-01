<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Test\SAML2\XML\saml;

use Dom;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\saml\AbstractSamlElement;
use SimpleSAML\SAML2\XML\saml\Advice;
use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\SAML2\XML\saml\AssertionIDRef;
use SimpleSAML\SAML2\XML\saml\AssertionURIRef;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\saml\AdviceTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('saml')]
#[CoversClass(Advice::class)]
#[CoversClass(AbstractSamlElement::class)]
final class AdviceTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /** @var \Dom\XMLDocument $assertionIDRef */
    private static Dom\XMLDocument $assertionIDRef;

    /** @var \Dom\XMLDocument $assertionURIRef */
    private static Dom\XMLDocument $assertionURIRef;

    /** @var \Dom\XMLDocument $assertion */
    private static Dom\XMLDocument $assertion;


    /**
     */
    public static function setUpBeforeClass(): void
    {
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
    }


    /**
     */
    public function testMarshalling(): void
    {
        $chunkXml = DOMDocumentFactory::fromString(
            '<ssp:Chunk xmlns:ssp="urn:x-simplesamlphp:namespace">Value</ssp:Chunk>',
        );
        $chunk = Chunk::fromXML($chunkXml->documentElement);

        $advice = new Advice(
            [AssertionIDRef::fromXML(self::$assertionIDRef->documentElement)],
            [AssertionURIRef::fromXML(self::$assertionURIRef->documentElement)],
            [Assertion::fromXML(self::$assertion->documentElement)],
            [],
            [$chunk],
        );

        $this->assertFalse($advice->isEmptyElement());

        $expectedXml = self::$xmlRepresentation->saveXml(self::$xmlRepresentation->documentElement);
        $this->assertNotFalse($expectedXml);
        $actualXml = strval($advice);

        $this->assertXmlStringEqualsXmlString($expectedXml, $actualXml);
    }


    /**
     */
    public function testMarshallingWithNoContent(): void
    {
        $advice = new Advice();
        $this->assertEquals(
            '<saml:Advice xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"/>',
            strval($advice),
        );
        $this->assertTrue($advice->isEmptyElement());
    }
}
