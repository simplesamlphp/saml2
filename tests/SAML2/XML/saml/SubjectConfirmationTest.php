<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use InvalidArgumentException;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\XML\saml\SubjectConfirmationData;
use SAML2\XML\saml\SubjectConfirmation;
use SAML2\XML\saml\NameID;
use SAML2\Utils;

/**
 * Class \SAML2\XML\saml\SubjectConfirmationTest
 */
final class SubjectConfirmationTest extends \PHPUnit\Framework\TestCase
{
    /** @var \DOMDocument */
    private $document;


    public function setup(): void
    {
        $samlNamespace = SubjectConfirmation::NS;

        $this->document = DOMDocumentFactory::fromString(<<<XML
<saml:SubjectConfirmation xmlns:saml="{$samlNamespace}" Method="SomeMethod">
  <saml:NameID>SomeNameIDValue</saml:NameID>
  <saml:SubjectConfirmationData/>
</saml:SubjectConfirmation>
XML
        );
    }


    // marshalling


    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $subjectConfirmation = new SubjectConfirmation(
            'SomeMethod',
            new NameID('SomeNameIDValue'),
            new SubjectConfirmationData()
        );

        $this->assertEquals('SomeMethod', $subjectConfirmation->getMethod());
        $this->assertNotNull($subjectConfirmation->getNameID());
        $this->assertNotNull($subjectConfirmation->getSubjectConfirmationData());

        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($subjectConfirmation)
        );
    }


    // unmarshalling


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $subjectConfirmation = SubjectConfirmation::fromXML($this->document->documentElement);

        $this->assertEquals('SomeMethod', $subjectConfirmation->getMethod());
        $this->assertTrue($subjectConfirmation->getNameID() instanceof NameID);
        $this->assertEquals('SomeNameIDValue', $subjectConfirmation->getNameID()->getValue());
        $this->assertTrue($subjectConfirmation->getSubjectConfirmationData() instanceof SubjectConfirmationData);
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($subjectConfirmation)
        );
    }


    /**
     * @return void
     */
    public function testMethodMissingThrowsException(): void
    {
        $document = $this->document->documentElement;
        $document->removeAttribute('Method');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('SubjectConfirmation element without Method attribute');

        $subjectConfirmation = SubjectConfirmation::fromXML($document);
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

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('More than one NameID in a SubjectConfirmation element');
        SubjectConfirmation::fromXML($document->documentElement);
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

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'More than one SubjectConfirmationData child in a SubjectConfirmation element'
        );
        SubjectConfirmation::fromXML($document->documentElement);
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(SubjectConfirmation::fromXML($this->document->documentElement))))
        );
    }
}
