<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\mdattr;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\saml\Assertion;
use SimpleSAML\SAML2\XML\saml\Attribute;
use SimpleSAML\SAML2\XML\saml\AttributeStatement;
use SimpleSAML\SAML2\XML\saml\AttributeValue;
use SimpleSAML\SAML2\XML\saml\AuthnContext;
use SimpleSAML\SAML2\XML\saml\AuthnContextClassRef;
use SimpleSAML\SAML2\XML\saml\AuthnContextDeclRef;
use SimpleSAML\SAML2\XML\saml\Audience;
use SimpleSAML\SAML2\XML\saml\AudienceRestriction;
use SimpleSAML\SAML2\XML\saml\Conditions;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\saml\NameID;
use SimpleSAML\SAML2\XML\saml\Subject;
use SimpleSAML\SAML2\XML\mdattr\EntityAttributes;
use SimpleSAML\Test\SAML2\Constants as C;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XMLSecurity\Alg\Signature\SignatureAlgorithmFactory;
use SimpleSAML\XMLSecurity\Key\PrivateKey;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;

use function dirname;
use function strval;

/**
 * Class \SAML2\XML\mdattr\EntityAttributesTest
 *
 * @covers \SimpleSAML\SAML2\XML\mdattr\EntityAttributes
 * @covers \SimpleSAML\SAML2\XML\mdattr\AbstractMdattrElement
 * @package simplesamlphp/saml2
 */

final class EntityAttributesTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public function setUp(): void
    {
        $this->schema = dirname(__FILE__, 5) . '/resources/schemas/sstc-metadata-attr.xsd';

        $this->testedClass = EntityAttributes::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 5) . '/resources/xml/mdattr_EntityAttributes.xml',
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $attribute1 = new Attribute(
            name: 'attrib1',
            nameFormat: C::NAMEFORMAT_URI,
            attributeValue: [
                new AttributeValue('is'),
                new AttributeValue('really'),
                new AttributeValue('cool'),
            ],
        );

        // Create an Issuer
        $issuer = new Issuer('testIssuer');

        // Create the conditions
        $conditions = new Conditions(
            condition: [],
            audienceRestriction: [
                new AudienceRestriction([
                    new Audience(C::ENTITY_IDP),
                    new Audience(C::ENTITY_URN),
                ]),
            ],
        );

        // Create the statements
        $attrStatement = new AttributeStatement([
            new Attribute(
                name: 'urn:mace:dir:attribute-def:uid',
                nameFormat: C::NAMEFORMAT_URI,
                attributeValue: [
                    new AttributeValue('student2'),
                ],
            ),
            new Attribute(
                name: 'urn:mace:terena.org:attribute-def:schacHomeOrganization',
                nameFormat: C::NAMEFORMAT_URI,
                attributeValue: [
                    new AttributeValue('university.example.org'),
                    new AttributeValue('bbb.cc'),
                ],
            ),
            new Attribute(
                name: 'urn:schac:attribute-def:schacPersonalUniqueCode',
                nameFormat: C::NAMEFORMAT_URI,
                attributeValue: [
                    new AttributeValue('urn:schac:personalUniqueCode:nl:local:uvt.nl:memberid:524020'),
                    new AttributeValue('urn:schac:personalUniqueCode:nl:local:surfnet.nl:studentid:12345'),
                ],
            ),
            new Attribute(
                name: 'urn:mace:dir:attribute-def:eduPersonAffiliation',
                nameFormat: C::NAMEFORMAT_URI,
                attributeValue: [
                    new AttributeValue('member'),
                    new AttributeValue('student'),
                ],
            ),
        ]);

        $subject = new Subject(new NameID(
            value: 'some:entity',
            Format: C::NAMEID_ENTITY,
        ));

        // Create an assertion
        $unsignedAssertion = new Assertion(
            $issuer,
            '_93af655219464fb403b34436cfb0c5cb1d9a5502',
            1665960678,
            $subject,
            $conditions,
            [$attrStatement]
        );

        // Sign the assertion
        $signer = (new SignatureAlgorithmFactory())->getAlgorithm(
            C::SIG_RSA_SHA256,
            PEMCertificatesMock::getPrivateKey(PEMCertificatesMock::PRIVATE_KEY)
        );
        $unsignedAssertion->sign($signer);
        $signedAssertion = Assertion::fromXML($unsignedAssertion->toXML());

        $attribute2 = new Attribute(
            name: 'foo',
            nameFormat: 'urn:simplesamlphp:v1:simplesamlphp',
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
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($entityAttributes),
        );
    }


    /**
     */
    public function testUnmarshalling(): void
    {
        $entityAttributes = EntityAttributes::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($entityAttributes),
        );
    }
}
