<?php

declare(strict_types=1);

namespace SAML2\Tests;

use SAML2\DOMDocumentFactory;
use SAML2\Exception\UnparseableXmlException;
use SAML2\Exception\InvalidArgumentException;
use SAML2\Exception\RuntimeException;

class DOMDocumentFactoryTest extends \PHPUnit\Framework\TestCase
{
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
     * @group domdocument
     */
    public function testFileThatDoesNotExistIsNotAccepted()
    {
        $filename = 'DoesNotExist.ext';
        $this->expectException(InvalidArgumentException::class);
        DOMDocumentFactory::fromFile($filename);
    }

    /**
     * @group domdocument
     */
    public function testFileThatDoesNotContainXMLCannotBeLoaded()
    {
        $file = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'domdocument_invalid_xml.xml';
        $this->expectException(RuntimeException::class);
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
        $file = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'domdocument_doctype.xml';
        $this->expectException(RuntimeException::class, 'Dangerous XML detected, DOCTYPE nodes are not allowed in the XML body');
        DOMDocumentFactory::fromFile($file);
    }

    /**
     * @group                    domdocument
     */
    public function testStringThatContainsDocTypeIsNotAccepted()
    {
        $xml = '<!DOCTYPE foo [<!ELEMENT foo ANY > <!ENTITY xxe SYSTEM "file:///dev/random" >]><foo />';
        $this->expectException(RuntimeException::class, 'Dangerous XML detected, DOCTYPE nodes are not allowed in the XML body');
        DOMDocumentFactory::fromString($xml);
    }

    /**
     * @group                    domdocument
     */
    public function testEmptyFileIsNotValid()
    {
        $file = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'domdocument_empty.xml';
        $this->expectException(RuntimeException::class, 'does not have content');
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
}
