<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use PHPUnit\Framework\TestCase;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;

/**
 * Test for the Organization metadata element.
 *
 * @package simplesamlphp/saml2
 */
final class OrganizationTest extends TestCase
{
    protected $document;


    protected function setUp(): void
    {
        $mdns = Constants::NS_MD;
        $this->document = DOMDocumentFactory::fromString(<<<XML
<md:Organization xmlns:md="{$mdns}">
  <md:OrganizationName xml:lang="en">Identity Providers R US</md:OrganizationName>
  <md:OrganizationDisplayName xml:lang="en">Identity Providers R US, a Division of Lerxst Corp.</md:OrganizationDisplayName>
  <md:OrganizationURL xml:lang="en">https://IdentityProvider.com</md:OrganizationURL>
</md:Organization>
XML
        );
    }


    /**
     * Test creating an Organization object from scratch
     */
    public function testMarshalling(): void
    {
        $org = new Organization(
            [new OrganizationName('en', 'Identity Providers R US')],
            [new OrganizationDisplayName('en', 'Identity Providers R US, a Division of Lerxst Corp.')],
            ['en' => 'https://IdentityProvider.com']
        );
        $root = DOMDocumentFactory::fromString('<root/>');
        $root->formatOutput = true;
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            $org->toXML($root->documentElement)->ownerDocument->saveXML($root->documentElement->firstChild)
        );
    }


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
}
