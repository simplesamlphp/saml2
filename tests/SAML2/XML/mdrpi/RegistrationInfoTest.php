<?php

declare(strict_types=1);

namespace SAML2\XML\mdrpi;

use SAML2\DOMDocumentFactory;
use SAML2\XML\mdrpi\RegistrationInfo;
use SAML2\Utils;

/**
 * Class \SAML2\XML\mdrpi\RegistrationInfoTest
 */
class RegistrationInfoTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $registrationInfo = new RegistrationInfo();
        $registrationInfo->setRegistrationAuthority('https://ExampleAuthority');
        $registrationInfo->setRegistrationInstant(1234567890);
        $registrationInfo->setRegistrationPolicy([
            'en' => 'http://EnglishRegistrationPolicy',
            'nl' => 'https://DutchRegistratiebeleid',
        ]);

        $document = DOMDocumentFactory::fromString('<root />');
        $xml = $registrationInfo->toXML($document->firstChild);

        $registrationInfoElements = Utils::xpQuery(
            $xml,
            '/root/*[local-name()=\'RegistrationInfo\' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:rpi\']'
        );
        $this->assertCount(1, $registrationInfoElements);
        $registrationInfoElement = $registrationInfoElements[0];

        $this->assertEquals('https://ExampleAuthority', $registrationInfoElement->getAttribute("registrationAuthority"));
        $this->assertEquals('2009-02-13T23:31:30Z', $registrationInfoElement->getAttribute("registrationInstant"));

        $usagePolicyElements = Utils::xpQuery(
            $registrationInfoElement,
            './*[local-name()=\'RegistrationPolicy\' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:rpi\']'
        );
        $this->assertCount(2, $usagePolicyElements);

        $this->assertEquals('en', $usagePolicyElements[0]->getAttributeNS("http://www.w3.org/XML/1998/namespace", "lang"));
        $this->assertEquals('http://EnglishRegistrationPolicy', $usagePolicyElements[0]->textContent);
        $this->assertEquals('nl', $usagePolicyElements[1]->getAttributeNS("http://www.w3.org/XML/1998/namespace", "lang"));
        $this->assertEquals('https://DutchRegistratiebeleid', $usagePolicyElements[1]->textContent);
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $document = DOMDocumentFactory::fromString(<<<XML
<mdrpi:RegistrationInfo xmlns:mdrpi="urn:oasis:names:tc:SAML:metadata:rpi"
                        registrationAuthority="urn:example:example.org"
                        registrationInstant="2006-05-29T11:34:27Z">
        <mdrpi:RegistrationPolicy xml:lang="en">
          http://www.example.org/aai/metadata/en_registration.html
        </mdrpi:RegistrationPolicy>
        <mdrpi:RegistrationPolicy xml:lang="de">
          http://www.example.org/aai/metadata/de_registration.html
        </mdrpi:RegistrationPolicy>
</mdrpi:RegistrationInfo>
XML
        );

        $registrationInfo = new RegistrationInfo($document->firstChild);

        $this->assertEquals('urn:example:example.org', $registrationInfo->getRegistrationAuthority());
        $this->assertEquals(1148902467, $registrationInfo->getRegistrationInstant());

        $registrationPolicy = $registrationInfo->getRegistrationPolicy();
        $this->assertCount(2, $registrationPolicy);
        $this->assertEquals('http://www.example.org/aai/metadata/en_registration.html', $registrationPolicy["en"]);
        $this->assertEquals('http://www.example.org/aai/metadata/de_registration.html', $registrationPolicy["de"]);
    }


    /**
     * @return void
     */
    public function testMissingPublisherThrowsException(): void
    {
        $document = DOMDocumentFactory::fromString(<<<XML
<mdrpi:RegistrationInfo xmlns:mdrpi="urn:oasis:names:tc:SAML:metadata:rpi"
                       registrationInstant="2011-01-01T00:00:00Z">
</mdrpi:RegistrationInfo>
XML
        );

        $this->expectException(\Exception::class, 'Missing required attribute "registrationAuthority"');
        $registrationInfo = new RegistrationInfo($document->firstChild);
    }


    /**
     * @return void
     */
    public function testEmptyRegistrationAuthorityOutboundThrowsException(): void
    {
        $registrationInfo = new RegistrationInfo();
        $registrationInfo->setRegistrationAuthority('');

        $document = DOMDocumentFactory::fromString('<root />');

        $this->expectException(\Exception::class, 'Missing required registration authority.');
        $xml = $registrationInfo->toXML($document->firstChild);
    }
}
