<?php

namespace SAML2\XML\mdrpi;

use SAML2\DOMDocumentFactory;
use SAML2\Utils;

/**
 * Class \SAML2\XML\mdrpi\RegistrationInfoTest
 */
class RegistrationInfoTest extends \PHPUnit_Framework_TestCase
{
    public function testMarshalling()
    {
        $registrationInfo = new RegistrationInfo();
        $registrationInfo->registrationAuthority = 'https://ExampleAuthority';
        $registrationInfo->registrationInstant = 1234567890;
        $registrationInfo->RegistrationPolicy = array(
            'en' => 'http://EnglishRegistrationPolicy',
            'nl' => 'https://DutchRegistratiebeleid',
        );

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

    public function testUnmarshalling()
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

        $this->assertEquals('urn:example:example.org', $registrationInfo->registrationAuthority);
        $this->assertEquals(1148902467, $registrationInfo->registrationInstant);
        $this->assertCount(2, $registrationInfo->RegistrationPolicy);
        $this->assertEquals('http://www.example.org/aai/metadata/en_registration.html', $registrationInfo->RegistrationPolicy["en"]);
        $this->assertEquals('http://www.example.org/aai/metadata/de_registration.html', $registrationInfo->RegistrationPolicy["de"]);
    }

    public function testMissingPublisherThrowsException()
    {
        $document = DOMDocumentFactory::fromString(<<<XML
<mdrpi:RegistrationInfo xmlns:mdrpi="urn:oasis:names:tc:SAML:metadata:rpi"
                       registrationInstant="2011-01-01T00:00:00Z">
</mdrpi:RegistrationInfo>
XML
        );

        $this->setExpectedException('Exception', 'Missing required attribute "registrationAuthority"');
        $registrationInfo = new RegistrationInfo($document->firstChild);
    }

    public function testEmptyRegistrationAuthorityOutboundThrowsException()
    {
        $registrationInfo = new RegistrationInfo();
        $registrationInfo->registrationAuthority = '';

        $document = DOMDocumentFactory::fromString('<root />');

        $this->setExpectedException('Exception', 'Missing required registration authority.');
        $xml = $registrationInfo->toXML($document->firstChild);
    }
}
