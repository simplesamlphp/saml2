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

        /** @psalm-var \SAML2\XML\Chunk[] $statusDetailElement */
        $statusDetailElement = $statusDetail->getDetails();
        $statusDetailElement = $statusDetailElement[0]->getXML();

        $this->assertEquals('Cause', $statusDetailElement->tagName);
        $this->assertEquals('org.sourceid.websso.profiles.idp.FailedAuthnSsoException', $statusDetailElement->textContent);
    }


    /**
     * Serialize an StatusDetail and Unserialize that again.
     * @return void
     */
    public function testSerialize(): void
    {
        $samlNamespace = Constants::NS_SAMLP;

        $document1 = DOMDocumentFactory::fromString(<<<XML
            <samlp:StatusDetail xmlns:samlp="{$samlNamespace}">
                <Cause>org.sourceid.websso.profiles.idp.FailedAuthnSsoException</Cause>
            </samlp:StatusDetail>
XML
        );

        $document2 = DOMDocumentFactory::fromString(<<<XML
            <samlp:StatusDetail xmlns:samlp="{$samlNamespace}">Some<Detail>no one cares</Detail> about</samlp:StatusDetail>
XML
        );

        $statusDetail1 = new StatusDetail($document1->documentElement->childNodes);
        $ser = $statusDetail1->serialize();

        $statusDetail2 = new StatusDetail($document2->documentElement->childNodes);
        $statusDetail2->unserialize($ser);

        /**
         * @psalm-var \DOMElement $statusDetailElement->childNodes[1]
         */
        $statusDetailElement = $statusDetail2->toXML();

        $this->assertEquals('Cause', $statusDetailElement->childNodes[1]->tagName);
    }
}

