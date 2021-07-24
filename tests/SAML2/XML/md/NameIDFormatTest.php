<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use DOMDocument;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\XML\md\NameIDFormat;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\md\NameIDFormatTest
 *
 * @covers \SimpleSAML\SAML2\XML\md\NameIDFormat
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMdElement
 * @package simplesamlphp/saml2
 */
final class NameIDFormatTest extends TestCase
{
    use SerializableXMLTestTrait;


    /**
     */
    protected function setUp(): void
    {
        $this->testedClass = NameIDFormat::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/md_NameIDFormat.xml'
        );
    }


    // marshalling


    /**
     */
    public function testMarshalling(): void
    {
        $nameIdFormat = new NameIDFormat(Constants::NAMEID_PERSISTENT);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($nameIdFormat)
        );
    }


    // unmarshalling


    /**
     */
    public function testUnmarshalling(): void
    {
        $nameIdFormat = NameIDFormat::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals(Constants::NAMEID_PERSISTENT, $nameIdFormat->getContent());
    }
}
