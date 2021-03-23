<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\XML\alg\DigestMethod;
use SimpleSAML\SAML2\XML\alg\SigningMethod;
use SimpleSAML\SAML2\XML\md\Extensions;
use SimpleSAML\SAML2\XML\mdattr\EntityAttributes;
use SimpleSAML\SAML2\XML\mdrpi\PublicationInfo;
use SimpleSAML\SAML2\XML\mdrpi\Publication;
use SimpleSAML\SAML2\XML\mdrpi\PublicationPath;
use SimpleSAML\SAML2\XML\mdrpi\RegistrationInfo;
use SimpleSAML\SAML2\XML\mdui\DiscoHints;
use SimpleSAML\SAML2\XML\mdui\DisplayName;
use SimpleSAML\SAML2\XML\mdui\UIInfo;
use SimpleSAML\SAML2\XML\shibmd\Scope;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;

/**
 * Class \SAML2\XML\md\ExtensionsTest.
 *
 * This class tests for currently supported metadata extensions.
 *
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMdElement
 * @covers \SimpleSAML\SAML2\XML\md\Extensions
 * @package simplesamlphp/saml2
 */
final class ExtensionsTest extends TestCase
{
    use SerializableXMLTestTrait;


    /**
     */
    protected function setUp(): void
    {
        $this->testedClass = Extensions::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/md_Extensions.xml'
        );
        $this->xmlRepresentation->normalizeDocument();
    }


    // test marshalling


    /**
     * Test creating an Extensions object from scratch.
     */
    public function testMarshalling(): void
    {
        $scope = new Scope('SomeScope');
        $ra = new RegistrationInfo('SomeAuthority');
        $pubInfo = new PublicationInfo('SomePublisher');
        $pubPath = new PublicationPath(
            [
                new Publication('SomePublisher')
            ]
        );
        $uiinfo = new UIInfo([new DisplayName('en', 'Example')]);
        $discoHints = new DiscoHints([], ['127.0.0.1']);
        $digestMethod = new DigestMethod('SomeAlgorithm');
        $signingMethod = new SigningMethod('SomeOtherAlgorithm', 1024, 4096);

        $extensions = new Extensions([
            $scope,
            $ra,
            $pubInfo,
            $pubPath,
            $uiinfo,
            $discoHints,
            $digestMethod,
            $signingMethod
        ]);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($extensions)
        );
    }


    /**
     * Adding an empty list to an Extensions element should yield an empty element. If there were contents already
     * there, those should be left untouched.
     */
    public function testMarshallingWithNoExtensions(): void
    {
        $mdns = Constants::NS_MD;
        $extensions = new Extensions([]);
        $this->assertEquals(
            "<md:Extensions xmlns:md=\"$mdns\"/>",
            strval($extensions)
        );
        $this->assertTrue($extensions->isEmptyElement());
    }


    // test unmarshalling


    /**
     * This method tests for known extensions.
     */
    public function testUnmarshalling(): void
    {
        $document = DOMDocumentFactory::fromString(<<<XML
<md:Extensions xmlns:md="urn:oasis:names:tc:SAML:2.0:metadata"
               xmlns:shibmd="urn:mace:shibboleth:metadata:1.0"
               xmlns:mdattr="urn:oasis:names:tc:SAML:metadata:attribute"
               xmlns:mdrpi="urn:oasis:names:tc:SAML:metadata:rpi"
               xmlns:mdui="urn:oasis:names:tc:SAML:metadata:ui"
               xmlns:ns="urn:some:ns"
               xmlns:alg="urn:oasis:names:tc:SAML:metadata:algsupport">
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
    <mdui:IPHint>127.0.0.1</mdui:IPHint>
  </mdui:DiscoHints>
  <alg:DigestMethod Algorithm="SomeAlgorithm"/>
  <alg:SigningMethod Algorithm="SomeOtherAlgorithm" MinKeySize="1024" MaxKeySize="4096"/>
  <ns:SomeChunk foo="bar">SomeText</ns:SomeChunk>
</md:Extensions>
XML
        );
        $extensions = Extensions::fromXML($document->documentElement);
        $list = $extensions->getList();
        $this->assertCount(10, $list);
        $this->assertInstanceOf(Scope::class, $list[0]);
        $this->assertInstanceOf(EntityAttributes::class, $list[1]);
        $this->assertInstanceOf(RegistrationInfo::class, $list[2]);
        $this->assertInstanceOf(PublicationInfo::class, $list[3]);
        $this->assertInstanceOf(PublicationPath::class, $list[4]);
        $this->assertInstanceOf(UIInfo::class, $list[5]);
        $this->assertInstanceOf(DiscoHints::class, $list[6]);
        $this->assertInstanceOf(DigestMethod::class, $list[7]);
        $this->assertInstanceOf(SigningMethod::class, $list[8]);
        $this->assertInstanceOf(Chunk::class, $list[9]);
        $this->assertFalse($extensions->isEmptyElement());
    }


    /**
     * Test that creating an Extensions object from XML works even if no extensions are specified.
     */
    public function testUnmarshallingWithNoExtensions(): void
    {
        $mdns = Constants::NS_MD;
        $document = DOMDocumentFactory::fromString("<md:Extensions xmlns:md=\"$mdns\"/>");
        $extensions = Extensions::fromXML($document->documentElement);
        $this->assertEmpty($extensions->getList());
    }
}
