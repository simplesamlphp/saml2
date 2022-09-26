<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use DOMDocument;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\XML\md\AttributeProfile;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\Test\XML\SchemaValidationTestTrait;
use SimpleSAML\Test\XML\SerializableElementTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\md\AttributeProfileTest
 *
 * @covers \SimpleSAML\SAML2\XML\md\AttributeProfile
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMdElement
 * @package simplesamlphp/saml2
 */
final class AttributeProfileTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    protected function setUp(): void
    {
        $this->schema = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/schemas/saml-schema-metadata-2.0.xsd';

        $this->testedClass = AttributeProfile::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/md_AttributeProfile.xml'
        );
    }


    // marshalling


    /**
     */
    public function testMarshalling(): void
    {
        $attributeProfile = new AttributeProfile(C::PROFILE_1);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($attributeProfile)
        );
    }


    // unmarshalling


    /**
     */
    public function testUnmarshalling(): void
    {
        $attributeProfile = AttributeProfile::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals(C::PROFILE_1, $attributeProfile->getContent());
    }
}
