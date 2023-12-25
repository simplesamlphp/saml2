<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\XML\saml\AuthnContextDecl;
use SimpleSAML\XML\Attribute as XMLAttribute;
use SimpleSAML\XML\Chunk;
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
    public static function setUpBeforeClass(): void
    {
        self::$schemaFile = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-assertion-2.0.xsd';

        self::$testedClass = AuthnContextDecl::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/saml_AuthnContextDecl.xml',
        );
    }


    // marshalling


    /**
     */
    public function testMarshalling(): void
    {
        $chunk = new Chunk(DOMDocumentFactory::fromString(<<<XML
  <ssp:AuthenticationContextDeclaration xmlns:ssp="urn:x-simplesamlphp:namespace">
    <ssp:Identification nym="verinymity">
      <ssp:Extension>
        <ssp:NoVerification/>
      </ssp:Extension>
    </ssp:Identification>
  </ssp:AuthenticationContextDeclaration>
XML
        )->documentElement);

        $authnContextDecl = new AuthnContextDecl(
            [$chunk],
            [new XMLAttribute('urn:x-simplesamlphp:namespace', 'ssp', 'attr1', 'testval1')],
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($authnContextDecl),
        );
    }
}
