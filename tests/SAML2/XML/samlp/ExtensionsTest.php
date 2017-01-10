<?php

namespace SAML2\XML\samlp;

use SAML2\DOMDocumentFactory;
use SAML2\XML\saml\Attribute;
use SAML2\XML\shibmd\Scope;

/**
 * Class \SAML2\XML\samlp\ExtensionsTest
 */
class ExtensionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \DOMElement
     */
    private $testElement;

    /**
     * Prepare a basic DOMelement to test against
     */
    public function setUp()
    {
        $document = DOMDocumentFactory::fromString(
<<<XML
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
        $this->testElement = $document->documentElement;
    }

    /**
     * Test the getList() method.
     */
    public function testExtensionsGet()
    {
        $list = Extensions::getList($this->testElement);

        $this->assertCount(2, $list);
        $this->assertEquals("urn:mynamespace", $list[0]->namespaceURI);
        $this->assertEquals("ExampleElement", $list[1]->localName);
    }

    /**
     * Adding empty list should leave existing extensions unchanged.
     */
    public function testExtensionsAddEmpty()
    {
        Extensions::addList($this->testElement, array());

        $list = Extensions::getList($this->testElement);

        $this->assertCount(2, $list);
        $this->assertEquals("urn:mynamespace", $list[0]->namespaceURI);
        $this->assertEquals("ExampleElement", $list[1]->localName);
    }

    /**
     * Test adding two random elements.
     */
    public function testExtensionsAddSome()
    {
        $attribute = new Attribute();
        $attribute->Name = 'TheName';
        $scope = new Scope();
        $scope->scope = "scope";

        Extensions::addList($this->testElement, array($attribute, $scope));

        $list = Extensions::getList($this->testElement);

        $this->assertCount(4, $list);
        $this->assertEquals("urn:mynamespace", $list[0]->namespaceURI);
        $this->assertEquals("ExampleElement", $list[1]->localName);
        $this->assertEquals("Attribute", $list[2]->localName);
        $this->assertEquals("urn:mace:shibboleth:metadata:1.0", $list[3]->namespaceURI);
    }
}
