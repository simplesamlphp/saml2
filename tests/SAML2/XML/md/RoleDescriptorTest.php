<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use PHPUnit\Framework\TestCase;
use SAML2\Constants as C;
use SAML2\Utils;
use SAML2\Utils\XPath;
use SimpleSAML\XML\DOMDocumentFactory;

class RoleDescriptorTest extends TestCase
{
    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $roleDescriptor = new RoleDescriptorMock();
        $roleDescriptor->setID('SomeID');
        $roleDescriptor->setValidUntil(1234567890);
        $roleDescriptor->setCacheDuration('PT5000S');
        $roleDescriptor->setProtocolSupportEnumeration([
            'protocol1',
            'protocol2',
        ]);
        $roleDescriptor->setErrorURL('https://example.org/error');
        $roleDescriptor->setKeyDescriptor([
            Utils::createKeyDescriptor("testCert")
        ]);

        $document = DOMDocumentFactory::fromString('<root />');
        $roleDescriptorElement = $roleDescriptor->toXML($document->firstChild);

        $xpCache = XPath::getXPath($roleDescriptorElement);
        $roleDescriptorElement = XPath::xpQuery($roleDescriptorElement, '/root/md:RoleDescriptor', $xpCache);
        $this->assertCount(1, $roleDescriptorElement);
        $roleDescriptorElement = $roleDescriptorElement[0];

        $this->assertEquals('SomeID', $roleDescriptorElement->getAttribute("ID"));
        $this->assertEquals('2009-02-13T23:31:30Z', $roleDescriptorElement->getAttribute("validUntil"));
        $this->assertEquals('PT5000S', $roleDescriptorElement->getAttribute("cacheDuration"));
        $this->assertEquals('protocol1 protocol2', $roleDescriptorElement->getAttribute("protocolSupportEnumeration"));
        $this->assertEquals('myns:MyElement', $roleDescriptorElement->getAttributeNS(C::NS_XSI, "type"));
        $this->assertEquals('http://example.org/mynsdefinition', $roleDescriptorElement->lookupNamespaceURI("myns"));
    }
}
