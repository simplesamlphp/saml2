<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\emd;

use DOMDocument;
use PHPUnit\Framework\TestCase;
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
 * @covers \SimpleSAML\SAML2\XML\emd\RepublishRequest
 * @covers \SimpleSAML\SAML2\XML\emd\AbstractEmdElement
 * @package simplesamlphp/saml2
 */
final class RepublishRequestTest extends TestCase
{
    use ArrayizableElementTestTrait;
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public function setUp(): void
    {
        $this->schema = dirname(__FILE__, 5) . '/resources/schemas/eduidmd.xsd';

        $this->testedClass = RepublishRequest::class;

        $this->arrayRepresentation = [
            'RepublishTarget' => 'http://edugain.org/',
        ];

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
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
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($republishRequest),
        );
    }


    /**
     * Unmarshalling
     */
    public function testUnmarshalling(): void
    {
        $republishRequest = RepublishRequest::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($republishRequest),
        );
    }
}
