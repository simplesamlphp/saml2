<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use DOMDocument;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\XML\md\AffiliateMember;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;

/**
 * Class \SimpleSAML\SAML2\XML\md\AffiliateMemberTest
 *
 * @covers \SimpleSAML\SAML2\XML\md\AffiliateMember
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMdElement
 * @package simplesamlphp/saml2
 */
final class AffiliateMemberTest extends TestCase
{
    use SerializableXMLTestTrait;


    /**
     */
    protected function setUp(): void
    {
        $this->testedClass = AffiliateMember::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/md_AffiliateMember.xml'
        );
    }


    // marshalling


    /**
     */
    public function testMarshalling(): void
    {
        $affiliateMember = new AffiliateMember('https://some.entity.org/id');

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($affiliateMember)
        );
    }


    /**
     */
    public function testMarshallingEmptyThrowsException(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('AffiliateMember cannot be empty');

        new AffiliateMember('');
    }


    /**
     */
    public function testMarshallingTooLongThrowsException(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('The entityID attribute cannot be longer than 1024 characters.');

        new AffiliateMember(str_pad('https://some.entity.org/', 1025, 'id'));
    }


    // unmarshalling


    /**
     */
    public function testUnmarshalling(): void
    {
        $affiliateMember = AffiliateMember::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals('https://some.entity.org/id', $affiliateMember->getContent());
    }
}
