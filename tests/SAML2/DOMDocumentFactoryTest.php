<?php

namespace SAML2;

use SAML2\DOMDocumentFactory;
use SAML2\Exception\UnparseableXmlException;
use SAML2\Exception\InvalidArgumentException;
use SAML2\Exception\RuntimeException;

class DOMDocumentFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param mixed $argument
     *
     * @group domdocument
     * @dataProvider nonStringProvider
     */
    public function testOnlyAStringIsAcceptedByFronString($argument)
    {
        $this->expectException(Exception\InvalidArgumentException::class);
        DOMDocumentFactory::fromString($argument);
    }


    /**
     * @group domdocument
     */
    public function testNotXmlStringRaisesAnException()
    {
        $this->expectException(UnparseableXmlException::class);
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
     */
    public function testOnlyAStringIsAcceptedByFromFile($argument)
    {
        $this->expectException(InvalidArgumentException::class);
        DOMDocumentFactory::fromFile($argument);
    }


    /**
     * @group        domdocument
     * @dataProvider nonStringProvider
     */
    public function testFileThatDoesNotExistIsNotAccepted()
    {
        $this->expectException(InvalidArgumentException::class);
        $filename = 'DoesNotExist.ext';
        DOMDocumentFactory::fromFile($filename);
    }


    /**
     * @group domdocument
     */
    public function testFileThatDoesNotContainXMLCannotBeLoaded()
    {
        $this->expectException(RuntimeException::class);
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
     */
    public function testFileThatContainsDocTypeIsNotAccepted()
    {
        $this->expectException(RuntimeException::class, 'Dangerous XML detected, DOCTYPE nodes are not allowed in the XML body');
        $file = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'domdocument_doctype.xml';
        $this->expectException(Exception\RuntimeException::class, 'Dangerous XML detected, DOCTYPE nodes are not allowed in the XML body');
        DOMDocumentFactory::fromFile($file);
    }


    /**
     * @group                    domdocument
     */
    public function testStringThatContainsDocTypeIsNotAccepted()
    {
        $this->expectException(RuntimeException::class, 'Dangerous XML detected, DOCTYPE nodes are not allowed in the XML body');
        $xml = '<!DOCTYPE foo [<!ELEMENT foo ANY > <!ENTITY xxe SYSTEM "file:///dev/random" >]><foo />';
        $this->expectException(Exception\RuntimeException::class, 'Dangerous XML detected, DOCTYPE nodes are not allowed in the XML body');
        DOMDocumentFactory::fromString($xml);
    }


    /**
     * @group                    domdocument
     */
    public function testEmptyFileIsNotValid()
    {
        $this->expectException(RuntimeException::class, 'does not have content');
        $file = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'domdocument_empty.xml';
        $this->expectException(Exception\RuntimeException::class, 'does not have content');
        DOMDocumentFactory::fromFile($file);
    }


    /**
     * @group                    domdocument
     */
    public function testEmptyStringIsNotValid()
    {
        $this->expectException(InvalidArgumentException::class, 'Invalid Argument type: "non-empty string" expected, "string" given');
        DOMDocumentFactory::fromString("");
    }


    /**
     * @return array
     */
    public function nonStringProvider()
    {
        return [
            'integer' => [1],
            'float'   => [1.234],
            'object'  => [new \stdClass()],
            'null'    => [null],
            'boolean' => [false],
            'array'   => [[]],
        ];
    }
}
