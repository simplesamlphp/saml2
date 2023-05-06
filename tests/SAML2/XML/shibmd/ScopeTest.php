<?php

declare(strict_types=1);

namespace SAML2\XML\shibmd;

use SAML2\XML\shibmd\Scope;
use SAML2\Utils\XPath;
use SimpleSAML\XML\DOMDocumentFactory;

/**
 * Class \SAML2\XML\shibmd\Scope
 */
class ScopeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Marshalling a scope in literal (non-regexp) form.
     * @return void
     */
    public function testMarshallingLiteral(): void
    {
        $scope = new Scope();
        $scope->setScope("example.org");
        $scope->setIsRegexpScope(false);

        $document = DOMDocumentFactory::fromString('<root />');
        $scopeElement = $scope->toXML($document->firstChild);

        $xpCache = XPath::getXPath($scopeElement);
        $scopeElements = XPath::xpQuery($scopeElement, '/root/shibmd:Scope', $xpCache);
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
        $scope = new Scope();
        $scope->setScope("example.org");

        $document = DOMDocumentFactory::fromString('<root />');
        $scopeElement = $scope->toXML($document->firstChild);

        $xpCache = XPath::getXPath($scopeElement);
        $scopeElements = XPath::xpQuery($scopeElement, '/root/shibmd:Scope', $xpCache);
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
        $scope = new Scope();
        $scope->setScope("^(.*\.)?example\.edu$");
        $scope->setIsRegexpScope(true);

        $document = DOMDocumentFactory::fromString('<root />');
        $scopeElement = $scope->toXML($document->firstChild);

        $xpCache = XPath::getXPath($scopeElement);
        $scopeElements = XPath::xpQuery($scopeElement, '/root/shibmd:Scope', $xpCache);
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
        $document = DOMDocumentFactory::fromString(<<<XML
<shibmd:Scope regexp="false">example.org</shibmd:Scope>
XML
        );
        $scope = new Scope($document->firstChild);

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
        $document = DOMDocumentFactory::fromString(<<<XML
<shibmd:Scope>example.org</shibmd:Scope>
XML
        );
        $scope = new Scope($document->firstChild);

        $this->assertEquals('example.org', $scope->getScope());
        $this->assertFalse($scope->isRegexpScope());
    }


    /**
     * Unmarshalling a scope in regexp form.
     * @return void
     */
    public function testUnmarshallingRegexp(): void
    {
        $document = DOMDocumentFactory::fromString(<<<XML
<shibmd:Scope regexp="true">^(.*|)example.edu$</shibmd:Scope>
XML
        );
        $scope = new Scope($document->firstChild);

        $this->assertEquals('^(.*|)example.edu$', $scope->getScope());
        $this->assertTrue($scope->isRegexpScope());
    }
}
