<?php

declare(strict_types=1);

namespace SAML2\XML\samlp;

use SAML2\Constants;
use SAML2\DOMDocumentFactory;

/**
 * Class \SAML2\XML\samlp\StatusDetailTest
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
class StatusDetailTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        /** @psalm-var \DOMElement $document->firstChild */
        $document = DOMDocumentFactory::fromString(
            '<Cause>org.sourceid.websso.profiles.idp.FailedAuthnSsoException</Cause>'
        );

        $statusDetail = StatusDetail::fromXML($document->firstChild);

        /** @psalm-var \DOMElement $statusDetailElement->firstChild */
        $statusDetailElement = $statusDetail->toXML();

        $this->assertEquals('Cause', $statusDetailElement->firstChild->tagName);
        $this->assertEquals(
            'org.sourceid.websso.profiles.idp.FailedAuthnSsoException',
            $statusDetailElement->firstChild->textContent
        );
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $document = DOMDocumentFactory::fromString(
            '<Cause>org.sourceid.websso.profiles.idp.FailedAuthnSsoException</Cause>'
        );

        /** @psalm-var \DOMElement $document->firstChild */
        $statusDetail = StatusDetail::fromXML($document->firstChild);

        /** @psalm-var \DOMElement $statusDetailElement */
        $statusDetailElement = $statusDetail->getDetail();
        $this->assertEquals('Cause', $statusDetailElement->tagName);
        $this->assertEquals('org.sourceid.websso.profiles.idp.FailedAuthnSsoException', $statusDetailElement->textContent);
    }
}

