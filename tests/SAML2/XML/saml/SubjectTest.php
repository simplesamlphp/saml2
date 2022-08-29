<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\saml\Audience;
use SimpleSAML\SAML2\XML\saml\NameID;
use SimpleSAML\SAML2\XML\saml\Subject;
use SimpleSAML\SAML2\XML\saml\SubjectConfirmation;
use SimpleSAML\SAML2\XML\saml\SubjectConfirmationData;
use SimpleSAML\Test\SAML2\CustomBaseID;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\TooManyElementsException;

use function dirname;
use function strval;

/**
 * Class \SAML2\XML\saml\SubjectTest
 *
 * @covers \SimpleSAML\SAML2\XML\saml\Subject
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractSamlElement
 *
 * @package SimpleSAMLphp
 */
final class SubjectTest extends TestCase
{
    use SerializableXMLTestTrait;


    /** @var \DOMDocument */
    private DOMDocument $subject;

    /** @var \DOMDocument */
    private DOMDocument $baseId;

    /** @var \DOMDocument */
    private DOMDocument $nameId;

    /** @var \DOMDocument */
    private DOMDocument $subjectConfirmation;


    public function setup(): void
    {
        $this->testedClass = Subject::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/saml_Subject.xml'
        );
        $this->subject = DOMDocumentFactory::fromString(<<<XML
<saml:Subject xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"></saml:Subject>
XML
        );
        $this->baseId = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/saml_BaseID.xml'
        );
        $this->nameId = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/saml_NameID.xml'
        );
        $this->subjectConfirmation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/saml_SubjectConfirmation.xml'
        );
    }


    // marshalling


    /**
     */
    public function testMarshallingNameID(): void
    {
        $subject = new Subject(
            new NameID(
                'SomeNameIDValue',
                null,
                'https://sp.example.org/authentication/sp/metadata',
                C::NAMEID_TRANSIENT,
                null
            ),
            [
                new SubjectConfirmation(
                    'urn:oasis:names:tc:SAML:2.0:cm:bearer',
                    new NameID(
                        'SomeOtherNameIDValue',
                        null,
                        'https://sp.example.org/authentication/sp/metadata',
                        C::NAMEID_TRANSIENT,
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

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($subject)
        );
    }


    /**
     */
    public function testMarshallingElementOrdering(): void
    {
        $subject = new Subject(
            new NameID(
                'SomeNameIDValue',
                null,
                'https://sp.example.org/authentication/sp/metadata',
                C::NAMEID_TRANSIENT,
                null
            ),
            [
                new SubjectConfirmation(
                    'urn:oasis:names:tc:SAML:2.0:cm:bearer',
                    new NameID(
                        'SomeOtherNameIDValue',
                        null,
                        'https://sp.example.org/authentication/sp/metadata',
                        C::NAMEID_TRANSIENT,
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
        $xpCache = XPath::getXPath($subjectElement);
        $subjectElements = XPath::xpQuery($subjectElement, './saml_assertion:NameID', $xpCache);
        $this->assertCount(1, $subjectElements);

        // Test ordering of Subject contents
        /** @psalm-var \DOMElement[] $subjectElements */
        $subjectElements = XPath::xpQuery($subjectElement, './saml_assertion:NameID/following-sibling::*', $xpCache);
        $this->assertCount(1, $subjectElements);
        $this->assertEquals('saml:SubjectConfirmation', $subjectElements[0]->tagName);
    }


    /**
     */
    public function testMarshallingBaseID(): void
    {
        $subject = new Subject(
            new CustomBaseID(
                [new Audience('urn:some:audience')],
                'https://sp.example.org/authentication/sp/metadata'
            ),
            [
                new SubjectConfirmation(
                    'urn:oasis:names:tc:SAML:2.0:cm:bearer',
                    new NameID(
                        'SomeOtherNameIDValue',
                        null,
                        'https://sp.example.org/authentication/sp/metadata',
                        C::NAMEID_TRANSIENT,
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
        $xsiNamespace = C::NS_XSI;

        $document = DOMDocumentFactory::fromString(<<<XML
<saml:Subject xmlns:saml="{$samlNamespace}">
  <saml:BaseID xmlns:xsi="{$xsiNamespace}" xsi:type="CustomBaseIDType">
    <saml:Audience>urn:some:audience</saml:Audience>
  </saml:BaseID>
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
}
