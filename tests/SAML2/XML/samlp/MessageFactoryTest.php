<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\samlp;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\saml\Attribute;
use SimpleSAML\SAML2\XML\samlp\ArtifactResolve;
use SimpleSAML\SAML2\XML\samlp\ArtifactResponse;
use SimpleSAML\SAML2\XML\samlp\AttributeQuery;
use SimpleSAML\SAML2\XML\samlp\AuthnRequest;
use SimpleSAML\SAML2\XML\samlp\LogoutRequest;
use SimpleSAML\SAML2\XML\samlp\LogoutResponse;
use SimpleSAML\SAML2\XML\samlp\MessageFactory;
use SimpleSAML\SAML2\XML\samlp\Response;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\InvalidDOMElementException;

use function dirname;

/**
 * @covers \SimpleSAML\SAML2\XML\samlp\MessageFactory
 * @package simplesamlphp/saml2
 */
#[CoversClass(MessageFactory::class)]
final class MessageFactoryTest extends TestCase
{
    /**
     * @return array
     */
    public static function provideMessages(): array
    {
        $base = dirname(__FILE__, 4) . '/resources/xml/';

        return [
            'AttributeQuery' => [$base . 'samlp_AttributeQuery.xml', AttributeQuery::class],
            'AuthnRequest' => [$base . 'samlp_AuthnRequest.xml', AuthnRequest::class],
            'LogoutResponse' => [$base . 'samlp_LogoutResponse.xml', LogoutResponse::class],
            'LogoutRequest' => [$base . 'samlp_LogoutRequest.xml', LogoutRequest::class],
            'Response' => [$base . 'samlp_Response.xml', Response::class],
            'ArtifactResponse' => [$base . 'samlp_ArtifactResponse.xml', ArtifactResponse::class],
            'ArtifactResolve' => [$base . 'samlp_ArtifactResolve.xml', ArtifactResolve::class],
        ];
    }


    /**
     * @param string $file
     * @param class-string $class
     */
    #[DataProvider('provideMessages')]
    public function testMessageFactory(string $file, string $class): void
    {
        $document = DOMDocumentFactory::fromFile($file);
        $result = MessageFactory::fromXML($document->documentElement);

        $this->assertEquals($class, get_class($result));
    }


    /**
     */
    public function testMessageFactoryWithOtherNamespace(): void
    {
        $document = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/saml_Attribute.xml',
        );

        $this->expectException(InvalidDOMElementException::class);
        $this->expectExceptionMessage('Unknown namespace of SAML message: \'' . Attribute::NS . '\'');

        MessageFactory::fromXML($document->documentElement);
    }


    /**
     */
    public function testMessageFactoryWithSameNamespaceButNotMessage(): void
    {
        $document = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/samlp_Status.xml',
        );

        $this->expectException(InvalidDOMElementException::class);
        $this->expectExceptionMessage('Unknown SAML message: \'Status\'');

        MessageFactory::fromXML($document->documentElement);
    }
}
