<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use Mockery;
use PHPUnit\Framework\TestCase;
use SimpleSAMLSAML2\Compat\ContainerInterface;
use SimpleSAMLSAML2\Compat\ContainerSingleton;
use SimpleSAMLSAML2\Constants;
use SimpleSAMLSAML2\CustomBaseID;
use SimpleSAMLSAML2\DOMDocumentFactory;
use SimpleSAMLSAML2\Exception\TooManyElementsException;
use SimpleSAMLSAML2\Utils;

/**
 * Class \SAML2\XML\saml\SubjectTest
 *
 * @covers \SAML2\XML\saml\Subject
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package SimpleSAMLphp
 */
final class SubjectTest extends TestCase
{
    /** @var \DOMDocument */
    private $document;

    /** @var \DOMDocument */
    private $subject;

    /** @var \DOMDocument */
    private $baseId;

    /** @var \DOMDocument */
    private $nameId;

    /** @var \DOMDocument */
    private $subjectConfirmation;


    public function setup(): void
    {
        $samlNamespace = Subject::NS;
        $xsiNamespace = Constants::NS_XSI;
        $nameid_transient = Constants::NAMEID_TRANSIENT;

        $this->document = DOMDocumentFactory::fromString(<<<XML
<saml:Subject xmlns:saml="{$samlNamespace}">
  <saml:NameID
    SPNameQualifier="https://sp.example.org/authentication/sp/metadata"
    Format="{$nameid_transient}">SomeOtherNameIDValue</saml:NameID>
  <saml:SubjectConfirmation Method="urn:oasis:names:tc:SAML:2.0:cm:bearer">
    <saml:NameID
        SPNameQualifier="https://sp.example.org/authentication/sp/metadata"
        Format="urn:oasis:names:tc:SAML:2.0:nameid-format:transient">SomeOtherNameIDValue</saml:NameID>
    <saml:SubjectConfirmationData
        NotOnOrAfter="2020-02-27T11:26:36Z"
        Recipient="https://sp.example.org/authentication/sp/consume-assertion"
        InResponseTo="def456"/>
  </saml:SubjectConfirmation>
</saml:Subject>
XML
        );

        $this->subject = DOMDocumentFactory::fromString(<<<XML
<saml:Subject xmlns:saml="{$samlNamespace}">
</saml:Subject>
XML
        );

        $this->baseId = DOMDocumentFactory::fromString(<<<XML
<saml:BaseID
    xmlns:saml="{$samlNamespace}"
    SPNameQualifier="https://sp.example.org/authentication/sp/metadata"
    xmlns:xsi="{$xsiNamespace}"
    xsi:type="CustomBaseID">123.456</saml:BaseID>
XML
        );

        $this->nameId = DOMDocumentFactory::fromString(<<<XML
<saml:NameID
    xmlns:saml="{$samlNamespace}"
    SPNameQualifier="https://sp.example.org/authentication/sp/metadata"
    Format="{$nameid_transient}">SomeOtherNameIDValue</saml:NameID>
XML
        );

        $this->subjectConfirmation = DOMDocumentFactory::fromString(<<<XML
<saml:SubjectConfirmation xmlns:saml="{$samlNamespace}" Method="urn:oasis:names:tc:SAML:2.0:cm:bearer">
  <saml:NameID
      SPNameQualifier="https://sp.example.org/authentication/sp/metadata"
      Format="urn:oasis:names:tc:SAML:2.0:nameid-format:transient">SomeOtherNameIDValue</saml:NameID>
  <saml:SubjectConfirmationData
      NotOnOrAfter="2020-02-27T11:26:36Z"
      Recipient="https://sp.example.org/authentication/sp/consume-assertion"
      InResponseTo="def456"/>
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
    public function testMarshallingNameID(): void
    {
        $subject = new Subject(
            new NameID(
                'SomeNameIDValue',
                null,
                'https://sp.example.org/authentication/sp/metadata',
                Constants::NAMEID_TRANSIENT,
                null
            ),
            [
                new SubjectConfirmation(
                    'urn:oasis:names:tc:SAML:2.0:cm:bearer',
                    new NameID(
                        'SomeOtherNameIDValue',
                        null,
                        'https://sp.example.org/authentication/sp/metadata',
                        Constants::NAMEID_TRANSIENT,
                        null
                    ),
                    new SubjectConfirmationData(
                        null,
                        1582802796,
                        'https://sp.example.org/authentication/sp/consume-assertion',
                        'def456'
                    )
                )
            ]
        );

        $this->assertNotNull($subject->getIdentifier());
        $this->assertInstanceOf(NameID::class, $subject->getIdentifier());

        $subjectConfirmation = $subject->getSubjectConfirmation();
        $this->assertNotEmpty($subjectConfirmation);

        $document = $this->subject;
        $document->documentElement->appendChild($document->importNode($this->nameId->documentElement, true));
        $document->documentElement->appendChild(
            $document->importNode($this->subjectConfirmation->documentElement, true)
        );

        $this->assertEqualXMLStructure($document->documentElement, $subject->toXML());
    }


    /**
     * @return void
     */
    public function testMarshallingElementOrdering(): void
    {
        $subject = new Subject(
            new NameID(
                'SomeNameIDValue',
                null,
                'https://sp.example.org/authentication/sp/metadata',
                Constants::NAMEID_TRANSIENT,
                null
            ),
            [
                new SubjectConfirmation(
                    'urn:oasis:names:tc:SAML:2.0:cm:bearer',
                    new NameID(
                        'SomeOtherNameIDValue',
                        null,
                        'https://sp.example.org/authentication/sp/metadata',
                        Constants::NAMEID_TRANSIENT,
                        null
                    ),
                    new SubjectConfirmationData(
                        null,
                        1582802796,
                        'https://sp.example.org/authentication/sp/consume-assertion',
                        'def456'
                    )
                )
            ]
        );

        // Marshall it to a \DOMElement
        $subjectElement = $subject->toXML();

        // Test for a NameID
        $subjectElements = Utils::xpQuery($subjectElement, './saml_assertion:NameID');
        $this->assertCount(1, $subjectElements);

        // Test ordering of Subject contents
        $subjectElements = Utils::xpQuery($subjectElement, './saml_assertion:NameID/following-sibling::*');
        $this->assertCount(1, $subjectElements);
        $this->assertEquals('saml:SubjectConfirmation', $subjectElements[0]->tagName);
    }


    /**
     * @return void
     */
    public function testMarshallingBaseID(): void
    {
        $subject = new Subject(
            new CustomBaseID(
                123.456,
                'https://sp.example.org/authentication/sp/metadata'
            ),
            [
                new SubjectConfirmation(
                    'urn:oasis:names:tc:SAML:2.0:cm:bearer',
                    new NameID(
                        'SomeOtherNameIDValue',
                        null,
                        'https://sp.example.org/authentication/sp/metadata',
                        Constants::NAMEID_TRANSIENT,
                        null
                    ),
                    new SubjectConfirmationData(
                        null,
                        1582802796,
                        'https://sp.example.org/authentication/sp/consume-assertion',
                        'def456'
                    )
                )
            ]
        );

        $this->assertNotNull($subject->getIdentifier());
        $this->assertInstanceOf(CustomBaseID::class, $subject->getIdentifier());

        $subjectConfirmation = $subject->getSubjectConfirmation();
        $this->assertNotEmpty($subjectConfirmation);

        $document = $this->subject;
        $document->documentElement->appendChild($document->importNode($this->baseId->documentElement, true));
        $document->documentElement->appendChild(
            $document->importNode($this->subjectConfirmation->documentElement, true)
        );

        $this->assertEqualXMLStructure($document->documentElement, $subject->toXML());
    }


    // unmarshalling


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $document = $this->subject;

        $nameId = $this->nameId;
        $document->documentElement->appendChild($document->importNode($nameId->documentElement, true));

        $subject = Subject::fromXML($document->documentElement);

        $this->assertNotNull($subject->getIdentifier());
        $this->assertInstanceOf(NameID::class, $subject->getIdentifier());

        $this->assertEqualXMLStructure($document->documentElement, $subject->toXML());
    }


    /**
     * @return void
     */
    public function testEmptySubjectThrowsException(): void
    {
        $document = DOMDocumentFactory::fromString('<saml:Subject xmlns:saml="' . Subject::NS . '"/>');

        $this->expectException(TooManyElementsException::class);
        $this->expectExceptionMessage(
            'A <saml:Subject> not containing <saml:SubjectConfirmation> should provide exactly one of <saml:BaseID>, <saml:NameID> or <saml:EncryptedID>'
        );

        Subject::fromXML($document->documentElement);
    }


    /**
     * @return void
     */
    public function testManyNameIDThrowsException(): void
    {
        $document = $this->subject;

        $nameId = $this->nameId;
        $document->documentElement->appendChild($document->importNode($nameId->documentElement, true));

        $nameId->documentElement->textContent = 'AnotherNameIDValue';
        $document->documentElement->appendChild($document->importNode($nameId->documentElement, true));

        $this->expectException(TooManyElementsException::class);
        $this->expectExceptionMessage('More than one <saml:NameID> in <saml:Subject>.');

        Subject::fromXML($document->documentElement);
    }


    /**
     * Test that unmarshalling a Subject from an XML with multiple identifiers fails.
     */
    public function testMultipleIdentifiers(): void
    {
        $samlNamespace = Subject::NS;
        $xsiNamespace = Constants::NS_XSI;

        $document = DOMDocumentFactory::fromString(<<<XML
<saml:Subject xmlns:saml="{$samlNamespace}">
  <saml:BaseID xmlns:xsi="{$xsiNamespace}" xsi:type="CustomBaseID">SomeBaseID</saml:BaseID>
  <saml:NameID
      SPNameQualifier="https://sp.example.org/authentication/sp/metadata"
      Format="urn:oasis:names:tc:SAML:2.0:nameid-format:transient">SomeOtherNameIDValue</saml:NameID>
  <saml:SubjectConfirmation Method="urn:oasis:names:tc:SAML:2.0:cm:bearer">
    <saml:SubjectConfirmationData
        NotOnOrAfter="2020-02-27T11:26:36Z"
        Recipient="https://sp.example.org/authentication/sp/consume-assertion"
        InResponseTo="def456"/>
  </saml:SubjectConfirmation>
</saml:Subject>
XML
        );

        $this->expectException(TooManyElementsException::class);
        $this->expectExceptionMessage(
            'A <saml:Subject> can contain exactly one of <saml:BaseID>, <saml:NameID> or <saml:EncryptedID>.'
        );
        Subject::fromXML($document->documentElement);
    }


    /**
     * Test that unmarshalling a Subject from XML returns a regular BaseID object when no handlers
     * are registered.
     */
    public function testNoCustomIDHandler(): void
    {
        $samlNamespace = Subject::NS;
        $xsiNamespace = Constants::NS_XSI;

        $document = DOMDocumentFactory::fromString(<<<XML
<saml:Subject xmlns:saml="{$samlNamespace}">
  <saml:BaseID xmlns:xsi="{$xsiNamespace}" xsi:type="CustomBaseID">SomeBaseID</saml:BaseID>
  <saml:SubjectConfirmation Method="urn:oasis:names:tc:SAML:2.0:cm:bearer">
    <saml:SubjectConfirmationData
        NotOnOrAfter="2020-02-27T11:26:36Z"
        Recipient="https://sp.example.org/authentication/sp/consume-assertion"
        InResponseTo="def456"/>
  </saml:SubjectConfirmation>
</saml:Subject>
XML
        );

        $subject = Subject::fromXML($document->documentElement);
        $identifier = $subject->getIdentifier();
        $this->assertInstanceOf(BaseID::class, $identifier);
        $this->assertEquals('CustomBaseID', $identifier->getType());
        $this->assertEquals(
            $document->saveXML($document->documentElement),
            strval($subject)
        );
    }


    /**
     * Test that unmarshalling a Subject from XML returns a custom identifier object if its corresponding
     * class has been registered.
     */
    public function testCustomIDHandler(): void
    {
        $container = ContainerSingleton::getInstance();
        $mock = Mockery::mock(ContainerInterface::class);
        /**
         * @psalm-suppress InvalidArgument
         * @psalm-suppress UndefinedMagicMethod
         */
        $mock->shouldReceive('getIdentifierHandler')->andReturn(CustomBaseID::class);
        /** @psalm-suppress InvalidArgument */
        ContainerSingleton::setContainer($mock);

        $samlNamespace = Subject::NS;
        $xsiNamespace = Constants::NS_XSI;

        $document = DOMDocumentFactory::fromString(<<<XML
<saml:Subject xmlns:saml="{$samlNamespace}">
  <saml:BaseID xmlns:xsi="{$xsiNamespace}" xsi:type="CustomBaseID">123.456</saml:BaseID>
  <saml:SubjectConfirmation Method="urn:oasis:names:tc:SAML:2.0:cm:bearer">
    <saml:SubjectConfirmationData
        NotOnOrAfter="2020-02-27T11:26:36Z"
        Recipient="https://sp.example.org/authentication/sp/consume-assertion"
        InResponseTo="def456"/>
  </saml:SubjectConfirmation>
</saml:Subject>
XML
        );

        $subject = Subject::fromXML($document->documentElement);
        $identifier = $subject->getIdentifier();
        $this->assertInstanceOf(BaseID::class, $identifier);
        $this->assertEquals(CustomBaseID::class, get_class($identifier));
        $this->assertEquals(
            $document->saveXML($document->documentElement),
            strval($subject)
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
            strval(unserialize(serialize(Subject::fromXML($this->document->documentElement))))
        );
    }
}
