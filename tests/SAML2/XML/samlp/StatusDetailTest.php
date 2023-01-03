<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\samlp;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\XML\samlp\StatusDetail;
use SimpleSAML\Test\XML\SerializableElementTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Chunk;

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
    use SerializableElementTestTrait;


    /**
     */
    public function setUp(): void
    {
        $this->testedClass = StatusDetail::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/samlp_StatusDetail.xml'
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $document = DOMDocumentFactory::fromString(
            '<ssp:Cause xmlns:ssp="urn:custom:ssp">org.sourceid.websso.profiles.idp.FailedAuthnSsoException</ssp:Cause>'
        );

        $statusDetail = new StatusDetail([new Chunk($document->documentElement)]);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($statusDetail)
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
            strval($statusDetail)
        );
        $this->assertTrue($statusDetail->isEmptyElement());
    }


    /**
     */
    public function testUnmarshalling(): void
    {
        $statusDetail = StatusDetail::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($statusDetail)
        );
    }
}
