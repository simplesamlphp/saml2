<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use DOMDocument;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\XML\md\AbstractLocalizedName;
use SimpleSAML\SAML2\XML\md\AbstractMdElement;
use SimpleSAML\SAML2\XML\md\ServiceDescription;
use SimpleSAML\XML\DOMDocumentFactory;

use function dirname;

/**
 * Tests for localized names.
 *
 * @package simplesamlphp/saml2
 */
#[Group('md')]
#[CoversClass(ServiceDescription::class)]
#[CoversClass(AbstractLocalizedName::class)]
#[CoversClass(AbstractMdElement::class)]
final class AbstractLocalizedNameTest extends TestCase
{
    /** @var \DOMDocument */
    private static DOMDocument $xmlRepresentation;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/md_ServiceDescription.xml',
        );
    }


    // test unmarshalling


    /**
     * Test that creating a ServiceDescription from XML works for empty values.
     */
    public function testUnmarshallingWithEmptyValue(): void
    {
        $xmlRepresentation = clone self::$xmlRepresentation;
        $xmlRepresentation->documentElement->textContent = '';

        $this->expectException(ProtocolViolationException::class);
        $this->expectExceptionMessage('"" is not a SAML2.0-compliant string');

        ServiceDescription::fromXML($xmlRepresentation->documentElement);
    }
}
