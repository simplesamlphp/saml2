<?php

declare(strict_types=1);

namespace SAML2;

use Exception;
use PHPUnit\Framework\TestCase;
use SimpleSAMLSAML2\Constants;
use SimpleSAMLSAML2\DOMDocumentFactory;
use SimpleSAMLSAML2\XML\ds\X509Data;
use SimpleSAMLSAML2\XML\saml\NameID;
use SimpleSAMLSAML2\XML\saml\Subject;
use SimpleSAMLSAML2\XML\samlp\AttributeQuery;
use SimpleSAMLSAML2\Utils;
use SimpleSAML\TestUtils\PEMCertificatesMock;

/**
 * Class \SAML2\UtilsTest
 *
 * @covers \SAML2\Utils
 * @package simplesamlphp\saml2
 */
final class UtilsTest extends TestCase
{
    /**
     * Test querying a SAML XML document.
     * @return void
     */
    public function testXpQuery(): void
    {
        $nameId_before = new NameID(
            'NameIDValue',
            'OurNameQualifier',
            'TheSPNameQualifier',
            'SomeNameIDFormat',
            null
        );

        $aq = new AttributeQuery(new Subject($nameId_before));

        $xml = $aq->toXML();

        $nameId_after = Utils::xpQuery($xml, './saml_assertion:Subject/saml_assertion:NameID');
        $this->assertTrue(count($nameId_after) === 1);
        $this->assertEquals('NameIDValue', $nameId_after[0]->textContent);
        $this->assertEquals('SomeNameIDFormat', $nameId_after[0]->getAttribute("Format"));
        $this->assertEquals('OurNameQualifier', $nameId_after[0]->getAttribute("NameQualifier"));
        $this->assertEquals('TheSPNameQualifier', $nameId_after[0]->getAttribute("SPNameQualifier"));
    }


    /**
     * Test adding an element with a string value.
     * @return void
     */
    public function testAddString(): void
    {
        $document = DOMDocumentFactory::fromString('<root/>');

        Utils::addString(
            $document->firstChild,
            'testns',
            'ns:somenode',
            'value'
        );
        $this->assertEquals(
            '<root><ns:somenode xmlns:ns="testns">value</ns:somenode></root>',
            $document->saveXML($document->firstChild)
        );

        $document->loadXML('<ns:root xmlns:ns="testns"/>');
        Utils::addString(
            $document->firstChild,
            'testns',
            'ns:somenode',
            'value'
        );
        $this->assertEquals(
            '<ns:root xmlns:ns="testns"><ns:somenode>value</ns:somenode></ns:root>',
            $document->saveXML($document->firstChild)
        );
    }


    /**
     * Test adding multiple elements of a given type with given values.
     * @return void
     */
    public function testGetAddStrings(): void
    {
        $document = DOMDocumentFactory::fromString('<root/>');
        Utils::addStrings(
            $document->firstChild,
            'testns',
            'ns:somenode',
            false,
            ['value1', 'value2']
        );
        $this->assertEquals(
            '<root>' .
            '<ns:somenode xmlns:ns="testns">value1</ns:somenode>' .
            '<ns:somenode xmlns:ns="testns">value2</ns:somenode>' .
            '</root>',
            $document->saveXML($document->firstChild)
        );

        $document->loadXML('<ns:root xmlns:ns="testns"/>');
        Utils::addStrings(
            $document->firstChild,
            'testns',
            'ns:somenode',
            false,
            ['value1', 'value2']
        );
        $this->assertEquals(
            '<ns:root xmlns:ns="testns">' .
            '<ns:somenode>value1</ns:somenode>' .
            '<ns:somenode>value2</ns:somenode>' .
            '</ns:root>',
            $document->saveXML($document->firstChild)
        );

        $document->loadXML('<root/>');
        Utils::addStrings(
            $document->firstChild,
            'testns',
            'ns:somenode',
            true,
            ['en' => 'value (en)', 'no' => 'value (no)']
        );
        $this->assertEquals(
            '<root>' .
            '<ns:somenode xmlns:ns="testns" xml:lang="en">value (en)</ns:somenode>' .
            '<ns:somenode xmlns:ns="testns" xml:lang="no">value (no)</ns:somenode>' .
            '</root>',
            $document->saveXML($document->firstChild)
        );

        $document->loadXML('<ns:root xmlns:ns="testns"/>');
        Utils::addStrings(
            $document->firstChild,
            'testns',
            'ns:somenode',
            true,
            ['en' => 'value (en)', 'no' => 'value (no)']
        );
        $this->assertEquals(
            '<ns:root xmlns:ns="testns">' .
            '<ns:somenode xml:lang="en">value (en)</ns:somenode>' .
            '<ns:somenode xml:lang="no">value (no)</ns:somenode>' .
            '</ns:root>',
            $document->saveXML($document->firstChild)
        );
    }


    /**
     * Test retrieval of a string value for a given node.
     * @return void
     */
    public function testExtractString(): void
    {
        $document = DOMDocumentFactory::fromString(
            '<root xmlns="' . Constants::NS_MD . '">' .
            '<somenode>value1</somenode>' .
            '<somenode>value2</somenode>' .
            '</root>'
        );

        $stringValues = Utils::extractStrings(
            $document->firstChild,
            Constants::NS_MD,
            'somenode'
        );

        $this->assertTrue(count($stringValues) === 2);
        $this->assertEquals('value1', $stringValues[0]);
        $this->assertEquals('value2', $stringValues[1]);
    }


    /**
     * Test retrieval of a localized string for a given node.
     * @return void
     */
    public function testExtractLocalizedString(): void
    {
        $document = DOMDocumentFactory::fromString(
            '<root xmlns="' . Constants::NS_MD . '">' .
            '<somenode xml:lang="en">value (en)</somenode>' .
            '<somenode xml:lang="no">value (no)</somenode>' .
            '</root>'
        );

        $localizedStringValues = Utils::extractLocalizedStrings(
            $document->firstChild,
            Constants::NS_MD,
            'somenode'
        );

        $this->assertTrue(count($localizedStringValues) === 2);
        $this->assertEquals('value (en)', $localizedStringValues["en"]);
        $this->assertEquals('value (no)', $localizedStringValues["no"]);
    }


    /**
     * Test xsDateTime format validity
     *
     * @dataProvider xsDateTimes
     * @return void
     */
    public function testXsDateTimeToTimestamp($shouldPass, $time, $expectedTs = null): void
    {
        try {
            $ts = Utils::xsDateTimeToTimestamp($time);
            $this->assertTrue($shouldPass);
            $this->assertEquals($expectedTs, $ts);
        } catch (\Exception $e) {
            $this->assertFalse($shouldPass);
        }
    }


    /**
     * @return array
     */
    public function xsDateTimes(): array
    {
        return [
            [true, '2015-01-01T00:00:00Z', 1420070400],
            [true, '2015-01-01T00:00:00.0Z', 1420070400],
            [true, '2015-01-01T00:00:00.1Z', 1420070400],
            [true, '2015-01-01T00:00:00.321Z', 1420070400],
            [true, '2015-01-01T00:00:00.587Z', 1420070400],
            [true, '2015-01-01T00:00:00.123456Z', 1420070400],
            [true, '2015-01-01T00:00:00.1234567Z', 1420070400],
            [false, '2015-01-01T00:00:00', 1420070400],
            [false, '2015-01-01T00:00:00.0', 1420070400],
            [false, 'junk'],
            [false, '2015-01-01T00:00:00-04:00'],
            [false, '2015-01-01T00:00:00.0-04:00'],
            [false, '2015-01-01T00:00:00.123456Z789012345', 1420070400],
        ];
    }


    /**
     * Test createKeyDescriptor.
     * @return void
     */
    public function testCreateKeyDescriptor(): void
    {
        $X509Data = PEMCertificatesMock::getPlainPrivateKeyContents();
        $keyDescriptor = Utils::createKeyDescriptor($X509Data);

        $this->assertInstanceOf(X509Data::class, $keyDescriptor->getKeyInfo()->getInfo()[0]);
    }
}
