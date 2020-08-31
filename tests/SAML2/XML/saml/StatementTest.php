<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\CustomStatement;
use SimpleSAML\SAML2\DOMDocumentFactory;
use SimpleSAML\Assert\AssertionFailedException;

/**
 * Class \SAML2\XML\saml\StatementTest
 *
 * @covers \SimpleSAML\SAML2\XML\saml\Statement
 * @package simplesamlphp/saml2
 */
final class StatementTest extends TestCase
{
    /** @var \DOMDocument */
    private $document;


    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->document = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/saml_Statement.xml'
        );
    }


    // marshalling


    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $statement = new CustomStatement('SomeStatement');

        $this->assertEquals('CustomStatement', $statement->getType());
        $this->assertEquals('SomeStatement', $statement->getValue());

        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($statement)
        );
    }


    // unmarshalling


    /**
     * @return void
     */
    public function testUnmarshallingCustomClass(): void
    {
        $statement = CustomStatement::fromXML($this->document->documentElement);

        $this->assertEquals('CustomStatement', $statement->getType());
        $this->assertEquals('SomeStatement', $statement->getValue());

        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($statement)
        );
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(CustomStatement::fromXML($this->document->documentElement))))
        );
    }
}
