<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Test\SAML2\XML\samlp;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\samlp\StatusMessage;
use SimpleSAML\Test\XML\SerializableElementTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\samlp\StatusMessageTest
 *
 * @covers \SimpleSAML\SAML2\XML\samlp\StatusMessage
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractSamlpElement
 *
 * @package simplesamlphp/saml2
 */
final class StatusMessageTest extends TestCase
{
    use SerializableElementTestTrait;

    /**
     */
    protected function setUp(): void
    {
        $this->testedClass = StatusMessage::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/samlp_StatusMessage.xml'
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $statusMessage = new StatusMessage('Something went wrong');

        $statusMessageElement = $statusMessage->toXML();
        $this->assertEquals('Something went wrong', $statusMessageElement->textContent);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($statusMessage)
        );
    }


    /**
     */
    public function testUnmarshalling(): void
    {
        $statusMessage = StatusMessage::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals('Something went wrong', $statusMessage->getContent());
    }
}

