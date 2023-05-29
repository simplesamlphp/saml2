<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\shibmd;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\shibmd\KeyAuthority;
use SimpleSAML\XML\Attribute as XMLAttribute;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XML\Utils as XMLUtils;
use SimpleSAML\XMLSecurity\XML\ds\KeyInfo;
use SimpleSAML\XMLSecurity\XML\ds\X509Certificate;
use SimpleSAML\XMLSecurity\XML\ds\X509Data;

use function array_pop;
use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\shibmd\KeyAuthority
 *
 * @covers \SimpleSAML\SAML2\XML\shibmd\KeyAuthority
 * @covers \SimpleSAML\SAML2\XML\shibmd\AbstractShibmdElement
 * @package simplesamlphp/saml2
 */
final class KeyAuthorityTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$schemaFile = dirname(__FILE__, 5) . '/resources/schemas/sstc-saml-metadata-shibmd-v1.0.xsd';

        self::$testedClass = KeyAuthority::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/shibmd_KeyAuthority.xml',
        );
    }


    /**
     * Marshalling a KeyAuthority.
     */
    public function testMarshalling(): void
    {
        $keys = [
            new KeyInfo(
                [
                    new X509Data(
                        [
                            new X509Certificate('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'),
                        ],
                    ),
                ],
                'abc123',
            ),
            new KeyInfo(
                [
                    new X509Data(
                        [
                            new X509Certificate('MIIFhDCCA2ygAwIBAgIEAJimITANBgkqhkiG9w0BAQsFADBiMQswCQYDVQQGEwJOTDEeMBwGA1UECgwVU3RhYXQgZGVyIE5lZGVybGFuZGVuMTMwMQYDVQQDDCpTdGFhdCBkZXIgTmVkZXJsYW5kZW4gUHJpdmF0ZSBSb290IENBIC0gRzEwHhcNMTMxMTE0MTM0ODU1WhcNMjgxMTEzMjMwMDAwWjBiMQswCQYDVQQGEwJOTDEeMBwGA1UECgwVU3RhYXQgZGVyIE5lZGVybGFuZGVuMTMwMQYDVQQDDCpTdGFhdCBkZXIgTmVkZXJsYW5kZW4gUHJpdmF0ZSBSb290IENBIC0gRzEwggIiMA0GCSqGSIb3DQEBAQUAA4ICDwAwggIKAoICAQDaIMh56ynwnEhE7Ey54KpX5j1XDoxbHDCgXctute55RjmG2hy6fuq++q/dCSsj38Pi/KYn/PN13EF05k39IRvakb0AQNVyHifNKXfta6Tzi5QcM4BK09DB4Ckb6TdZTNUtWyEcAtRblYaVSQ4Xr5QODNqu2FGQucraVXqCIx81azlOE2JbZli9AZKn94pP57A11dUYhxMsh70YosJEKVB8Ue4ROksHhb/nnOISG+2y9FD5M8u8jYhp00TGZGVu5z0IFgtqX0i8GmrH0ub9AWjf/iU4MWjGVRSq0cwUHEeKRj/UD9a8xIEn9TxIfYj+6+s4tn9dW/4PV5jc6iGJx6ExTPfOR7VHpxS4XujrZb5Ba/+oj/ONdOfR0JSm2itCytbtjQBBL0oocIIqaqOna1cufHkcn9VleF7Zvz/8njQIpAU4J4nJ4pE5pQ3k4ORAGNnq5R9hAqqUQGDlo3Uj8PBou0nPzQ7JNgGkN+my/lGr4rceUNK/8CoGnYFUH+UyFtJkvlLlEkb688/IdNdGgY+vuXCAB6xfKlJjAGChFUBb6swbNeNctVEdUj7Weg4Jt5gXu78C2mjs9x5lcHOgMO4ZmvYJ3Ejp4k3nNa45HOIVkYrfQrrBHzBhR0BuReAagurcbtUjJFd7BtufGVLfU3CUn1l6u3/9eG4DGH6pq+dSKQIDAQABo0IwQDAPBgNVHRMBAf8EBTADAQH/MA4GA1UdDwEB/wQEAwIBBjAdBgNVHQ4EFgQUKv25Kx76w4SHBtuB/4aXdQ3rAYswDQYJKoZIhvcNAQELBQADggIBAEvpmXMOOKdQwUPysrsdIkGJUFF+dvmsJDiOuAqV0A1nNTooL3esvDLEZAWZwKTOwRomnHzeCfS/QxRKTkVX21pfrHf9ufDKykpzjl9uAILTS76FJ6//R0RTIPMrzknQpG2fCLR5DFEbHWU/jWAxGmncfx6HQYl/azHaWbv0dhZOUjPdkGAQ6EPvHcyNU9yMkETdw0X6ioxqzMwkGM893oBrMmtduiqIf3/H6HTXoRKAc+/DXZIq/pAc6eVMa6x43kokluaam9L78yDrlHbGd2VYAr/HZ0TjDZTtI2t2/ySTb7JjC8wL8rSqxYmLpNrnhZzPW87sl2OCFC3re3ZhtJkIHNP85jj1gqewTC7DCW6llZdB3hBzfHWby0EX2RlcwgaMfNBEV5U0IogccdXV+S6zWK4F+yBr0sXUrdbdMFu+g3I9CbXxt0q4eVJtoaun4M2Z+bZMqZvy9FryBdSfhpgmJqwFz2luOhPOVCblCPhLrUeewrvuBXoZQWt1ZjuHfwJZ1dgjszVEqwY9S0SdqCg2ZlL9s3vDIrrd3wLWrcHLQMd9gwsppNv9c7JfIJdlcZLTmF9EuL6eCvVVrqBVqLHjva4erqYol6K/jbSfUtRCy8IlFU7LYu1KLehZKYvj3vekj3Cn08Aqljr/Q8Pw+OfUZTzKg4PVDQVfFqKtyosv'),
                        ],
                    ),
                ],
                'def456',
            ),
        ];
        $attr1 = new XMLAttribute('urn:test:something', 'test', 'attr1', 'testval1');
        $keyAuthority = new KeyAuthority($keys, 2, [$attr1]);

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($keyAuthority),
        );
    }


    /**
     * Unmarshalling a KeyAuthority.
     */
    public function testUnmarshalling(): void
    {
        $keyAuthority = KeyAuthority::fromXML(self::$xmlRepresentation->documentElement);
        $this->assertEquals(2, $keyAuthority->getVerifyDepth());

        $keys = $keyAuthority->getKeys();
        $this->assertCount(2, $keys);

        $this->assertEquals('abc123', $keys[0]->getId());
        $this->assertEquals('def456', $keys[1]->getId());

        $attributes = $keyAuthority->getAttributesNS();
        $this->assertCount(1, $attributes);

        $attribute = array_pop($attributes);
        $this->assertEquals(
            [
                'namespaceURI' => 'urn:test:something',
                'namespacePrefix' => 'test',
                'attrName' => 'attr1',
                'attrValue' => 'testval1',
            ],
            $attribute->toArray(),
        );
    }
}
