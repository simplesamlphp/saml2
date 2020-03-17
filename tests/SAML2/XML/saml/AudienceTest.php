<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use PHPUnit\Framework\TestCase;

/**
 * Class \SAML2\XML\saml\AudienceTest
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
class AudienceTest extends TestCase
{
    /** @var \DOMDocument */
    private $document;


    /**
     * @return void
     */
    public function setUp(): void
    {
        $ns = Audience::NS;
        $this->document = DOMDocumentFactory::fromString(<<<XML
<saml:Audience xmlns:saml="{$ns}">urn:audience</saml:Audience>
XML
        );
    }


    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $audience = new Audience('urn:audience');

        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($audience)
        );
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $audience = Audience::fromXML($this->document->documentElement);
        $this->assertEquals('urn:audience', $audience->getAudience());
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(Audience::fromXML($this->document->documentElement))))
        );
    }
}
