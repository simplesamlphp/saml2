<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Test\SAML2\XML\saml;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\saml\Action;
use SimpleSAML\SAML2\XML\saml\AuthzDecisionStatement;
use SimpleSAML\SAML2\XML\saml\Evidence;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\saml\AuthzDecisionStatementTest
 *
 * @covers \SimpleSAML\SAML2\XML\saml\AuthzDecisionStatement
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractStatement
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractSamlElement
 *
 * @package simplesamlphp/saml2
 */
final class AuthzDecisionStatementTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;

    /** @var \DOMDocument $evidence */
    private DOMDocument $evidence;


    /**
     */
    protected function setUp(): void
    {
        $this->schema = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-assertion-2.0.xsd';

        $this->testedClass = AuthzDecisionStatement::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 5) . '/resources/xml/saml_AuthzDecisionStatement.xml',
        );

        $this->evidence = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 5) . '/resources/xml/saml_Evidence.xml',
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $authzDecisionStatement = new AuthzDecisionStatement(
            'urn:x-simplesamlphp:resource',
            'Permit',
            [
                new Action('urn:x-simplesamlphp:namespace', 'SomeAction'),
                new Action('urn:x-simplesamlphp:namespace', 'OtherAction'),
            ],
            Evidence::fromXML($this->evidence->documentElement),
        );

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($authzDecisionStatement),
        );
    }


    /**
     */
    public function testUnmarshalling(): void
    {
        $authzDecisionStatement = AuthzDecisionStatement::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($authzDecisionStatement),
        );
    }
}
