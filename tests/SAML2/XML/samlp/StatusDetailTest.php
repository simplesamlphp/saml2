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
            '<Cause>org.sourceid.websso.profiles.idp.FailedAuthnSsoException</Cause>'
        );

        $statusDetail = new StatusDetail([new Chunk($document->documentElement)]);

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
        $document = DOMDocumentFactory::fromString(
            '<samlp:StatusDetail xmlns:samlp="' . Constants::NS_SAMLP . '">'
                . '<Cause>org.sourceid.websso.profiles.idp.FailedAuthnSsoException</Cause>'
                . '</samlp:StatusDetail>'
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

        $document1 = DOMDocumentFactory::fromString(
            '<Cause>org.sourceid.websso.profiles.idp.FailedAuthnSsoException</Cause>'
        );

        $statusDetail1 = new StatusDetail([new Chunk($document1->documentElement)]);
        $statusDetail2 = unserialize(serialize($statusDetail1));

        $this->assertEquals(strval($statusDetail1), strval($statusDetail2));
    }
}

