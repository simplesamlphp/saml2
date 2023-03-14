<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Test\SAML2\XML\saml;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\SAML2\XML\saml\AssertionIDRef;
use SimpleSAML\SAML2\XML\saml\AssertionURIRef;
use SimpleSAML\SAML2\XML\saml\EncryptedAssertion;
use SimpleSAML\SAML2\XML\saml\Evidence;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\saml\EvidenceTest
 *
 * @covers \SimpleSAML\SAML2\XML\saml\Evidence
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractSamlElement
 *
 * @package simplesamlphp/saml2
 */
final class EvidenceTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;

    /** @var \DOMDocument $assertionIDRef */
    private DOMDocument $assertionIDRef;

    /** @var \DOMDocument $assertionURIRef */
    private DOMDocument $assertionURIRef;

    /** @var \DOMDocument $assertion */
    private DOMDocument $assertion;

    /** @var \DOMDocument $encryptedAssertion */
    private DOMDocument $encryptedAssertion;


    /**
     */
    protected function setUp(): void
    {
        $this->schema = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-assertion-2.0.xsd';

        $this->testedClass = Evidence::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/saml_Evidence.xml',
        );

        $this->assertionIDRef = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/saml_AssertionIDRef.xml',
        );

        $this->assertionURIRef = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/saml_AssertionURIRef.xml',
        );

        $this->assertion = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/saml_Assertion.xml',
        );

        $this->encryptedAssertion = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/saml_EncryptedAssertion.xml',
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $evidence = new Evidence(
            [AssertionIDRef::fromXML($this->assertionIDRef->documentElement)],
            [AssertionURIRef::fromXML($this->assertionURIRef->documentElement)],
            [Assertion::fromXML($this->assertion->documentElement)],
            [EncryptedAssertion::fromXML($this->encryptedAssertion->documentElement)],
        );

        $this->assertFalse($evidence->isEmptyElement());

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($evidence),
        );
    }


    /**
     */
    public function testMarshallingWithNoContent(): void
    {
        $evidence = new Evidence();
        $this->assertEquals(
            '<saml:Evidence xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"/>',
            strval($evidence)
        );
        $this->assertTrue($evidence->isEmptyElement());
    }


    /**
     */
    public function testUnmarshalling(): void
    {
        $evidence = Evidence::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($evidence),
        );
    }
}
