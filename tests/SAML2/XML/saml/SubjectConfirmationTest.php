<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use Exception;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\XML\saml\SubjectConfirmationData;
use SAML2\XML\saml\SubjectConfirmation;
use SAML2\XML\saml\NameID;
use SAML2\Utils;

/**
 * Class \SAML2\XML\saml\SubjectConfirmationTest
 */
class SubjectConfirmationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $nameId = new NameID();
        $nameId->setValue('SomeNameIDValue');

        $subjectConfirmation = new SubjectConfirmation();
        $subjectConfirmation->setMethod('SomeMethod');
        $subjectConfirmation->setNameID($nameId);
        $subjectConfirmation->setSubjectConfirmationData(new SubjectConfirmationData());

        $document = DOMDocumentFactory::fromString('<root />');
        $subjectConfirmationElement = $subjectConfirmation->toXML($document->firstChild);
        $subjectConfirmationElements = Utils::xpQuery(
            $subjectConfirmationElement,
            '//saml_assertion:SubjectConfirmation'
        );
        $this->assertCount(1, $subjectConfirmationElements);
        $subjectConfirmationElement = $subjectConfirmationElements[0];

        $this->assertEquals('SomeMethod', $subjectConfirmationElement->getAttribute("Method"));
        $this->assertCount(1, Utils::xpQuery($subjectConfirmationElement, "./saml_assertion:NameID"));
        $this->assertCount(1, Utils::xpQuery($subjectConfirmationElement, "./saml_assertion:SubjectConfirmationData"));
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $samlNamespace = Constants::NS_SAML;
        $document = DOMDocumentFactory::fromString(<<<XML
<saml:SubjectConfirmation xmlns:saml="{$samlNamespace}" Method="SomeMethod">
  <saml:NameID>SomeNameIDValue</saml:NameID>
  <saml:SubjectConfirmationData/>
</saml:SubjectConfirmation>
XML
        );

        $subjectConfirmation = new SubjectConfirmation($document->firstChild);
        $this->assertEquals('SomeMethod', $subjectConfirmation->getMethod());
        $this->assertTrue($subjectConfirmation->getNameID() instanceof NameID);
        $this->assertEquals('SomeNameIDValue', $subjectConfirmation->getNameID()->getValue());
        $this->assertTrue($subjectConfirmation->getSubjectConfirmationData() instanceof SubjectConfirmationData);
    }


    /**
     * @return void
     */
    public function testMethodMissingThrowsException(): void
    {
        $samlNamespace = Constants::NS_SAML;
        $document = DOMDocumentFactory::fromString(<<<XML
<saml:SubjectConfirmation xmlns:saml="{$samlNamespace}">
  <saml:NameID>SomeNameIDValue</saml:NameID>
  <saml:SubjectConfirmationData/>
</saml:SubjectConfirmation>
XML
        );

        $this->expectException(Exception::class, 'SubjectConfirmation element without Method attribute');
        $subjectConfirmation = new SubjectConfirmation($document->firstChild);
    }


    /**
     * @return void
     */
    public function testManyNameIDThrowsException(): void
    {
        $samlNamespace = Constants::NS_SAML;
        $document = DOMDocumentFactory::fromString(<<<XML
<saml:SubjectConfirmation xmlns:saml="{$samlNamespace}" Method="SomeMethod">
  <saml:NameID>SomeNameIDValue</saml:NameID>
  <saml:NameID>AnotherNameIDValue</saml:NameID>
  <saml:SubjectConfirmationData/>
</saml:SubjectConfirmation>
XML
        );

        $this->expectException(Exception::class, 'More than one NameID in a SubjectConfirmation element');
        $subjectConfirmation = new SubjectConfirmation($document->firstChild);
    }


    /**
     * @return void
     */
    public function testManySubjectConfirmationDataThrowsException(): void
    {
        $samlNamespace = Constants::NS_SAML;
        $document = DOMDocumentFactory::fromString(<<<XML
<saml:SubjectConfirmation xmlns:saml="{$samlNamespace}" Method="SomeMethod">
  <saml:NameID>SomeNameIDValue</saml:NameID>
  <saml:SubjectConfirmationData Recipient="Me" />
  <saml:SubjectConfirmationData Recipient="Someone Else" />
</saml:SubjectConfirmation>
XML
        );

        $this->expectException(
            Exception::class,
            'More than one SubjectConfirmationData child in a SubjectConfirmation element'
        );
        $subjectConfirmation = new SubjectConfirmation($document->firstChild);
    }
}
