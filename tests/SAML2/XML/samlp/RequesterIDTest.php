<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Test\SAML2\XML\samlp;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\samlp\RequesterID;
use SimpleSAML\Test\XML\SerializableElementTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\samlp\RequesterIDTest
 *
 * @covers \SimpleSAML\SAML2\XML\samlp\RequesterID
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractSamlpElement
 *
 * @package simplesamlphp/saml2
 */
final class RequesterIDTest extends TestCase
{
    use SerializableElementTestTrait;

    /**
     */
    protected function setUp(): void
    {
        $this->testedClass = RequesterID::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/samlp_RequesterID.xml'
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $requesterId = new RequesterID('urn:some:requester');

        $requesterIdElement = $requesterId->toXML();
        $this->assertEquals('urn:some:requester', $requesterIdElement->textContent);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($requesterId)
        );
    }


    /**
     */
    public function testUnmarshalling(): void
    {
        $requesterId = RequesterID::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($requesterId)
        );
    }
}

