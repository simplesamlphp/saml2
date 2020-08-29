<?php

declare(strict_types=1);

namespace SAML2\XML\mdrpi;

use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\DOMDocumentFactory;
use SimpleSAML\SAML2\Exception\MissingAttributeException;
use SimpleSAML\SAML2\Utils;

/**
 * Class \SAML2\XML\mdrpi\RegistrationInfoTest
 *
 * @covers \SimpleSAML\SAML2\XML\mdrpi\RegistrationInfo
 * @package simplesamlphp/saml2
 */
final class RegistrationInfoTest extends TestCase
{
    /** @var \DOMDocument */
    protected $document;


    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->document = DOMDocumentFactory::fromString(<<<XML
<mdrpi:RegistrationInfo xmlns:mdrpi="urn:oasis:names:tc:SAML:metadata:rpi"
    registrationAuthority="urn:example:example.org"
    registrationInstant="2006-05-29T11:34:27Z">
  <mdrpi:RegistrationPolicy
      xml:lang="en">http://www.example.org/aai/metadata/en_registration.html</mdrpi:RegistrationPolicy>
  <mdrpi:RegistrationPolicy
      xml:lang="de">http://www.example.org/aai/metadata/de_registration.html</mdrpi:RegistrationPolicy>
</mdrpi:RegistrationInfo>
XML
        );
    }


    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $registrationInfo = new RegistrationInfo(
            'https://ExampleAuthority',
            1234567890,
            [
                'en' => 'http://EnglishRegistrationPolicy',
                'nl' => 'https://DutchRegistratiebeleid',
            ]
        );

        $document = DOMDocumentFactory::fromString('<root />');
        $xml = $registrationInfo->toXML($document->documentElement);

        /** @var \DOMElement[] $registrationInfoElements */
        $registrationInfoElements = Utils::xpQuery(
            $xml,
            '/root/*[local-name()=\'RegistrationInfo\' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:rpi\']'
        );
        $this->assertCount(1, $registrationInfoElements);
        $registrationInfoElement = $registrationInfoElements[0];

        $this->assertEquals(
            'https://ExampleAuthority',
            $registrationInfoElement->getAttribute("registrationAuthority")
        );
        $this->assertEquals('2009-02-13T23:31:30Z', $registrationInfoElement->getAttribute("registrationInstant"));

        /** @var \DOMElement[] $usagePolicyElements */
        $usagePolicyElements = Utils::xpQuery(
            $registrationInfoElement,
            './*[local-name()=\'RegistrationPolicy\' and namespace-uri()=\'urn:oasis:names:tc:SAML:metadata:rpi\']'
        );
        $this->assertCount(2, $usagePolicyElements);

        $this->assertEquals(
            'en',
            $usagePolicyElements[0]->getAttributeNS("http://www.w3.org/XML/1998/namespace", "lang")
        );
        $this->assertEquals('http://EnglishRegistrationPolicy', $usagePolicyElements[0]->textContent);
        $this->assertEquals(
            'nl',
            $usagePolicyElements[1]->getAttributeNS("http://www.w3.org/XML/1998/namespace", "lang")
        );
        $this->assertEquals('https://DutchRegistratiebeleid', $usagePolicyElements[1]->textContent);
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $registrationInfo = RegistrationInfo::fromXML($this->document->documentElement);

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

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage("Missing 'registrationAuthority' attribute on mdrpi:RegistrationInfo.");
        RegistrationInfo::fromXML($document->documentElement);
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(RegistrationInfo::fromXML($this->document->documentElement))))
        );
    }
}
