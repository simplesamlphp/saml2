<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\samlp;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\Type\SAMLDateTimeValue;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\saml\NameID;
use SimpleSAML\SAML2\XML\samlp\AbstractMessage;
use SimpleSAML\SAML2\XML\samlp\AbstractRequest;
use SimpleSAML\SAML2\XML\samlp\AbstractSamlpElement;
use SimpleSAML\SAML2\XML\samlp\ManageNameIDRequest;
use SimpleSAML\SAML2\XML\samlp\Terminate;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XMLSchema\Type\IDValue;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\samlp\ManageNameIDRequestTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('saml')]
#[CoversClass(ManageNameIDRequest::class)]
#[CoversClass(AbstractRequest::class)]
#[CoversClass(AbstractMessage::class)]
#[CoversClass(AbstractSamlpElement::class)]
final class ManageNameIDRequestTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = ManageNameIDRequest::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/samlp_ManageNameIDRequest.xml',
        );
    }


    // marshalling


    /**
     */
    public function testMarshalling(): void
    {
        $nameId = new NameID(
            SAMLStringValue::fromString('TheNameIDValue'),
            SAMLStringValue::fromString('urn:x-simplesamlphp:namequalifier'),
            SAMLStringValue::fromString('urn:x-simplesamlphp:spnamequalifier'),
            SAMLAnyURIValue::fromString('urn:the:format'),
            SAMLStringValue::fromString('TheSPProvidedID'),
        );

        $manageNameIdRequest = new ManageNameIDRequest(
            identifier: $nameId,
            newIdentifier: new Terminate(),
            issuer: new Issuer(
                SAMLStringValue::fromString('https://gateway.stepup.org/saml20/sp/metadata'),
            ),
            id: IDValue::fromString('_2b0226190ca1c22de6f66e85f5c95158'),
            issueInstant: SAMLDateTimeValue::fromString('2014-09-22T13:42:00Z'),
            destination: SAMLAnyURIValue::fromString('https://tiqr.stepup.org/idp/profile/saml2/Redirect/SSO'),
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($manageNameIdRequest),
        );
    }
}
