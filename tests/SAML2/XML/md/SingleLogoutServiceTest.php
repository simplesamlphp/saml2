<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\XML\md\AbstractMdElement;
use SimpleSAML\SAML2\XML\md\SingleLogoutService;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\XML\Attribute as XMLAttribute;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XMLSchema\Type\BooleanValue;

use function dirname;
use function strval;

/**
 * Tests for md:SingleLogoutService.
 *
 * @package simplesamlphp/saml2
 */
#[Group('md')]
#[CoversClass(SingleLogoutService::class)]
#[CoversClass(AbstractMdElement::class)]
final class SingleLogoutServiceTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = SingleLogoutService::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/md_SingleLogoutService.xml',
        );
    }


    // test marshalling


    /**
     * Test creating a SingleLogoutService from scratch.
     */
    public function testMarshalling(): void
    {
        $supportsAsynchronous = new XMLAttribute(
            C::NS_ASLO,
            'aslo',
            'supportsAsynchronous',
            BooleanValue::fromBoolean(true),
        );

        $sloep = new SingleLogoutService(
            SAMLAnyURIValue::fromString(C::BINDING_HTTP_POST),
            SAMLAnyURIValue::fromString(C::LOCATION_A),
            SAMLAnyURIValue::fromString(C::LOCATION_B),
            [$supportsAsynchronous],
        );

        $expectedXml = self::$xmlRepresentation->saveXml(self::$xmlRepresentation->documentElement);
        $this->assertNotFalse($expectedXml);
        $actualXml = strval($sloep);

        $this->assertXmlStringEqualsXmlString($expectedXml, $actualXml);
    }
}
