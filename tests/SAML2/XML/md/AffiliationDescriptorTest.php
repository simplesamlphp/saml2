<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use Exception;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\XML\md\AffiliationDescriptor;
use SimpleSAML\SAML2\XML\md\AffiliateMember;
use SimpleSAML\SAML2\XML\md\KeyDescriptor;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XMLSecurity\XML\ds\KeyInfo;
use SimpleSAML\XMLSecurity\XML\ds\X509Certificate;
use SimpleSAML\XMLSecurity\XML\ds\X509Data;

class AffiliationDescriptorTest extends TestCase
{
    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $document = DOMDocumentFactory::fromString('<root />');

        $affiliationDescriptorElement = new AffiliationDescriptor();
        $affiliationDescriptorElement->setAffiliationOwnerID('TheOwner');
        $affiliationDescriptorElement->setID('TheID');
        $affiliationDescriptorElement->setValidUntil(1234567890);
        $affiliationDescriptorElement->setCacheDuration('PT5000S');
        $affiliationDescriptorElement->setAffiliateMember([
            new AffiliateMember('Member1'),
            new AffiliateMember('Member2'),
        ]);
        $kd = new KeyDescriptor(new KeyInfo([new X509Data([new X509Certificate(
            '/CTj03d1DB5e2t7CTo9BEzCf5S9NRzwnBgZRlm32REI='
        )])]));
        $affiliationDescriptorElement->setKeyDescriptor([$kd]);

        $affiliationDescriptorElement = $affiliationDescriptorElement->toXML($document->firstChild);

        $xpCache = XPath::getXPath($affiliationDescriptorElement);
        $affiliationDescriptorElements = XPath::xpQuery(
            $affiliationDescriptorElement,
            '/root/saml_metadata:AffiliationDescriptor',
            $xpCache,
        );
        $this->assertCount(1, $affiliationDescriptorElements);
        /** @var \DOMElement $affiliationDescriptorElement */
        $affiliationDescriptorElement = $affiliationDescriptorElements[0];

        $this->assertEquals('TheOwner', $affiliationDescriptorElement->getAttribute("affiliationOwnerID"));
        $this->assertEquals('TheID', $affiliationDescriptorElement->getAttribute("ID"));
        $this->assertEquals('2009-02-13T23:31:30Z', $affiliationDescriptorElement->getAttribute("validUntil"));
        $this->assertEquals('PT5000S', $affiliationDescriptorElement->getAttribute("cacheDuration"));

        $affiliateMembers = XPath::xpQuery(
            $affiliationDescriptorElement,
            './saml_metadata:AffiliateMember',
            $xpCache,
        );
        $this->assertCount(2, $affiliateMembers);
        $this->assertEquals('Member1', $affiliateMembers[0]->textContent);
        $this->assertEquals('Member2', $affiliateMembers[1]->textContent);
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $mdNamespace = C::NS_MD;
        $document = DOMDocumentFactory::fromString(<<<XML
<md:AffiliationDescriptor xmlns:md="{$mdNamespace}" affiliationOwnerID="TheOwner" ID="TheID" validUntil="2009-02-13T23:31:30Z" cacheDuration="PT5000S">
    <md:AffiliateMember>Member</md:AffiliateMember>
    <md:AffiliateMember>OtherMember</md:AffiliateMember>
</md:AffiliationDescriptor>
XML
        );

        $affiliateDescriptor = new AffiliationDescriptor($document->firstChild);
        $this->assertEquals('TheOwner', $affiliateDescriptor->getAffiliationOwnerID());
        $this->assertEquals('TheID', $affiliateDescriptor->getID());
        $this->assertEquals(1234567890, $affiliateDescriptor->getValidUntil());
        $this->assertEquals('PT5000S', $affiliateDescriptor->getCacheDuration());
        $affiliateMember = $affiliateDescriptor->getAffiliateMember();
        $this->assertCount(2, $affiliateMember);
        $this->assertEquals('Member', $affiliateMember[0]->getContent());
        $this->assertEquals('OtherMember', $affiliateMember[1]->getContent());
    }


    /**
     * @return void
     */
    public function testUnmarshallingWithoutMembers(): void
    {
        $mdNamespace = C::NS_MD;
        $document = DOMDocumentFactory::fromString(<<<XML
<md:AffiliationDescriptor xmlns:md="{$mdNamespace}" affiliationOwnerID="TheOwner" ID="TheID" validUntil="2009-02-13T23:31:30Z" cacheDuration="PT5000S">
</md:AffiliationDescriptor>
XML
        );
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Missing AffiliateMember in AffiliationDescriptor.');
        new AffiliationDescriptor($document->firstChild);
    }


    /**
     * @return void
     */
    public function testUnmarshallingWithoutOwner(): void
    {
        $mdNamespace = C::NS_MD;
        $document = DOMDocumentFactory::fromString(<<<XML
    <md:AffiliationDescriptor xmlns:md="{$mdNamespace}" ID="TheID" validUntil="2009-02-13T23:31:30Z" cacheDuration="PT5000S">
    <md:AffiliateMember>Member</md:AffiliateMember>
    <md:AffiliateMember>OtherMember</md:AffiliateMember>
</md:AffiliationDescriptor>
XML
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Missing affiliationOwnerID on AffiliationDescriptor.');
        new AffiliationDescriptor($document->firstChild);
    }
}
