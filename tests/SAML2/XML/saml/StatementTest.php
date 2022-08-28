<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use DOMDocument;
use DOMElement;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\SAML2\Compat\MockContainer;
use SimpleSAML\SAML2\XML\saml\Audience;
use SimpleSAML\Test\SAML2\CustomStatement;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;

use function dirname;
use function strval;

/**
 * Class \SAML2\XML\saml\StatementTest
 *
 * @covers \SimpleSAML\SAML2\XML\saml\Statement
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractStatementType
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

        $container = new MockContainer();
        $container->registerExtensionHandler(CustomStatement::class);
        ContainerSingleton::setContainer($container);
    }


    // marshalling


    /**
     */
    public function testMarshalling(): void
    {
        $statement = new CustomStatement(
            [new Audience('urn:some:audience')],
        );

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

        $this->assertEquals('ssp:CustomStatementType', $statement->getXsiType());
        $audience = $statement->getAudience();
        $this->assertCount(1, $audience);
        $this->assertEquals('urn:some:audience', $audience[0]->getContent());

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($statement)
        );
    }
}
