<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\XML\shibmd\Scope;
use SAML2\XML\alg\DigestMethod;

/**
 * Class \SAML2\XML\md\ExtensionsTest.
 *
 * This class tests for currently supported metadata extensions.
 *
 * @package simplesamlphp/saml2
 */
class ExtensionsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Adding an empty list to an Extensions element should yield an empty element. If there were contents already
     * there, those should be left untouched.
     * @return void
     */
    public function testExtensionAddEmpty() : void
    {
        $d = DOMDocumentFactory::create();
        $r = $d->createElementNS(Constants::NS_MD, 'md:Extensions');
        $r = $d->createElement('root');
        $d->appendChild($r);
        $d->formatOutput = true;

        // add an empty list on an empty Extensions element
        Extensions::addList($r, []);
        $list = Extensions::getList($r);
        $this->assertCount(0, $list);
        $this->assertEquals(<<<XML
<?xml version="1.0"?>
<root/>
XML
            ,
            trim($d->saveXML())
        );

        // add an empty list on a non-empty Extensions element
        $e = $d->createElementNS(Constants::NS_MD, 'md:Extensions');
        $chunk = $d->createElementNS("urn:some:ns", 'ns:SomeChunk', 'Contents');
        $chunk->setAttribute('foo', 'bar');
        $e->appendChild($chunk);
        $r->appendChild($e);
        Extensions::addList($r, []);
        $list = Extensions::getList($r);
        $this->assertCount(1, $list);
        $this->assertEquals(<<<XML
<?xml version="1.0"?>
<root>
  <md:Extensions xmlns:md="urn:oasis:names:tc:SAML:2.0:metadata" xmlns:ns="urn:some:ns">
    <ns:SomeChunk xmlns:ns="urn:some:ns" foo="bar">Contents</ns:SomeChunk>
  </md:Extensions>
</root>
XML
            ,
            trim($d->saveXML())
        );
        $this->assertInstanceOf(\SAML2\XML\Chunk::class, $list[0]);
    }


    /**
     * This method tests for known extensions.
     * @return void
     */
    public function testSupportedExtensions() : void
    {
        $document = DOMDocumentFactory::fromString(
<<<XML
<root>
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
</root>
XML
        );
        $list = Extensions::getList($document->documentElement);
        $this->assertCount(9, $list);
        $this->assertInstanceOf(\SAML2\XML\shibmd\Scope::class, $list[0]);
        $this->assertInstanceOf(\SAML2\XML\mdattr\EntityAttributes::class, $list[1]);
        $this->assertInstanceOf(\SAML2\XML\mdrpi\RegistrationInfo::class, $list[2]);
        $this->assertInstanceOf(\SAML2\XML\mdrpi\PublicationInfo::class, $list[3]);
        $this->assertInstanceOf(\SAML2\XML\mdui\UIInfo::class, $list[4]);
        $this->assertInstanceOf(\SAML2\XML\mdui\DiscoHints::class, $list[5]);
        $this->assertInstanceOf(\SAML2\XML\alg\DigestMethod::class, $list[6]);
        $this->assertInstanceOf(\SAML2\XML\alg\SigningMethod::class, $list[7]);
        $this->assertInstanceOf(\SAML2\XML\Chunk::class, $list[8]);
    }


    /**
     * This methods tests adding an md:Extensions element to a DOMElement.
     * @return void
     */
    public function testAddExtensions() : void
    {
        $document = DOMDocumentFactory::create();
        $document->formatOutput = true;
        $r = $document->createElement('root');
        $document->appendChild($r);
        $scope = new Scope();
        $scope->setScope('SomeScope');
        $digest = new DigestMethod();
        $digest->setAlgorithm('SomeAlgorithm');
        $extensions = [
            $scope,
            $digest,
        ];
        Extensions::addList($r, $extensions);
        $this->assertEquals(
<<<XML
<?xml version="1.0"?>
<root>
  <md:Extensions xmlns:md="urn:oasis:names:tc:SAML:2.0:metadata">
    <shibmd:Scope xmlns:shibmd="urn:mace:shibboleth:metadata:1.0" regexp="false">SomeScope</shibmd:Scope>
    <alg:DigestMethod xmlns:alg="urn:oasis:names:tc:SAML:metadata:algsupport" Algorithm="SomeAlgorithm"/>
  </md:Extensions>
</root>
XML
            ,
            trim($r->ownerDocument->saveXML())
        );
    }
}
