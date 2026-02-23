<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\XML\md\EndpointType;
use SAML2\Utils;

/**
 * Class \SAML2\XML\md\EndpointType
 */
class EndpointTypeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $endpointType = new EndpointType();
        $endpointType->setBinding('TestBinding');
        $endpointType->setLocation('TestLocation');

        $document = DOMDocumentFactory::fromString('<root />');
        $endpointTypeElement = $endpointType->toXML($document->firstChild, 'md:Test');
        $endpointTypeElements = Utils::xpQuery($endpointTypeElement, '/root/saml_metadata:Test');

        $this->assertCount(1, $endpointTypeElements);
        $endpointTypeElement = $endpointTypeElements[0];

        $this->assertEquals('TestBinding', $endpointTypeElement->getAttribute('Binding'));
        $this->assertEquals('TestLocation', $endpointTypeElement->getAttribute('Location'));
        $this->assertFalse($endpointTypeElement->hasAttribute('ResponseLocation'));

        $endpointType->setResponseLocation('TestResponseLocation');

        $document->loadXML('<root />');
        $endpointTypeElement = $endpointType->toXML($document->firstChild, 'md:Test');

        $endpointTypeElement = Utils::xpQuery($endpointTypeElement, '/root/saml_metadata:Test');
        $this->assertCount(1, $endpointTypeElement);
        $endpointTypeElement = $endpointTypeElement[0];

        $this->assertEquals('TestResponseLocation', $endpointTypeElement->getAttribute('ResponseLocation'));
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $mdNamespace = Constants::NS_MD;
        $document = DOMDocumentFactory::fromString(<<<XML
<md:Test xmlns:md="{$mdNamespace}" Binding="urn:something" Location="https://whatever/" xmlns:test="urn:test" test:attr="value" />
XML
        );
        $endpointType = new EndpointType($document->firstChild);
        $this->assertEquals(true, $endpointType->hasAttributeNS('urn:test', 'attr'));
        $this->assertEquals('value', $endpointType->getAttributeNS('urn:test', 'attr'));
        $this->assertEquals(false, $endpointType->hasAttributeNS('urn:test', 'invalid'));
        $this->assertEquals('', $endpointType->getAttributeNS('urn:test', 'invalid'));

        $endpointType->removeAttributeNS('urn:test', 'attr');
        $this->assertEquals(false, $endpointType->hasAttributeNS('urn:test', 'attr'));
        $this->assertEquals('', $endpointType->getAttributeNS('urn:test', 'attr'));

        $endpointType->setAttributeNS('urn:test2', 'test2:attr2', 'value2');
        $this->assertEquals('value2', $endpointType->getAttributeNS('urn:test2', 'attr2'));

        $document->loadXML('<root />');
        $endpointTypeElement = $endpointType->toXML($document->firstChild, 'md:Test');
        $endpointTypeElements = Utils::xpQuery($endpointTypeElement, '/root/saml_metadata:Test');
        $this->assertCount(1, $endpointTypeElements);
        $endpointTypeElement = $endpointTypeElements[0];

        $this->assertEquals('value2', $endpointTypeElement->getAttributeNS('urn:test2', 'attr2'));
        $this->assertEquals(false, $endpointTypeElement->hasAttributeNS('urn:test', 'attr'));
    }
}
