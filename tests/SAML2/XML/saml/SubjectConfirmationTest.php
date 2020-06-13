<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use PHPUnit\Framework\TestCase;
use SAML2\Compat\ContainerInterface;
use SAML2\Compat\ContainerSingleton;
use SAML2\Constants;
use SAML2\CustomBaseID;
use SAML2\DOMDocumentFactory;
use SimpleSAML\Assert\AssertionFailedException;

/**
 * Class \SAML2\XML\saml\SubjectConfirmationTest
 */
final class SubjectConfirmationTest extends TestCase
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


    public function tearDown(): void
    {
        \Mockery::close();
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
        $this->assertNotNull($subjectConfirmation->getIdentifier());
        $this->assertInstanceOf(NameID::class, $subjectConfirmation->getIdentifier());
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
        $this->assertInstanceOf(NameID::class, $subjectConfirmation->getIdentifier());
        $this->assertEquals('SomeNameIDValue', $subjectConfirmation->getIdentifier()->getValue());
        $this->assertInstanceOf(SubjectConfirmationData::class, $subjectConfirmation->getSubjectConfirmationData());
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

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('SubjectConfirmation element without Method attribute');

        SubjectConfirmation::fromXML($document);
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

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('More than one <saml:NameID> in <saml:SubjectConfirmation>.');
        SubjectConfirmation::fromXML($document->documentElement);
    }


    /**
     * Test that creating a SubjectConfirmation fails with multiple identifiers of different types.
     */
    public function testMultipleIdentifiers(): void
    {
        $samlNamespace = Constants::NS_SAML;
        $document = DOMDocumentFactory::fromString(<<<XML
<saml:SubjectConfirmation xmlns:saml="{$samlNamespace}" Method="SomeMethod">
  <saml:BaseID xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:type="someType">SomeNameIDValue</saml:BaseID>
  <saml:NameID>AnotherNameIDValue</saml:NameID>
  <saml:SubjectConfirmationData/>
</saml:SubjectConfirmation>
XML
        );

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('A <saml:SubjectConfirmation> can contain exactly one of <saml:BaseID>, <saml:NameID> or <saml:EncryptedID>.');
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

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage(
            'More than one <saml:SubjectConfirmationData> in <saml:SubjectConfirmation>.'
        );
        SubjectConfirmation::fromXML($document->documentElement);
    }


    /**
     * Test that when no custom identifier handlers are registered, a regular BaseID is used.
     */
    public function testNoCustomIDHandler(): void
    {
        $samlNamespace = Constants::NS_SAML;
        $document = DOMDocumentFactory::fromString(<<<XML
<saml:SubjectConfirmation xmlns:saml="{$samlNamespace}" Method="SomeMethod">
  <saml:BaseID xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:type="CustomBaseID">SomeIDValue</saml:BaseID>
  <saml:SubjectConfirmationData Recipient="Me" />
</saml:SubjectConfirmation>
XML
        );

        $subjectConfirmation = SubjectConfirmation::fromXML($document->documentElement);
        $this->assertEquals('SomeMethod', $subjectConfirmation->getMethod());
        $this->assertEquals(BaseID::class, get_class($subjectConfirmation->getIdentifier()));
        $this->assertEquals('CustomBaseID', $subjectConfirmation->getIdentifier()->getType());
        $this->assertEquals('SomeIDValue', $subjectConfirmation->getIdentifier()->getValue());
        $this->assertInstanceOf(SubjectConfirmationData::class, $subjectConfirmation->getSubjectConfirmationData());
        $this->assertEquals(
            $document->saveXML($document->documentElement),
            strval($subjectConfirmation)
        );
    }


    /**
     * Test that custom ID handlers work when processing SubjectConfirmation objects from XML.
     */
    public function testCustomIDHandler(): void
    {
        $container = ContainerSingleton::getInstance();
        $mock = \Mockery::mock(ContainerInterface::class);
        $mock->shouldReceive('getIdentifierHandler')->andReturn(CustomBaseID::class);
        ContainerSingleton::setContainer($mock);

        $samlNamespace = Constants::NS_SAML;
        $document = DOMDocumentFactory::fromString(<<<XML
<saml:SubjectConfirmation xmlns:saml="{$samlNamespace}" Method="SomeMethod">
  <saml:BaseID xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:type="CustomBaseID">123.456</saml:BaseID>
  <saml:SubjectConfirmationData Recipient="Me" />
</saml:SubjectConfirmation>
XML
        );

        $subjectConfirmation = SubjectConfirmation::fromXML($document->documentElement);
        $this->assertEquals('SomeMethod', $subjectConfirmation->getMethod());
        $this->assertInstanceOf(CustomBaseID::class, $subjectConfirmation->getIdentifier());
        $this->assertEquals('123.456', $subjectConfirmation->getIdentifier()->getValue());
        $this->assertInstanceOf(SubjectConfirmationData::class, $subjectConfirmation->getSubjectConfirmationData());
        $this->assertEquals(
            $document->saveXML($document->documentElement),
            strval($subjectConfirmation)
        );

        ContainerSingleton::setContainer($container);
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
