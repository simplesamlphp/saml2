<?php

namespace SAML2\XML\saml;

use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\Utils;

/**
 * Class \SAML2\XML\saml\SubjectConfirmationTest
 */
class SubjectConfirmationTest extends \PHPUnit_Framework_TestCase
{
    public function testMarshalling()
    {
        $subjectConfirmation = new SubjectConfirmation();
        $subjectConfirmation->Method = 'SomeMethod';
        $subjectConfirmation->NameID = new NameID();
        $subjectConfirmation->NameID->value = 'SomeNameIDValue';
        $subjectConfirmation->SubjectConfirmationData = new SubjectConfirmationData();

        $document = DOMDocumentFactory::fromString('<root />');
        $subjectConfirmationElement = $subjectConfirmation->toXML($document->firstChild);
        $subjectConfirmationElements = Utils::xpQuery($subjectConfirmationElement, '//saml_assertion:SubjectConfirmation');
        $this->assertCount(1, $subjectConfirmationElements);
        $subjectConfirmationElement = $subjectConfirmationElements[0];

        $this->assertEquals('SomeMethod', $subjectConfirmationElement->getAttribute("Method"));
        $this->assertCount(1, Utils::xpQuery($subjectConfirmationElement, "./saml_assertion:NameID"));
        $this->assertCount(1, Utils::xpQuery($subjectConfirmationElement, "./saml_assertion:SubjectConfirmationData"));
    }

    public function testUnmarshalling()
    {
        $samlNamespace = Constants::NS_SAML;
        $document = DOMDocumentFactory::fromString(
<<<XML
<saml:SubjectConfirmation xmlns:saml="{$samlNamespace}" Method="SomeMethod">
  <saml:NameID>SomeNameIDValue</saml:NameID>
  <saml:SubjectConfirmationData/>
</saml:SubjectConfirmation>
XML
        );

        $subjectConfirmation = new SubjectConfirmation($document->firstChild);
        $this->assertEquals('SomeMethod', $subjectConfirmation->Method);
        $this->assertTrue($subjectConfirmation->NameID instanceof NameID);
        $this->assertEquals('SomeNameIDValue', $subjectConfirmation->NameID->value);
        $this->assertTrue($subjectConfirmation->SubjectConfirmationData instanceof SubjectConfirmationData);
    }
}
