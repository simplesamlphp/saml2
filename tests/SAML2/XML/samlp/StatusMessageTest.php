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
    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $statusMessage = new StatusMessage('Something went horribly wrong');

        $this->assertEquals(
            strval($statusMessage),
            '<samlp:StatusMessage xmlns:samlp="' . Constants::NS_SAMLP
                . '">Something went horribly wrong</samlp:StatusMessage>'
        );
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $samlNamespace = Constants::NS_SAMLP;

        $document = DOMDocumentFactory::fromString(<<<XML
<samlp:StatusMessage xmlns:samlp="{$samlNamespace}">Something went horribly wrong</samlp:StatusMessage>
XML
        );

        /** @psalm-var \DOMElement $document->firstChild */
        $statusMessage = StatusMessage::fromXML($document->firstChild);
        $this->assertEquals('Something went horribly wrong', $statusMessage->getMessage());
    }
}

