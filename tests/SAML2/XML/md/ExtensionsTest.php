<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\Type\CIDRValue;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\SAML2\XML\alg\DigestMethod;
use SimpleSAML\SAML2\XML\alg\SigningMethod;
use SimpleSAML\SAML2\XML\emd\RepublishRequest;
use SimpleSAML\SAML2\XML\emd\RepublishTarget;
use SimpleSAML\SAML2\XML\idpdisc\DiscoveryResponse;
use SimpleSAML\SAML2\XML\md\AbstractMdElement;
use SimpleSAML\SAML2\XML\md\Extensions;
use SimpleSAML\SAML2\XML\mdattr\EntityAttributes;
use SimpleSAML\SAML2\XML\mdrpi\Publication;
use SimpleSAML\SAML2\XML\mdrpi\PublicationInfo;
use SimpleSAML\SAML2\XML\mdrpi\PublicationPath;
use SimpleSAML\SAML2\XML\mdrpi\RegistrationInfo;
use SimpleSAML\SAML2\XML\mdui\DiscoHints;
use SimpleSAML\SAML2\XML\mdui\DisplayName;
use SimpleSAML\SAML2\XML\mdui\IPHint;
use SimpleSAML\SAML2\XML\mdui\UIInfo;
use SimpleSAML\SAML2\XML\saml\AttributeValue;
use SimpleSAML\SAML2\XML\shibmd\Scope;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XMLSchema\Type\LanguageValue;
use SimpleSAML\XMLSchema\Type\PositiveIntegerValue;
use SimpleSAML\XMLSchema\Type\UnsignedShortValue;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\md\ExtensionsTest.
 *
 * This class tests for currently supported metadata extensions.
 *
 * @package simplesamlphp/saml2
 */
#[Group('md')]
#[CoversClass(Extensions::class)]
#[CoversClass(AbstractMdElement::class)]
final class ExtensionsTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = Extensions::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/md_Extensions.xml',
        );
    }


    // test marshalling


    /**
     * Test creating an Extensions object from scratch.
     */
    public function testMarshalling(): void
    {
        $scope = new Scope(
            SAMLStringValue::fromString('SomeScope'),
        );
        $ra = new RegistrationInfo(
            SAMLStringValue::fromString('SomeAuthority'),
        );
        $pubInfo = new PublicationInfo(
            SAMLStringValue::fromString('SomePublisher'),
        );
        $pubPath = new PublicationPath(
            [
                new Publication(
                    SAMLStringValue::fromString('SomePublisher'),
                ),
            ],
        );
        $uiinfo = new UIInfo([
            new DisplayName(
                LanguageValue::fromString('en'),
                SAMLStringValue::fromString('Example'),
            ),
        ]);
        $idpdisc = new DiscoveryResponse(
            UnsignedShortValue::fromInteger(1),
            SAMLAnyURIValue::fromString(C::NS_IDPDISC),
            SAMLAnyURIValue::fromString('https://example.org/authenticate/sp'),
        );
        $discoHints = new DiscoHints([], [
            new IPHint(
                CIDRValue::fromString('127.0.0.0/8'),
            ),
        ]);
        $digestMethod = new DigestMethod(
            SAMLAnyURIValue::fromString(C::DIGEST_SHA256),
        );
        $signingMethod = new SigningMethod(
            SAMLAnyURIValue::fromString(C::SIG_RSA_SHA256),
            PositiveIntegerValue::fromInteger(1024),
            PositiveIntegerValue::fromInteger(4096),
        );
        $republishRequest = new RepublishRequest(
            new RepublishTarget(
                SAMLAnyURIValue::fromString('http://edugain.org/'),
            ),
        );

        $extensions = new Extensions([
            $scope,
            $ra,
            $pubInfo,
            $pubPath,
            $uiinfo,
            $discoHints,
            $idpdisc,
            $digestMethod,
            $signingMethod,
            $republishRequest,
        ]);

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($extensions),
        );
    }


    /**
     * Adding an empty list to an Extensions element should yield an empty element. If there were contents already
     * there, those should be left untouched.
     */
    public function testMarshallingWithNoExtensions(): void
    {
        $extensions = new Extensions([]);
        $this->assertEquals(
            '<md:Extensions xmlns:md="urn:oasis:names:tc:SAML:2.0:metadata"/>',
            strval($extensions),
        );
        $this->assertTrue($extensions->isEmptyElement());
    }


    /**
     * Adding a non-namespaced element to an md:Extensions element should throw an exception
     */
    public function testMarshallingWithNonNamespacedExtensions(): void
    {
        $this->expectException(ProtocolViolationException::class);
        $this->expectExceptionMessage('Extensions MUST NOT include global (non-namespace-qualified) elements.');

        new Extensions([new Chunk(DOMDocumentFactory::fromString('<child/>')->documentElement)]);
    }


    /**
     * Adding an element from SAML-defined namespaces element should throw an exception
     */
    public function testMarshallingWithSamlDefinedNamespacedExtensions(): void
    {
        $this->expectException(ProtocolViolationException::class);
        $this->expectExceptionMessage('Extensions MUST NOT include any SAML-defined namespace elements.');

        new Extensions([new AttributeValue('something')]);
    }


    // test unmarshalling


    /**
     * This method tests for known extensions.
     */
    public function testUnmarshalling(): void
    {
        $document = DOMDocumentFactory::fromString(
            <<<XML
<md:Extensions xmlns:md="urn:oasis:names:tc:SAML:2.0:metadata"
               xmlns:shibmd="urn:mace:shibboleth:metadata:1.0"
               xmlns:mdattr="urn:oasis:names:tc:SAML:metadata:attribute"
               xmlns:mdrpi="urn:oasis:names:tc:SAML:metadata:rpi"
               xmlns:mdui="urn:oasis:names:tc:SAML:metadata:ui"
               xmlns:ns="urn:some:ns"
               xmlns:alg="urn:oasis:names:tc:SAML:metadata:algsupport"
               xmlns:idpdisc="urn:oasis:names:tc:SAML:profiles:SSO:idp-discovery-protocol">
  <shibmd:Scope>SomeScope</shibmd:Scope>
  <mdattr:EntityAttributes>SomeAttribute</mdattr:EntityAttributes>
  <mdrpi:RegistrationInfo registrationAuthority="SomeAuthority"/>
  <mdrpi:PublicationInfo publisher="SomePublisher"/>
  <mdrpi:PublicationPath>
    <mdrpi:Publication publisher="SomePublisher" />
  </mdrpi:PublicationPath>
  <mdui:UIInfo>
    <mdui:DisplayName xml:lang="en">Example</mdui:DisplayName>
  </mdui:UIInfo>
  <mdui:DiscoHints>
    <mdui:IPHint>127.0.0.0/8</mdui:IPHint>
  </mdui:DiscoHints>
  <idpdisc:DiscoveryResponse Binding="urn:oasis:names:tc:SAML:profiles:SSO:idp-discovery-protocol"
    Location="https://example.org/authenticate/sp"
    index="1"/>
  <alg:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/>
  <alg:SigningMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha224" MinKeySize="1024" MaxKeySize="4096"/>
  <emd:RepublishRequest xmlns:emd="http://eduid.cz/schema/metadata/1.0">
    <emd:RepublishTarget>http://edugain.org/</emd:RepublishTarget>
  </emd:RepublishRequest>
  <ns:SomeChunk foo="bar">SomeText</ns:SomeChunk>
</md:Extensions>
XML
            ,
        );
        $extensions = Extensions::fromXML($document->documentElement);
        $list = $extensions->getList();
        $this->assertCount(12, $list);
        $this->assertInstanceOf(Scope::class, $list[0]);
        $this->assertInstanceOf(EntityAttributes::class, $list[1]);
        $this->assertInstanceOf(RegistrationInfo::class, $list[2]);
        $this->assertInstanceOf(PublicationInfo::class, $list[3]);
        $this->assertInstanceOf(PublicationPath::class, $list[4]);
        $this->assertInstanceOf(UIInfo::class, $list[5]);
        $this->assertInstanceOf(DiscoHints::class, $list[6]);
        $this->assertInstanceOf(DiscoveryResponse::class, $list[7]);
        $this->assertInstanceOf(DigestMethod::class, $list[8]);
        $this->assertInstanceOf(SigningMethod::class, $list[9]);
        $this->assertInstanceOf(RepublishRequest::class, $list[10]);
        $this->assertInstanceOf(Chunk::class, $list[11]);
        $this->assertFalse($extensions->isEmptyElement());
    }


    /**
     * Test that creating an Extensions object from XML works even if no extensions are specified.
     */
    public function testUnmarshallingWithNoExtensions(): void
    {
        $document = DOMDocumentFactory::fromString('<md:Extensions xmlns:md="urn:oasis:names:tc:SAML:2.0:metadata"/>');
        $extensions = Extensions::fromXML($document->documentElement);
        $this->assertEmpty($extensions->getList());
    }
}
