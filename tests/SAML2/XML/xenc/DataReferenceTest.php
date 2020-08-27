<?php

declare(strict_types=1);

namespace SAML2\XML\xenc;

use PHPUnit\Framework\TestCase;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;

/**
 * Class \SAML2\XML\xenc\DataReferenceTest
 *
 * @covers \SAML2\XML\xenc\AbstractReference
 * @covers \SAML2\XML\xenc\DataReference
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
final class DataReferenceTest extends TestCase
{
    /** @var \DOMDocument $document */
    private $document;


    /**
     * @return void
     */
    public function setup(): void
    {
        $xencNamespace = Constants::NS_XENC;

        $this->document = DOMDocumentFactory::fromString(<<<XML
<xenc:DataReference xmlns:xenc="{$xencNamespace}" URI="#Encrypted_DATA_ID"/>
XML
        );
    }


    // marshalling


    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $dataReference = new DataReference('#Encrypted_DATA_ID');

        $this->assertEquals('#Encrypted_DATA_ID', $dataReference->getURI());

        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($dataReference)
        );
    }


    // unmarshalling


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $dataReference = DataReference::fromXML($this->document->documentElement);

        $this->assertEquals('#Encrypted_DATA_ID', $dataReference->getURI());

        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($dataReference)
        );
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(DataReference::fromXML($this->document->documentElement))))
        );
    }
}
