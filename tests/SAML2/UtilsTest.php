<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2;

use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\AttributeQuery;
use SimpleSAML\SAML2\XML\saml\NameID;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Utils as XMLUtils;

/**
 * Class \SimpleSAML\SAML2\UtilsTest
 */
class UtilsTest extends TestCase
{
    /**
     * Test parseBoolean, XML allows both 1 and true as values.
     * @return void
     */
    public function testParseBoolean(): void
    {
        // variations of true: "true", 1, and captalizations
        $document = DOMDocumentFactory::fromString(
            '<somenode anattribute="true"></somenode>'
        );
        $result = Utils::parseBoolean($document->firstChild, 'anattribute');
        $this->assertTrue($result);

        $document = DOMDocumentFactory::fromString(
            '<somenode anattribute="1"></somenode>'
        );
        $result = Utils::parseBoolean($document->firstChild, 'anattribute');
        $this->assertTrue($result);

        $document = DOMDocumentFactory::fromString(
            '<somenode anattribute="True"></somenode>'
        );

        // variations of false: "false", 0
        $result = Utils::parseBoolean($document->firstChild, 'anattribute');
        $this->assertTrue($result);

        $document = DOMDocumentFactory::fromString(
            '<somenode anattribute="false"></somenode>'
        );
        $result = Utils::parseBoolean($document->firstChild, 'anattribute');
        $this->assertFalse($result);

        $document = DOMDocumentFactory::fromString(
            '<somenode anattribute="0"></somenode>'
        );
        $result = Utils::parseBoolean($document->firstChild, 'anattribute');
        $this->assertFalse($result);

        // Usage of the default if attribute not found
        $document = DOMDocumentFactory::fromString(
            '<somenode anattribute="true"></somenode>'
        );
        $result = Utils::parseBoolean($document->firstChild, 'otherattribute');
        $this->assertNull($result);

        $document = DOMDocumentFactory::fromString(
            '<somenode anattribute="true"></somenode>'
        );
        $result = Utils::parseBoolean($document->firstChild, 'otherattribute', '404');
        $this->assertEquals($result, '404');

        // Exception on invalid value
        $this->expectException(\Exception::class, "Invalid value of boolean attribute 'anattribute': 'yes'");

        $document = DOMDocumentFactory::fromString(
            '<somenode anattribute="yes"></somenode>'
        );
        $result = Utils::parseBoolean($document->firstChild, 'anattribute');
    }
}
