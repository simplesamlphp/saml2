<?php

namespace SAML2\XML\md;

use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\Utils;

/**
 * Class \SAML2\XML\md\AdditionalMetadataLocationTest
 */
class AdditionalMetadataLocationTest extends \PHPUnit_Framework_TestCase
{
    public function testMarshalling()
    {
        $document = DOMDocumentFactory::fromString('<root/>');

        $additionalMetadataLocation = new AdditionalMetadataLocation();
        $additionalMetadataLocation->namespace = 'NamespaceAttribute';
        $additionalMetadataLocation->location = 'TheLocation';
        $additionalMetadataLocationElement = $additionalMetadataLocation->toXML($document->firstChild);

        $additionalMetadataLocationElements = Utils::xpQuery(
            $additionalMetadataLocationElement,
            '/root/saml_metadata:AdditionalMetadataLocation'
        );
        $this->assertCount(1, $additionalMetadataLocationElements);
        $additionalMetadataLocationElement = $additionalMetadataLocationElements[0];

        $this->assertEquals('TheLocation', $additionalMetadataLocationElement->textContent);
        $this->assertEquals('NamespaceAttribute', $additionalMetadataLocationElement->getAttribute("namespace"));
    }

    public function testUnmarshalling()
    {
        $document = DOMDocumentFactory::fromString(
            '<md:AdditionalMetadataLocation xmlns:md="' . Constants::NS_MD . '"'.
            ' namespace="TheNamespaceAttribute">LocationText</md:AdditionalMetadataLocation>'
        );
        $additionalMetadataLocation = new AdditionalMetadataLocation($document->firstChild);
        $this->assertEquals('TheNamespaceAttribute', $additionalMetadataLocation->namespace);
        $this->assertEquals('LocationText', $additionalMetadataLocation->location);

        $document->loadXML(
            '<md:AdditionalMetadataLocation xmlns:md="' . Constants::NS_MD . '"'.
            '>LocationText</md:AdditionalMetadataLocation>'
        );
        $this->setExpectedException('Exception', 'Missing namespace attribute on AdditionalMetadataLocation element.');
        new AdditionalMetadataLocation($document->firstChild);
    }
}
