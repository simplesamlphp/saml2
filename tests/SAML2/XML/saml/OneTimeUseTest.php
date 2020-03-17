<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use PHPUnit\Framework\TestCase;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;

/**
 * Class \SAML2\XML\saml\OneTimeUseTest
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
final class OneTimeUseTest extends TestCase
{
    /** @var \DOMDocument $document */
    private $document;


    /**
     * @return void
     */
    public function setup(): void
    {
        $samlNamespace = OneTimeUse::NS;

        $this->document = DOMDocumentFactory::fromString(<<<XML
<saml:OneTimeUse xmlns:saml="{$samlNamespace}"/>
XML
        );
    }


    // marshalling


    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $condition = new OneTimeUse();

        $this->assertEquals('', $condition->getValue());

        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($condition)
        );
    }


    // unmarshalling


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $condition = OneTimeUse::fromXML($this->document->documentElement);

        $this->assertEquals('', $condition->getValue());

        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($condition)
        );
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(OneTimeUse::fromXML($this->document->documentElement))))
        );
    }
}
