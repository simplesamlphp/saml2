<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\shibmd;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\shibmd\Scope;
use SimpleSAML\Test\XML\SchemaValidationTestTrait;
use SimpleSAML\Test\XML\SerializableElementTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\shibmd\Scope
 *
 * @covers \SimpleSAML\SAML2\XML\shibmd\Scope
 * @covers \SimpleSAML\SAML2\XML\shibmd\AbstractShibmdElement
 * @package simplesamlphp/saml2
 */
final class ScopeTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public function setUp(): void
    {
        $this->schema = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/schemas/shibboleth.xsd';

        $this->testedClass = Scope::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/shibmd_Scope.xml'
        );
    }


    /**
     * Marshalling a scope in literal (non-regexp) form.
     */
    public function testMarshallingLiteral(): void
    {
        $scope = new Scope("example.org", false);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($scope)
        );
    }


    /**
     * Marshalling a scope which does not specificy the value for
     * regexp explicitly (expect it to default to 'false').
     */
    public function testMarshallingImplicitRegexpValue(): void
    {
        $scope = new Scope("example.org");

        $document = DOMDocumentFactory::fromString('<root />');
        $scopeElement = $scope->toXML($document->documentElement);

        /** @var \DOMElement[] $scopeElements */
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
     */
    public function testMarshallingRegexp(): void
    {
        $scope = new Scope("^(.*\.)?example\.edu$", true);

        $document = DOMDocumentFactory::fromString('<root />');
        $scopeElement = $scope->toXML($document->documentElement);

        /** @var \DOMElement[] $scopeElements */
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
     */
    public function testUnmarshallingLiteral(): void
    {
        $scope = Scope::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals('example.org', $scope->getContent());
        $this->assertFalse($scope->isRegexpScope());
    }


    /**
     * Unmarshalling a scope that does not specify an explicit
     * regexp value (assumed to be false).
     */
    public function testUnmarshallingWithoutRegexpValue(): void
    {
        $scope = Scope::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals('example.org', $scope->getContent());
        $this->assertFalse($scope->isRegexpScope());
    }


    /**
     * Unmarshalling a scope in regexp form.
     */
    public function testUnmarshallingRegexp(): void
    {
        $document = $this->xmlRepresentation;
        $document->documentElement->setAttribute('regexp', 'true');
        $document->documentElement->textContent = '^(.*|)example.edu$';

        $scope = Scope::fromXML($document->documentElement);
        $this->assertEquals('^(.*|)example.edu$', $scope->getContent());
        $this->assertTrue($scope->isRegexpScope());
    }
}
