<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use DOMDocument;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\saml\AuthenticatingAuthority;
use SimpleSAML\SAML2\XML\saml\AuthnContext;
use SimpleSAML\SAML2\XML\saml\AuthnContextClassRef;
use SimpleSAML\SAML2\XML\saml\AuthnStatement;
use SimpleSAML\SAML2\XML\saml\SubjectLocality;
use SimpleSAML\Test\XML\SchemaValidationTestTrait;
use SimpleSAML\Test\XML\SerializableElementTestTrait;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XML\Exception\MissingElementException;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XML\Utils as XMLUtils;

use function dirname;
use function strval;

/**
 * Class \SAML2\XML\saml\AuthnStatementTest
 *
 * @covers \SimpleSAML\SAML2\XML\saml\AuthnStatement
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractSamlElement
 * @package simplesamlphp/saml2
 */
final class AuthnStatementTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;

    /**
     */
    protected function setUp(): void
    {
        $this->schema = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/schemas/saml-schema-assertion-2.0.xsd';

        $this->testedClass = AuthnStatement::class;

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(dirname(dirname(dirname(__FILE__)))) . '/resources/xml/saml_AuthnStatement.xml'
        );
    }


    // marshalling


    /**
     */
    public function testMarshalling(): void
    {
        $authnStatement = new AuthnStatement(
            new AuthnContext(
                new AuthnContextClassRef(C::AC_PASSWORD_PROTECTED_TRANSPORT),
                null,
                null,
                [new AuthenticatingAuthority('https://idp.example.com/SAML2')]
            ),
            XMLUtils::xsDateTimeToTimestamp('2020-03-23T23:37:24Z'),
            XMLUtils::xsDateTimeToTimestamp('2020-03-23T23:37:24Z'),
            '123',
            new SubjectLocality('1.1.1.1', 'idp.example.org')
        );

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($authnStatement)
        );
    }


    /**
     */
    public function testMarshallingElementOrdering(): void
    {
        $authnStatement = new AuthnStatement(
            new AuthnContext(
                new AuthnContextClassRef(C::AC_PASSWORD_PROTECTED_TRANSPORT),
                null,
                null,
                [new AuthenticatingAuthority('https://idp.example.com/SAML2')]
            ),
            XMLUtils::xsDateTimeToTimestamp('2020-03-23T23:37:24Z'),
            XMLUtils::xsDateTimeToTimestamp('2020-03-23T23:37:24Z'),
            '123',
            new SubjectLocality('1.1.1.1', 'idp.example.org')
        );

        // Marshall it to a \DOMElement
        $authnStatementElement = $authnStatement->toXML();

        // Test for a SubjectLocality
        $xpCache = XPath::getXPath($authnStatementElement);
        $authnStatementElements = XPath::xpQuery($authnStatementElement, './saml_assertion:SubjectLocality', $xpCache);
        $this->assertCount(1, $authnStatementElements);

        // Test ordering of AuthnStatement contents
        /** @psalm-var \DOMElement[] $authnStatementElements */
        $authnStatementElements = XPath::xpQuery(
            $authnStatementElement,
            './saml_assertion:SubjectLocality/following-sibling::*',
            $xpCache
        );
        $this->assertCount(1, $authnStatementElements);
        $this->assertEquals('saml:AuthnContext', $authnStatementElements[0]->tagName);
    }


    // unmarshalling


    /**
     */
    public function testUnmarshalling(): void
    {
        $authnStatement = AuthnStatement::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($authnStatement)
        );
    }


    /**
     */
    public function testUnmarshallingWithoutAuthnContextThrowsException(): void
    {
        $samlNamespace = C::NS_SAML;
        $document = DOMDocumentFactory::fromString(<<<XML
<saml:AuthnStatement xmlns:saml="{$samlNamespace}"
    AuthnInstant="2020-03-23T23:37:24Z"
    SessionIndex="123"
    SessionNotOnOrAfter="2020-03-23T23:37:24Z">
</saml:AuthnStatement>
XML
        );

        $this->expectException(MissingElementException::class);
        $this->expectExceptionMessage('Missing <saml:AuthnContext> in <saml:AuthnStatement>');

        AuthnStatement::fromXML($document->documentElement);
    }


    /**
     * @return void
     */
    public function testUnmarshallingMissingAuthnInstantThrowsException(): void
    {
        $document = $this->xmlRepresentation->documentElement;
        $document->removeAttribute('AuthnInstant');

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage("Missing 'AuthnInstant' attribute on saml:AuthnStatement.");

        AuthnStatement::fromXML($document);
    }


    /**
     * More than one AuthnContext inside AuthnStatement will throw Exception.
     */
    public function testMoreThanOneAuthnContextThrowsException(): void
    {
        $samlNamespace = C::NS_SAML;
        $xml = <<<XML
<saml:AuthnStatement xmlns:saml="{$samlNamespace}" AuthnInstant="2010-03-05T13:34:28Z">
  <saml:AuthnContext>
    <saml:AuthnContextClassRef>urn:test:someAuthnContext</saml:AuthnContextClassRef>
    <saml:AuthenticatingAuthority>urn:test:someIdP1</saml:AuthenticatingAuthority>
  </saml:AuthnContext>
  <saml:AuthnContext>
    <saml:AuthnContextClassRef>urn:test:someAuthnContext</saml:AuthnContextClassRef>
    <saml:AuthenticatingAuthority>urn:test:someIdP2</saml:AuthenticatingAuthority>
  </saml:AuthnContext>
</saml:AuthnStatement>
XML;
        $document = DOMDocumentFactory::fromString($xml);

        $this->expectException(TooManyElementsException::class);
        $this->expectExceptionMessage("More than one <saml:AuthnContext> in <saml:AuthnStatement>");

        AuthnStatement::fromXML($document->documentElement);
    }


    /**
     * Missing AuthnContext inside AuthnStatement will throw Exception.
     */
    public function testMissingAuthnContextThrowsException(): void
    {
        $samlNamespace = C::NS_SAML;
        $xml = <<<XML
<saml:AuthnStatement xmlns:saml="{$samlNamespace}" AuthnInstant="2010-03-05T13:34:28Z">
</saml:AuthnStatement>
XML;
        $document = DOMDocumentFactory::fromString($xml);

        $this->expectException(MissingElementException::class);
        $this->expectExceptionMessage("Missing <saml:AuthnContext> in <saml:AuthnStatement>");

        AuthnStatement::fromXML($document->documentElement);
    }
}
