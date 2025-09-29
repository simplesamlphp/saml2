<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\XML\idpdisc\DiscoveryResponse;
use SimpleSAML\SAML2\XML\md\AbstractIndexedEndpointType;
use SimpleSAML\SAML2\XML\md\AbstractMdElement;
use SimpleSAML\SAML2\XML\md\ArtifactResolutionService;
use SimpleSAML\SAML2\XML\md\AssertionConsumerService;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\MissingAttributeException;

use function dirname;
use function sprintf;

/**
 * Class \SimpleSAML\SAML2\XML\md\IndexedEndpointTypeTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('md')]
#[CoversClass(AbstractIndexedEndpointType::class)]
#[CoversClass(AbstractMdElement::class)]
final class IndexedEndpointTypeTest extends TestCase
{
    private static string $resourcePath;


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
     * @param string $xmlRepresentation
     */
    #[DataProvider('classProvider')]
    public function testMarshallingWithoutIsDefault(string $class, string $xmlRepresentation): void
    {
        $binding = ($class === DiscoveryResponse::class) ? C::BINDING_IDPDISC : C::BINDING_HTTP_POST;
        $idxep = new $class(42, $binding, C::LOCATION_A);
        $this->assertNull($idxep->getIsDefault());
    }


    // test unmarshalling


    /**
     * Test that creating an IndexedEndpointType from XML with a numeric string index succeeds.
     *
     * @param class-string $class
     * @param string $xmlRepresentation
     */
    #[DataProvider('classProvider')]
    public function testUnmarshallingWithNumericString(string $class, string $xmlRepresentation): void
    {
        $xmlRepresentation = DOMDocumentFactory::fromFile(
            self::$resourcePath . $xmlRepresentation,
        );
        $xmlRepresentation->documentElement->setAttribute('index', '+0000000000000000000005');

        $endpoint = $class::fromXML($xmlRepresentation->documentElement);
        $this->assertEquals(5, $endpoint->getIndex());
    }


    /**
     * Test that creating an IndexedEndpointType from XML checks the actual name of the endpoint.
     */
    public function testUnmarshallingUnexpectedEndpoint(): void
    {
        $xmlRepresentation = DOMDocumentFactory::fromFile(
            self::$resourcePath . 'md_AssertionConsumerService.xml',
        );

        $this->expectException(InvalidDOMElementException::class);
        $this->expectExceptionMessage(
            'Unexpected name for endpoint: AssertionConsumerService. Expected: ArtifactResolutionService.',
        );

        ArtifactResolutionService::fromXML($xmlRepresentation->documentElement);
    }


    /**
     * Test that creating an IndexedEndpointType from XML without an index fails.
     *
     * @param class-string $class
     * @param string $xmlRepresentation
     */
    #[DataProvider('classProvider')]
    public function testUnmarshallingWithoutIndex(string $class, string $xmlRepresentation): void
    {
        $xmlRepresentation = DOMDocumentFactory::fromFile(
            self::$resourcePath . $xmlRepresentation,
        );
        $xmlRepresentation->documentElement->removeAttribute('index');

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage(sprintf(
            'Missing \'index\' attribute on %s:%s',
            $class::getNamespacePrefix(),
            $class::getLocalName(),
        ));

        $class::fromXML($xmlRepresentation->documentElement);
    }


    /**
     * Test that creating an IndexedEndpointType from XML with a non-numeric index fails.
     *
     * @param class-string $class
     * @param string $xmlRepresentation
     */
    #[DataProvider('classProvider')]
    public function testUnmarshallingWithWrongIndex(string $class, string $xmlRepresentation): void
    {
        $xmlRepresentation = DOMDocumentFactory::fromFile(
            self::$resourcePath . $xmlRepresentation,
        );
        $xmlRepresentation->documentElement->setAttribute('index', 'value');

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage(sprintf(
            'The \'index\' attribute of %s:%s must be numerical.',
            $class::getNamespacePrefix(),
            $class::getLocalName(),
        ));

        $class::fromXML($xmlRepresentation->documentElement);
    }


    /**
     * Test that creating an IndexedEndpointType from XML without isDefault works.
     *
     * @param class-string $class
     * @param string $xmlRepresentation
     */
    #[DataProvider('classProvider')]
    public function testUnmarshallingWithoutIsDefault(string $class, string $xmlRepresentation): void
    {
        $xmlRepresentation = DOMDocumentFactory::fromFile(
            self::$resourcePath . $xmlRepresentation,
        );

        $xmlRepresentation->documentElement->removeAttribute('isDefault');
        $acs = $class::fromXML($xmlRepresentation->documentElement);
        $this->assertNull($acs->getIsDefault());
    }


    /**
     * Test that creating an IndexedEndpointType from XML with isDefault of a non-boolean value fails.
     *
     * @param class-string $class
     * @param string $xmlRepresentation
     */
    #[DataProvider('classProvider')]
    public function testUnmarshallingWithWrongIsDefault(string $class, string $xmlRepresentation): void
    {
        $xmlRepresentation = DOMDocumentFactory::fromFile(
            self::$resourcePath . $xmlRepresentation,
        );
        $xmlRepresentation->documentElement->setAttribute('isDefault', 'non-bool');

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage(sprintf(
            'The \'isDefault\' attribute of %s:%s must be a boolean.',
            $class::getNamespacePrefix(),
            $class::getLocalName(),
        ));

        $class::fromXML($xmlRepresentation->documentElement);
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
