<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\samlp;

use DateTimeImmutable;
use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\samlp\ArtifactResolve;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XMLSecurity\TestUtils\SignedElementTestTrait;

use function dirname;
use function strval;

/**
 * @covers \SimpleSAML\SAML2\XML\samlp\ArtifactResolve
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractRequest
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractMessage
 * @covers \SimpleSAML\SAML2\XML\samlp\AbstractSamlpElement
 * @package simplesamlphp/saml2
 */
final class ArtifactResolveTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;
    use SignedElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$schemaFile = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-protocol-2.0.xsd';

        self::$testedClass = ArtifactResolve::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/samlp_ArtifactResolve.xml',
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $issuer = new Issuer('https://ServiceProvider.com/SAML');
        $artifact = 'AAQAADWNEw5VT47wcO4zX/iEzMmFQvGknDfws2ZtqSGdkNSbsW1cmVR0bzU=';

        $artifactResolve = new ArtifactResolve(
            $artifact,
            new DateTimeImmutable('2004-01-21T19:00:49Z'),
            $issuer,
            '_6c3a4f8b9c2d',
            '2.0',
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($artifactResolve),
        );
    }
}
