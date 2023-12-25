<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\samlp;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\XML\samlp\StatusDetail;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

use function dirname;
use function strval;

/**
 * Class \SAML2\XML\samlp\StatusDetailTest
 *
 * @covers \SimpleSAML\SAML2\XML\samlp\StatusDetail
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractSamlpElement
 *
 * @package simplesamlphp/saml2
 */
final class StatusDetailTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$schemaFile = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-protocol-2.0.xsd';

        self::$testedClass = StatusDetail::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/samlp_StatusDetail.xml',
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $document = DOMDocumentFactory::fromString(
            '<ssp:Cause xmlns:ssp="urn:custom:ssp">org.sourceid.websso.profiles.idp.FailedAuthnSsoException</ssp:Cause>',
        );

        $statusDetail = new StatusDetail([new Chunk($document->documentElement)]);

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($statusDetail),
        );
    }


    /**
     * Adding an empty StatusDetail element should yield an empty element.
     */
    public function testMarshallingEmptyElement(): void
    {
        $samlpns = C::NS_SAMLP;
        $statusDetail = new StatusDetail([]);
        $this->assertEquals(
            "<samlp:StatusDetail xmlns:samlp=\"$samlpns\"/>",
            strval($statusDetail),
        );
        $this->assertTrue($statusDetail->isEmptyElement());
    }
}
