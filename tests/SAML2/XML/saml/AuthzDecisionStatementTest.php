<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Test\SAML2\XML\saml;

use DOMDocument;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Type\DecisionTypeValue;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\SAML2\XML\saml\AbstractSamlElement;
use SimpleSAML\SAML2\XML\saml\AbstractStatement;
use SimpleSAML\SAML2\XML\saml\Action;
use SimpleSAML\SAML2\XML\saml\AuthzDecisionStatement;
use SimpleSAML\SAML2\XML\saml\DecisionTypeEnum;
use SimpleSAML\SAML2\XML\saml\Evidence;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SimpleSAML\SAML2\XML\saml\AuthzDecisionStatementTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('saml')]
#[CoversClass(AuthzDecisionStatement::class)]
#[CoversClass(AbstractStatement::class)]
#[CoversClass(AbstractSamlElement::class)]
final class AuthzDecisionStatementTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /** @var \DOMDocument $evidence */
    private static DOMDocument $evidence;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = AuthzDecisionStatement::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/saml_AuthzDecisionStatement.xml',
        );

        self::$evidence = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/saml_Evidence.xml',
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $authzDecisionStatement = new AuthzDecisionStatement(
            SAMLAnyURIValue::fromString('urn:x-simplesamlphp:resource'),
            DecisionTypeValue::fromEnum(DecisionTypeEnum::Permit),
            [
                new Action(
                    SAMLAnyURIValue::fromString('urn:x-simplesamlphp:namespace'),
                    SAMLStringValue::fromString('SomeAction'),
                ),
                new Action(
                    SAMLAnyURIValue::fromString('urn:x-simplesamlphp:namespace'),
                    SAMLStringValue::fromString('OtherAction'),
                ),
            ],
            Evidence::fromXML(self::$evidence->documentElement),
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($authzDecisionStatement),
        );
    }
}
