<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\ds;

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
        $this->document = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/ds_X509SubjectName.xml'
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
