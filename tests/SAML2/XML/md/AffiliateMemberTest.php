<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\XML\md\AffiliateMember;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\SchemaViolationException;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

use function dirname;
use function sprintf;
use function str_pad;
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
    public static function setUpBeforeClass(): void
    {
        self::$schemaFile = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-metadata-2.0.xsd';

        self::$testedClass = AffiliateMember::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/md_AffiliateMember.xml',
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
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($affiliateMember),
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
            sprintf('The AffiliateMember cannot be longer than %d characters.', C::ENTITYID_MAX_LENGTH),
        );

        new AffiliateMember(str_pad('https://some.entity.org/id', C::ENTITYID_MAX_LENGTH + 1, 'a'));
    }
}
