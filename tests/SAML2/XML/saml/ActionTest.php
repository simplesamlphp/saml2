<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\XML\saml\Action;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\Test\XML\SerializableElementTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;

use function dirname;
use function strval;

/**
 * Class \SAML2\XML\saml\ActionTest
 *
 * @covers \SimpleSAML\SAML2\XML\saml\Action
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractSamlElement
 * @package simplesamlphp/saml2
 */
final class ActionTest extends TestCase
{
    use SerializableElementTestTrait;


    /**
     */
    public function setup(): void
    {
        $this->testedClass = Action::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/saml_Action.xml'
        );
    }


    // marshalling


    /**
     */
    public function testMarshalling(): void
    {
        $action = new Action(
            C::NAMESPACE,
            'SomeAction'
        );

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($action)
        );
    }


    // unmarshalling


    /**
     */
    public function testUnmarshalling(): void
    {
        $action = Action::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals('SomeAction', $action->getContent());
        $this->assertEquals(C::NAMESPACE, $action->getNamespace());
    }
}
