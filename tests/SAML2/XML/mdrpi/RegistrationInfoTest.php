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
use SimpleSAML\XML\TestUtils\ArrayizableElementTestTrait;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XML\Utils as XMLUtils;

use function dirname;
use function strval;

/**
 * Class \SAML2\XML\mdrpi\RegistrationInfoTest
 *
 * @covers \SimpleSAML\SAML2\XML\mdrpi\RegistrationInfo
 * @covers \SimpleSAML\SAML2\XML\mdrpi\AbstractMdrpiElement
 *
 * @package simplesamlphp/saml2
 */
final class RegistrationInfoTest extends TestCase
{
    use ArrayizableElementTestTrait;
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$schemaFile = dirname(__FILE__, 5) . '/resources/schemas/saml-metadata-rpi-v1.0.xsd';

        self::$testedClass = RegistrationInfo::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/mdrpi_RegistrationInfo.xml',
        );

        self::$arrayRepresentation = [
            'registrationAuthority' => 'https://ExampleAuthority',
            'registrationInstant' => '2011-01-01T00:00:00Z',
            'registrationPolicy' => [
                'en' => 'http://www.example.org/aai/metadata/en_registration.html',
                'de' => 'http://www.example.org/aai/metadata/de_registration.html',
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
            ],
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($registrationInfo),
        );
    }


    /**
     */
    public function testUnmarshalling(): void
    {
        $registrationInfo = RegistrationInfo::fromXML(self::$xmlRepresentation->documentElement);

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($registrationInfo),
        );
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
        $document = clone self::$xmlRepresentation->documentElement;
        $document->setAttribute('registrationInstant', '2011-01-01T00:00:00WT');

        $this->expectException(ProtocolViolationException::class);
        $this->expectExceptionMessage("'2011-01-01T00:00:00WT' is not a valid DateTime");
        RegistrationInfo::fromXML($document);
    }


    /**
     */
    public function testMultipleRegistrationPoliciesWithSameLanguageThrowsException(): void
    {
        $document = clone self::$xmlRepresentation;

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
