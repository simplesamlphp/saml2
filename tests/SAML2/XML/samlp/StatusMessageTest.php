<?php

declare(strict_types=1);

namespace SAML2\XML\samlp;

use SAML2\Constants;
use SAML2\DOMDocumentFactory;

/**
 * Class \SAML2\XML\samlp\StatusMessageTest
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
class StatusMessageTest extends \PHPUnit\Framework\TestCase
{
    /** @var \DOMDocument */
    private $document;


    /**
     * @return void
     */
    public function setUp(): void
    {
        $ns = StatusMessage::NS;
        $this->document = DOMDocumentFactory::fromString(<<<XML
<samlp:StatusMessage xmlns:samlp="{$ns}">Something went horribly wrong</samlp:StatusMessage>
XML
        );
    }


    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $statusMessage = new StatusMessage('Something went horribly wrong');

        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($statusMessage)
        );
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $statusMessage = StatusMessage::fromXML($this->document->documentElement);
        $this->assertEquals('Something went horribly wrong', $statusMessage->getMessage());
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(StatusMessage::fromXML($this->document->documentElement))))
        );
    }
}
