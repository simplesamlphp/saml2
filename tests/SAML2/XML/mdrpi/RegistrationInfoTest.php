<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\mdrpi;

use DOMDocument;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\Type\EntityIDValue;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\Type\SAMLDateTimeValue;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\SAML2\XML\md\AffiliationDescriptor;
use SimpleSAML\SAML2\XML\md\EntitiesDescriptor;
use SimpleSAML\SAML2\XML\md\EntityDescriptor;
use SimpleSAML\SAML2\XML\md\Extensions;
use SimpleSAML\SAML2\XML\mdrpi\AbstractMdrpiElement;
use SimpleSAML\SAML2\XML\mdrpi\RegistrationInfo;
use SimpleSAML\SAML2\XML\mdrpi\RegistrationPolicy;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\ArrayizableElementTestTrait;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XML\Type\LangValue;
use SimpleSAML\XMLSchema\Exception\MissingAttributeException;

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


    /** @var \DOMDocument */
    private static DOMDocument $affiliationDescriptor;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = RegistrationInfo::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/mdrpi_RegistrationInfo.xml',
        );

        self::$affiliationDescriptor = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/md_AffiliationDescriptor.xml',
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
            SAMLStringValue::fromString('https://ExampleAuthority'),
            SAMLDateTimeValue::fromString('2009-02-13T23:31:30Z'),
            [
                new RegistrationPolicy(
                    LangValue::fromString('en'),
                    SAMLAnyURIValue::fromString('http://www.example.org/aai/metadata/en_registration.html'),
                ),
                new RegistrationPolicy(
                    LangValue::fromString('de'),
                    SAMLAnyURIValue::fromString('http://www.example.org/aai/metadata/de_registration.html'),
                ),
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
    public function testMultipleRegistrationPoliciesWithSameLanguageThrowsException(): void
    {
        $document = clone self::$xmlRepresentation->documentElement;

        // Append another 'en' RegistrationPolicy to the document
        $x = new RegistrationPolicy(
            LangValue::fromString('en'),
            SAMLAnyURIValue::fromString('https://example.org'),
        );
        $x->toXML($document);

        $this->expectException(ProtocolViolationException::class);
        $this->expectExceptionMessage(
            'There MUST NOT be more than one <mdrpi:RegistrationPolicy>,'
            . ' within a given <mdrpi:RegistrationInfo>, for a given language',
        );
        RegistrationInfo::fromXML($document);
    }


    /**
     */
    public function testNestedRegistrationInfoThrowsException(): void
    {
        $registrationInfo = RegistrationInfo::fromXML(self::$xmlRepresentation->documentElement);
        $extensions = new Extensions([$registrationInfo]);

        $ed = new EntityDescriptor(
            entityId: EntityIDValue::fromString('urn:x-simplesamlphp:entity'),
            affiliationDescriptor: AffiliationDescriptor::fromXML(self::$affiliationDescriptor->documentElement),
        );

        $this->expectException(ProtocolViolationException::class);
        new EntitiesDescriptor(
            extensions: $extensions,
            entitiesDescriptors: [
                new EntitiesDescriptor(
                    extensions: $extensions,
                    entityDescriptors: [$ed],
                ),
            ],
        );
    }
}
