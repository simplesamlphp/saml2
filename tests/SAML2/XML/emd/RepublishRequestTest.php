<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\emd;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\emd\AbstractEmdElement;
use SimpleSAML\SAML2\XML\emd\RepublishRequest;
use SimpleSAML\SAML2\XML\emd\RepublishTarget;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\ArrayizableElementTestTrait;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\emd\RepublishRequest
 *
 * @package simplesamlphp/saml2
 */
#[Group('emd')]
#[CoversClass(RepublishRequest::class)]
#[CoversClass(AbstractEmdElement::class)]
final class RepublishRequestTest extends TestCase
{
    use ArrayizableElementTestTrait;
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = RepublishRequest::class;

        self::$arrayRepresentation = [
            'RepublishTarget' => 'http://edugain.org/',
        ];

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/emd_RepublishRequest.xml',
        );
    }


    /**
     * Marshalling
     */
    public function testMarshalling(): void
    {
        $republishRequest = new RepublishRequest(
            new RepublishTarget('http://edugain.org/'),
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($republishRequest),
        );
    }
}
