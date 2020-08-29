<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\shibmd;

use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\shibmd\Scope;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Utils as XMLUtils;

/**
 * Class \SAML2\XML\shibmd\Scope
 *
 * @covers \SimpleSAML\SAML2\XML\shibmd\Scope
 * @package simplesamlphp/saml2
 */
final class ScopeTest extends TestCase
{
    /** @var \DOMDocument */
    private $document;


    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->document = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/shibmd_Scope.xml'
        );
    }


    /**
     * Marshalling a scope in literal (non-regexp) form.
     * @return void
     */
    public function testMarshallingLiteral(): void
    {
        $scope = new Scope("example.org", false);

        $document = DOMDocumentFactory::fromString('<root />');
        $scopeElement = $scope->toXML($document->documentElement);

        /** @var \DOMElement[] $scopeElements */
        $scopeElements = XMLUtils::xpQuery($scopeElement, '/root/shibmd:Scope');
        $this->assertCount(1, $scopeElements);
        $scopeElement = $scopeElements[0];

        $this->assertEquals('example.org', $scopeElement->nodeValue);
        $this->assertEquals('urn:mace:shibboleth:metadata:1.0', $scopeElement->namespaceURI);
        $this->assertEquals('false', $scopeElement->getAttribute('regexp'));
    }


    /**
     * Marshalling a scope which does not specificy the value for
     * regexp explicitly (expect it to default to 'false').
     * @return void
     */
    public function testMarshallingImplicitRegexpValue(): void
    {
        $scope = new Scope("example.org");

        $document = DOMDocumentFactory::fromString('<root />');
        $scopeElement = $scope->toXML($document->documentElement);

        /** @var \DOMElement[] $scopeElements */
        $scopeElements = XMLUtils::xpQuery($scopeElement, '/root/shibmd:Scope');
        $this->assertCount(1, $scopeElements);
        $scopeElement = $scopeElements[0];

        $this->assertEquals('example.org', $scopeElement->nodeValue);
        $this->assertEquals('urn:mace:shibboleth:metadata:1.0', $scopeElement->namespaceURI);
        $this->assertEquals('false', $scopeElement->getAttribute('regexp'));
    }


    /**
     * Marshalling a scope which is in regexp form.
     * @return void
     */
    public function testMarshallingRegexp(): void
    {
        $scope = new Scope("^(.*\.)?example\.edu$", true);

        $document = DOMDocumentFactory::fromString('<root />');
        $scopeElement = $scope->toXML($document->documentElement);

        /** @var \DOMElement[] $scopeElements */
        $scopeElements = XMLUtils::xpQuery($scopeElement, '/root/shibmd:Scope');
        $this->assertCount(1, $scopeElements);
        $scopeElement = $scopeElements[0];

        $this->assertEquals('^(.*\.)?example\.edu$', $scopeElement->nodeValue);
        $this->assertEquals('urn:mace:shibboleth:metadata:1.0', $scopeElement->namespaceURI);
        $this->assertEquals('true', $scopeElement->getAttribute('regexp'));
    }


    /**
     * Unmarshalling a scope in literal (non-regexp) form.
     * @return void
     */
    public function testUnmarshallingLiteral(): void
    {
        $scope = Scope::fromXML($this->document->documentElement);

        $this->assertEquals('example.org', $scope->getScope());
        $this->assertFalse($scope->isRegexpScope());
    }


    /**
     * Unmarshalling a scope that does not specify an explicit
     * regexp value (assumed to be false).
     * @return void
     */
    public function testUnmarshallingWithoutRegexpValue(): void
    {
        $scope = Scope::fromXML($this->document->documentElement);

        $this->assertEquals('example.org', $scope->getScope());
        $this->assertFalse($scope->isRegexpScope());
    }


    /**
     * Unmarshalling a scope in regexp form.
     * @return void
     */
    public function testUnmarshallingRegexp(): void
    {
        $document = $this->document;
        $document->documentElement->setAttribute('regexp', 'true');
        $document->documentElement->textContent = '^(.*|)example.edu$';

        $scope = Scope::fromXML($document->documentElement);
        $this->assertEquals('^(.*|)example.edu$', $scope->getScope());
        $this->assertTrue($scope->isRegexpScope());
    }


    /**
     * Test serialization and unserialization of Scope elements.
     */
    public function testSerialization(): void
    {
        $scope = Scope::fromXML($this->document->documentElement);
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize($scope)))
        );
    }
}
