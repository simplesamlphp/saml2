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
    xsi:type="CustomStatement">
  <Statement>SomeStatement</Statement>
</saml:Statement>
XML
        );
    }


    // marshalling


    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $statement = new CustomStatement(
            new DOMElement('Statement', 'SomeStatement')
        );

        $this->assertEquals('CustomStatement', $statement->getType());

        $value = $statement->getValue();
        $this->assertInstanceOf(DOMElement::class, $value);
        $this->assertEquals('Statement', $value->localName);
        $this->assertEquals('SomeStatement', $value->textContent);

        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($statement)
        );
    }


    // unmarshalling


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $statement = CustomStatement::fromXML($this->document->documentElement);
        $this->assertEquals('CustomStatement', $statement->getType());

        $value = $statement->getValue();
        $this->assertInstanceOf(DOMElement::class, $value);
        $this->assertEquals('Statement', $value->localName);
        $this->assertEquals('SomeStatement', trim($value->textContent));

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
