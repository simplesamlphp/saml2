<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\md\AbstractMdElement;
use SimpleSAML\SAML2\XML\md\EncryptionMethod;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XMLSchema\Exception\MissingAttributeException;
use SimpleSAML\XMLSchema\Type\Base64BinaryValue;
use SimpleSAML\XMLSecurity\Type\KeySizeValue;
use SimpleSAML\XMLSecurity\XML\xenc\KeySize;
use SimpleSAML\XMLSecurity\XML\xenc\OAEPparams;

use function dirname;
use function strval;

/**
 * Tests for the md:EncryptionMethod element.
 *
 * @package simplesamlphp/saml2
 */
#[Group('md')]
#[CoversClass(EncryptionMethod::class)]
#[CoversClass(AbstractMdElement::class)]
final class EncryptionMethodTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$schemaFile = dirname(__FILE__, 4) . '/resources/schemas/simplesamlphp.xsd';

        self::$testedClass = EncryptionMethod::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/md_EncryptionMethod.xml',
        );
    }


    // test marshalling


    /**
     * Test creating an EncryptionMethod object from scratch.
     */
    public function testMarshalling(): void
    {
        $chunkXml = DOMDocumentFactory::fromString(
            '<ssp:Chunk xmlns:ssp="urn:x-simplesamlphp:namespace">Value</ssp:Chunk>',
        );
        $chunk = Chunk::fromXML($chunkXml->documentElement);

        $encryptionMethod = new EncryptionMethod(
            SAMLAnyURIValue::fromString(C::KEY_TRANSPORT_OAEP_MGF1P),
            new KeySize(
                KeySizeValue::fromInteger(10),
            ),
            new OAEPparams(
                Base64BinaryValue::fromString('9lWu3Q=='),
            ),
            [$chunk],
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($encryptionMethod),
        );
    }


    /**
     * Test that creating an EncryptionMethod object from scratch works when no optional elements have been specified.
     */
    public function testMarshallingWithoutOptionalParameters(): void
    {
        $encryptionMethod = new EncryptionMethod(
            SAMLAnyURIValue::fromString(C::KEY_TRANSPORT_OAEP_MGF1P),
        );
        $document = DOMDocumentFactory::fromString(sprintf(
            '<md:EncryptionMethod xmlns:md="%s" Algorithm="http://www.w3.org/2001/04/xmlenc#rsa-oaep-mgf1p"/>',
            C::NS_MD,
        ));

        $this->assertEquals(
            $document->saveXML($document->documentElement),
            strval($encryptionMethod),
        );
    }


    /**
     */
    public function testMarshallingElementOrdering(): void
    {
        $alg = C::KEY_TRANSPORT_OAEP_MGF1P;
        $chunkXml = DOMDocumentFactory::fromString(
            '<ssp:Chunk xmlns:ssp="urn:x-simplesamlphp:namespace">Value</ssp:Chunk>',
        );
        $chunk = Chunk::fromXML($chunkXml->documentElement);

        $em = new EncryptionMethod(
            SAMLAnyURIValue::fromString(C::KEY_TRANSPORT_OAEP_MGF1P),
            new KeySize(
                KeySizeValue::fromInteger(10),
            ),
            new OAEPparams(
                Base64BinaryValue::fromString('9lWu3Q=='),
            ),
            [$chunk],
        );

        // Marshall it to a \DOMElement
        $emElement = $em->toXML();

        // Test for a KeySize
        $xpCache = XPath::getXPath($emElement);
        $keySizeElements = XPath::xpQuery($emElement, './xenc:KeySize', $xpCache);
        $this->assertCount(1, $keySizeElements);
        $this->assertEquals('10', $keySizeElements[0]->textContent);

        // Test ordering of EncryptionMethod contents
        /** @var \DOMElement[] $emElements */
        $emElements = XPath::xpQuery($emElement, './xenc:KeySize/following-sibling::*', $xpCache);

        $this->assertCount(2, $emElements);
        $this->assertEquals('xenc:OAEPparams', $emElements[0]->tagName);
        $this->assertEquals('ssp:Chunk', $emElements[1]->tagName);
    }


    // test unmarshalling


    /**
     * Test that creating an EncryptionMethod object from XML without an Algorithm attribute fails.
     */
    public function testUnmarshallingWithoutAlgorithm(): void
    {
        $xmlRepresentation = clone self::$xmlRepresentation;

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage('Missing \'Algorithm\' attribute on md:EncryptionMethod.');

        $xmlRepresentation->documentElement->removeAttribute('Algorithm');
        EncryptionMethod::fromXML($xmlRepresentation->documentElement);
    }


    /**
     * Test that creating an EncryptionMethod object from XML works if no optional elements are present.
     */
    public function testUnmarshallingWithoutOptionalParameters(): void
    {
        $document = DOMDocumentFactory::fromString(sprintf(
            '<md:EncryptionMethod xmlns:md="%s" Algorithm="http://www.w3.org/2001/04/xmlenc#rsa-oaep-mgf1p"/>',
            C::NS_MD,
        ));

        $em = EncryptionMethod::fromXML($document->documentElement);
        $this->assertNull($em->getKeySize());
        $this->assertNull($em->getOAEPParams());
        $this->assertEmpty($em->getElements());
        $this->assertEquals(
            $document->saveXML($document->documentElement),
            strval($em),
        );
    }
}
