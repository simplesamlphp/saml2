<?php

declare(strict_types=1);

namespace \SimpleSAML\SAML2\XML\md;

use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\DOMDocumentFactory;
use SimpleSAML\SAML2\Exception\MissingElementException;
use SimpleSAML\SAML2\XML\Chunk;

/**
 * Test for the Organization metadata element.
 *
 * @covers \SimpleSAML\SAML2\XML\md\Organization
 * @package simplesamlphp/saml2
 */
final class OrganizationTest extends TestCase
{
    /** @var \DOMDocument */
    protected $document;


    /**
     * @return void
     */
    protected function setUp(): void
    {
        $mdns = Constants::NS_MD;
        $this->document = DOMDocumentFactory::fromString(<<<XML
<md:Organization xmlns:md="{$mdns}">
  <md:Extensions>
    <some:Ext xmlns:some="urn:mace:some:metadata:1.0">SomeExtension</some:Ext>
  </md:Extensions>
  <md:OrganizationName xml:lang="en">Identity Providers R US</md:OrganizationName>
  <md:OrganizationDisplayName
      xml:lang="en">Identity Providers R US, a Division of Lerxst Corp.</md:OrganizationDisplayName>
  <md:OrganizationURL xml:lang="en">https://IdentityProvider.com</md:OrganizationURL>
</md:Organization>
XML
        );
    }


    // test marshalling


    /**
     * Test creating an Organization object from scratch
     */
    public function testMarshalling(): void
    {
        $ext = DOMDocumentFactory::fromString(
            '<some:Ext xmlns:some="urn:mace:some:metadata:1.0">SomeExtension</some:Ext>'
        );

        $org = new Organization(
            [new OrganizationName('en', 'Identity Providers R US')],
            [new OrganizationDisplayName('en', 'Identity Providers R US, a Division of Lerxst Corp.')],
            ['en' => 'https://IdentityProvider.com'],
            new Extensions(
                [
                    new Chunk($ext->documentElement)
                ]
            )
        );
        $root = DOMDocumentFactory::fromString('<root/>');
        $root->formatOutput = true;

        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($org)
        );
    }


    // test unmarshalling


    /**
     * Test creating an Organization object from XML
     */
    public function testUnmarshalling(): void
    {
        $org = Organization::fromXML($this->document->documentElement);
        $this->assertCount(1, $org->getOrganizationName());
        $this->assertEquals(
            strval(new OrganizationName('en', 'Identity Providers R US')),
            strval($org->getOrganizationName()[0])
        );
        $this->assertEquals(
            strval(new OrganizationDisplayName('en', 'Identity Providers R US, a Division of Lerxst Corp.')),
            strval($org->getOrganizationDisplayName()[0])
        );
        $this->assertEquals(
            [
                'en' => 'https://IdentityProvider.com',
            ],
            $org->getOrganizationURL()
        );
    }


    /**
     * Test creating an Organization object from XML containing no url
     */
    public function testUnmarshallingEmptyUrl(): void
    {
        $mdns = Constants::NS_MD;
        $document = DOMDocumentFactory::fromString(<<<XML
<md:Organization xmlns:md="{$mdns}">
  <md:OrganizationName xml:lang="en">Identity Providers R US</md:OrganizationName>
  <md:OrganizationDisplayName
      xml:lang="en">Identity Providers R US, a Division of Lerxst Corp.</md:OrganizationDisplayName>
  <md:OrganizationURL xml:lang="en"></md:OrganizationURL>
</md:Organization>
XML
        );

        $this->expectException(MissingElementException::class);
        $this->expectExceptionMessage('No localized organization URL found.');

        Organization::fromXML($document->documentElement);
    }


    /**
     * Test serialization and unserialization of AdditionalMetadataLocation elements.
     */
    public function testSerialization(): void
    {
        $org = Organization::fromXML($this->document->documentElement);
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize($org)))
        );
    }
}
