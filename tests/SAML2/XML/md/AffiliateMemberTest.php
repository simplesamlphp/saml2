<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\XML\md\AffiliateMember;
use SimpleSAML\Test\XML\SchemaValidationTestTrait;
use SimpleSAML\Test\XML\SerializableElementTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\SchemaViolationException;

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
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    protected function setUp(): void
    {
        $this->schema = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/schemas/saml-schema-metadata-2.0.xsd';

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
        $this->expectException(SchemaViolationException::class);

        new AffiliateMember('');
    }


    /**
     */
    public function testMarshallingTooLongContentThrowsException(): void
    {
        $this->expectException(ProtocolViolationException::class);
        $this->expectExceptionMessage(
            sprintf('The AffiliateMember cannot be longer than %d characters.', C::ENTITYID_MAX_LENGTH)
        );

        new AffiliateMember(str_pad('https://some.entity.org/id', C::ENTITYID_MAX_LENGTH + 1, 'a'));
    }


    // test unmarshalling


    /**
     * Test creating a AffiliateMember from XML.
     */
    public function testUnmarshalling(): void
    {
        $affiliateMember = AffiliateMember::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($affiliateMember)
        );
    }
}
