<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\samlp;

use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\saml\Attribute;
use SimpleSAML\SAML2\XML\samlp\AttributeQuery;
use SimpleSAML\SAML2\XML\samlp\ArtifactResolve;
use SimpleSAML\SAML2\XML\samlp\ArtifactResponse;
use SimpleSAML\SAML2\XML\samlp\AuthnRequest;
use SimpleSAML\SAML2\XML\samlp\LogoutRequest;
use SimpleSAML\SAML2\XML\samlp\LogoutResponse;
use SimpleSAML\SAML2\XML\samlp\MessageFactory;
use SimpleSAML\SAML2\XML\samlp\Response;
use SimpleSAML\SAML2\XML\samlp\Status;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\InvalidDOMElementException;

use function dirname;
use function strval;

/**
 * @covers \SimpleSAML\SAML2\XML\samlp\MessageFactory
 * @package simplesamlphp/saml2
 */
final class MessageFactoryTest extends TestCase
{
    /**
     * @return array
     */
    public function provideMessages(): array
    {
        $base = dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/';

        return [
           [$base . 'samlp_AttributeQuery.xml', AttributeQuery::class],
           [$base . 'samlp_AuthnRequest.xml', AuthnRequest::class],
           [$base . 'samlp_LogoutResponse.xml', LogoutResponse::class],
           [$base . 'samlp_LogoutRequest.xml', LogoutRequest::class],
           [$base . 'samlp_Response.xml', Response::class],
           [$base . 'samlp_ArtifactResponse.xml', ArtifactResponse::class],
           [$base . 'samlp_ArtifactResolve.xml', ArtifactResolve::class],
        ];
    }


    /**
     * @param string $file
     * @param class-string class
     * @dataProvider provideMessages
     */
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
        $document = DOMDocumentFactory::fromFile(dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/saml_Attribute.xml');

        $this->expectException(InvalidDOMElementException::class);
        $this->expectExceptionMessage('Unknown namespace of SAML message: \'' . Attribute::NS . '\'');

        MessageFactory::fromXML($document->documentElement);
    }


    /**
     */
    public function testMessageFactoryWithSameNamespaceButNotMessage(): void
    {
        $document = DOMDocumentFactory::fromFile(dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/samlp_Status.xml');

        $this->expectException(InvalidDOMElementException::class);
        $this->expectExceptionMessage('Unknown SAML message: \'Status\'');

        MessageFactory::fromXML($document->documentElement);
    }
}
