<?php

namespace SAML2;

class DOMDocumentFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param mixed $argument
     *
     * @group domdocument
     * @dataProvider nonStringProvider
     * @expectedException \SAML2\Exception\InvalidArgumentException
     */
    public function testOnlyAStringIsAcceptedByFronString($argument)
    {
        DOMDocumentFactory::fromString($argument);
    }

    /**
     * @group domdocument
     * @expectedException \SAML2\Exception\RuntimeException
     */
    public function testNotXmlStringRaisesAnException()
    {
        DOMDocumentFactory::fromString('this is not xml');
    }

    /**
     * @group domdocument
     */
    public function testXmlStringIsCorrectlyLoaded()
    {
        $xml = '<root/>';

        $document = DOMDocumentFactory::fromString($xml);

        $this->assertXmlStringEqualsXmlString($xml, $document->saveXML());
    }

    /**
     * @param mixed $argument
     *
     * @group        domdocument
     * @dataProvider nonStringProvider
     * @expectedException \SAML2\Exception\InvalidArgumentException
     */
    public function testOnlyAStringIsAcceptedByFromFile($argument)
    {
        DOMDocumentFactory::fromFile($argument);
    }

    /**
     * @group        domdocument
     * @dataProvider nonStringProvider
     * @expectedException \SAML2\Exception\InvalidArgumentException
     */
    public function testFileThatDoesNotExistIsNotAccepted()
    {
        $filename = 'DoesNotExist.ext';

        DOMDocumentFactory::fromFile($filename);
    }

    /**
     * @group domdocument
     * @expectedException \SAML2\Exception\RuntimeException
     */
    public function testFileThatDoesNotContainXMLCannotBeLoaded()
    {
        $file = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'domdocument_invalid_xml.xml';

        DOMDocumentFactory::fromFile($file);
    }

    /**
     * @group domdocument
     */
    public function testFileWithValidXMLCanBeLoaded()
    {
        $file = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'domdocument_valid_xml.xml';

        $document = DOMDocumentFactory::fromFile($file);

        $this->assertXmlStringEqualsXmlFile($file, $document->saveXML());
    }

    /**
     * @group                    domdocument
     * @expectedException        \SAML2\Exception\RuntimeException
     * @expectedExceptionMessage Dangerous XML detected, DOCTYPE nodes are not allowed in the XML body
     */
    public function testFileThatContainsDocTypeIsNotAccepted()
    {
        $file = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'domdocument_doctype.xml';
        DOMDocumentFactory::fromFile($file);
    }

    /**
     * @group                    domdocument
     * @expectedException        \SAML2\Exception\RuntimeException
     * @expectedExceptionMessage Dangerous XML detected, DOCTYPE nodes are not allowed in the XML body
     */
    public function testStringThatContainsDocTypeIsNotAccepted()
    {
        $xml = '<!DOCTYPE foo [<!ELEMENT foo ANY > <!ENTITY xxe SYSTEM "file:///dev/random" >]><foo />';
        DOMDocumentFactory::fromString($xml);
    }

    /**
     * @group                    domdocument
     * @expectedException        \SAML2\Exception\RuntimeException
     * @expectedExceptionMessage does not have content
     */
    public function testEmptyFileIsNotValid()
    {
        $file = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'domdocument_empty.xml';
        DOMDocumentFactory::fromFile($file);
    }

    /**
     * @group                    domdocument
     * @expectedException        \SAML2\Exception\InvalidArgumentException
     * @expectedExceptionMessage Invalid Argument type: "non-empty string" expected, "string" given
     */
    public function testEmptyStringIsNotValid()
    {
        DOMDocumentFactory::fromString("");
    }

    /**
     * @return array
     */
    public function nonStringProvider()
    {
        return array(
            'integer' => array(1),
            'float'   => array(1.234),
            'object'  => array(new \stdClass()),
            'null'    => array(null),
            'boolean' => array(false),
            'array'   => array(array()),
        );
    }
}
