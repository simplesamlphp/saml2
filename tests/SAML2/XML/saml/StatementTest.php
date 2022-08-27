<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use DOMDocument;
use DOMElement;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\Test\SAML2\CustomStatement;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;

use function dirname;
use function strval;

/**
 * Class \SAML2\XML\saml\StatementTest
 *
 * @covers \SimpleSAML\SAML2\XML\saml\Statement
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractSamlElement
 * @package simplesamlphp/saml2
 */
final class StatementTest extends TestCase
{
    use SerializableXMLTestTrait;


    /**
     */
    protected function setUp(): void
    {
        $this->testedClass = CustomStatement::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/saml_Statement.xml'
        );
    }


    // marshalling


    /**
     */
    public function testMarshalling(): void
    {
        $statement = new CustomStatement('SomeStatement');

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($statement)
        );
    }


    // unmarshalling


    /**
     */
    public function testUnmarshallingCustomClass(): void
    {
        $statement = CustomStatement::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals('CustomStatement', $statement->getType());
        $this->assertEquals('SomeStatement', $statement->getValue());

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($statement)
        );
    }
}
