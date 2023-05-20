<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\md\KeyDescriptor;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XMLSecurity\XML\ds\KeyInfo;
use SimpleSAML\XMLSecurity\XML\ds\X509Certificate;
use SimpleSAML\XMLSecurity\XML\ds\X509Data;

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
        $kd = new KeyDescriptor(new KeyInfo([new X509Data([new X509Certificate(
            '/CTj03d1DB5e2t7CTo9BEzCf5S9NRzwnBgZRlm32REI='
        )])]));
        $roleDescriptor->setKeyDescriptor([$kd]);

        $document = DOMDocumentFactory::fromString('<root />');
        $roleDescriptorElement = $roleDescriptor->toXML($document->firstChild);

        $xpCache = XPath::getXPath($roleDescriptorElement);
        $roleDescriptorElement = XPath::xpQuery($roleDescriptorElement, '/root/md:RoleDescriptor', $xpCache);
        $this->assertCount(1, $roleDescriptorElement);
        /** @var \DOMElement $roleDescriptorElement */
        $roleDescriptorElement = $roleDescriptorElement[0];

        $this->assertEquals('SomeID', $roleDescriptorElement->getAttribute("ID"));
        $this->assertEquals('2009-02-13T23:31:30Z', $roleDescriptorElement->getAttribute("validUntil"));
        $this->assertEquals('PT5000S', $roleDescriptorElement->getAttribute("cacheDuration"));
        $this->assertEquals('protocol1 protocol2', $roleDescriptorElement->getAttribute("protocolSupportEnumeration"));
        $this->assertEquals('myns:MyElement', $roleDescriptorElement->getAttributeNS(C::NS_XSI, "type"));
        $this->assertEquals('http://example.org/mynsdefinition', $roleDescriptorElement->lookupNamespaceURI("myns"));
    }
}
