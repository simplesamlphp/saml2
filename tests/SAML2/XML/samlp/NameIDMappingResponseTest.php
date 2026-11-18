<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\samlp;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\Type\SAMLDateTimeValue;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\saml\NameID;
use SimpleSAML\SAML2\XML\samlp\AbstractMessage;
use SimpleSAML\SAML2\XML\samlp\AbstractSamlpElement;
use SimpleSAML\SAML2\XML\samlp\AbstractStatusResponse;
use SimpleSAML\SAML2\XML\samlp\NameIDMappingResponse;
use SimpleSAML\SAML2\XML\samlp\Status;
use SimpleSAML\SAML2\XML\samlp\StatusCode;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XMLSchema\Type\IDValue;
use SimpleSAML\XMLSchema\Type\NCNameValue;
use SimpleSAML\XMLSecurity\TestUtils\SignedElementTestTrait;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\samlp\NameIDMappingResponseTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('samlp')]
#[CoversClass(NameIDMappingResponse::class)]
#[CoversClass(AbstractStatusResponse::class)]
#[CoversClass(AbstractMessage::class)]
#[CoversClass(AbstractSamlpElement::class)]
final class NameIDMappingResponseTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;
    use SignedElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = NameIDMappingResponse::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/samlp_NameIDMappingResponse.xml',
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $status = new Status(
            new StatusCode(
                SAMLAnyURIValue::fromString(C::STATUS_SUCCESS),
            ),
        );
        $issuer = new Issuer(
            SAMLStringValue::fromString('https://IdentityProvider.com'),
        );

        $nameId = new NameID(
            SAMLStringValue::fromString('TheNameIDValue'),
            SAMLStringValue::fromString('urn:x-simplesamlphp:namequalifier'),
            SAMLStringValue::fromString('urn:x-simplesamlphp:spnamequalifier'),
            SAMLAnyURIValue::fromString('urn:the:format'),
            SAMLStringValue::fromString('TheSPProvidedID'),
        );

        $nameIdMappingResponse = new NameIDMappingResponse(
            id: IDValue::fromString('abc123'),
            status: $status,
            issuer: $issuer,
            destination: SAMLAnyURIValue::fromString('https://example.org/metadata'),
            consent: SAMLAnyURIValue::fromString(C::CONSENT_EXPLICIT),
            inResponseTo: NCNameValue::fromString('PHPUnit'),
            issueInstant: SAMLDateTimeValue::fromString('2021-03-25T16:53:26Z'),
            identifier: $nameId,
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($nameIdMappingResponse),
        );
    }
}
