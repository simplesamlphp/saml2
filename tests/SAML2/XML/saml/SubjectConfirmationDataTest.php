<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use SAML2\Constants;
use SAML2\DOMDocumentFactory;
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
        $subjectConfirmationData = new SubjectConfirmationData();
        $subjectConfirmationData->setNotBefore(987654321);
        $subjectConfirmationData->setNotOnOrAfter(1234567890);
        $subjectConfirmationData->setRecipient('https://sp.example.org/asdf');
        $subjectConfirmationData->setInResponseTo('SomeRequestID');
        $subjectConfirmationData->setAddress('127.0.0.1');

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
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $samlNamespace = Constants::NS_SAML;
        $document = DOMDocumentFactory::fromString(<<<XML
<saml:SubjectConfirmationData
    xmlns:saml="{$samlNamespace}"
    NotBefore="2001-04-19T04:25:21Z"
    NotOnOrAfter="2009-02-13T23:31:30Z"
    Recipient="https://sp.example.org/asdf"
    InResponseTo="SomeRequestID"
    Address="127.0.0.1"
    />
XML
        );

        $subjectConfirmationData = new SubjectConfirmationData($document->firstChild);
        $this->assertEquals(987654321, $subjectConfirmationData->getNotBefore());
        $this->assertEquals(1234567890, $subjectConfirmationData->getNotOnOrAfter());
        $this->assertEquals('https://sp.example.org/asdf', $subjectConfirmationData->getRecipient());
        $this->assertEquals('SomeRequestID', $subjectConfirmationData->getInResponseTo());
        $this->assertEquals('127.0.0.1', $subjectConfirmationData->getAddress());
    }
}
