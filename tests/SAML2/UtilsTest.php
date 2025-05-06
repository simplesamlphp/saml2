<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\saml\NameID;
use SimpleSAML\SAML2\XML\saml\Subject;
use SimpleSAML\SAML2\XML\samlp\AttributeQuery;

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
            'NameIDValue',
            'urn:x-simplesamlphp:namequalifier',
            'urn:x-simplesamlphp:spnamequalifier',
            C::NAMEID_TRANSIENT,
        );

        $aq = new AttributeQuery(new Subject($nameId_before), self::$clock->now());

        $xml = $aq->toXML();

        $xpCache = XPath::getXPath($xml);
        $nameId_after = XPath::xpQuery($xml, './saml_assertion:Subject/saml_assertion:NameID', $xpCache);
        $this->assertTrue(count($nameId_after) === 1);

        /** @var \DOMNode $nameId_after[0] */
        $this->assertEquals('NameIDValue', $nameId_after[0]->textContent);
        $this->assertEquals(C::NAMEID_TRANSIENT, $nameId_after[0]->getAttribute("Format"));
        $this->assertEquals('urn:x-simplesamlphp:namequalifier', $nameId_after[0]->getAttribute("NameQualifier"));
        $this->assertEquals('urn:x-simplesamlphp:spnamequalifier', $nameId_after[0]->getAttribute("SPNameQualifier"));
    }
}
