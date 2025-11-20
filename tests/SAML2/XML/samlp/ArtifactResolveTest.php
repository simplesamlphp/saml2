<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\samlp;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Type\SAMLDateTimeValue;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\samlp\AbstractMessage;
use SimpleSAML\SAML2\XML\samlp\AbstractRequest;
use SimpleSAML\SAML2\XML\samlp\AbstractSamlpElement;
use SimpleSAML\SAML2\XML\samlp\Artifact;
use SimpleSAML\SAML2\XML\samlp\ArtifactResolve;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XMLSchema\Type\Base64BinaryValue;
use SimpleSAML\XMLSchema\Type\IDValue;
use SimpleSAML\XMLSecurity\TestUtils\SignedElementTestTrait;

use function dirname;
use function strval;

/**
 * @package simplesamlphp/saml2
 */
#[Group('samlp')]
#[CoversClass(ArtifactResolve::class)]
#[CoversClass(AbstractRequest::class)]
#[CoversClass(AbstractMessage::class)]
#[CoversClass(AbstractSamlpElement::class)]
final class ArtifactResolveTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;
    use SignedElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = ArtifactResolve::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/samlp_ArtifactResolve.xml',
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $issuer = new Issuer(
            SAMLStringValue::fromString('https://ServiceProvider.com/SAML'),
        );
        $artifact = Base64BinaryValue::fromString('AAQAADWNEw5VT47wcO4zX/iEzMmFQvGknDfws2ZtqSGdkNSbsW1cmVR0bzU=');

        $artifactResolve = new ArtifactResolve(
            IDValue::fromString('_6c3a4f8b9c2d'),
            new Artifact($artifact),
            SAMLDateTimeValue::fromString('2004-01-21T19:00:49Z'),
            $issuer,
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($artifactResolve),
        );
    }
}
