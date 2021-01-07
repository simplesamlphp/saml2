<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Chunk;

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
    /** @var \DOMDocument */
    private DOMDocument $document;


    /**
     */
    public function setUp(): void
    {
        $this->document = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/samlp_StatusDetail.xml'
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $document = DOMDocumentFactory::fromString(
            '<Cause>org.sourceid.websso.profiles.idp.FailedAuthnSsoException</Cause>'
        );

        $statusDetail = new StatusDetail([new Chunk($document->documentElement)]);
        $this->assertFalse($statusDetail->isEmptyElement());
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($statusDetail)
        );
    }


    /**
     * Adding an empty StatusDetail element should yield an empty element.
     */
    public function testMarshallingEmptyElement(): void
    {
        $samlpns = Constants::NS_SAMLP;
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
        $statusDetail = StatusDetail::fromXML($this->document->documentElement);

        $statusDetailElement = $statusDetail->getDetails();
        $statusDetailElement = $statusDetailElement[0]->getXML();

        $this->assertEquals('Cause', $statusDetailElement->tagName);
        $this->assertEquals(
            'org.sourceid.websso.profiles.idp.FailedAuthnSsoException',
            $statusDetailElement->textContent
        );
        $this->assertFalse($statusDetail->isEmptyElement());
   }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(StatusDetail::fromXML($this->document->documentElement))))
        );
    }
}
