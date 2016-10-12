<?php

namespace SAML2\XML\md;

use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\Utils;

/**
 * Class \SAML2\XML\md\ContactPersonTest
 */
class ContactPersonTest extends \PHPUnit_Framework_TestCase {
    public function testCompleteContact()
    {
        $document = DOMDocumentFactory::fromString(
<<<XML
<md:Test xmlns:md="{$mdNamespace}" Binding="urn:something" Location="https://whatever/" xmlns:test="urn:test" test:attr="value" />
XML
        );
        $contactPerson = new ContactPerson();
        $contactPerson->contactType = "other";
        $contactPerson->Company = "Test Company";
        $contactPerson->GivenName = "John";
        $contactPerson->SurName = "Doe";
        $contactPerson->EmailAddress = array('jdoe@test.company');
        $contactPerson->TelephoneNumber = array('1-234-567-8901');
        $contactPerson->ContactPersonAttributes = array('testattr'=>'testval');

        $cp = $contactPerson->toXML($document);

        echo $cp;
    }
}