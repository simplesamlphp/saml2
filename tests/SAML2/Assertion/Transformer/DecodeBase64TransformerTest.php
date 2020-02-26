<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use PHPUnit\Framework\TestCase;
use SAML2\Assertion\Exception\InvalidAssertionException;
use SAML2\Assertion\ProcessorBuilder;
use SAML2\Configuration\Destination;
use SAML2\Configuration\IdentityProvider;
use SAML2\Configuration\ServiceProvider;
use SAML2\DOMDocumentFactory;
use SAML2\Signature\Validator;
use SAML2\Utilities\ArrayCollection;
use SAML2\XML\samlp\Response;

/**
 * Tests for decoding base64 encoded attributes.
 *
 * @package simplesamlphp/saml2
 */
final class DecodeBase64TransformerTest extends TestCase
{
    /** @var \DOMDocument */
    protected $document;

    /**
     * @var \SAML2\Assertion\Processor
     */
    protected $assertionProcessor;

    /**
     * @var \SAML2\Configuration\IdentityProvider
     */
    protected $identityProviderConfiguration;

    /**
     * @var \SAML2\Configuration\ServiceProvider
     */
    protected $serviceProviderConfiguration;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
   
    /**
     * @var \SAML2\Response\Validation\Validator
     */
    protected $validator;

    /**
     * @var \SAML2\Configuration\Destination
     */
    protected $destination;

    /**
     * @var \SAML2\xml\samlp\Response
     */
    protected $response;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $spentity = 'urn:mace:feide.no:services:no.feide.moodle';

        $this->logger = new \Psr\Log\NullLogger();
        $this->validator = new Validator($this->logger);
        $this->destination = new Destination($spentity);
        $this->response = new Response();

        $this->identityProviderConfiguration
            = new IdentityProvider(['base64EncodedAttributes' => true]);
        $this->serviceProviderConfiguration
            = new ServiceProvider(['entityId' => $spentity]);

        $this->assertionProcessor = ProcessorBuilder::build(
            $this->logger,
            $this->validator,
            $this->destination,
            $this->identityProviderConfiguration,
            $this->serviceProviderConfiguration,
            $this->response
        );

        $this->document = DOMDocumentFactory::fromString(<<<XML
    <saml:Assertion xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                    xmlns:xs="http://www.w3.org/2001/XMLSchema"
                    xmlns:saml="urn:oasis:names:tc:SAML:2.0:assertion"
                    ID="_45e42090d8cbbfa52d5a394b01049fc2221e274182"
                    Version="2.0"
                    IssueInstant="2020-02-26T12:04:42Z"
                    >
        <saml:Issuer>urn:thki:sid:idp2</saml:Issuer>
        <saml:Conditions/>
        <saml:AttributeStatement>
            <saml:Attribute Name="urn:mace:dir:attribute-def:eduPersonAffiliation"
                            NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri"
                            >
                <saml:AttributeValue xsi:type="xs:string">bWVtYmVy</saml:AttributeValue>
                <saml:AttributeValue xsi:type="xs:string">YWZmaWxpYXRl</saml:AttributeValue>
            </saml:Attribute>
            <saml:Attribute Name="urn:mace:dir:attribute-def:eduPersonAffiliationAlternative"
                            NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri"
                            >
                <saml:AttributeValue xsi:type="xs:string">bWVtYmVy_YWZmaWxpYXRl</saml:AttributeValue>
            </saml:Attribute>
            <saml:Attribute Name="urn:mace:dir:attribute-def:eduPersonPrincipalName"
                            NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri"
                            >
                <saml:AttributeValue xsi:type="xs:string">YXNqZW1lbm91QGxvZWtpLnR2</saml:AttributeValue>
            </saml:Attribute>
            <saml:Attribute Name="urn:mace:dir:attribute-def:displayName"
                            NameFormat="urn:oasis:names:tc:SAML:2.0:attrname-format:uri"
                            >
                <saml:AttributeValue xsi:type="xs:string">SWVtYW5kIEFuZGVycw==</saml:AttributeValue>
            </saml:Attribute>
        </saml:AttributeStatement>
    </saml:Assertion>
XML
        );
    }

    /**
     * Verifies that we can create decoded AttributeValues.
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @return void
     */
    public function testBasicDecoding(): void
    {
        $this->assertTrue($this->identityProviderConfiguration->hasBase64EncodedAttributes());

        $assertion = new Assertion($this->document->firstChild);

        $processed = $this->assertionProcessor->process($assertion);
        $attributes = $processed->getAttributes();

        $displayName = $attributes['urn:mace:dir:attribute-def:displayName'];
        $this->assertCount(1, $displayName);
        $this->assertEquals("Iemand Anders", $displayName[0]);

        $eduPersonPrincipalName = $attributes['urn:mace:dir:attribute-def:eduPersonPrincipalName'];
        $this->assertCount(1, $eduPersonPrincipalName);
        $this->assertEquals("asjemenou@loeki.tv", $eduPersonPrincipalName[0]);
    }

    /**
     * Multi-valued attributes are also decoded correctly.
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @return void
     */
    public function testRegularMultivaluedDecoding(): void
    {
        $assertion = new Assertion($this->document->firstChild);

        $processed = $this->assertionProcessor->process($assertion);
        $attributes = $processed->getAttributes();

        $affiliation = $attributes['urn:mace:dir:attribute-def:eduPersonAffiliation'];
        $this->assertCount(2, $affiliation);
        $this->assertEquals("member", $affiliation[0]);
        $this->assertEquals("affiliate", $affiliation[1]);
    }

    /**
     * Multi-valued concatenated attributes are also decoded correctly.
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @return void
     */
    public function testConcatenatedMultivaluedDecoding(): void
    {
        $assertion = new Assertion($this->document->firstChild);

        $processed = $this->assertionProcessor->process($assertion);
        $attributes = $processed->getAttributes();

        $affiliation = $attributes['urn:mace:dir:attribute-def:eduPersonAffiliationAlternative'];
        $this->assertCount(2, $affiliation);
        $this->assertEquals("member", $affiliation[0]);
        $this->assertEquals("affiliate", $affiliation[1]);
    }

    /**
     * Check that attribute values with characters not in the base64 alphabet
     * throw an exception.
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @return void
     */
    public function testInvalidBase64(): void
    {
        $assertion = new Assertion($this->document->firstChild);

        $parsedAttributes = $assertion->getAttributes();
        $parsedAttributes['urn:mace:dir:attribute-def:displayName'][0] =
            strtr($parsedAttributes['urn:mace:dir:attribute-def:displayName'][0], 'k', '.');
        $assertion->setAttributes($parsedAttributes);

        $this->expectException(InvalidAssertionException::class);
        $this->expectExceptionMessage('Invalid base64 encoded attribute value "SWVtYW5.IEFuZGVycw=="');
        $processed = $this->assertionProcessor->process($assertion);
    }

    /**
     * If we disable base64encoded attributes nothing happens
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @return void
     */
    public function testBase64encodedNotSet(): void
    {
        $noBase64IdP = new IdentityProvider([]);

        $this->assertFalse((bool)$noBase64IdP->hasBase64EncodedAttributes());
        $noBase64AssertionProcessor = ProcessorBuilder::build(
            $this->logger,
            $this->validator,
            $this->destination,
            $noBase64IdP,
            $this->serviceProviderConfiguration,
            $this->response
        );

        $assertion = new Assertion($this->document->firstChild);

        $processed = $noBase64AssertionProcessor->process($assertion);
        $attributes = $processed->getAttributes();

        $displayName = $attributes['urn:mace:dir:attribute-def:displayName'];
        $this->assertCount(1, $displayName);
        $this->assertEquals("SWVtYW5kIEFuZGVycw==", $displayName[0]);
    }

    /**
     * Run the decoder through processAssertions.
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @return void
     */
    public function testDecodingProcessAssertions(): void
    {
        $assertion = new Assertion($this->document->firstChild);
        $assertions = new ArrayCollection([$assertion]);

        $processed = $this->assertionProcessor->processAssertions($assertions);
        $this->assertCount(1, $processed);
        $attributes = $processed->getOnlyElement()->getAttributes();

        $displayName = $attributes['urn:mace:dir:attribute-def:displayName'];
        $this->assertCount(1, $displayName);
        $this->assertEquals("Iemand Anders", $displayName[0]);

        $eduPersonPrincipalName = $attributes['urn:mace:dir:attribute-def:eduPersonPrincipalName'];
        $this->assertCount(1, $eduPersonPrincipalName);
        $this->assertEquals("asjemenou@loeki.tv", $eduPersonPrincipalName[0]);
    }

}
