<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\mdrpi;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\XML\mdrpi\AbstractMdrpiElement;
use SimpleSAML\SAML2\XML\mdrpi\RegistrationInfo;
use SimpleSAML\SAML2\XML\mdrpi\RegistrationPolicy;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XML\TestUtils\ArrayizableElementTestTrait;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\mdrpi\RegistrationInfoTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('mdrpi')]
#[CoversClass(RegistrationInfo::class)]
#[CoversClass(AbstractMdrpiElement::class)]
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
            'RegistrationPolicy' => [
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
            new DateTimeImmutable('2009-02-13T23:31:30Z'),
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
    public function testMissingPublisherThrowsException(): void
    {
        $document = DOMDocumentFactory::fromString(
            <<<XML
<mdrpi:RegistrationInfo xmlns:mdrpi="urn:oasis:names:tc:SAML:metadata:rpi"
                       registrationInstant="2011-01-01T00:00:00Z">
</mdrpi:RegistrationInfo>
XML
            ,
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
        $this->expectExceptionMessage(
            "\"2011-01-01T00:00:00WT\" is not a DateTime expressed in the UTC timezone "
            . "using the 'Z' timezone identifier.",
        );
        RegistrationInfo::fromXML($document);
    }


    /**
     */
    public function testMultipleRegistrationPoliciesWithSameLanguageThrowsException(): void
    {
        $document = clone self::$xmlRepresentation->documentElement;

        // Append another 'en' RegistrationPolicy to the document
        $x = new RegistrationPolicy('en', 'https://example.org');
        $x->toXML($document);

        $this->expectException(ProtocolViolationException::class);
        $this->expectExceptionMessage(
            'There MUST NOT be more than one <mdrpi:RegistrationPolicy>,'
            . ' within a given <mdrpi:RegistrationInfo>, for a given language',
        );
        RegistrationInfo::fromXML($document);
    }
}
