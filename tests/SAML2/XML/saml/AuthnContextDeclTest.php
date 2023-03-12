<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\XML\saml\AuthnContextDecl;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

use function dirname;
use function strval;

/**
 * Class \SAML2\XML\saml\AuthnContextDeclTest
 *
 * @covers \SimpleSAML\SAML2\XML\saml\AuthnContextDecl
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractSamlElement
 * @package simplesamlphp/saml2
 */
final class AuthnContextDeclTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;

    /**
     */
    protected function setUp(): void
    {
        $this->schema = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-assertion-2.0.xsd';

        $this->testedClass = AuthnContextDecl::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 5) . '/resources/xml/saml_AuthnContextDecl.xml',
        );
    }


    // marshalling


    /**
     */
    public function testMarshalling(): void
    {
        $authnContextDecl = new AuthnContextDecl($this->xmlRepresentation->documentElement->childNodes);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($authnContextDecl),
        );
    }


    // unmarshalling


    /**
     */
    public function testUnmarshalling(): void
    {
        $authnContextDecl = AuthnContextDecl::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($authnContextDecl),
        );
    }
}
