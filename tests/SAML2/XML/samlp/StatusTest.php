<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\samlp;

use DOMDocument;
use PHPUnit\Framework\Attributes\{CoversClass, Group};
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Type\{SAMLAnyURIValue, SAMLStringValue};
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\samlp\{AbstractSamlpElement, Status, StatusCode, StatusDetail, StatusMessage};
use SimpleSAML\XML\{Chunk, DOMDocumentFactory};
use SimpleSAML\XML\TestUtils\{SchemaValidationTestTrait, SerializableElementTestTrait};

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\samlp\StatusTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('samlp')]
#[CoversClass(Status::class)]
#[CoversClass(AbstractSamlpElement::class)]
final class StatusTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /** @var \DOMDocument $detail */
    private static DOMDocument $detail;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = Status::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/samlp_Status.xml',
        );

        self::$detail = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/samlp_StatusDetail.xml',
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $status = new Status(
            new StatusCode(
                SAMLAnyURIValue::fromString(C::STATUS_RESPONDER),
                [
                    new StatusCode(
                        SAMLAnyURIValue::fromString(C::STATUS_REQUEST_DENIED),
                    ),
                ],
            ),
            new StatusMessage(
                SAMLStringValue::fromString('Something went wrong'),
            ),
            [
                StatusDetail::fromXML(
                    DOMDocumentFactory::fromFile(
                        dirname(__FILE__, 4) . '/resources/xml/samlp_StatusDetail.xml',
                    )->documentElement,
                ),
            ],
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($status),
        );
    }


    /**
     */
    public function testMarshallingElementOrdering(): void
    {
        $status = new Status(
            new StatusCode(
                SAMLAnyURIValue::fromString(C::STATUS_RESPONDER),
                [
                    new StatusCode(
                        SAMLAnyURIValue::fromString(C::STATUS_REQUEST_DENIED),
                    ),
                ],
            ),
            new StatusMessage(
                SAMLStringValue::fromString('Something went wrong'),
            ),
            [
                new StatusDetail([new Chunk(self::$detail->documentElement)]),
            ],
        );

        $statusElement = $status->toXML();

        // Test for a StatusCode
        $xpCache = XPath::getXPath($statusElement);
        $statusElements = XPath::xpQuery($statusElement, './saml_protocol:StatusCode', $xpCache);
        $this->assertCount(1, $statusElements);

        // Test ordering of Status contents
        /** @psalm-var \DOMElement[] $statusElements */
        $statusElements = XPath::xpQuery($statusElement, './saml_protocol:StatusCode/following-sibling::*', $xpCache);
        $this->assertCount(2, $statusElements);
        $this->assertEquals('samlp:StatusMessage', $statusElements[0]->tagName);
        $this->assertEquals('samlp:StatusDetail', $statusElements[1]->tagName);
    }
}
