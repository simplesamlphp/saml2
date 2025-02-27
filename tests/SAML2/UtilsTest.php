<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Type\{SAMLAnyURIValue, SAMLDateTimeValue, SAMLStringValue};
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\saml\{NameID, Subject};
use SimpleSAML\SAML2\XML\samlp\AttributeQuery;
use SimpleSAML\XML\Type\IDValue;

use function count;

/**
 * Class \SAML2\UtilsTest
 *
 * @package simplesamlphp\saml2
 */
#[CoversClass(Utils::class)]
final class UtilsTest extends TestCase
{
    /** @var \Psr\Clock\ClockInterface */
    private static ClockInterface $clock;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$clock = Utils::getContainer()->getClock();
    }


    /**
     * Test querying a SAML XML document.
     */
    public function testXpQuery(): void
    {
        $nameId_before = new NameID(
            SAMLStringValue::fromString('NameIDValue'),
            SAMLStringValue::fromString('urn:x-simplesamlphp:namequalifier'),
            SAMLStringValue::fromString('urn:x-simplesamlphp:spnamequalifier'),
            SAMLAnyURIValue::fromString(C::NAMEID_TRANSIENT),
        );

        $aq = new AttributeQuery(
            id: IDValue::fromString('abc123'),
            subject: new Subject($nameId_before),
            issueInstant: SAMLDateTimeValue::fromDateTime(self::$clock->now()),
        );

        $xml = $aq->toXML();

        $xpCache = XPath::getXPath($xml);
        /** @var \DOMElement[] $nameId_after */
        $nameId_after = XPath::xpQuery($xml, './saml_assertion:Subject/saml_assertion:NameID', $xpCache);
        $this->assertTrue(count($nameId_after) === 1);

        /** @var \DOMElement $first */
        $first = $nameId_after[0];
        $this->assertEquals('NameIDValue', $first->textContent);
        $this->assertEquals(C::NAMEID_TRANSIENT, $first->getAttribute("Format"));
        $this->assertEquals('urn:x-simplesamlphp:namequalifier', $first->getAttribute("NameQualifier"));
        $this->assertEquals('urn:x-simplesamlphp:spnamequalifier', $first->getAttribute("SPNameQualifier"));
    }
}
