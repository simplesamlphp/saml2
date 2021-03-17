<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\mdrpi;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\XML\mdrpi\RegistrationInfo;
use SimpleSAML\SAML2\XML\mdrpi\RegistrationPolicy;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XML\Utils as XMLUtils;

/**
 * Class \SAML2\XML\mdrpi\RegistrationInfoTest
 *
 * @covers \SimpleSAML\SAML2\XML\mdrpi\RegistrationInfo
 * @covers \SimpleSAML\SAML2\XML\mdrpi\AbstractMdrpiElement
 * @package simplesamlphp/saml2
 */
final class RegistrationInfoTest extends TestCase
{
    /** @var \DOMDocument */
    protected DOMDocument $document;


    /**
     */
    protected function setUp(): void
    {
        $this->document = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/mdrpi_RegistrationInfo.xml'
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $registrationInfo = new RegistrationInfo(
            'https://ExampleAuthority',
            1234567890,
            [
                new RegistrationPolicy('en', 'http://EnglishRegistrationPolicy'),
                new RegistrationPolicy('nl', 'https://DutchRegistratiebeleid'),
            ]
        );

        $document = DOMDocumentFactory::fromString('<root />');
        $xml = $registrationInfo->toXML($document->documentElement);

        /** @var \DOMElement[] $registrationInfoElements */
        $registrationInfoElements = XMLUtils::xpQuery(
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
        $usagePolicyElements = XMLUtils::xpQuery(
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
     */
    public function testUnmarshalling(): void
    {
        $registrationInfo = RegistrationInfo::fromXML($this->document->documentElement);

        $this->assertEquals('urn:example:example.org', $registrationInfo->getRegistrationAuthority());
        $this->assertEquals(1148902467, $registrationInfo->getRegistrationInstant());

        $registrationPolicy = $registrationInfo->getRegistrationPolicy();
        $this->assertCount(2, $registrationPolicy);
        $this->assertEquals('http://www.example.org/aai/metadata/en_registration.html', $registrationPolicy[0]->getValue());
        $this->assertEquals('en', $registrationPolicy[0]->getLanguage());
        $this->assertEquals('http://www.example.org/aai/metadata/de_registration.html', $registrationPolicy[1]->getValue());
        $this->assertEquals('de', $registrationPolicy[1]->getLanguage());
    }


    /**
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
     */
    public function testRegistrationInstantTimezoneNotZuluThrowsException(): void
    {
        $document = $this->document->documentElement;
        $document->setAttribute('registrationInstant', '2011-01-01T00:00:00WT');

        $this->expectException(ProtocolViolationException::class);
        $this->expectExceptionMessage(
            "Time values MUST be expressed in the UTC timezone using the 'Z' timezone identifier."
        );
        RegistrationInfo::fromXML($document);
    }


    /**
     */
    public function testMultipleRegistrationPoliciesWithSameLanguageThrowsException(): void
    {
        $document = $this->document;

        // Append another 'en' RegistrationPolicy to the document
        $x = new RegistrationPolicy('en', 'https://example.org');
        $x->toXML($document->documentElement);

        $this->expectException(ProtocolViolationException::class);
        $this->expectExceptionMessage(
            'There MUST NOT be more than one <mdrpi:RegistrationPolicy>,'
            . ' within a given <mdrpi:RegistrationInfo>, for a given language'
        );
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
