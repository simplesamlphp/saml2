<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use SAML2\DOMDocumentFactory;
use SAML2\XML\md\IndexedEndpointType;
use SAML2\Utils;

/**
 * Class \SAML2\XML\md\IndexedEndpointTypeTest
 */
class IndexedEndpointTypeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $indexedEndpointType = new IndexedEndpointType();
        $indexedEndpointType->setBinding('TestBinding');
        $indexedEndpointType->setLocation('TestLocation');
        $indexedEndpointType->setIndex(42);
        $indexedEndpointType->setIsDefault(false);

        $document = DOMDocumentFactory::fromString('<root />');
        $indexedEndpointTypeElement = $indexedEndpointType->toXML($document->firstChild, 'md:Test');

        $indexedEndpointElements = Utils::xpQuery($indexedEndpointTypeElement, '/root/saml_metadata:Test');
        $this->assertCount(1, $indexedEndpointElements);
        $indexedEndpointElement = $indexedEndpointElements[0];

        $this->assertEquals('TestBinding', $indexedEndpointElement->getAttribute('Binding'));
        $this->assertEquals('TestLocation', $indexedEndpointElement->getAttribute('Location'));
        $this->assertEquals('42', $indexedEndpointElement->getAttribute('index'));
        $this->assertEquals('false', $indexedEndpointElement->getAttribute('isDefault'));

        $indexedEndpointType->setIsDefault(true);
        $document->loadXML('<root />');
        $indexedEndpointTypeElement = $indexedEndpointType->toXML($document->firstChild, 'md:Test');
        $indexedEndpointTypeElement = Utils::xpQuery($indexedEndpointTypeElement, '/root/saml_metadata:Test');
        $this->assertCount(1, $indexedEndpointTypeElement);
        $this->assertEquals('true', $indexedEndpointTypeElement[0]->getAttribute('isDefault'));

        $indexedEndpointType->setIsDefault(null);
        $document->loadXML('<root />');
        $indexedEndpointTypeElement = $indexedEndpointType->toXML($document->firstChild, 'md:Test');
        $indexedEndpointTypeElement = Utils::xpQuery($indexedEndpointTypeElement, '/root/saml_metadata:Test');
        $this->assertCount(1, $indexedEndpointTypeElement);
        $this->assertTrue(!$indexedEndpointTypeElement[0]->hasAttribute('isDefault'));
    }
}
