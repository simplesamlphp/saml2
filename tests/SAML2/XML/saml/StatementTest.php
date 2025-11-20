<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Compat\AbstractContainer;
use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\XML\saml\AbstractSamlElement;
use SimpleSAML\SAML2\XML\saml\AbstractStatement;
use SimpleSAML\SAML2\XML\saml\AbstractStatementType;
use SimpleSAML\SAML2\XML\saml\Audience;
use SimpleSAML\SAML2\XML\saml\UnknownStatement;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\Test\SAML2\CustomStatement;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XMLSchema\Constants as C_XSI;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\saml\StatementTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('saml')]
#[CoversClass(UnknownStatement::class)]
#[CoversClass(AbstractStatement::class)]
#[CoversClass(AbstractStatementType::class)]
#[CoversClass(AbstractSamlElement::class)]
final class StatementTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /** @var \SimpleSAML\SAML2\Compat\AbstractContainer */
    private static AbstractContainer $containerBackup;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$containerBackup = ContainerSingleton::getInstance();

        self::$schemaFile = dirname(__FILE__, 4) . '/resources/schemas/simplesamlphp.xsd';

        self::$testedClass = AbstractStatement::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/saml_Statement.xml',
        );

        $container = clone self::$containerBackup;
        $container->registerExtensionHandler(CustomStatement::class);
        ContainerSingleton::setContainer($container);
    }


    /**
     */
    public static function tearDownAfterClass(): void
    {
        ContainerSingleton::setContainer(self::$containerBackup);
    }


    // marshalling


    /**
     */
    public function testMarshalling(): void
    {
        $statement = new CustomStatement([
            new Audience(
                SAMLAnyURIValue::fromString('urn:some:audience'),
            ),
        ]);

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($statement),
        );
    }


    // unmarshalling


    /**
     * Test unmarshalling a registered class
     */
    public function testUnmarshalling(): void
    {
        $statement = CustomStatement::fromXML(self::$xmlRepresentation->documentElement);
        $this->assertInstanceOf(CustomStatement::class, $statement);

        $this->assertEquals('ssp:CustomStatementType', $statement->getXsiType());
        $audience = $statement->getAudience();
        $this->assertCount(1, $audience);
        $this->assertEquals('urn:some:audience', $audience[0]->getContent());

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($statement),
        );
    }


    /**
     */
    public function testUnmarshallingUnregistered(): void
    {
        $element = clone self::$xmlRepresentation->documentElement;
        $element->setAttributeNS(C_XSI::NS_XSI, 'xsi:type', 'ssp:UnknownStatementType');

        $statement = AbstractStatement::fromXML($element);

        $this->assertInstanceOf(UnknownStatement::class, $statement);
        $this->assertEquals(
            '{urn:x-simplesamlphp:namespace}ssp:UnknownStatementType',
            $statement->getXsiType()->getRawValue(),
        );

        $chunk = $statement->getRawStatement();
        $this->assertEquals('saml', $chunk->getPrefix());
        $this->assertEquals('Statement', $chunk->getLocalName());
        $this->assertEquals(C::NS_SAML, $chunk->getNamespaceURI());

        $this->assertEquals($element->ownerDocument?->saveXML($element), strval($statement));
    }
}
