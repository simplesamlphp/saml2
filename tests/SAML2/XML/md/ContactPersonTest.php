<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use Exception;
use PHPUnit\Framework\TestCase;
use SAML2\Constants as C;
use SAML2\XML\md\ContactPerson;
use SAML2\Utils;
use SimpleSAML\XML\DOMDocumentFactory;

use function array_pop;
use function count;
use function in_array;
use function preg_replace;

/**
 * Class \SAML2\XML\md\ContactPersonTest
 */
class ContactPersonTest extends TestCase
{
    /**
     * @return void
     */
    public function testContactPerson(): void
    {
        $contactType = "other";
        $Company = "Test Company";
        $GivenName = "John";
        $SurName = "Doe";
        $EmailAddress = ['jdoe@test.company', 'mailto:john.doe@test.company'];
        $TelephoneNumber = ['1-234-567-8901'];
        $ContactPersonAttributes = ['testattr' => 'testval', 'testattr2' => 'testval2'];

        $mdNamespace = C::NS_MD;
        $document = DOMDocumentFactory::fromString(<<<XML
<md:Test xmlns:md="{$mdNamespace}" Binding="urn:something" Location="https://whatever/" xmlns:test="urn:test" test:attr="value">
</md:Test>
XML
        );
        $contactPerson = new ContactPerson();
        $contactPerson->setContactType($contactType);
        $contactPerson->setCompany($Company);
        $contactPerson->setGivenName($GivenName);
        $contactPerson->setSurName($SurName);
        $contactPerson->setEmailAddress($EmailAddress);
        $contactPerson->setTelephoneNumber($TelephoneNumber);
        $contactPerson->setContactPersonAttributes($ContactPersonAttributes);

        $contactPerson->toXML($document->firstChild);

        $contactPersonElement = $document->getElementsByTagName('ContactPerson')->item(0);

        $this->assertEquals($contactType, $contactPersonElement->getAttribute('contactType'));
        $this->assertEquals($Company, $contactPersonElement->getElementsByTagName('Company')->item(0)->nodeValue);
        $this->assertEquals($GivenName, $contactPersonElement->getElementsByTagName('GivenName')->item(0)->nodeValue);
        $this->assertEquals($SurName, $contactPersonElement->getElementsByTagName('SurName')->item(0)->nodeValue);

        $this->assertEquals(count($EmailAddress), $contactPersonElement->getElementsByTagName('EmailAddress')->length);
        foreach ($contactPersonElement->getElementsByTagName('EmailAddress') as $element) {
            $this->assertTrue(
                in_array($element->nodeValue, $EmailAddress) ||
                in_array(preg_replace('/^mailto:/', '', $element->nodeValue), $EmailAddress)
            );
        }

        $this->assertEquals(
            count($TelephoneNumber),
            $contactPersonElement->getElementsByTagName('TelephoneNumber')->length
        );
        foreach ($contactPersonElement->getElementsByTagName('TelephoneNumber') as $element) {
            $this->assertTrue(in_array($element->nodeValue, $TelephoneNumber));
        }

        foreach ($ContactPersonAttributes as $attr => $val) {
            $this->assertEquals($val, $contactPersonElement->getAttribute($attr));
        }
    }


    /**
     * Test more methods inside ContactPerson
     *
     * @return void
     */
    public function testContactPersonExtraMethods(): void
    {
        $contactType = "administrative";
        $Company = "Test Company";
        $GivenName = "John";
        $SurName = "Doe";
        $EmailAddress = ['jdoe@test.company', 'mailto:john.doe@test.company'];
        $AnotherEmail = 'john@doe.xyz';
        $TelephoneNumber = ['1-234-567-8901'];
        $MoreTelephoneNumber = '+31-887874000';
        $ContactPersonAttributes = ['testattr' => 'testval', 'testattr2' => 'testval2'];
        $ExtraAttribute = ['another' => 'this'];

        $mdNamespace = C::NS_MD;
        $document = DOMDocumentFactory::fromString(<<<XML
<md:Test xmlns:md="{$mdNamespace}" Binding="urn:something" Location="https://whatever/" xmlns:test="urn:test" test:attr="value">
</md:Test>
XML
        );
        $contactPerson = new ContactPerson();
        $contactPerson->setContactType($contactType);
        $contactPerson->setCompany($Company);
        $contactPerson->setGivenName($GivenName);
        $contactPerson->setSurName($SurName);
        $contactPerson->setEmailAddress($EmailAddress);
        $contactPerson->setTelephoneNumber($TelephoneNumber);
        $contactPerson->setContactPersonAttributes($ContactPersonAttributes);

        $contactPerson->addEmailAddress($AnotherEmail);
        $contactPerson->addTelephoneNumber($MoreTelephoneNumber);
        $contactPerson->addContactPersonAttributes(key($ExtraAttribute), array_pop($ExtraAttribute));

        $contactPerson->toXML($document->firstChild);

        $contactPersonElement = $document->getElementsByTagName('ContactPerson')->item(0);

        $this->assertEquals(
            count($EmailAddress) + 1,
            $contactPersonElement->getElementsByTagName('EmailAddress')->length
        );
        foreach ($contactPersonElement->getElementsByTagName('EmailAddress') as $element) {
            $this->assertTrue(
                in_array($element->nodeValue, $EmailAddress) ||
                in_array(preg_replace('/^mailto:/', '', $element->nodeValue), $EmailAddress) ||
                preg_replace('/^mailto:/', '', $element->nodeValue) === $AnotherEmail
            );
        }

        $this->assertEquals(
            count($TelephoneNumber) + 1,
            $contactPersonElement->getElementsByTagName('TelephoneNumber')->length
        );
        foreach ($contactPersonElement->getElementsByTagName('TelephoneNumber') as $element) {
            $this->assertTrue(
                in_array($element->nodeValue, $TelephoneNumber) ||
                $element->nodeValue === $MoreTelephoneNumber
            );
        }

        foreach ($ContactPersonAttributes + $ExtraAttribute as $attr => $val) {
            $this->assertEquals($val, $contactPersonElement->getAttribute($attr));
        }
    }


    /**
     * @return void
     */
    public function testContactPersonFromXML(): void
    {
        $mdNamespace = C::NS_MD;
        $document = DOMDocumentFactory::fromString(<<<XML
<?xml version="1.0"?>
<md:Test xmlns:md="{$mdNamespace}" xmlns:test="urn:test" Binding="urn:something" Location="https://whatever/" test:attr="value">
    <md:ContactPerson contactType="other" testattr="testval" testattr2="testval2">
        <md:Company>Test Company</md:Company>
        <md:GivenName>John</md:GivenName>
        <md:SurName>Doe</md:SurName>
        <md:EmailAddress>jdoe@test.company</md:EmailAddress>
        <md:EmailAddress>mailto:john.doe@test.company</md:EmailAddress>
        <md:TelephoneNumber>1-234-567-8901</md:TelephoneNumber>
    </md:ContactPerson>
</md:Test>
XML
        );

        $contactPerson = new ContactPerson($document->getElementsByTagName('ContactPerson')->item(0));

        $this->assertEquals('Test Company', $contactPerson->getCompany());
        $this->assertEquals('John', $contactPerson->getGivenName());
        $this->assertEquals('Doe', $contactPerson->getSurName());
        $this->assertTrue(in_array('jdoe@test.company', $contactPerson->getEmailAddress()));
        $this->assertTrue(in_array('john.doe@test.company', $contactPerson->getEmailAddress()));
        $this->assertTrue(in_array('1-234-567-8901', $contactPerson->getTelephoneNumber()));
        $this->assertEquals('testval', $contactPerson->getContactPersonAttributes()['testattr']);
        $this->assertEquals('testval2', $contactPerson->getContactPersonAttributes()['testattr2']);
    }


    /**
     * @return void
     */
    public function testMultipleNamesXML(): void
    {
        $mdNamespace = C::NS_MD;
        $document = DOMDocumentFactory::fromString(<<<XML
<?xml version="1.0"?>
<md:Test xmlns:md="{$mdNamespace}" xmlns:test="urn:test" Binding="urn:something" Location="https://whatever/" test:attr="value">
    <md:ContactPerson contactType="other" testattr="testval" testattr2="testval2">
        <md:Company>Test Company</md:Company>
        <md:GivenName>John</md:GivenName>
        <md:GivenName>Jonathon</md:GivenName>
        <md:SurName>Doe</md:SurName>
        <md:EmailAddress>mailto:jdoe@test.company</md:EmailAddress>
        <md:EmailAddress>mailto:john.doe@test.company</md:EmailAddress>
        <md:TelephoneNumber>1-234-567-8901</md:TelephoneNumber>
    </md:ContactPerson>
</md:Test>
XML
        );

        $this->expectException(Exception::class, 'More than one GivenName in md:ContactPerson');

        $contactPerson = new ContactPerson($document->getElementsByTagName('ContactPerson')->item(0));
    }


    /**
     * @return void
     */
    public function testEmptySurNameXML(): void
    {
        $mdNamespace = C::NS_MD;
        $document = DOMDocumentFactory::fromString(<<<XML
<?xml version="1.0"?>
<md:Test xmlns:md="{$mdNamespace}" xmlns:test="urn:test" Binding="urn:something" Location="https://whatever/" test:attr="value">
    <md:ContactPerson contactType="other">
        <md:Company>Test Company</md:Company>
        <md:GivenName>John</md:GivenName>
        <md:EmailAddress>mailto:jdoe@test.company</md:EmailAddress>
        <md:EmailAddress>mailto:john.doe@test.company</md:EmailAddress>
        <md:TelephoneNumber>1-234-567-8901</md:TelephoneNumber>
    </md:ContactPerson>
</md:Test>
XML
        );

        $contactPerson = new ContactPerson($document->getElementsByTagName('ContactPerson')->item(0));

        $this->assertNull($contactPerson->getSurName());
    }


    /**
     * @return void
     */
    public function testMissingContactTypeXML(): void
    {
        $mdNamespace = C::NS_MD;
        $document = DOMDocumentFactory::fromString(<<<XML
<?xml version="1.0"?>
<md:Test xmlns:md="{$mdNamespace}" xmlns:test="urn:test" Binding="urn:something" Location="https://whatever/" test:attr="value">
    <md:ContactPerson>
    </md:ContactPerson>
</md:Test>
XML
        );

        $this->expectException(Exception::class, 'Missing contactType on ContactPerson.');

        $contactPerson = new ContactPerson($document->getElementsByTagName('ContactPerson')->item(0));
    }
}
