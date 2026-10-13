<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\gssp;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\SAML2\XML\gssp\AbstractGsspElement;
use SimpleSAML\SAML2\XML\gssp\UserAttributes;
use SimpleSAML\SAML2\XML\saml\Attribute;
use SimpleSAML\SAML2\XML\saml\AttributeValue;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XMLSchema\Exception\MissingElementException;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\gssp\UserAttributesTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('gssp')]
#[CoversClass(UserAttributes::class)]
#[CoversClass(AbstractGsspElement::class)]
final class UserAttributesTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = UserAttributes::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/gssp_UserAttributes.xml',
        );
    }


    // marshalling


    /**
     */
    public function testMarshalling(): void
    {
        $userAttributes = new UserAttributes(
            [
                new Attribute(
                    name: SAMLStringValue::fromString('urn:mace:dir:attribute-def:mail'),
                    nameFormat: SAMLAnyURIValue::fromString(C::NAMEFORMAT_URI),
                    attributeValue: [
                        new AttributeValue(SAMLStringValue::fromString('user@example.com')),
                    ],
                ),
            ],
        );

        $expectedXml = self::$xmlRepresentation->saveXml(self::$xmlRepresentation->documentElement);
        $this->assertNotFalse($expectedXml);
        $actualXml = strval($userAttributes);

        $this->assertXmlStringEqualsXmlString($expectedXml, $actualXml);
    }


    /**
     */
    public function testMarshallingMissingAttributesThrowsException(): void
    {
        $this->expectException(MissingElementException::class);

        new UserAttributes([]);
    }


    // unmarshalling


    /**
     */
    public function testUnmarshallingMissingAttributesThrowsException(): void
    {
        $document = DOMDocumentFactory::fromString(
            <<<XML
<saml:UserAttributes xmlns:saml="urn:mace:surf.nl:stepup:gssp-extensions">
</saml:UserAttributes>
XML
            ,
        );

        $this->expectException(MissingElementException::class);
        UserAttributes::fromXML($document->documentElement);
    }
}
