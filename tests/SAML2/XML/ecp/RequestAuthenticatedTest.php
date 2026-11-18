<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\ecp;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\ecp\AbstractEcpElement;
use SimpleSAML\SAML2\XML\ecp\RequestAuthenticated;
use SimpleSAML\SOAP11\Constants as C;
use SimpleSAML\SOAP11\Type\MustUnderstandValue;
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
#[CoversClass(RequestAuthenticated::class)]
#[CoversClass(AbstractEcpElement::class)]
final class RequestAuthenticatedTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = RequestAuthenticated::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/ecp_RequestAuthenticated.xml',
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $ra = new RequestAuthenticated(
            MustUnderstandValue::fromBoolean(true),
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($ra),
        );
    }


    /**
     */
    public function testMarshallingMustUnderstandFalseIsIgnored(): void
    {
        $ra = new RequestAuthenticated(
            MustUnderstandValue::fromBoolean(false),
        );

        $xml = $ra->toXML();
        $this->assertFalse($xml->hasAttributeNS(C::NS_SOAP_ENV, 'mustUnderstand'));
    }


    /**
     */
    public function testUnmarshallingWithMissingActorThrowsException(): void
    {
        $document = clone self::$xmlRepresentation->documentElement;
        $document->removeAttributeNS(C::NS_SOAP_ENV, 'actor');

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage('Missing env:actor attribute in <ecp:RequestAuthenticated>.');

        RequestAuthenticated::fromXML($document);
    }
}
