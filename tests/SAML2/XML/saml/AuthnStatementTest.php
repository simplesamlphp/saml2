<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Type\DomainValue;
use SimpleSAML\SAML2\Type\SAMLDateTimeValue;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\saml\AbstractSamlElement;
use SimpleSAML\SAML2\XML\saml\AuthenticatingAuthority;
use SimpleSAML\SAML2\XML\saml\AuthnContext;
use SimpleSAML\SAML2\XML\saml\AuthnContextClassRef;
use SimpleSAML\SAML2\XML\saml\AuthnStatement;
use SimpleSAML\SAML2\XML\saml\SubjectLocality;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XMLSchema\Exception\MissingAttributeException;
use SimpleSAML\XMLSchema\Exception\MissingElementException;
use SimpleSAML\XMLSchema\Exception\TooManyElementsException;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\saml\AuthnStatementTest
 *
 * @package simplesamlphp/saml2
 */
#[Group('saml')]
#[CoversClass(AuthnStatement::class)]
#[CoversClass(AbstractSamlElement::class)]
final class AuthnStatementTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$testedClass = AuthnStatement::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/saml_AuthnStatement.xml',
        );
    }


    // marshalling


    /**
     */
    public function testMarshalling(): void
    {
        $authnStatement = new AuthnStatement(
            new AuthnContext(
                AuthnContextClassRef::fromString(C::AC_PASSWORD_PROTECTED_TRANSPORT),
                null,
                null,
                [
                    AuthenticatingAuthority::fromString('https://idp.example.com/SAML2'),
                ],
            ),
            SAMLDateTimeValue::fromString('2020-03-23T23:37:24Z'),
            SAMLDateTimeValue::fromString('2020-03-23T23:37:24Z'),
            SAMLStringValue::fromString('123'),
            new SubjectLocality(
                SAMLStringValue::fromString('1.1.1.1'),
                DomainValue::fromString('idp.example.org'),
            ),
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($authnStatement),
        );
    }


    /**
     */
    public function testMarshallingElementOrdering(): void
    {
        $authnStatement = new AuthnStatement(
            new AuthnContext(
                AuthnContextClassRef::fromString(C::AC_PASSWORD_PROTECTED_TRANSPORT),
                null,
                null,
                [
                    AuthenticatingAuthority::fromString('https://idp.example.com/SAML2'),
                ],
            ),
            SAMLDateTimeValue::fromString('2020-03-23T23:37:24Z'),
            SAMLDateTimeValue::fromString('2020-03-23T23:37:24Z'),
            SAMLStringValue::fromString('123'),
            new SubjectLocality(
                SAMLStringValue::fromString('1.1.1.1'),
                DomainValue::fromString('idp.example.org'),
            ),
        );

        // Marshall it to a \DOMElement
        $authnStatementElement = $authnStatement->toXML();

        // Test for a SubjectLocality
        $xpCache = XPath::getXPath($authnStatementElement);
        $authnStatementElements = XPath::xpQuery($authnStatementElement, './saml_assertion:SubjectLocality', $xpCache);
        $this->assertCount(1, $authnStatementElements);

        // Test ordering of AuthnStatement contents
        /** @var \DOMElement[] $authnStatementElements */
        $authnStatementElements = XPath::xpQuery(
            $authnStatementElement,
            './saml_assertion:SubjectLocality/following-sibling::*',
            $xpCache,
        );
        $this->assertCount(1, $authnStatementElements);
        $this->assertEquals('saml:AuthnContext', $authnStatementElements[0]->tagName);
    }


    // unmarshalling


    /**
     */
    public function testUnmarshallingWithoutAuthnContextThrowsException(): void
    {
        $samlNamespace = C::NS_SAML;
        $document = DOMDocumentFactory::fromString(
            <<<XML
<saml:AuthnStatement xmlns:saml="{$samlNamespace}"
    AuthnInstant="2020-03-23T23:37:24Z"
    SessionIndex="123"
    SessionNotOnOrAfter="2020-03-23T23:37:24Z">
</saml:AuthnStatement>
XML
            ,
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
        $document = clone self::$xmlRepresentation->documentElement;
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
