<?php

namespace SAML2;

/**
 * Class \SAML2\UtilsTest
 */
class UtilsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test querying a SAML XML document.
     */
    public function testXpQuery()
    {
        $aq = new AttributeQuery();
        $aq->setNameID(array(
            'Value' => 'NameIDValue',
            'Format' => 'SomeNameIDFormat',
            'NameQualifier' => 'OurNameQualifier',
            'SPNameQualifier' => 'TheSPNameQualifier',
        ));

        $xml = $aq->toUnsignedXML();

        $nameID = Utils::xpQuery($xml, './saml_assertion:Subject/saml_assertion:NameID');
        $this->assertTrue(count($nameID) === 1);
        $this->assertEquals('SomeNameIDFormat', $nameID[0]->getAttribute("Format"));
        $this->assertEquals('OurNameQualifier', $nameID[0]->getAttribute("NameQualifier"));
        $this->assertEquals('TheSPNameQualifier', $nameID[0]->getAttribute("SPNameQualifier"));
        $this->assertEquals('NameIDValue', $nameID[0]->textContent);
    }

    /**
     * Test adding an element with a string value.
     */
    public function testAddString()
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
     */
    public function testGetAddStrings()
    {
        $document = DOMDocumentFactory::fromString('<root/>');
        Utils::addStrings(
            $document->firstChild,
            'testns',
            'ns:somenode',
            false,
            array('value1', 'value2')
        );
        $this->assertEquals(
            '<root>'.
            '<ns:somenode xmlns:ns="testns">value1</ns:somenode>'.
            '<ns:somenode xmlns:ns="testns">value2</ns:somenode>'.
            '</root>',
            $document->saveXML($document->firstChild)
        );

        $document->loadXML('<ns:root xmlns:ns="testns"/>');
        Utils::addStrings(
            $document->firstChild,
            'testns',
            'ns:somenode',
            false,
            array('value1', 'value2')
        );
        $this->assertEquals(
            '<ns:root xmlns:ns="testns">'.
            '<ns:somenode>value1</ns:somenode>'.
            '<ns:somenode>value2</ns:somenode>'.
            '</ns:root>',
            $document->saveXML($document->firstChild)
        );

        $document->loadXML('<root/>');
        Utils::addStrings(
            $document->firstChild,
            'testns',
            'ns:somenode',
            true,
            array('en' => 'value (en)', 'no' => 'value (no)')
        );
        $this->assertEquals(
            '<root>'.
            '<ns:somenode xmlns:ns="testns" xml:lang="en">value (en)</ns:somenode>'.
            '<ns:somenode xmlns:ns="testns" xml:lang="no">value (no)</ns:somenode>'.
            '</root>',
            $document->saveXML($document->firstChild)
        );

        $document->loadXML('<ns:root xmlns:ns="testns"/>');
        Utils::addStrings(
            $document->firstChild,
            'testns',
            'ns:somenode',
            true,
            array('en' => 'value (en)', 'no' => 'value (no)')
        );
        $this->assertEquals(
            '<ns:root xmlns:ns="testns">'.
            '<ns:somenode xml:lang="en">value (en)</ns:somenode>'.
            '<ns:somenode xml:lang="no">value (no)</ns:somenode>'.
            '</ns:root>',
            $document->saveXML($document->firstChild)
        );
    }

    /**
     * Test retrieval of a string value for a given node.
     */
    public function testExtractString()
    {
        $document = DOMDocumentFactory::fromString(
            '<root xmlns="' . Constants::NS_MD . '">'.
            '<somenode>value1</somenode>'.
            '<somenode>value2</somenode>'.
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
     */
    public function testExtractLocalizedString()
    {
        $document = DOMDocumentFactory::fromString(
            '<root xmlns="' . Constants::NS_MD . '">'.
            '<somenode xml:lang="en">value (en)</somenode>'.
            '<somenode xml:lang="no">value (no)</somenode>'.
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
     */
    public function testXsDateTimeToTimestamp($shouldPass, $time, $expectedTs = null)
    {
        try {
            $ts = Utils::xsDateTimeToTimestamp($time);
            $this->assertTrue($shouldPass);
            $this->assertEquals($expectedTs, $ts);
        } catch (\Exception $e) {
            $this->assertFalse($shouldPass);
        }
    }

    public function xsDateTimes()
    {
        return array(
            array(true, '2015-01-01T00:00:00Z', 1420070400),
            array(true, '2015-01-01T00:00:00.0Z', 1420070400),
            array(true, '2015-01-01T00:00:00.1Z', 1420070400),
            array(false, '2015-01-01T00:00:00', 1420070400),
            array(false, '2015-01-01T00:00:00.0', 1420070400),
            array(false, 'junk'),
            array(false, '2015-01-01T00:00:00-04:00'),
            array(false, '2015-01-01T00:00:00.0-04:00'),
        );
    }

    /**
     * Test parseBoolean, XML allows both 1 and true as values.
     */
    public function testParseBoolean()
    {
        // variations of true: "true", 1, and captalizations
        $document = DOMDocumentFactory::fromString(
            '<somenode anattribute="true"></somenode>'
        );
        $result = Utils::parseBoolean($document->firstChild, 'anattribute' );
        $this->assertTrue($result);

        $document = DOMDocumentFactory::fromString(
            '<somenode anattribute="1"></somenode>'
        );
        $result = Utils::parseBoolean($document->firstChild, 'anattribute' );
        $this->assertTrue($result);

        $document = DOMDocumentFactory::fromString(
            '<somenode anattribute="True"></somenode>'
        );

        // variations of false: "false", 0
        $result = Utils::parseBoolean($document->firstChild, 'anattribute' );
        $this->assertTrue($result);

        $document = DOMDocumentFactory::fromString(
            '<somenode anattribute="false"></somenode>'
        );
        $result = Utils::parseBoolean($document->firstChild, 'anattribute' );
        $this->assertFalse($result);

        $document = DOMDocumentFactory::fromString(
            '<somenode anattribute="0"></somenode>'
        );
        $result = Utils::parseBoolean($document->firstChild, 'anattribute' );
        $this->assertFalse($result);

        // Usage of the default if attribute not found
        $document = DOMDocumentFactory::fromString(
            '<somenode anattribute="true"></somenode>'
        );
        $result = Utils::parseBoolean($document->firstChild, 'otherattribute' );
        $this->assertNull($result);

        $document = DOMDocumentFactory::fromString(
            '<somenode anattribute="true"></somenode>'
        );
        $result = Utils::parseBoolean($document->firstChild, 'otherattribute', '404' );
        $this->assertEquals($result, '404');

        // Exception on invalid value
        $this->setExpectedException('Exception', "Invalid value of boolean attribute 'anattribute': 'yes'");

        $document = DOMDocumentFactory::fromString(
            '<somenode anattribute="yes"></somenode>'
        );
        $result = Utils::parseBoolean($document->firstChild, 'anattribute' );
    }

}
