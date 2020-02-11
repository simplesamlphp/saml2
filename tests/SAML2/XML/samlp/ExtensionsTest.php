<?php

declare(strict_types=1);

namespace SAML2\XML\samlp;

use SAML2\DOMDocumentFactory;
use SAML2\XML\Chunk;
use SAML2\XML\saml\Attribute;
use SAML2\XML\samlp\Extensions;
use SAML2\XML\shibmd\Scope;

/**
 * Class \SAML2\XML\samlp\ExtensionsTest
 */
class ExtensionsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \DOMDocument
     */
    private $document;


    /**
     * Prepare a basic DOMelement to test against
     * @return void
     */
    public function setUp(): void
    {
        $this->document = DOMDocumentFactory::fromString(<<<XML
<samlp:Response xmlns:samlp="urn:oasis:names:tc:SAML:2.0:protocol"
                xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                ID="s2a0da3504aff978b0f8c80f6a62c713c4a2f64c5b"
                InResponseTo="_bec424fa5103428909a30ff1e31168327f79474984"
                Version="2.0"
                IssueInstant="2007-12-10T11:39:48Z"
                Destination="http://moodle.bridge.feide.no/simplesaml/saml2/sp/AssertionConsumerService.php">
    <saml:Issuer>max.feide.no</saml:Issuer>
    <samlp:Extensions>
        <myns:AttributeList xmlns:myns="urn:mynamespace">
            <myns:Attribute name="UserName" value=""/>
        </myns:AttributeList>
        <ExampleElement name="AnotherExtension" />
    </samlp:Extensions>
    <samlp:Status>
        <samlp:StatusCode Value="urn:oasis:names:tc:SAML:2.0:status:Success"/>
    </samlp:Status>
</samlp:Response>
XML
        );
    }


    /**
     * Test the getList() method.
     * @return void
     */
    public function testExtensionsGet(): void
    {
        $list = Extensions::getList($this->document->documentElement);

        $this->assertCount(2, $list);
        $this->assertEquals("urn:mynamespace", $list[0]->getNamespaceURI());
        $this->assertEquals("ExampleElement", $list[1]->getLocalName());
    }


    /**
     * Adding empty list should leave existing extensions unchanged.
     * @return void
     */
    public function testExtensionsAddEmpty(): void
    {
        Extensions::addList($this->document->documentElement, []);

        $list = Extensions::getList($this->document->documentElement);

        $this->assertCount(2, $list);
        $this->assertEquals("urn:mynamespace", $list[0]->getNamespaceURI());
        $this->assertEquals("ExampleElement", $list[1]->getLocalName());
    }


    /**
     * Test adding two random elements.
     * @return void
     */
    public function testExtensionsAddSome(): void
    {
        $attribute = new Attribute('TheName');
        $scope = new Scope("scope");

        Extensions::addList(
            $this->document->documentElement,
            [
                new Chunk($attribute->toXML()),
                new Chunk($scope->toXML())
            ]
        );

        $list = Extensions::getList($this->document->documentElement);

        $this->assertCount(4, $list);
        $this->assertEquals("urn:mynamespace", $list[0]->getNamespaceURI());
        $this->assertEquals("ExampleElement", $list[1]->getLocalName());
        $this->assertEquals("Attribute", $list[2]->getLocalName());
        $this->assertEquals("urn:mace:shibboleth:metadata:1.0", $list[3]->getNamespaceURI());
    }
}
