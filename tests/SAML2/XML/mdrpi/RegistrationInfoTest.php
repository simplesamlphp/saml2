<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\mdrpi;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\XML\mdrpi\RegistrationInfo;
use SimpleSAML\SAML2\XML\mdrpi\RegistrationPolicy;
use SimpleSAML\Test\XML\ArrayizableXMLTestTrait;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
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
    use ArrayizableXMLTestTrait;
    use SerializableXMLTestTrait;


    /**
     */
    protected function setUp(): void
    {
        $this->testedClass = RegistrationInfo::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/mdrpi_RegistrationInfo.xml'
        );

        $this->arrayRepresentation = [
            'registrationAuthority' => 'https://ExampleAuthority',
            'registrationInstant' => 1234567890,
            'registrationPolicy' => [
                'en' => 'http://www.example.org/aai/metadata/en_registration.html',
                'de' => 'http://www.example.org/aai/metadata/de_registration.html'
            ],
        ];
    }


    /**
     */
    public function testMarshalling(): void
    {
        $registrationInfo = new RegistrationInfo(
            'https://ExampleAuthority',
            1234567890,
            [
                new RegistrationPolicy('en', 'http://www.example.org/aai/metadata/en_registration.html'),
                new RegistrationPolicy('de', 'http://www.example.org/aai/metadata/de_registration.html'),
            ]
        );

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($registrationInfo)
        );
    }


    /**
     */
    public function testUnmarshalling(): void
    {
        $registrationInfo = RegistrationInfo::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals('https://ExampleAuthority', $registrationInfo->getRegistrationAuthority());
        $this->assertEquals(1234567890, $registrationInfo->getRegistrationInstant());

        $registrationPolicy = $registrationInfo->getRegistrationPolicy();
        $this->assertCount(2, $registrationPolicy);
        $this->assertEquals(
            'http://www.example.org/aai/metadata/en_registration.html',
            $registrationPolicy[0]->getValue()
        );
        $this->assertEquals('en', $registrationPolicy[0]->getLanguage());
        $this->assertEquals(
            'http://www.example.org/aai/metadata/de_registration.html',
            $registrationPolicy[1]->getValue()
        );
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
        $document = $this->xmlRepresentation->documentElement;
        $document->setAttribute('registrationInstant', '2011-01-01T00:00:00WT');

        $this->expectException(ProtocolViolationException::class);
        $this->expectExceptionMessage("'2011-01-01T00:00:00WT' is not a valid DateTime");
        RegistrationInfo::fromXML($document);
    }


    /**
     */
    public function testMultipleRegistrationPoliciesWithSameLanguageThrowsException(): void
    {
        $document = $this->xmlRepresentation;

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
}
