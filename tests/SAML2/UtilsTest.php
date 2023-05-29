<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2;

use Exception;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\saml\NameID;
use SimpleSAML\SAML2\XML\saml\Subject;
use SimpleSAML\SAML2\XML\samlp\AttributeQuery;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Utils as XMLUtils;
use SimpleSAML\XMLSecurity\TestUtils\PEMCertificatesMock;
use SimpleSAML\XMLSecurity\XML\ds\X509Data;

use function count;

/**
 * Class \SAML2\UtilsTest
 *
 * @covers \SimpleSAML\SAML2\Utils
 * @package simplesamlphp\saml2
 */
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
            'OurNameQualifier',
            'TheSPNameQualifier',
            C::NAMEID_TRANSIENT,
        );

        $aq = new AttributeQuery(new Subject($nameId_before), self::$clock->now());

        $xml = $aq->toXML();

        $xpCache = XPath::getXPath($xml);
        $nameId_after = XPath::xpQuery($xml, './saml_assertion:Subject/saml_assertion:NameID', $xpCache);
        $this->assertTrue(count($nameId_after) === 1);
        $this->assertEquals('NameIDValue', $nameId_after[0]->textContent);
        $this->assertEquals(C::NAMEID_TRANSIENT, $nameId_after[0]->getAttribute("Format"));
        $this->assertEquals('OurNameQualifier', $nameId_after[0]->getAttribute("NameQualifier"));
        $this->assertEquals('TheSPNameQualifier', $nameId_after[0]->getAttribute("SPNameQualifier"));
    }
}
