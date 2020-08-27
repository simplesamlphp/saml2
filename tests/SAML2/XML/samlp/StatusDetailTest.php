<?php

declare(strict_types=1);

namespace SAML2\XML\samlp;

use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\XML\Chunk;

/**
 * Class \SAML2\XML\samlp\StatusDetailTest
 *
 * @covers \SAML2\XML\samlp\StatusDetail
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
class StatusDetailTest extends \PHPUnit\Framework\TestCase
{
    /** @var \DOMDocument */
    private $document;


    /**
     * @return void
     */
    public function setUp(): void
    {
        $nssamlp = StatusDetail::NS;
        $this->document = DOMDocumentFactory::fromString(<<<XML
<samlp:StatusDetail xmlns:samlp="{$nssamlp}">
  <Cause>org.sourceid.websso.profiles.idp.FailedAuthnSsoException</Cause>
</samlp:StatusDetail>
XML
        );
    }


    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $document = DOMDocumentFactory::fromString(
            '<Cause>org.sourceid.websso.profiles.idp.FailedAuthnSsoException</Cause>'
        );

        $statusDetail = new StatusDetail([new Chunk($document->documentElement)]);
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($statusDetail)
        );
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $statusDetail = StatusDetail::fromXML($this->document->documentElement);

        $statusDetailElement = $statusDetail->getDetails();
        $statusDetailElement = $statusDetailElement[0]->getXML();

        $this->assertEquals('Cause', $statusDetailElement->tagName);
        $this->assertEquals('org.sourceid.websso.profiles.idp.FailedAuthnSsoException', $statusDetailElement->textContent);
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
