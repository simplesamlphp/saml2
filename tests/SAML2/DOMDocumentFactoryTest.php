<?php

declare(strict_types=1);

namespace SAML2;

use SAML2\DOMDocumentFactory;
use SAML2\Exception\UnparseableXmlException;
use SAML2\Exception\InvalidArgumentException;
use SAML2\Exception\RuntimeException;

/**
 * @covers \SAML2\DOMDocumentFactory
 * @package simplesamlphp\saml2
 */
class DOMDocumentFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @group domdocument
     * @return void
     */
    public function testNotXmlStringRaisesAnException(): void
    {
        $this->expectException(UnparseableXmlException::class);
        DOMDocumentFactory::fromString('this is not xml');
    }


    /**
     * @group domdocument
     * @return void
     */
    public function testXmlStringIsCorrectlyLoaded(): void
    {
        $xml = '<root/>';

        $document = DOMDocumentFactory::fromString($xml);

        $this->assertXmlStringEqualsXmlString($xml, $document->saveXML());
    }


    /**
     * @return void
     */
    public function testFileThatDoesNotExistIsNotAccepted(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $filename = 'DoesNotExist.ext';
        DOMDocumentFactory::fromFile($filename);
    }


    /**
     * @group domdocument
     * @return void
     */
    public function testFileThatDoesNotContainXMLCannotBeLoaded(): void
    {
        $this->expectException(RuntimeException::class);
        $file = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'domdocument_invalid_xml.xml';
        DOMDocumentFactory::fromFile($file);
    }


    /**
     * @group domdocument
     * @return void
     */
    public function testFileWithValidXMLCanBeLoaded(): void
    {
        $file = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'domdocument_valid_xml.xml';

        $document = DOMDocumentFactory::fromFile($file);

        $this->assertXmlStringEqualsXmlFile($file, $document->saveXML());
    }


    /**
     * @group                    domdocument
     * @return void
     */
    public function testFileThatContainsDocTypeIsNotAccepted(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Dangerous XML detected, DOCTYPE nodes are not allowed in the XML body'
        );
        $file = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'domdocument_doctype.xml';
        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage(
            'Dangerous XML detected, DOCTYPE nodes are not allowed in the XML body'
        );
        DOMDocumentFactory::fromFile($file);
    }


    /**
     * @group                    domdocument
     * @return void
     */
    public function testStringThatContainsDocTypeIsNotAccepted(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Dangerous XML detected, DOCTYPE nodes are not allowed in the XML body'
        );
        $xml = '<!DOCTYPE foo [<!ELEMENT foo ANY > <!ENTITY xxe SYSTEM "file:///dev/random" >]><foo />';
        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage(
            'Dangerous XML detected, DOCTYPE nodes are not allowed in the XML body'
        );
        DOMDocumentFactory::fromString($xml);
    }


    /**
     * @group                    domdocument
     * @return void
     */
    public function testEmptyFileIsNotValid(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('does not have content');
        $file = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'domdocument_empty.xml';
        $this->expectException(Exception\RuntimeException::class);
        $this->expectExceptionMessage('does not have content');
        DOMDocumentFactory::fromFile($file);
    }


    /**
     * @group                    domdocument
     * @return void
     */
    public function testEmptyStringIsNotValid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Invalid Argument type: "non-empty string" expected, "string" given'
        );
        DOMDocumentFactory::fromString("");
    }
}
