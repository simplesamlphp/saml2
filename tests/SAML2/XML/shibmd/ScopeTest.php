<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\shibmd;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\shibmd\Scope;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

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
    public static function setUpBeforeClass(): void
    {
        self::$schemaFile = dirname(__FILE__, 5) . '/resources/schemas/sstc-saml-metadata-shibmd-v1.0.xsd';

        self::$testedClass = Scope::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/shibmd_Scope.xml',
        );
    }


    /**
     * Marshalling a scope in literal (non-regexp) form.
     */
    public function testMarshallingLiteral(): void
    {
        $scope = new Scope("example.org", false);

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($scope),
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

        $xpCache = XPath::getXPath($scopeElement);
        /** @var \DOMElement[] $scopeElements */
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

        $xpCache = XPath::getXPath($scopeElement);
        /** @var \DOMElement[] $scopeElements */
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
        $scope = Scope::fromXML(self::$xmlRepresentation->documentElement);

        $this->assertEquals('example.org', $scope->getContent());
        $this->assertFalse($scope->isRegexpScope());
    }


    /**
     * Unmarshalling a scope that does not specify an explicit
     * regexp value (assumed to be false).
     */
    public function testUnmarshallingWithoutRegexpValue(): void
    {
        $scope = Scope::fromXML(self::$xmlRepresentation->documentElement);

        $this->assertEquals('example.org', $scope->getContent());
        $this->assertFalse($scope->isRegexpScope());
    }


    /**
     * Unmarshalling a scope in regexp form.
     */
    public function testUnmarshallingRegexp(): void
    {
        $document = clone self::$xmlRepresentation;
        $document->documentElement->setAttribute('regexp', 'true');
        $document->documentElement->textContent = '^(.*|)example.edu$';

        $scope = Scope::fromXML($document->documentElement);
        $this->assertEquals('^(.*|)example.edu$', $scope->getContent());
        $this->assertTrue($scope->isRegexpScope());
    }
}
