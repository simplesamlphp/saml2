<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2;

use DOMDocument;
use DOMElement;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\Assert;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\XML\md\EntitiesDescriptor;
use SimpleSAML\XML\DOMDocumentFactory;

/**
 * Class \SimpleSAML\SAML2\EntitiesDescriptorTest
 *
 * @package simplesamlphp\saml2
 */
final class EntitiesDescriptorTest extends TestCase
{
    /**
     * @dataProvider provideMetadata
     * @param boolean $shouldPass
     * @param \DOMElement $metadata;
     */
    public function testUnmarshalling(bool $shouldPass, DOMElement $metadata): void
    {
        try {
            EntitiesDescriptor::fromXML($metadata);
            $this->assertTrue($shouldPass);
        } catch (AssertionFailedException $e) {
            $this->assertFalse($shouldPass);
        }
    }


    /**
     * @return array
     */
    public static function provideMetadata(): array
    {
        return [
            'eduGAIN' => [
                true,
                DOMDocumentFactory::fromFile('/tmp/metadata/edugain.xml')->documentElement,
            ],
        ];
    }
}
