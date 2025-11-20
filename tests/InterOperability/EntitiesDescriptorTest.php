<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2;

use DOMElement;
use Exception;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\md\EntityDescriptor;
use SimpleSAML\XML\DOMDocumentFactory;

use function array_merge;
use function sprintf;

/**
 * Class \SimpleSAML\SAML2\EntitiesDescriptorTest
 *
 * @package simplesamlphp\saml2
 */
final class EntitiesDescriptorTest extends TestCase
{
    private int $failures;


    /**
     * @param \DOMElement $metadata;
     */
    #[DataProvider('provideMetadata')]
    public function testUnmarshalling(DOMElement $metadata): void
    {
        $this->failures = 0;

        $this->parseMetadata($metadata);

        $this->assertEquals(0, $this->failures);
    }


    /**
     *
     */
    private function parseMetadata(DOMElement $metadata): void
    {
        $xpCache = XPath::getXPath($metadata);
        if ($metadata->localName === 'EntitiesDescriptor') {
            // Test for an EntitiesDescriptor or EntityDescriptor
            $entityDescriptorElements = XPath::xpQuery($metadata, './saml_metadata:EntityDescriptor', $xpCache);
            $entitiesDescriptorElements = XPath::xpQuery($metadata, './saml_metadata:EntitiesDescriptor', $xpCache);
            $descriptors = array_merge($entityDescriptorElements, $entitiesDescriptorElements);

            foreach ($descriptors as $descriptor) {
                /** @var \DOMElement $descriptor */
                $this->parseMetadata($descriptor);
            }
        } elseif ($metadata->localName === 'EntityDescriptor') {
            /** @var \DOMAttr[] $entityID */
            $entityID = XPath::xpQuery($metadata, './@entityID', $xpCache);

            try {
                EntityDescriptor::fromXML($metadata);
            } catch (Exception $e) {
                $this->failures = $this->failures + 1;

                echo "EntityID: " . $entityID[0]->value . PHP_EOL;
                echo "          " . $e->getMessage() . PHP_EOL;
                ob_flush();
            }
        } else {
            throw new Exception(sprintf(
                "Shouldn't happen. Element %s:%s was found.",
                $metadata->namespaceURI,
                $metadata->localName,
            ));
        }
    }


    /**
     * @return array
     */
    public static function provideMetadata(): array
    {
        return [
            'eduGAIN' => [
                DOMDocumentFactory::fromFile('/tmp/metadata/edugain.xml')->documentElement,
            ],
            'GRNET' => [
                DOMDocumentFactory::fromFile('/tmp/metadata/grnet.xml')->documentElement,
            ],
            'EduID' => [
                DOMDocumentFactory::fromFile('/tmp/metadata/eduid.xml')->documentElement,
            ],
        ];
    }
}
