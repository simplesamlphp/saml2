<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use DOMDocument;
use DOMElement;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\SAML2\Compat\MockContainer;
use SimpleSAML\SAML2\XML\saml\AbstractStatement;
use SimpleSAML\SAML2\XML\saml\Audience;
use SimpleSAML\SAML2\XML\saml\UnknownStatement;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\Test\SAML2\CustomStatement;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

use function dirname;
use function strval;

/**
 * Class \SAML2\XML\saml\StatementTest
 *
 * @covers \SimpleSAML\SAML2\XML\saml\UnknownStatement
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractStatement
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractStatementType
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractSamlElement
 * @package simplesamlphp/saml2
 */
final class StatementTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;

    /**
     */
    protected function setUp(): void
    {
        $this->schema = dirname(__FILE__, 5) . '/resources/schemas/simplesamlphp.xsd';

        $this->testedClass = CustomStatement::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 5) . '/resources/xml/saml_Statement.xml',
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
            strval($statement),
        );
    }


    // unmarshalling


    /**
     */
    public function testUnmarshallingRegistered(): void
    {
        $statement = CustomStatement::fromXML($this->xmlRepresentation->documentElement);
        $this->assertInstanceOf(CustomStatement::class, $statement);

        $this->assertEquals('ssp:CustomStatementType', $statement->getXsiType());
        $audience = $statement->getAudience();
        $this->assertCount(1, $audience);
        $this->assertEquals('urn:some:audience', $audience[0]->getContent());

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($statement),
        );
    }


    /**
     */
    public function testUnmarshallingUnregistered(): void
    {
        $element = $this->xmlRepresentation->documentElement;
        $element->setAttributeNS(C::NS_XSI, 'xsi:type', 'ssp:UnknownStatementType');

        $statement = AbstractStatement::fromXML($element);

        $this->assertInstanceOf(UnknownStatement::class, $statement);
        $this->assertEquals('urn:x-simplesamlphp:namespace:UnknownStatementType', $statement->getXsiType());

        $chunk = $statement->getRawStatement();
        $this->assertEquals('saml', $chunk->getPrefix());
        $this->assertEquals('Statement', $chunk->getLocalName());
        $this->assertEquals(C::NS_SAML, $chunk->getNamespaceURI());

        $this->assertEquals($element->ownerDocument?->saveXML($element), strval($statement));
    }
}
