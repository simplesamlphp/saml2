<?php

declare(strict_types=1);

namespace SAML2\XML\samlp;

use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\XML\Chunk;

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
            '<samlp:StatusDetail xmlns:samlp="' . Constants::NS_SAMLP . '">'
                . '<Cause>org.sourceid.websso.profiles.idp.FailedAuthnSsoException</Cause>'
                . '</samlp:StatusDetail>'
        );

        $statusDetail = new StatusDetail($document->documentElement->childNodes);

        $this->assertEquals(
            '<samlp:StatusDetail xmlns:samlp="' . Constants::NS_SAMLP
                . '"><Cause>org.sourceid.websso.profiles.idp.FailedAuthnSsoException</Cause></samlp:StatusDetail>',
            strval($statusDetail)
        );
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $samlNamespace = Constants::NS_SAMLP;

        $document = DOMDocumentFactory::fromString(<<<XML
            <samlp:StatusDetail xmlns:samlp="{$samlNamespace}">
                <Cause>org.sourceid.websso.profiles.idp.FailedAuthnSsoException</Cause>
            </samlp:StatusDetail>
XML
        );

        /** @psalm-var \DOMElement $document->firstChild */
        $statusDetail = StatusDetail::fromXML($document->firstChild);

        /** @psalm-var \DOMNodeList $statusDetailElement */
        $statusDetailElement = $statusDetail->getDetail();

        /** @psalm-var \DOMElement $statusDetailElement */
        $statusDetailElement = $statusDetailElement[1];

        $this->assertEquals('Cause', $statusDetailElement->tagName);
        $this->assertEquals('org.sourceid.websso.profiles.idp.FailedAuthnSsoException', $statusDetailElement->textContent);
    }
}

