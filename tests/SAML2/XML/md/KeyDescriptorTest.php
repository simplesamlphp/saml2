<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use PHPUnit\Framework\TestCase;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\XML\ds\AbstractDsElement;

/**
 * A set of tests for the md:KeyDescriptor element
 *
 * @package simplesamlphp/saml2
 */
final class KeyDescriptorTest extends TestCase
{
    protected $document;


    protected function setUp(): void
    {
        $mdns = Constants::NS_MD;
        $dsns = AbstractDsElement::NS;
        $this->document = DOMDocumentFactory::fromString(<<<XML
<md:KeyDescriptor xmlns:md="{$mdns}" use="signing">
  <ds:KeyInfo xmlns:ds="{$dsns}">
    <ds:KeyName>IdentityProvider.com SSO Key</ds:KeyName>
  </ds:KeyInfo>
  <md:EncryptionMethod Algorithm="http://www.w3.org/2001/04/xmlenc#rsa-1_5"/>
</md:KeyDescriptor>
XML
        );
    }


    // @todo: add tests for marshalling / unmarshalling


    /**
     * Test serialization / unserialization
     */
    public function testSerialization(): void
    {
        $this->assertEquals(
            $this->document->saveXML($this->document->documentElement),
            strval(unserialize(serialize(KeyDescriptor::fromXML($this->document->documentElement))))
        );
    }
}
