<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use Mockery;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Compat\ContainerInterface;
use SimpleSAML\SAML2\Compat\ContainerSingleton;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\CustomBaseID;
use SimpleSAML\SAML2\DOMDocumentFactory;
use SimpleSAML\SAML2\Exception\MissingAttributeException;
use SimpleSAML\SAML2\Exception\TooManyElementsException;
use SimpleSAML\SAML2\Utils;

/**
 * Class \SAML2\XML\saml\SubjectConfirmationTest
 *
 * @covers \SimpleSAML\SAML2\XML\saml\SubjectConfirmation
 * @package simplesamlphp/saml2
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
        Mockery::close();
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


    /**
     * @return void
     */
    public function testMarshallingElementOrdering(): void
    {
        $subjectConfirmation = new SubjectConfirmation(
            'SomeMethod',
            new NameID('SomeNameIDValue'),
            new SubjectConfirmationData()
        );

        // Marshall it to a \DOMElement
        $subjectConfirmationElement = $subjectConfirmation->toXML();

        // Test for a NameID
        $subjectConfirmationElements = Utils::xpQuery($subjectConfirmationElement, './saml_assertion:NameID');
        $this->assertCount(1, $subjectConfirmationElements);

        // Test ordering of SubjectConfirmation contents
        $subjectConfirmationElements = Utils::xpQuery(
            $subjectConfirmationElement,
            './saml_assertion:NameID/following-sibling::*'
        );
        $this->assertCount(1, $subjectConfirmationElements);
        $this->assertEquals('saml:SubjectConfirmationData', $subjectConfirmationElements[0]->tagName);
    }


    // unmarshalling


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $subjectConfirmation = SubjectConfirmation::fromXML($this->document->documentElement);
        $identifier = $subjectConfirmation->getIdentifier();

        $this->assertEquals('SomeMethod', $subjectConfirmation->getMethod());
        $this->assertInstanceOf(NameID::class, $identifier);
        $this->assertEquals('SomeNameIDValue', $identifier->getValue());
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

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage("Missing 'Method' attribute on saml:SubjectConfirmation.");

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

        $this->expectException(TooManyElementsException::class);
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

        $this->expectException(TooManyElementsException::class);
        $this->expectExceptionMessage(
            'A <saml:SubjectConfirmation> can contain exactly one of <saml:BaseID>, <saml:NameID> or <saml:EncryptedID>.'
        );
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

        $this->expectException(TooManyElementsException::class);
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
        /** @psalm-var \SAML2\XML\saml\BaseID $identifier */
        $identifier = $subjectConfirmation->getIdentifier();
        $this->assertEquals('SomeMethod', $subjectConfirmation->getMethod());
        $this->assertEquals(BaseID::class, get_class($identifier));
        $this->assertEquals('CustomBaseID', $identifier->getType());
        $this->assertEquals('SomeIDValue', $identifier->getValue());
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
        $mock = Mockery::mock(ContainerInterface::class);
        /**
         * @psalm-suppress UndefinedMagicMethod
         * @psalm-suppress InvalidArgument
         */
        $mock->shouldReceive('getIdentifierHandler')->andReturn(CustomBaseID::class);
        /** @psalm-suppress InvalidArgument */
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
        $identifier = $subjectConfirmation->getIdentifier();
        $this->assertEquals('SomeMethod', $subjectConfirmation->getMethod());
        $this->assertInstanceOf(CustomBaseID::class, $identifier);
        $this->assertEquals('123.456', $identifier->getValue());
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
