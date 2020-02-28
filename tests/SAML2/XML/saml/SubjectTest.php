<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\XML\saml\Subject;
use SAML2\XML\saml\NameID;
use SAML2\Utils;

/**
 * Class \SAML2\XML\saml\SubjectTest
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
        $nameid_transient = Constants::NAMEID_TRANSIENT;

        $this->document = DOMDocumentFactory::fromString(<<<XML
<saml:Subject xmlns:saml="{$samlNamespace}">
  <saml:NameID SPNameQualifier="https://sp.example.org/authentication/sp/metadata" Format="{$nameid_transient}">SomeOtherNameIDValue</saml:NameID>
  <saml:SubjectConfirmation Method="urn:oasis:names:tc:SAML:2.0:cm:bearer">
    <saml:NameID SPNameQualifier="https://sp.example.org/authentication/sp/metadata" Format="urn:oasis:names:tc:SAML:2.0:nameid-format:transient">SomeOtherNameIDValue</saml:NameID>
    <saml:SubjectConfirmationData NotOnOrAfter="2020-02-27T11:26:36Z" Recipient="https://sp.example.org/authentication/sp/consume-assertion" InResponseTo="def456"/>
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
<saml:BaseID xmlns:saml="{$samlNamespace}" SPNameQualifier="https://sp.example.org/authentication/sp/metadata"/>
XML
        );

        $this->nameId = DOMDocumentFactory::fromString(<<<XML
<saml:NameID xmlns:saml="{$samlNamespace}" SPNameQualifier="https://sp.example.org/authentication/sp/metadata" Format="{$nameid_transient}">SomeOtherNameIDValue</saml:NameID>
XML
        );

        $this->subjectConfirmation = DOMDocumentFactory::fromString(<<<XML
<saml:SubjectConfirmation xmlns:saml="{$samlNamespace}" Method="urn:oasis:names:tc:SAML:2.0:cm:bearer">
  <saml:NameID SPNameQualifier="https://sp.example.org/authentication/sp/metadata" Format="urn:oasis:names:tc:SAML:2.0:nameid-format:transient">SomeOtherNameIDValue</saml:NameID>
  <saml:SubjectConfirmationData NotOnOrAfter="2020-02-27T11:26:36Z" Recipient="https://sp.example.org/authentication/sp/consume-assertion" InResponseTo="def456"/>
</saml:SubjectConfirmation>
XML
        );
    }


    // marshalling


    /**
     * @return void
     */
    public function testMarshallingNameID(): void
    {
        $subject = new Subject(
            null,
            new NameID(
                'SomeNameIDValue',
                null,
                'https://sp.example.org/authentication/sp/metadata',
                Constants::NAMEID_TRANSIENT,
                null
            ),
            null,
            [
                new SubjectConfirmation(
                    'urn:oasis:names:tc:SAML:2.0:cm:bearer',
                    null,
                    new NameID(
                        'SomeOtherNameIDValue',
                        null,
                        'https://sp.example.org/authentication/sp/metadata',
                        Constants::NAMEID_TRANSIENT,
                        null
                    ),
                    null,
                    new SubjectConfirmationData(
                        null,
                        1582802796,
                        'https://sp.example.org/authentication/sp/consume-assertion',
                        'def456'
                    )
                )
            ]
        );

        $this->assertNull($subject->getBaseID());
        $this->assertNotNull($subject->getNameID());
        $this->assertNull($subject->getEncryptedID());

        $subjectConfirmation = $subject->getSubjectConfirmation();
        $this->assertNotEmpty($subjectConfirmation);

        $document = $this->subject;
        $document->documentElement->appendChild($document->importNode($this->nameId->documentElement, true));
        $document->documentElement->appendChild($document->importNode($this->subjectConfirmation->documentElement, true));

        $this->assertEqualXMLStructure($document->documentElement, $subject->toXML());
    }


    /**
     * @return void
     */
    public function testMarshallingBaseID(): void
    {
        $subject = new Subject(
            new BaseID(
                null,
                'https://sp.example.org/authentication/sp/metadata'
            ),
            null,
            null,
            [
                new SubjectConfirmation(
                    'urn:oasis:names:tc:SAML:2.0:cm:bearer',
                    null,
                    new NameID(
                        'SomeOtherNameIDValue',
                        null,
                        'https://sp.example.org/authentication/sp/metadata',
                        Constants::NAMEID_TRANSIENT,
                        null
                    ),
                    null,
                    new SubjectConfirmationData(
                        null,
                        1582802796,
                        'https://sp.example.org/authentication/sp/consume-assertion',
                        'def456'
                    )
                )
            ]
        );

        $this->assertNotNull($subject->getBaseID());
        $this->assertNull($subject->getNameID());
        $this->assertNull($subject->getEncryptedID());

        $subjectConfirmation = $subject->getSubjectConfirmation();
        $this->assertNotEmpty($subjectConfirmation);

        $document = $this->subject;
        $document->documentElement->appendChild($document->importNode($this->baseId->documentElement, true));
        $document->documentElement->appendChild($document->importNode($this->subjectConfirmation->documentElement, true));

        $this->assertEqualXMLStructure($document->documentElement, $subject->toXML());
    }


    /**
     * @return void
     */
    public function testMarshallingMultipleIdentifiersAndEmptySubjectConfirmation(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A <saml:Subject> not containing <saml:SubjectConfirmation> should provide exactly one of <saml:BaseID>, <saml:NameID> or <saml:EncryptedID>');

        new Subject(
            new BaseID('https://sp.example.org/authentication/sp/metadata'),
            new NameID(
                'SomeNameIDValue',
                null,
                'https://sp.example.org/authentication/sp/metadata',
                Constants::NAMEID_TRANSIENT,
                null
            ),
            null,
            []
        );
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

        $this->assertNull($subject->getBaseID());
        $this->assertInstanceOf(NameID::class, $subject->getNameID());
        $this->assertNull($subject->getEncryptedID());
        $this->assertNotNull($subject->getSubjectConfirmation());

        $this->assertEqualXMLStructure($document->documentElement, $subject->toXML());
    }


    /**
     * @return void
     */
    public function testEmptySubjectThrowsException(): void
    {
        $document = DOMDocumentFactory::fromString('<saml:Subject xmlns:saml="' . Subject::NS . '"/>');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A <saml:Subject> not containing <saml:SubjectConfirmation> should provide exactly one of <saml:BaseID>, <saml:NameID> or <saml:EncryptedID>');

        Subject::fromXML($document->documentElement);
    }


    /**
     * @return void
     */
    public function testManyNameIDThrowsException(): void
    {
        $samlNamespace = Constants::NS_SAML;
        $document = $this->subject;

        $nameId = $this->nameId;
        $document->documentElement->appendChild($document->importNode($nameId->documentElement, true));

        $nameId->documentElement->textContent = 'AnotherNameIDValue';
        $document->documentElement->appendChild($document->importNode($nameId->documentElement, true));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('More than one <saml:NameID> in <saml:Subject>.');

        Subject::fromXML($document->documentElement);
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
