<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use DOMDocument;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\SAML2\XML\md\AbstractMdElement;
use SimpleSAML\SAML2\XML\md\Extensions;
use SimpleSAML\SAML2\XML\md\Organization;
use SimpleSAML\SAML2\XML\md\OrganizationDisplayName;
use SimpleSAML\SAML2\XML\md\OrganizationName;
use SimpleSAML\SAML2\XML\md\OrganizationURL;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\XML\Attribute as XMLAttribute;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\ArrayizableElementTestTrait;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XML\Type\LangValue;
use SimpleSAML\XMLSchema\Type\StringValue;

use function dirname;
use function strval;

/**
 * Test for the Organization metadata element.
 *
 * @package simplesamlphp/saml2
 */
#[Group('md')]
#[CoversClass(Organization::class)]
#[CoversClass(AbstractMdElement::class)]
final class OrganizationTest extends TestCase
{
    use ArrayizableElementTestTrait;
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /** @var \DOMDocument */
    private static DOMDocument $ext;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = Organization::class;

        self::$ext = DOMDocumentFactory::fromString(
            '<some:Ext xmlns:some="urn:mace:some:metadata:1.0">SomeExtension</some:Ext>',
        );

        self::$arrayRepresentation = [
            'OrganizationName' => ['en' => 'SSP'],
            'OrganizationDisplayName' => ['en' => 'SimpleSAMLphp'],
            'OrganizationURL' => ['en' => 'https://simplesamlphp.org'],
            'Extensions' => [new Chunk(self::$ext->documentElement)],
            'attributes' => [
                [
                    'namespaceURI' => 'urn:test:something',
                    'namespacePrefix' => 'test',
                    'attrName' => 'attr',
                    'attrValue' => 'value',
                ],
            ],
        ];

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/md_Organization.xml',
        );
    }


    // test marshalling


    /**
     * Test creating an Organization object from scratch
     */
    public function testMarshalling(): void
    {
        $org = new Organization(
            [
                new OrganizationName(
                    LangValue::fromString('en'),
                    SAMLStringValue::fromString('Identity Providers R US'),
                ),
            ],
            [
                new OrganizationDisplayName(
                    LangValue::fromString('en'),
                    SAMLStringValue::fromString('Identity Providers R US, a Division of Lerxst Corp.'),
                ),
            ],
            [
                new OrganizationURL(
                    LangValue::fromString('en'),
                    SAMLAnyURIValue::fromString('https://IdentityProvider.com'),
                ),
            ],
            new Extensions(
                [
                    new Chunk(self::$ext->documentElement),
                ],
            ),
            [new XMLAttribute(C::NAMESPACE, 'ssp', 'attr1', StringValue::fromString('value1'))],
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($org),
        );
    }
}
