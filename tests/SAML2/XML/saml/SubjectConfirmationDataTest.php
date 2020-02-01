<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\XML\Chunk;
use SAML2\XML\ds\KeyInfo;
use SAML2\XML\ds\KeyName;
use SAML2\XML\saml\SubjectConfirmationData;
use SAML2\Utils;

/**
 * Class \SAML2\XML\saml\SubjectConfirmationDataTest
 */
class SubjectConfirmationDataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $subjectConfirmationData = new SubjectConfirmationData(
            987654321,
            1234567890,
            'https://sp.example.org/asdf',
            'SomeRequestID',
            '127.0.0.1'
        );
        $subjectConfirmationData->addInfo(
            new KeyInfo([new KeyName('SomeKey')])
        );

        $document = DOMDocumentFactory::fromString('<root />');
        $subjectConfirmationDataElement = $subjectConfirmationData->toXML($document->firstChild);

        $subjectConfirmationDataElements = Utils::xpQuery(
            $subjectConfirmationDataElement,
            '//saml_assertion:SubjectConfirmationData'
        );
        $this->assertCount(1, $subjectConfirmationDataElements);
        $subjectConfirmationDataElement = $subjectConfirmationDataElements[0];

        $this->assertEquals('2001-04-19T04:25:21Z', $subjectConfirmationDataElement->getAttribute("NotBefore"));
        $this->assertEquals('2009-02-13T23:31:30Z', $subjectConfirmationDataElement->getAttribute("NotOnOrAfter"));
        $this->assertEquals('https://sp.example.org/asdf', $subjectConfirmationDataElement->getAttribute("Recipient"));
        $this->assertEquals('SomeRequestID', $subjectConfirmationDataElement->getAttribute("InResponseTo"));
        $this->assertEquals('127.0.0.1', $subjectConfirmationDataElement->getAttribute("Address"));

        $keyInfoElements = Utils::xpQuery(
            $subjectConfirmationDataElement,
            '//ds:KeyInfo'
        );
        $this->assertCount(1, $keyInfoElements);
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $samlNamespace = Constants::NS_SAML;
        $dsNamespace = KeyInfo::NS;
        $document = DOMDocumentFactory::fromString(<<<XML
<saml:SubjectConfirmationData
    xmlns:saml="{$samlNamespace}"
    NotBefore="2001-04-19T04:25:21Z"
    NotOnOrAfter="2009-02-13T23:31:30Z"
    Recipient="https://sp.example.org/asdf"
    InResponseTo="SomeRequestID"
    Address="127.0.0.1">
  <ds:KeyInfo xmlns:ds="{$dsNamespace}">
    <ds:KeyName>SomeKey</ds:KeyName>
  </ds:KeyInfo>
</saml:SubjectConfirmationData>
XML
        );

        $subjectConfirmationData = SubjectConfirmationData::fromXML($document->firstChild);
        $this->assertEquals(987654321, $subjectConfirmationData->getNotBefore());
        $this->assertEquals(1234567890, $subjectConfirmationData->getNotOnOrAfter());
        $this->assertEquals('https://sp.example.org/asdf', $subjectConfirmationData->getRecipient());
        $this->assertEquals('SomeRequestID', $subjectConfirmationData->getInResponseTo());
        $this->assertEquals('127.0.0.1', $subjectConfirmationData->getAddress());

        $info = $subjectConfirmationData->getInfo();
        $this->assertEquals('SomeKey', $info[0]->getInfo()[0]->getName());
    }


    /**
     * @return void
     */
    public function testUnmarshallingEmpty(): void
    {
        $samlNamespace = Constants::NS_SAML;
        $document = DOMDocumentFactory::fromString(<<<XML
<saml:SubjectConfirmationData xmlns:saml="{$samlNamespace}">
</saml:SubjectConfirmationData>
XML
        );

        $subjectConfirmationData = SubjectConfirmationData::fromXML($document->firstChild);
        $this->assertTrue($subjectConfirmationData->isEmptyElement());
    }
}
