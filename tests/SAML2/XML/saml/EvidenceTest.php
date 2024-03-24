<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Test\SAML2\XML\saml;

use DOMDocument;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\saml\AbstractSamlElement;
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
 * @package simplesamlphp/saml2
 */
#[Group('saml')]
#[CoversClass(Evidence::class)]
#[CoversClass(AbstractSamlElement::class)]
final class EvidenceTest extends TestCase
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
    protected function setUp(): void
    {
        self::$schemaFile = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-assertion-2.0.xsd';

        self::$testedClass = Evidence::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/saml_Evidence.xml',
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
        $evidence = new Evidence(
            [AssertionIDRef::fromXML(self::$assertionIDRef->documentElement)],
            [AssertionURIRef::fromXML(self::$assertionURIRef->documentElement)],
            [Assertion::fromXML(self::$assertion->documentElement)],
            [EncryptedAssertion::fromXML(self::$encryptedAssertion->documentElement)],
        );

        $this->assertFalse($evidence->isEmptyElement());

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
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
}
