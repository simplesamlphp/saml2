<?php

namespace SAML2\XML\md;

use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\Utils;

require 'RoleDescriptorMock.php';

class RoleDescriptorTest extends \PHPUnit_Framework_TestCase
{
    public function testMarshalling()
    {
        $roleDescriptor = new RoleDescriptorMock();
        $roleDescriptor->ID = 'SomeID';
        $roleDescriptor->validUntil = 1234567890;
        $roleDescriptor->cacheDuration = 'PT5000S';
        $roleDescriptor->protocolSupportEnumeration = array(
            'protocol1',
            'protocol2',
        );
        $roleDescriptor->errorURL = 'https://example.org/error';

        $document = DOMDocumentFactory::fromString('<root />');
        $roleDescriptorElement = $roleDescriptor->toXML($document->firstChild);

        $roleDescriptorElement = Utils::xpQuery($roleDescriptorElement, '/root/md:RoleDescriptor');
        $this->assertCount(1, $roleDescriptorElement);
        $roleDescriptorElement = $roleDescriptorElement[0];

        $this->assertEquals('SomeID', $roleDescriptorElement->getAttribute("ID"));
        $this->assertEquals('2009-02-13T23:31:30Z', $roleDescriptorElement->getAttribute("validUntil"));
        $this->assertEquals('PT5000S', $roleDescriptorElement->getAttribute("cacheDuration"));
        $this->assertEquals('protocol1 protocol2', $roleDescriptorElement->getAttribute("protocolSupportEnumeration"));
        $this->assertEquals('myns:MyElement', $roleDescriptorElement->getAttributeNS(Constants::NS_XSI, "type"));
        $this->assertEquals('http://example.org/mynsdefinition', $roleDescriptorElement->lookupNamespaceURI("myns"));
    }
}
