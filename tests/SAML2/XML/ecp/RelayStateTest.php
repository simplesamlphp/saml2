<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\ecp;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\ecp\AbstractEcpElement;
use SimpleSAML\SAML2\XML\ecp\RelayState;
use SimpleSAML\SOAP11\Constants as SOAP;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XMLSchema\Exception\MissingAttributeException;

use function dirname;
use function strval;

/**
 * @package simplesamlphp/saml2
 */
#[Group('ecp')]
#[CoversClass(RelayState::class)]
#[CoversClass(AbstractEcpElement::class)]
final class RelayStateTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = RelayState::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/ecp_RelayState.xml',
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $relayState = RelayState::fromString('AGDY854379dskssda');

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($relayState),
        );
    }


    /**
     */
    public function testUnmarshallingWithMissingMustUnderstandThrowsException(): void
    {
        $document = clone self::$xmlRepresentation->documentElement;
        $document->removeAttributeNS(SOAP::NS_SOAP_ENV, 'mustUnderstand');

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage('Missing env:mustUnderstand attribute in <ecp:RelayState>.');

        RelayState::fromXML($document);
    }


    /**
     */
    public function testUnmarshallingWithMissingActorThrowsException(): void
    {
        $document = clone self::$xmlRepresentation->documentElement;
        $document->removeAttributeNS(SOAP::NS_SOAP_ENV, 'actor');

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage('Missing env:actor attribute in <ecp:RelayState>.');

        RelayState::fromXML($document);
    }
}
