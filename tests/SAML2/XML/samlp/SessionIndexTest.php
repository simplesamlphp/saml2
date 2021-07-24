<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Test\SAML2\XML\samlp;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\samlp\SessionIndex;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\samlp\SessionIndexTest
 *
 * @covers \SimpleSAML\SAML2\XML\samlp\SessionIndex
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractSamlpElement
 *
 * @package simplesamlphp/saml2
 */
final class SessionIndexTest extends TestCase
{
    use SerializableXMLTestTrait;

    /**
     */
    protected function setUp(): void
    {
        $this->testedClass = SessionIndex::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/samlp_SessionIndex.xml'
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $sessionIndex = new SessionIndex('SomeSessionIndex1');

        $sessionIndexElement = $sessionIndex->toXML();
        $this->assertEquals('SomeSessionIndex1', $sessionIndexElement->textContent);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($sessionIndex)
        );
    }


    /**
     */
    public function testUnmarshalling(): void
    {
        $sessionIndex = SessionIndex::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals('SomeSessionIndex1', $sessionIndex->getContent());
    }
}

