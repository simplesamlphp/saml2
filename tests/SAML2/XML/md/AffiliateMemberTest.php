<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\XML\md\AffiliateMember;
use SimpleSAML\Test\XML\SerializableXMLTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;

use function dirname;
use function strval;

/**
 * Tests for AffiliateMember.
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


    // test marshalling


    /**
     * Test creating a AffiliateMember object from scratch.
     */
    public function testMarshalling(): void
    {
        $am = new AffiliateMember('urn:some:entity');

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($am)
        );
    }


    /**
     */
    public function testMarshallingEmptyThrowsException(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('Cannot specify an empty string as an affiliation member entityID.');

        new AffiliateMember('');
    }


    /**
     */
    public function testMarshallingTooLongContentThrowsException(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage(
            sprintf('The AffiliateMember cannot be longer than %d characters.', C::ENTITYID_MAX_LENGTH)
        );

        new AffiliateMember(str_pad('urn:entityid:', C::ENTITYID_MAX_LENGTH + 1, 'a'));
    }


    // test unmarshalling


    /**
     * Test creating a AffiliateMember from XML.
     */
    public function testUnmarshalling(): void
    {
        $am = AffiliateMember::fromXML($this->xmlRepresentation->documentElement);
        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($am)
        );
    }
}
