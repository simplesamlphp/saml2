<?php

namespace SAML2\XML\md;

use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\Utils;

/**
 * Class \SAML2\XML\md\ContactPersonTest
 */
class ContactPersonTest extends \PHPUnit_Framework_TestCase {
    public function testContactPerson()
    {
        $contactType = "other";
        $Company = "Test Company";
        $GivenName = "John";
        $SurName = "Doe";
        $EmailAddress = array('jdoe@test.company', 'john.doe@test.company');
        $TelephoneNumber = array('1-234-567-8901');
        $ContactPersonAttributes = array('testattr' => 'testval', 'testattr2' => 'testval2');

        $mdNamespace = Constants::NS_MD;
        $document = DOMDocumentFactory::fromString(
<<<XML
<md:Test xmlns:md="{$mdNamespace}" Binding="urn:something" Location="https://whatever/" xmlns:test="urn:test" test:attr="value">
</md:Test>
XML
        );
        $contactPerson = new ContactPerson();
        $contactPerson->contactType = $contactType;
        $contactPerson->Company = $Company;
        $contactPerson->GivenName = $GivenName;
        $contactPerson->SurName = $SurName;
        $contactPerson->EmailAddress = $EmailAddress;
        $contactPerson->TelephoneNumber = $TelephoneNumber;
        $contactPerson->ContactPersonAttributes = $ContactPersonAttributes;

        $contactPerson->toXML($document->firstChild);

        $contactPersonElement = $document->getElementsByTagName('ContactPerson')->item(0);

        $this->assertEquals($contactType, $contactPersonElement->getAttribute('contactType'));
        $this->assertEquals($Company, $contactPersonElement->getElementsByTagName('Company')->item(0)->nodeValue);
        $this->assertEquals($GivenName, $contactPersonElement->getElementsByTagName('GivenName')->item(0)->nodeValue);
        $this->assertEquals($SurName, $contactPersonElement->getElementsByTagName('SurName')->item(0)->nodeValue);

        $this->assertEquals(count($EmailAddress), $contactPersonElement->getElementsByTagName('EmailAddress')->length);
        foreach ($contactPersonElement->getElementsByTagName('EmailAddress') as $element) {
            $this->assertTrue(in_array($element->nodeValue, $EmailAddress));
        }

        $this->assertEquals(count($TelephoneNumber), $contactPersonElement->getElementsByTagName('TelephoneNumber')->length);
        foreach ($contactPersonElement->getElementsByTagName('TelephoneNumber') as $element) {
            $this->assertTrue(in_array($element->nodeValue, $TelephoneNumber));
        }

        foreach ($ContactPersonAttributes as $attr => $val) {
            $this->assertEquals($val, $contactPersonElement->getAttribute($attr));
        }
    }

    public function testContactPersonFromXML()
    {
        $mdNamespace = Constants::NS_MD;
        $document = DOMDocumentFactory::fromString(
<<<XML
<?xml version="1.0"?>
<md:Test xmlns:md="{$mdNamespace}" xmlns:test="urn:test" Binding="urn:something" Location="https://whatever/" test:attr="value">
    <md:ContactPerson contactType="other" testattr="testval" testattr2="testval2">
        <md:Company>Test Company</md:Company>
        <md:GivenName>John</md:GivenName>
        <md:SurName>Doe</md:SurName>
        <md:EmailAddress>jdoe@test.company</md:EmailAddress>
        <md:EmailAddress>john.doe@test.company</md:EmailAddress>
        <md:TelephoneNumber>1-234-567-8901</md:TelephoneNumber>
    </md:ContactPerson>
</md:Test>
XML
        );

        $contactPerson = new ContactPerson($document->getElementsByTagName('ContactPerson')->item(0));

        $this->assertEquals('Test Company', $contactPerson->Company);
        $this->assertEquals('John', $contactPerson->GivenName);
        $this->assertEquals('Doe', $contactPerson->SurName);
        $this->assertTrue(in_array('jdoe@test.company', $contactPerson->EmailAddress));
        $this->assertTrue(in_array('john.doe@test.company', $contactPerson->EmailAddress));
        $this->assertTrue(in_array('1-234-567-8901', $contactPerson->TelephoneNumber));
        $this->assertEquals('testval', $contactPerson->ContactPersonAttributes['testattr']);
        $this->assertEquals('testval2', $contactPerson->ContactPersonAttributes['testattr2']);
    }

    public function testMultipleNamesXML()
    {
        $mdNamespace = Constants::NS_MD;
        $document = DOMDocumentFactory::fromString(
<<<XML
<?xml version="1.0"?>
<md:Test xmlns:md="{$mdNamespace}" xmlns:test="urn:test" Binding="urn:something" Location="https://whatever/" test:attr="value">
    <md:ContactPerson contactType="other" testattr="testval" testattr2="testval2">
        <md:Company>Test Company</md:Company>
        <md:GivenName>John</md:GivenName>
        <md:GivenName>Jonathon</md:GivenName>
        <md:SurName>Doe</md:SurName>
        <md:EmailAddress>jdoe@test.company</md:EmailAddress>
        <md:EmailAddress>john.doe@test.company</md:EmailAddress>
        <md:TelephoneNumber>1-234-567-8901</md:TelephoneNumber>
    </md:ContactPerson>
</md:Test>
XML
        );

        $this->setExpectedException('Exception', 'More than one GivenName in md:ContactPerson');

        $contactPerson = new ContactPerson($document->getElementsByTagName('ContactPerson')->item(0));
    }

    public function testEmptySurNameXML()
    {
        $mdNamespace = Constants::NS_MD;
        $document = DOMDocumentFactory::fromString(
<<<XML
<?xml version="1.0"?>
<md:Test xmlns:md="{$mdNamespace}" xmlns:test="urn:test" Binding="urn:something" Location="https://whatever/" test:attr="value">
    <md:ContactPerson contactType="other">
        <md:Company>Test Company</md:Company>
        <md:GivenName>John</md:GivenName>
        <md:EmailAddress>jdoe@test.company</md:EmailAddress>
        <md:EmailAddress>john.doe@test.company</md:EmailAddress>
        <md:TelephoneNumber>1-234-567-8901</md:TelephoneNumber>
    </md:ContactPerson>
</md:Test>
XML
        );

        $contactPerson = new ContactPerson($document->getElementsByTagName('ContactPerson')->item(0));

        $this->assertNull($contactPerson->SurName);
    }

    public function testMissingContactTypeXML()
    {
        $mdNamespace = Constants::NS_MD;
        $document = DOMDocumentFactory::fromString(
<<<XML
<?xml version="1.0"?>
<md:Test xmlns:md="{$mdNamespace}" xmlns:test="urn:test" Binding="urn:something" Location="https://whatever/" test:attr="value">
    <md:ContactPerson>
    </md:ContactPerson>
</md:Test>
XML
        );

        $this->setExpectedException('Exception', 'Missing contactType on ContactPerson.');

        $contactPerson = new ContactPerson($document->getElementsByTagName('ContactPerson')->item(0));
    }
}