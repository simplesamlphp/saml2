<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

use function dirname;
use function sprintf;

/**
 * Tests for element registry.
 *
 * @package simplesamlphp/saml2
 */
#[Group('utils')]
final class ElementRegistryTest extends TestCase
{
    /**
     * Test that the class-name can be resolved and it's localname matches.
     */
    public function testElementRegistry(): void
    {
        $elementRegistry = dirname(__FILE__, 4) . '/classes/element.registry.php';
        $namespaces = include($elementRegistry);

        foreach ($namespaces as $namespaceURI => $elements) {
            foreach ($elements as $localName => $fqdn) {
                $this->assertTrue(class_exists($fqdn), sprintf('Class \'%s\' could not be found.', $fqdn));
                $this->assertEquals($fqdn::getLocalName(), $localName);
                $this->assertEquals($fqdn::getNamespaceURI(), $namespaceURI);
            }
        }
    }
}
