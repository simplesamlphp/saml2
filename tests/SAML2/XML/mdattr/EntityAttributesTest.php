<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\mdattr;

use PHPUnit\Framework\Attributes\{CoversClass, Group};
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Type\{SAMLAnyURIValue, SAMLDateTimeValue, SAMLStringValue};
use SimpleSAML\SAML2\XML\mdattr\{AbstractMdattrElement, EntityAttributes};
use SimpleSAML\SAML2\XML\saml\{
    Assertion,
    Attribute,
    AttributeStatement,
    AttributeValue,
    Audience,
    AudienceRestriction,
    Conditions,
    Issuer,
    NameID,
    Subject,
};
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\{SchemaValidationTestTrait, SerializableElementTestTrait};
use SimpleSAML\XML\Type\IDValue;
use SimpleSAML\XMLSecurity\Alg\Signature\SignatureAlgorithmFactory;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\mdattr\EntityAttributesTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('mdattr')]
#[CoversClass(EntityAttributes::class)]
#[CoversClass(AbstractMdattrElement::class)]
final class EntityAttributesTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = EntityAttributes::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/mdattr_EntityAttributes.xml',
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $attribute1 = new Attribute(
            name: SAMLStringValue::fromString('attrib1'),
            nameFormat: SAMLAnyURIValue::fromString(C::NAMEFORMAT_BASIC),
            attributeValue: [
                new AttributeValue('is'),
                new AttributeValue('really'),
                new AttributeValue('cool'),
            ],
        );

        // Create an Issuer
        $issuer = new Issuer(
            SAMLStringValue::fromString('urn:x-simplesamlphp:issuer'),
        );

        // Create the conditions
        $conditions = new Conditions(
            condition: [],
            audienceRestriction: [
                new AudienceRestriction([
                    new Audience(
                        SAMLAnyURIValue::fromString(C::ENTITY_IDP),
                    ),
                    new Audience(
                        SAMLAnyURIValue::fromString(C::ENTITY_URN),
                    ),
                ]),
            ],
        );

        // Create the statements
        $attrStatement = new AttributeStatement([
            new Attribute(
                name: SAMLStringValue::fromString('urn:mace:dir:attribute-def:uid'),
                nameFormat: SAMLAnyURIValue::fromString(C::NAMEFORMAT_URI),
                attributeValue: [
                    new AttributeValue('student2'),
                ],
            ),
            new Attribute(
                name: SAMLStringValue::fromString('urn:mace:terena.org:attribute-def:schacHomeOrganization'),
                nameFormat: SAMLAnyURIValue::fromString(C::NAMEFORMAT_URI),
                attributeValue: [
                    new AttributeValue('university.example.org'),
                    new AttributeValue('bbb.cc'),
                ],
            ),
            new Attribute(
                name: SAMLStringValue::fromString('urn:schac:attribute-def:schacPersonalUniqueCode'),
                nameFormat: SAMLAnyURIValue::fromString(C::NAMEFORMAT_URI),
                attributeValue: [
                    new AttributeValue('urn:schac:personalUniqueCode:nl:local:uvt.nl:memberid:524020'),
                    new AttributeValue('urn:schac:personalUniqueCode:nl:local:surfnet.nl:studentid:12345'),
                ],
            ),
            new Attribute(
                name: SAMLStringValue::fromString('urn:mace:dir:attribute-def:eduPersonAffiliation'),
                nameFormat: SAMLAnyURIValue::fromString(C::NAMEFORMAT_URI),
                attributeValue: [
                    new AttributeValue('member'),
                    new AttributeValue('student'),
                ],
            ),
        ]);

        $subject = new Subject(
            new NameID(
                value: SAMLStringValue::fromString('some:entity'),
                Format: SAMLAnyURIValue::FromString(C::NAMEID_ENTITY),
            ),
        );

        // Create an assertion
        $unsignedAssertion = new Assertion(
            $issuer,
            SAMLDateTimeValue::fromString('2024-07-23T20:35:34Z'),
            IDValue::fromString('_93af655219464fb403b34436cfb0c5cb1d9a5502'),
            $subject,
            $conditions,
            [$attrStatement],
        );

        // Sign the assertion
        $signer = (new SignatureAlgorithmFactory())->getAlgorithm(
            C::SIG_RSA_SHA256,
            PEMCertificatesMock::getPrivateKey(PEMCertificatesMock::PRIVATE_KEY),
        );
        $unsignedAssertion->sign($signer);
        $signedAssertion = Assertion::fromXML($unsignedAssertion->toXML());

        $attribute2 = new Attribute(
            name: SAMLStringValue::fromString('foo'),
            nameFormat: SAMLAnyURIValue::fromString('urn:simplesamlphp:v1:simplesamlphp'),
            attributeValue: [
                new AttributeValue('is'),
                new AttributeValue('really'),
                new AttributeValue('cool'),
            ],
        );

        $entityAttributes = new EntityAttributes([$attribute1]);
        $entityAttributes->addChild($signedAssertion);
        $entityAttributes->addChild($attribute2);

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($entityAttributes),
        );
    }
}
