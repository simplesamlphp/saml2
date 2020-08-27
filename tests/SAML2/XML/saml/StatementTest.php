<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use DOMElement;
use PHPUnit\Framework\TestCase;
use SAML2\Constants;
use SAML2\CustomStatement;
use SAML2\DOMDocumentFactory;
use SimpleSAML\Assert\AssertionFailedException;

/**
 * Class \SAML2\XML\saml\StatementTest
 *
 * @covers \SAML2\XML\saml\Statement
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
        $samlNamespace = Statement::NS;
        $xsiNamespace = Constants::NS_XSI;

        $this->document = DOMDocumentFactory::fromString(<<<XML
<saml:Statement
    xmlns:saml="{$samlNamespace}"
    xmlns:xsi="{$xsiNamespace}"
    xsi:type="CustomStatement">SomeStatement</saml:Statement>
XML
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
