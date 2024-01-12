<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\emd;

use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\XML\emd\RepublishTarget;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\emd\RepublishTarget
 *
 * @covers \SimpleSAML\SAML2\XML\emd\RepublishTarget
 * @covers \SimpleSAML\SAML2\XML\emd\AbstractEmdElement
 * @package simplesamlphp/saml2
 */
final class RepublishTargetTest extends TestCase
{
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = RepublishTarget::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/emd_RepublishTarget.xml',
        );
    }


    /**
     * Marshalling
     */
    public function testMarshalling(): void
    {
        $republishTarget = new RepublishTarget('http://edugain.org/');

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($republishTarget),
        );
    }


    /**
     */
    public function testMarshallingIncorrectValueThrowsException(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage(
            'Expected a value identical to "http://edugain.org/". Got: "http://example.org/"',
        );

        new RepublishTarget('http://example.org/');
    }
}
