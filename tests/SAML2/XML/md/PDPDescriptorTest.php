<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Type\SAMLAnyURIListValue;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\XML\md\AbstractMdElement;
use SimpleSAML\SAML2\XML\md\AbstractMetadataDocument;
use SimpleSAML\SAML2\XML\md\AbstractRoleDescriptor;
use SimpleSAML\SAML2\XML\md\AbstractRoleDescriptorType;
use SimpleSAML\SAML2\XML\md\AbstractSignedMdElement;
use SimpleSAML\SAML2\XML\md\AssertionIDRequestService;
use SimpleSAML\SAML2\XML\md\AuthzService;
use SimpleSAML\SAML2\XML\md\NameIDFormat;
use SimpleSAML\SAML2\XML\md\PDPDescriptor;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XMLSchema\Type\IDValue;
use SimpleSAML\XMLSecurity\TestUtils\SignedElementTestTrait;

use function dirname;
use function strval;

/**
 * Tests for md:PDPDescriptor
 *
 * @package simplesamlphp/saml2
 */
#[Group('md')]
#[CoversClass(PDPDescriptor::class)]
#[CoversClass(AbstractRoleDescriptor::class)]
#[CoversClass(AbstractRoleDescriptorType::class)]
#[CoversClass(AbstractMetadataDocument::class)]
#[CoversClass(AbstractSignedMdElement::class)]
#[CoversClass(AbstractMdElement::class)]
final class PDPDescriptorTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;
    use SignedElementTestTrait;


    /** @var \SimpleSAML\SAML2\XML\md\AuthzService */
    private static AuthzService $authzService;

    /** @var \SimpleSAML\SAML2\XML\md\AssertionIDRequestService */
    private static AssertionIDRequestService $assertionIDRequestService;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = PDPDescriptor::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/md_PDPDescriptor.xml',
        );

        self::$authzService = new AuthzService(
            SAMLAnyURIValue::fromString(C::BINDING_SOAP),
            SAMLAnyURIValue::fromString('https://IdentityProvider.com/SAML/AA/SOAP'),
        );

        self::$assertionIDRequestService = new AssertionIDRequestService(
            SAMLAnyURIValue::fromString(C::BINDING_URI),
            SAMLAnyURIValue::fromString('https://IdentityProvider.com/SAML/AA/URI'),
        );
    }


    // test marshalling


    /**
     * Test creating a PDPDescriptor object from scratch.
     */
    public function testMarshalling(): void
    {
        $pdpd = new PDPDescriptor(
            [self::$authzService],
            SAMLAnyURIListValue::fromString(C::NS_SAMLP),
            [self::$assertionIDRequestService],
            [
                new NameIDFormat(
                    SAMLAnyURIValue::fromString(C::NAMEID_X509_SUBJECT_NAME),
                ),
                new NameIDFormat(
                    SAMLAnyURIValue::fromString(C::NAMEID_PERSISTENT),
                ),
                new NameIDFormat(
                    SAMLAnyURIValue::fromString(C::NAMEID_TRANSIENT),
                ),
            ],
            IDValue::fromString('phpunit'),
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($pdpd),
        );
    }


    /**
     * Test that creating a PDPDescriptor from scratch fails when an invalid AuthzService is passed.
     */
    public function testMarshallingWithWrongAuthzService(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('All md:AuthzService endpoints must be an instance of AuthzService.');

        new PDPDescriptor(
            /** @phpstan-ignore argument.type */
            [self::$authzService, self::$assertionIDRequestService],
            SAMLAnyURIListValue::fromString(C::NS_SAMLP),
        );
    }


    /**
     * Test that creating a PDPDescriptor from scratch fails when an invalid AssertionIDRequestService is passed.
     */
    public function testMarshallingWithWrongAssertionIDRequestService(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage(
            'All md:AssertionIDRequestService endpoints must be an instance of AssertionIDRequestService.',
        );

        new PDPDescriptor(
            [self::$authzService],
            SAMLAnyURIListValue::fromString(C::NS_SAMLP),
            /** @phpstan-ignore argument.type */
            [self::$assertionIDRequestService, self::$authzService],
        );
    }


    /**
     * Test that creating a PDPDescriptor from scratch without any optional arguments works.
     */
    public function testMarshallingWithoutOptionalArguments(): void
    {
        $pdpd = new PDPDescriptor(
            [self::$authzService],
            SAMLAnyURIListValue::fromString(C::NS_SAMLP),
        );
        $this->assertEmpty($pdpd->getAssertionIDRequestService());
        $this->assertEmpty($pdpd->getNameIDFormat());
    }


    // test unmarshalling


    /**
     * Test that creating a PDPDescriptor from XML fails when there's no AuthzService endpoint.
     */
    public function testUnmarshallingWithoutAuthzServiceDescriptors(): void
    {
        $xmlRepresentation = clone self::$xmlRepresentation;
        /**
         * @psalm-suppress PossiblyNullArgument
         * @psalm-suppress PossiblyNullPropertyFetch
         */
        $xmlRepresentation->documentElement->removeChild(
            $xmlRepresentation->documentElement->firstChild->nextSibling,
        );

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('At least one md:AuthzService endpoint must be present.');

        PDPDescriptor::fromXML($xmlRepresentation->documentElement);
    }


    /**
     * Test that creating a PDPDescriptor from XML works when no optional arguments are found.
     */
    public function testUnmarshallingWithoutOptionalArguments(): void
    {
        $mdns = C::NS_MD;
        $document = DOMDocumentFactory::fromString(
            <<<XML
<md:PDPDescriptor protocolSupportEnumeration="urn:oasis:names:tc:SAML:2.0:protocol" xmlns:md="{$mdns}">
  <md:AuthzService Binding="urn:oasis:names:tc:SAML:2.0:bindings:SOAP"
      Location="https://IdentityProvider.com/SAML/AA/SOAP"/>
</md:PDPDescriptor>
XML
            ,
        );
        $pdpd = PDPDescriptor::fromXML($document->documentElement);
        $this->assertEmpty($pdpd->getAssertionIDRequestService());
        $this->assertEmpty($pdpd->getNameIDFormat());
    }
}
