<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\samlp;

use PHPUnit\Framework\Attributes\{CoversClass, Group};
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Type\{SAMLAnyURIValue, SAMLDateTimeValue, SAMLStringValue};
use SimpleSAML\SAML2\XML\saml\{AssertionIDRef, Issuer};
use SimpleSAML\SAML2\XML\samlp\{AbstractMessage, AbstractRequest, AbstractSamlpElement, AssertionIDRequest};
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\{SchemaValidationTestTrait, SerializableElementTestTrait};
use SimpleSAML\XMLSchema\Type\{IDValue, NCNameValue};
use SimpleSAML\XMLSecurity\TestUtils\SignedElementTestTrait;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\samlp\AssertionIDRequestTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('samlp')]
#[CoversClass(AssertionIDRequest::class)]
#[CoversClass(AbstractRequest::class)]
#[CoversClass(AbstractMessage::class)]
#[CoversClass(AbstractSamlpElement::class)]
final class AssertionIDRequestTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;
    use SignedElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = AssertionIDRequest::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/samlp_AssertionIDRequest.xml',
        );
    }


    // Marshalling


    /**
     */
    public function testMarshalling(): void
    {
        $assertionIDRequest = new AssertionIDRequest(
            assertionIDRef: [
                new AssertionIDRef(
                    NCNameValue::fromString('_abc123'),
                ),
                new AssertionIDRef(
                    NCNameValue::fromString('_def456'),
                ),
            ],
            issuer: new Issuer(
                SAMLStringValue::fromString('https://gateway.stepup.org/saml20/sp/metadata'),
            ),
            id: IDValue::fromString('_2b0226190ca1c22de6f66e85f5c95158'),
            issueInstant: SAMLDateTimeValue::fromString('2014-09-22T13:42:00Z'),
            destination: SAMLAnyURIValue::fromString('https://tiqr.stepup.org/idp/profile/saml2/Redirect/SSO'),
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($assertionIDRequest),
        );
    }
}
