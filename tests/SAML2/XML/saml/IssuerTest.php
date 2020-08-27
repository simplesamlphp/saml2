<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use PHPUnit\Framework\TestCase;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\Utils;
use SimpleSAML\Assert\AssertionFailedException;

/**
 * Class \SAML2\XML\saml\IssuerTest
 *
 * @covers \SAML2\XML\saml\Issuer
 * @package simplesamlphp/saml2
 */
final class IssuerTest extends TestCase
{
    /** @var \DOMDocument $document */
    private $document;


    /**
     * @return void
     */
    public function setup(): void
    {
        $samlNamespace = Issuer::NS;
        $this->document = DOMDocumentFactory::fromString(<<<XML
<saml:Issuer
  xmlns:saml="{$samlNamespace}"
  NameQualifier="TheNameQualifier"
  SPNameQualifier="TheSPNameQualifier"
  Format="TheFormat"
  SPProvidedID="TheSPProvidedID">TheIssuerValue</saml:Issuer>
XML
        );
    }


    // marshalling


    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $issuer = new Issuer(
            'TheIssuerValue',
            'TheNameQualifier',
            'TheSPNameQualifier',
            'TheFormat',
            'TheSPProvidedID'
        );

        $this->assertEquals('TheIssuerValue', $issuer->getValue());
        $this->assertEquals('TheNameQualifier', $issuer->getNameQualifier());
        $this->assertEquals('TheSPNameQualifier', $issuer->getSPNameQualifier());
        $this->assertEquals('TheSPProvidedID', $issuer->getSPProvidedID());
        $this->assertEquals('TheFormat', $issuer->getFormat());

        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval($issuer)
        );
    }


    /**
     * Test that creating an Issuer from scratch contains no attributes when format is "entity".
     */
    public function testMarshallingEntityFormat(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Illegal combination of attributes being used');

        $issuer = new Issuer(
            'TheIssuerValue',
            'TheNameQualifier',
            'TheSPNameQualifier',
            Constants::NAMEID_ENTITY,
            'TheSPProvidedID'
        );
    }


    /**
     * Test that creating an Issuer from scratch with no format defaults to "entity", and it therefore contains no other
     * attributes.
     */
    public function testMarshallingNoFormat(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Illegal combination of attributes being used');

        $issuer = new Issuer(
            'TheIssuerValue',
            'TheNameQualifier',
            'TheSPNameQualifier',
            null,
            'TheSPProvidedID'
        );
    }


    // unmarshalling


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $issuer = Issuer::fromXML($this->document->documentElement);

        $this->assertEquals('TheIssuerValue', $issuer->getValue());
        $this->assertEquals('TheNameQualifier', $issuer->getNameQualifier());
        $this->assertEquals('TheSPNameQualifier', $issuer->getSPNameQualifier());
        $this->assertEquals('TheFormat', $issuer->getFormat());
        $this->assertEquals('TheSPProvidedID', $issuer->getSPProvidedID());
    }


    /**
     * Test that creating an Issuer from XML contains no attributes when format is "entity".
     */
    public function testUnmarshallingEntityFormat(): void
    {
        $this->document->documentElement->setAttribute('Format', Constants::NAMEID_ENTITY);

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Illegal combination of attributes being used');

        $issuer = Issuer::fromXML($this->document->documentElement);
    }


    /**
     * Test that creating an Issuer from XML contains no attributes when there's no format (defaults to "entity").
     */
    public function testUnmarshallingNoFormat(): void
    {
        $this->document->documentElement->removeAttribute('Format');

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Illegal combination of attributes being used');

        $issuer = Issuer::fromXML($this->document->documentElement);
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(Issuer::fromXML($this->document->documentElement))))
        );
    }
}
