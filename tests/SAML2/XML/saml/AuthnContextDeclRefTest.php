<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\XML\saml\AuthnContextDeclRef;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;

use function dirname;
use function strval;

/**
 * Class \SAML2\XML\saml\AuthnContextDeclRefTest
 *
 * @covers \SimpleSAML\SAML2\XML\saml\AuthnContextDeclRef
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractSamlElement
 * @package simplesamlphp/saml2
 */
final class AuthnContextDeclRefTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;

    /**
     */
    protected function setUp(): void
    {
        $this->schema = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-assertion-2.0.xsd';

        $this->testedClass = AuthnContextDeclRef::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 5) . '/resources/xml/saml_AuthnContextDeclRef.xml',
        );
    }


    // marshalling


    /**
     */
    public function testMarshalling(): void
    {
        $authnContextDeclRef = new AuthnContextDeclRef('https://example.org/relative/path/to/document.xml');

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($authnContextDeclRef),
        );
    }


    // unmarshalling


    /**
     */
    public function testUnmarshalling(): void
    {
        $authnContextDeclRef = AuthnContextDeclRef::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($authnContextDeclRef),
        );
    }
}
