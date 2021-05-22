<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use DOMDocument;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\XML\md\AttributeProfile;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;

/**
 * Class \SimpleSAML\SAML2\XML\md\AttributeProfileTest
 *
 * @covers \SimpleSAML\SAML2\XML\md\AttributeProfile
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMdElement
 * @package simplesamlphp/saml2
 */
final class AttributeProfileTest extends TestCase
{
    use SerializableXMLTestTrait;


    /**
     */
    protected function setUp(): void
    {
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
        $attributeProfile = new AttributeProfile('profile1');

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

        $this->assertEquals('profile1', $attributeProfile->getContent());
    }
}
