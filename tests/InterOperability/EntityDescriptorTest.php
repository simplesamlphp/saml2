<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2;

use DOMElement;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\XML\md\EntityDescriptor;
use SimpleSAML\XML\DOMDocumentFactory;

/**
 * Class \SimpleSAML\SAML2\EntityDescriptorTest
 *
 * @package simplesamlphp\saml2
 */
final class EntityDescriptorTest extends TestCase
{
    /**
     * @param boolean $shouldPass
     * @param \DOMElement $metadata;
     */
    #[DataProvider('provideMetadata')]
    public function testUnmarshalling(bool $shouldPass, DOMElement $metadata): void
    {
        try {
            EntityDescriptor::fromXML($metadata);
            $this->assertTrue($shouldPass);
        } catch (AssertionFailedException $e) {
            fwrite(STDERR, $e->getFile() . '(' . strval($e->getLine()) . '):' . $e->getMessage());
            fwrite(STDERR, $e->getTraceAsString());
            $this->assertFalse($shouldPass);
        }
    }


    /**
     * @return array
     */
    public static function provideMetadata(): array
    {
        return [
            // Known bug: Microsoft doensn't produce a schema-valid XML
            // This was reported to them in 2022: TrackingID#2210040050001949
            'MicrosoftOnline' => [
                true,
                DOMDocumentFactory::fromFile('/tmp/metadata/microsoftonline.xml')->documentElement,
            ],
        ];
    }
}
