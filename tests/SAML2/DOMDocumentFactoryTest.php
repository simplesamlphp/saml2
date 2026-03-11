<?php

declare(strict_types=1);

namespace SAML2;

use SimpleSAML\Assert\Assert;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\IOException;
use SimpleSAML\XML\Exception\RuntimeException;
use SimpleSAML\XML\Exception\UnparseableXMLException;

use function sprintf;

class DOMDocumentFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @group domdocument
     * @return void
     */
    public function testNotXmlStringRaisesAnException(): void
    {
        $this->expectException(UnparseableXMLException::class);
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
        $this->expectException(IOException::class);
        $filename = 'DoesNotExist.ext';
        DOMDocumentFactory::fromFile($filename);
    }


    /**
     * @group domdocument
     * @return void
     */
    public function testFileThatDoesNotContainXMLCannotBeLoaded(): void
    {
        $this->expectException(UnparseableXMLException::class);
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
        $this->expectExceptionMessage('Dangerous XML detected, DOCTYPE nodes are not allowed in the XML body');
        $file = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'domdocument_doctype.xml';
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Dangerous XML detected, DOCTYPE nodes are not allowed in the XML body');
        DOMDocumentFactory::fromFile($file);
    }


    /**
     * @group                    domdocument
     * @return void
     */
    public function testStringThatContainsDocTypeIsNotAccepted(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Dangerous XML detected, DOCTYPE nodes are not allowed in the XML body');
        $xml = '<!DOCTYPE foo [<!ELEMENT foo ANY > <!ENTITY xxe SYSTEM "file:///dev/random" >]><foo />';
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Dangerous XML detected, DOCTYPE nodes are not allowed in the XML body');
        DOMDocumentFactory::fromString($xml);
    }


    /**
     * @group                    domdocument
     * @return void
     */
    public function testStringThatContainsDocTypeIsNotAccepted2(): void
    {
        $xml = '<?xml version="1.0" encoding="ISO-8859-1"?>
               <!DOCTYPE foo [<!ENTITY % exfiltrate SYSTEM "file://dev/random">%exfiltrate;]>
               <foo>y</foo>';
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Dangerous XML detected, DOCTYPE nodes are not allowed in the XML body',
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
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(sprintf('File "%s" does not have content', $file));
        DOMDocumentFactory::fromFile($file);
    }


    /**
     * @group                    domdocument
     * @return void
     */
    public function testEmptyStringIsNotValid(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Expected a non-whitespace string. Got: ""');
        DOMDocumentFactory::fromString("");
    }
}
