<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\mdrpi;

use DOMDocument;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\XML\md\AbstractLocalizedName;
use SimpleSAML\SAML2\XML\mdrpi\RegistrationPolicy;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;

/**
 * Tests for localized names.
 *
 * @covers \SimpleSAML\SAML2\XML\mdrpi\RegistrationPolicy
 * @covers \SimpleSAML\SAML2\XML\md\AbstractLocalizedName
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMdElement
 * @package simplesamlphp/saml2
 */
final class RegistrationPolicyTest extends TestCase
{
    use SerializableXMLTestTrait;


    /**
     */
    protected function setUp(): void
    {
        $this->testedClass = RegistrationPolicy::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/mdrpi_RegistrationPolicy.xml'
        );
    }


    // test marshalling


    /**
     * Test creating a RegistrationPolicy object from scratch.
     */
    public function testMarshalling(): void
    {
        $name = new RegistrationPolicy('en', 'http://www.example.edu/en/');

        $this->assertEquals('en', $name->getLanguage());
        $this->assertEquals('http://www.example.edu/en/', $name->getValue());

        $this->assertEquals($this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement), strval($name));
    }


    /**
     * Test that creating a RegistrationPolicy from scratch with an empty language fails.
     */
    public function testMarshallingWithEmptyLang(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('xml:lang cannot be empty.');

        new RegistrationPolicy('', 'http://www.example.edu/en/');
    }


    /**
     * Test that creating a RegistrationPolicy from scratch with an empty value works.
     */
    public function testMarshallingWithEmptyValue(): void
    {
        $name = new RegistrationPolicy('en', '');

        $this->xmlRepresentation->documentElement->textContent = '';

        $this->assertEquals($this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement), strval($name));
    }


    // test unmarshalling


    /**
     * Test creating a RegistrationPolicy from XML.
     */
    public function testUnmarshalling(): void
    {
        $name = RegistrationPolicy::fromXML($this->xmlRepresentation->documentElement);
        $this->assertEquals($this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement), strval($name));
    }


    /**
     * Test that creating a RegistrationPolicy from XML fails when xml:lang is missing.
     */
    public function testUnmarshallingWithoutLang(): void
    {
        $this->xmlRepresentation->documentElement->removeAttributeNS(RegistrationPolicy::XML_NS, 'lang');

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Missing xml:lang from RegistrationPolicy');

        RegistrationPolicy::fromXML($this->xmlRepresentation->documentElement);
    }


    /**
     * Test that creating a RegistrationPolicy from XML fails when xml:lang is empty.
     */
    public function testUnmarshallingWithEmptyLang(): void
    {
        $this->xmlRepresentation->documentElement->setAttributeNS(RegistrationPolicy::XML_NS, 'lang', '');

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('xml:lang cannot be empty.');

        RegistrationPolicy::fromXML($this->xmlRepresentation->documentElement);
    }


    /**
     * Test that creating a RegistrationPolicy from XML works for empty values.
     */
    public function testUnmarshallingWithEmptyValue(): void
    {
        $this->xmlRepresentation->documentElement->textContent = '';
        $name = RegistrationPolicy::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals('en', $name->getLanguage());
        $this->assertEquals('', $name->getValue());
        $this->assertEquals($this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement), strval($name));
    }


    /**
     * Test that creating a RegistrationPolicy with an invalid url throws an exception
     */
    public function testUnmarshallingFailsInvalidURL(): void
    {
        $document = $this->xmlRepresentation;
        $document->documentElement->textContent = 'this is no url';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('mdrpi:RegistrationPolicy is not a valid URL.');
        RegistrationPolicy::fromXML($document->documentElement);
    }
}
