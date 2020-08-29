<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\md;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XML\Chunk;
use SimpleSAML\Assert\AssertionFailedException;

/**
 * Tests for the ContactPerson class.
 *
 * @covers \SimpleSAML\SAML2\XML\md\ContactPerson
 * @package simplesamlphp/saml2
 */
final class ContactPersonTest extends TestCase
{
    /** @var \DOMDocument */
    protected $document;


    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->document = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/md_ContactPerson.xml'
        );
    }


    // test marshalling


    /**
     * Test marshalling a ContactPerson from scratch.
     */
    public function testMarshalling(): void
    {
        $ext = DOMDocumentFactory::fromString(
            '<some:Ext xmlns:some="urn:mace:some:metadata:1.0">SomeExtension</some:Ext>'
        );

        $attr1 = $this->document->createAttributeNS('urn:test', 'test:attr1');
        $attr1->value = 'testval1';
        $attr2 = $this->document->createAttributeNS('urn:test', 'test:attr2');
        $attr2->value = 'testval2';
        $cp = new ContactPerson(
            'other',
            'Test Company',
            'John',
            'Doe',
            new Extensions(
                [
                    new Chunk($ext->documentElement)
                ]
            ),
            ['jdoe@test.company', 'john.doe@test.company'],
            ['1-234-567-8901'],
            [$attr1, $attr2]
        );

        $this->assertEquals('other', $cp->getContactType());
        $this->assertEquals('Test Company', $cp->getCompany());
        $this->assertEquals('John', $cp->getGivenName());
        $this->assertEquals('Doe', $cp->getSurName());
        $this->assertEquals(['jdoe@test.company', 'john.doe@test.company'], $cp->getEmailAddresses());
        $this->assertEquals(['1-234-567-8901'], $cp->getTelephoneNumbers());
        $this->assertEquals(
            [
                '{urn:test}attr1' => [
                    'qualifiedName' => 'test:attr1',
                    'namespaceURI' => 'urn:test',
                    'value' => 'testval1'
                ],
                '{urn:test}attr2' => [
                    'qualifiedName' => 'test:attr2',
                    'namespaceURI' => 'urn:test',
                    'value' => 'testval2'
                ]
            ],
            $cp->getAttributesNS()
        );
        $this->assertEquals('testval1', $cp->getAttributeNS('urn:test', 'attr1'));
        $this->assertEquals('testval2', $cp->getAttributeNS('urn:test', 'attr2'));

        $this->assertEquals($this->document->saveXML($this->document->documentElement), strval($cp));
    }


    /**
     * Test that creating a ContactPerson from scratch with the wrong type fails.
     */
    public function testMarshallingWithWrongType(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage(
            'Expected one of: "technical", "support", "administrative", "billing", "other". Got: "wrong"'
        );
        new ContactPerson('wrong');
    }


    /**
     * Test that creating a ContactPerson from scratch with an invalid email address fails.
     */
    public function testMarshallingWithWrongEmail(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email address for ContactPerson: \'this is wrong\'');
        new ContactPerson(
            'other',
            'Test Company',
            'John',
            'Doe',
            null,
            ['this is wrong']
        );
    }


    /**
     * Test that creating a ContactPerson from scratch without any optional arguments works.
     */
    public function testMarshallingWithoutOptionalProperties(): void
    {
        $mdNamespace = Constants::NS_MD;
        $document = DOMDocumentFactory::fromString(<<<XML
<md:ContactPerson contactType="other" xmlns:md="{$mdNamespace}"></md:ContactPerson>
XML
        );
        $cp = new ContactPerson('other');
        $this->assertEquals($document->saveXML($document->documentElement), strval($cp));
        $this->assertNull($cp->getCompany());
        $this->assertNull($cp->getGivenName());
        $this->assertNull($cp->getSurName());
        $this->assertEquals([], $cp->getEmailAddresses());
        $this->assertEquals([], $cp->getTelephoneNumbers());
        $this->assertEquals([], $cp->getAttributesNS());
    }


    // test unmarshalling


    /**
     * Test creating a ContactPerson from XML.
     */
    public function testUnmarshalling(): void
    {
        $cp = ContactPerson::fromXML($this->document->documentElement);
        $this->assertEquals('other', $cp->getContactType());
        $this->assertEquals('Test Company', $cp->getCompany());
        $this->assertEquals('John', $cp->getGivenName());
        $this->assertEquals('Doe', $cp->getSurName());
        $this->assertEquals(['jdoe@test.company', 'john.doe@test.company'], $cp->getEmailAddresses());
        $this->assertEquals(['1-234-567-8901'], $cp->getTelephoneNumbers());
        $this->assertEquals(
            [
                '{urn:test}attr1' => [
                    'qualifiedName' => 'test:attr1',
                    'namespaceURI' => 'urn:test',
                    'value' => 'testval1'
                ],
                '{urn:test}attr2' => [
                    'qualifiedName' => 'test:attr2',
                    'namespaceURI' => 'urn:test',
                    'value' => 'testval2'
                ]
            ],
            $cp->getAttributesNS()
        );
        $this->assertEquals('testval1', $cp->getAttributeNS('urn:test', 'attr1'));
        $this->assertEquals('testval2', $cp->getAttributeNS('urn:test', 'attr2'));
    }


    /**
     * Test that creating a ContactPerson from XML without a contactType attribute fails.
     */
    public function testUnmarshallingWithoutType(): void
    {
        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage("Missing 'contactType' attribute on md:ContactPerson.");
        $this->document->documentElement->removeAttribute('contactType');
        ContactPerson::fromXML($this->document->documentElement);
    }


    /**
     * Test that creating a ContactPerson from XML fails when the contact type is not supported.
     */
    public function testUnmarshallingWithWrongType(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage(
            'Expected one of: "technical", "support", "administrative", "billing", "other". Got: "wrong"'
        );
        $this->document->documentElement->setAttribute('contactType', 'wrong');
        ContactPerson::fromXML($this->document->documentElement);
    }


    /**
     * Test that creating a ContactPerson from XML fails when multiple Company elements are found.
     */
    public function testUnmarshallingMultipleCompanies(): void
    {
        $company = $this->document->getElementsByTagNameNS(Constants::NS_MD, 'Company');
        $newCompany = $this->document->createElementNS(Constants::NS_MD, 'Company', 'Alt. Co.');
        /** @psalm-suppress PossiblyNullPropertyFetch */
        $this->document->documentElement->insertBefore($newCompany, $company->item(0)->nextSibling);
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('More than one Company in md:ContactPerson');
        ContactPerson::fromXML($this->document->documentElement);
    }


    /**
     * Test that creating a ContactPerson from XML fails when multiple GivenName elements are found.
     */
    public function testUnmarshallingMultipleGivenNames(): void
    {
        $givenName = $this->document->getElementsByTagNameNS(Constants::NS_MD, 'GivenName');
        $newName = $this->document->createElementNS(Constants::NS_MD, 'GivenName', 'New Name');
        /** @psalm-suppress PossiblyNullPropertyFetch */
        $this->document->documentElement->insertBefore($newName, $givenName->item(0)->nextSibling);
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('More than one GivenName in md:ContactPerson');
        ContactPerson::fromXML($this->document->documentElement);
    }


    /**
     * Test that creating a ContactPerson from XML fails when multiple SurName elements are found.
     */
    public function testUnmarshallingMultipleSurNames(): void
    {
        $surName = $this->document->getElementsByTagNameNS(Constants::NS_MD, 'SurName');
        $newName = $this->document->createElementNS(Constants::NS_MD, 'SurName', 'New Name');
        /** @psalm-suppress PossiblyNullPropertyFetch */
        $this->document->documentElement->insertBefore($newName, $surName->item(0)->nextSibling);
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('More than one SurName in md:ContactPerson');
        ContactPerson::fromXML($this->document->documentElement);
    }


    /**
     * Test that creating a ContactPerson from XML fails when an invalid email address is found.
     */
    public function testUnmarshallingWithInvalidEmail(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email address for ContactPerson: \'this is not an email\'');
        $emails = $this->document->getElementsByTagNameNS(Constants::NS_MD, 'EmailAddress');
        /** @psalm-suppress PossiblyNullPropertyAssignment */
        $emails->item(1)->textContent = 'this is not an email';
        ContactPerson::fromXML($this->document->documentElement);
    }


    /**
     * Test that creating a ContactPerson from XML works when all optional elements are missing.
     */
    public function testUnmarshallingWithoutOptionalArguments(): void
    {
        $mdNamespace = Constants::NS_MD;
        $document = DOMDocumentFactory::fromString(<<<XML
<md:ContactPerson contactType="other" xmlns:md="{$mdNamespace}"/>
XML
        );
        $cp = ContactPerson::fromXML($document->documentElement);
        $this->assertEquals($document->saveXML($document->documentElement), strval($cp));
        $this->assertNull($cp->getCompany());
        $this->assertNull($cp->getGivenName());
        $this->assertNull($cp->getSurName());
        $this->assertEquals([], $cp->getEmailAddresses());
        $this->assertEquals([], $cp->getTelephoneNumbers());
        $this->assertEquals([], $cp->getAttributesNS());
    }


    /**
     * @return void
     */
    public function testInvalidEmailThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email address for');
        new ContactPerson('technical', null, null, null, null, ['not so valid']);
    }


    /**
     * @return void
     */
    public function testInvalidEmailInSetThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid email address for');
        new ContactPerson(
            'technical',
            null,
            null,
            null,
            null,
            ['bob@alice.edu', 'user@example.org', 'not so valid', 'aap@noot.nl']
        );
    }


    /**
     * Test that serialization / unserialization works.
     */
    public function testSerialize(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(ContactPerson::fromXML($this->document->documentElement))))
        );
    }
}
