<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\XML\md\ContactPerson;
use SimpleSAML\SAML2\XML\md\Company;
use SimpleSAML\SAML2\XML\md\EmailAddress;
use SimpleSAML\SAML2\XML\md\Extensions;
use SimpleSAML\SAML2\XML\md\GivenName;
use SimpleSAML\SAML2\XML\md\SurName;
use SimpleSAML\SAML2\XML\md\TelephoneNumber;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XML\TestUtils\ArrayizableElementTestTrait;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XML\Utils as XMLUtils;

use function dirname;
use function strval;

/**
 * Tests for the ContactPerson class.
 *
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMdElement
 * @covers \SimpleSAML\SAML2\XML\md\ContactPerson
 * @package simplesamlphp/saml2
 */
final class ContactPersonTest extends TestCase
{
    use ArrayizableElementTestTrait;
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    protected function setUp(): void
    {
        $this->schema = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-metadata-2.0.xsd';

        $this->testedClass = ContactPerson::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 5) . '/resources/xml/md_ContactPerson.xml',
        );

        $this->arrayRepresentation = [
            'ContactType' => 'administrative',
            'Company' => 'SimpleSAMLphp',
            'GivenName' => 'Lead',
            'SurName' => 'Developer',
            'Extensions' => null,
            'EmailAddress' => ['mailto:lead.developer@example.org'],
            'TelephoneNumber' => ['+1234567890'],
            'urn:test:something' => ['test:attr' => 'value'],
        ];
    }


    // test marshalling


    /**
     * Test marshalling a ContactPerson from scratch.
     */
    public function testMarshalling(): void
    {
        $ext = DOMDocumentFactory::fromString(
            '<some:Ext xmlns:some="urn:mace:some:metadata:1.0">SomeExtension</some:Ext>',
        );

        $attr1 = $this->xmlRepresentation->createAttributeNS('urn:test:something', 'test:attr1');
        $attr1->value = 'testval1';
        $attr2 = $this->xmlRepresentation->createAttributeNS('urn:test:something', 'test:attr2');
        $attr2->value = 'testval2';
        $contactPerson = new ContactPerson(
            'other',
            new Company('Test Company'),
            new GivenName('John'),
            new SurName('Doe'),
            new Extensions(
                [
                    new Chunk($ext->documentElement),
                ],
            ),
            [new EmailAddress('jdoe@test.company'), new EmailAddress('john.doe@test.company')],
            [new TelephoneNumber('1-234-567-8901')],
            [$attr1, $attr2],
        );

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($contactPerson),
        );
    }


    /**
     * Test that creating a ContactPerson from scratch with the wrong type fails.
     */
    public function testMarshallingWithWrongType(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage(
            'Expected one of: "technical", "support", "administrative", "billing", "other". Got: "wrong"',
        );
        new ContactPerson('wrong');
    }


    // test unmarshalling


    /**
     * Test creating a ContactPerson from XML.
     */
    public function testUnmarshalling(): void
    {
        $contactPerson = ContactPerson::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($contactPerson),
        );
    }


    /**
     * Test that creating a ContactPerson from XML without a contactType attribute fails.
     */
    public function testUnmarshallingWithoutType(): void
    {
        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage("Missing 'contactType' attribute on md:ContactPerson.");
        $this->xmlRepresentation->documentElement->removeAttribute('contactType');
        ContactPerson::fromXML($this->xmlRepresentation->documentElement);
    }


    /**
     * Test that creating a ContactPerson from XML fails when the contact type is not supported.
     */
    public function testUnmarshallingWithWrongType(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage(
            'Expected one of: "technical", "support", "administrative", "billing", "other". Got: "wrong"',
        );
        $this->xmlRepresentation->documentElement->setAttribute('contactType', 'wrong');
        ContactPerson::fromXML($this->xmlRepresentation->documentElement);
    }


    /**
     * Test that creating a ContactPerson from XML fails when multiple Company elements are found.
     */
    public function testUnmarshallingMultipleCompanies(): void
    {
        $company = $this->xmlRepresentation->getElementsByTagNameNS(C::NS_MD, 'Company');
        $newCompany = $this->xmlRepresentation->createElementNS(C::NS_MD, 'Company', 'Alt. Co.');
        /** @psalm-suppress PossiblyNullPropertyFetch */
        $this->xmlRepresentation->documentElement->insertBefore($newCompany, $company->item(0)->nextSibling);
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('More than one Company in md:ContactPerson');
        ContactPerson::fromXML($this->xmlRepresentation->documentElement);
    }


    /**
     * Test that creating a ContactPerson from XML fails when multiple GivenName elements are found.
     */
    public function testUnmarshallingMultipleGivenNames(): void
    {
        $givenName = $this->xmlRepresentation->getElementsByTagNameNS(C::NS_MD, 'GivenName');
        $newName = $this->xmlRepresentation->createElementNS(C::NS_MD, 'GivenName', 'New Name');
        /** @psalm-suppress PossiblyNullPropertyFetch */
        $this->xmlRepresentation->documentElement->insertBefore($newName, $givenName->item(0)->nextSibling);
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('More than one GivenName in md:ContactPerson');
        ContactPerson::fromXML($this->xmlRepresentation->documentElement);
    }


    /**
     * Test that creating a ContactPerson from XML fails when multiple SurName elements are found.
     */
    public function testUnmarshallingMultipleSurNames(): void
    {
        $surName = $this->xmlRepresentation->getElementsByTagNameNS(C::NS_MD, 'SurName');
        $newName = $this->xmlRepresentation->createElementNS(C::NS_MD, 'SurName', 'New Name');
        /** @psalm-suppress PossiblyNullPropertyFetch */
        $this->xmlRepresentation->documentElement->insertBefore($newName, $surName->item(0)->nextSibling);
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('More than one SurName in md:ContactPerson');
        ContactPerson::fromXML($this->xmlRepresentation->documentElement);
    }


    /**
     * Test that creating a ContactPerson from XML works when all optional elements are missing.
     */
    public function testUnmarshallingWithoutOptionalArguments(): void
    {
        $mdNamespace = C::NS_MD;
        $document = DOMDocumentFactory::fromString(<<<XML
<md:ContactPerson contactType="other" xmlns:md="{$mdNamespace}"/>
XML
        );
        $cp = ContactPerson::fromXML($document->documentElement);
        $this->assertEquals($document->saveXML($document->documentElement), strval($cp));
        $this->assertNull($cp->getCompany());
        $this->assertNull($cp->getGivenName());
        $this->assertNull($cp->getSurName());
        $this->assertEquals([], $cp->getEmailAddress());
        $this->assertEquals([], $cp->getTelephoneNumber());
        $this->assertEquals([], $cp->getAttributesNS());
    }
}
