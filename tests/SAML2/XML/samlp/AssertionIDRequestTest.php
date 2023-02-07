<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\samlp;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\saml\AssertionIDRef;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\samlp\AssertionIDRequest;
use SimpleSAML\Test\XML\SerializableElementTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XMLSecurity\TestUtils\SignedElementTestTrait;

use function dirname;
use function strval;

/**
 * Class \SAML2\XML\samlp\AssertionIDRequestTest
 *
 * @covers \SimpleSAML\SAML2\XML\samlp\AassertionIDRequestRequest
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractRequest
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractMessage
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractSamlpElement
 * @package simplesamlphp/saml2
 */
final class AssertionIDRequestTest extends TestCase
{
    use SerializableElementTestTrait;
    use SignedElementTestTrait;


    /**
     */
    public function setUp(): void
    {
        $this->testedClass = AuthnRequest::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/samlp_AssertionIDRequest.xml',
        );
    }


    // Marshalling


    public function testMarshalling(): void
    {
        $assertionIDRequest = new AssertionIDRequest(
            assertionIDRef: [new AssertionIDRef('_abc123'), new AssertionIDRef('_def456')],
            issuer: new Issuer('https://gateway.stepup.org/saml20/sp/metadata'),
            id: '_2b0226190ca1c22de6f66e85f5c95158',
            issueInstant: 1411393320,
            destination: 'https://tiqr.stepup.org/idp/profile/saml2/Redirect/SSO',
        );

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($assertionIDRequest),
        );
    }


    /**
     */
    public function testUnmarshalling(): void
    {
        $assertionIDRequest = AssertionIDRequest::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($assertionIDRequest),
        );
    }
}
