<?php

declare(strict_types=1);

namespace SAML2\XML\ds;

use PHPUnit\Framework\TestCase;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use SimpleSAML\SAML2\DOMDocumentFactory;
use SimpleSAML\SAML2\Utils;

/**
 * Class \SAML2\XML\ds\X509SubjectNameTest
 *
 * @covers \SimpleSAML\SAML2\XML\ds\X509SubjectName
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
final class X509SubjectNameTest extends TestCase
{
    /** @var \DOMDocument */
    private $document;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $ns = X509SubjectName::NS;
        $this->document = DOMDocumentFactory::fromString(<<<XML
<ds:X509SubjectName xmlns:ds="{$ns}">some name</ds:X509SubjectName>
XML
        );
    }


    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $subjectName = new X509SubjectName('some name');

        $this->assertEquals('some name', $subjectName->getName());

        $this->assertEquals($this->document->saveXML($this->document->documentElement), strval($subjectName));
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $subjectName = X509SubjectName::fromXML($this->document->documentElement);

        $this->assertEquals('some name', $subjectName->getName());
    }


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(X509SubjectName::fromXML($this->document->documentElement))))
        );
    }
}
