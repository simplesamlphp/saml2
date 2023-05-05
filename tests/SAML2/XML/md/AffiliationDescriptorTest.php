<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use SAML2\Constants;
use SAML2\XML\md\AffiliationDescriptor;
use SAML2\Utils;
use SimpleSAML\XML\DOMDocumentFactory;

class AffiliationDescriptorTest extends \PHPUnit\Framework\TestCase
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
            'Member1',
            'Member2',
        ]);
        $affiliationDescriptorElement->setKeyDescriptor([
            Utils::createKeyDescriptor("testCert")
        ]);

        $affiliationDescriptorElement = $affiliationDescriptorElement->toXML($document->firstChild);

        $affiliationDescriptorElements = Utils::xpQuery(
            $affiliationDescriptorElement,
            '/root/saml_metadata:AffiliationDescriptor'
        );
        $this->assertCount(1, $affiliationDescriptorElements);
        $affiliationDescriptorElement = $affiliationDescriptorElements[0];

        $this->assertEquals('TheOwner', $affiliationDescriptorElement->getAttribute("affiliationOwnerID"));
        $this->assertEquals('TheID', $affiliationDescriptorElement->getAttribute("ID"));
        $this->assertEquals('2009-02-13T23:31:30Z', $affiliationDescriptorElement->getAttribute("validUntil"));
        $this->assertEquals('PT5000S', $affiliationDescriptorElement->getAttribute("cacheDuration"));

        $affiliateMembers = Utils::xpQuery($affiliationDescriptorElement, './saml_metadata:AffiliateMember');
        $this->assertCount(2, $affiliateMembers);
        $this->assertEquals('Member1', $affiliateMembers[0]->textContent);
        $this->assertEquals('Member2', $affiliateMembers[1]->textContent);
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $mdNamespace = Constants::NS_MD;
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
        $this->assertEquals('Member', $affiliateMember[0]);
        $this->assertEquals('OtherMember', $affiliateMember[1]);
    }


    /**
     * @return void
     */
    public function testUnmarshallingWithoutMembers(): void
    {
        $mdNamespace = Constants::NS_MD;
        $document = DOMDocumentFactory::fromString(<<<XML
<md:AffiliationDescriptor xmlns:md="{$mdNamespace}" affiliationOwnerID="TheOwner" ID="TheID" validUntil="2009-02-13T23:31:30Z" cacheDuration="PT5000S">
</md:AffiliationDescriptor>
XML
        );
        $this->expectException(\Exception::class, 'Missing AffiliateMember in AffiliationDescriptor.');
        new AffiliationDescriptor($document->firstChild);
    }


    /**
     * @return void
     */
    public function testUnmarshallingWithoutOwner(): void
    {
        $mdNamespace = Constants::NS_MD;
        $document = DOMDocumentFactory::fromString(<<<XML
    <md:AffiliationDescriptor xmlns:md="{$mdNamespace}" ID="TheID" validUntil="2009-02-13T23:31:30Z" cacheDuration="PT5000S">
    <md:AffiliateMember>Member</md:AffiliateMember>
    <md:AffiliateMember>OtherMember</md:AffiliateMember>
</md:AffiliationDescriptor>
XML
        );

        $this->expectException(\Exception::class, 'Missing affiliationOwnerID on AffiliationDescriptor.');
        new AffiliationDescriptor($document->firstChild);
    }
}
