<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\XML\idpdisc\DiscoveryResponse;
use SimpleSAML\SAML2\XML\md\ArtifactResolutionService;
use SimpleSAML\SAML2\XML\md\AssertionConsumerService;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\MissingAttributeException;

use function dirname;
use function sprintf;
use function strval;

/**
 * Class \SAML2\XML\md\IndexedEndpointTypeTest
 *
 * @covers \SimpleSAML\SAML2\XML\md\AbstractIndexedEndpointType
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMdElement
 * @package simplesamlphp/saml2
 */
final class IndexedEndpointTypeTest extends TestCase
{
    protected static string $resourcePath;

    protected DOMDocument $xmlRepresentation;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$resourcePath = dirname(__FILE__, 4) . '/resources/xml/';
    }


    // test marshalling


    /**
     * Test that creating an IndexedEndpointType from scratch without specifying isDefault works.
     *
     * @param class-string $class
     *
     * @dataProvider classProvider
     */
    public function testMarshallingWithoutIsDefault(string $class): void
    {
        $idxep = new $class(42, C::BINDING_HTTP_POST, C::LOCATION_A);
        $this->assertNull($idxep->getIsDefault());
    }


    // test unmarshalling


    /**
     * Test that creating an IndexedEndpointType from XML checks the actual name of the endpoint.
     */
    public function testUnmarshallingUnexpectedEndpoint(): void
    {
        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            self::$resourcePath . 'md_AssertionConsumerService.xml',
        );

        $this->expectException(InvalidDOMElementException::class);
        $this->expectExceptionMessage(
            'Unexpected name for endpoint: AssertionConsumerService. Expected: ArtifactResolutionService.',
        );
        ArtifactResolutionService::fromXML($this->xmlRepresentation->documentElement);
    }


    /**
     * Test that creating an IndexedEndpointType from XML without an index fails.
     *
     * @param class-string $class
     * @param string $xmlRepresentation
     *
     * @dataProvider classProvider
     */
    public function testUnmarshallingWithoutIndex(string $class, string $xmlRepresentation): void
    {
        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            self::$resourcePath . $xmlRepresentation,
        );

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage(sprintf(
            'Missing \'index\' attribute on %s:%s',
            $class::getNamespacePrefix(),
            $class::getLocalName(),
        ));
        $this->xmlRepresentation->documentElement->removeAttribute('index');
        $class::fromXML($this->xmlRepresentation->documentElement);
    }


    /**
     * Test that creating an IndexedEndpointType from XML with a non-numeric index fails.
     *
     * @param class-string $class
     * @param string $xmlRepresentation
     *
     * @dataProvider classProvider
     */
    public function testUnmarshallingWithWrongIndex(string $class, string $xmlRepresentation): void
    {
        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            self::$resourcePath . $xmlRepresentation,
        );

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage(sprintf(
            'The \'index\' attribute of %s:%s must be numerical.',
            $class::getNamespacePrefix(),
            $class::getLocalName(),
        ));
        $this->xmlRepresentation->documentElement->setAttribute('index', 'value');
        $class::fromXML($this->xmlRepresentation->documentElement);
    }


    /**
     * Test that creating an IndexedEndpointType from XML without isDefault works.
     *
     * @param class-string $class
     * @param string $xmlRepresentation
     *
     * @dataProvider classProvider
     */
    public function testUnmarshallingWithoutIsDefault(string $class, string $xmlRepresentation): void
    {
        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            self::$resourcePath . $xmlRepresentation,
        );


        $this->xmlRepresentation->documentElement->removeAttribute('isDefault');
        $acs = $class::fromXML($this->xmlRepresentation->documentElement);
        $this->assertNull($acs->getIsDefault());
    }


    /**
     * Test that creating an IndexedEndpointType from XML with isDefault of a non-boolean value fails.
     *
     * @param class-string $class
     * @param string $xmlRepresentation
     *
     * @dataProvider classProvider
     */
    public function testUnmarshallingWithWrongIsDefault(string $class, string $xmlRepresentation): void
    {
        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            self::$resourcePath . $xmlRepresentation,
        );

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage(sprintf(
            'The \'isDefault\' attribute of %s:%s must be a boolean.',
            $class::getNamespacePrefix(),
            $class::getLocalName(),
        ));
        $this->xmlRepresentation->documentElement->setAttribute('isDefault', 'non-bool');
        $class::fromXML($this->xmlRepresentation->documentElement);
    }


    /**
     */
    public static function classProvider(): array
    {
        return [
            'md:ArtifactResolutionService' => [ArtifactResolutionService::class, 'md_ArtifactResolutionService.xml'],
            'md:AssertionConsumerService' => [AssertionConsumerService::class, 'md_AssertionConsumerService.xml'],
            'idpdisc:DiscoveryResponse' => [DiscoveryResponse::class, 'idpdisc_DiscoveryResponse.xml'],
        ];
    }
}
