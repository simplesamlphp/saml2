<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use DOMElement;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use Webmozart\Assert\Assert;

/**
 * Class representing SAML2 AuthnContextDeclRef
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package SimpleSAMLphp
 */
final class AuthnContextDeclRef extends \SAML2\XML\AbstractConvertable
{
    /** @var string */
    protected $declRef;


    /**
     * Initialize an AuthnContextDeclRef.
     *
     * @param string $declRef
     */
    public function __construct(string $declRef)
    {
        $this->setDeclRef($declRef);
    }


    /**
     * Collect the value of the declRef-property
     *
     * @return string
     *
     * @throws \InvalidArgumentException if assertions are false
     */
    public function getDeclRef(): string
    {
        return $this->declRef;
    }


    /**
     * Set the value of the declRef-property
     *
     * @param string $name
     * @return void
     */
    private function setDeclRef(string $declRef): void
    {
        $this->declRef = trim($declRef);
    }


    /**
     * Convert XML into a AuthnContextDeclRef
     *
     * @param \DOMElement $xml The XML element we should load
     * @return \SAML2\XML\saml\AuthnContextDeclRef
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'AuthnContextDeclRef');
        Assert::same($xml->namespaceURI, Constants::NS_SAML);

        return new self($xml->textContent);
    }


    /**
     * Convert this AuthContextDeclRef to XML.
     *
     * @param \DOMElement|null $parent The element we should append this AuthnContextDeclRef to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        if ($parent === null) {
            $doc = DOMDocumentFactory::create();
            $e = $doc->createElementNS(Constants::NS_SAML, 'saml:AuthnContextDeclRef');
            $doc->appendChild($e);
        } else {
            $e = $parent->ownerDocument->createElementNS(Constants::NS_SAML, 'saml:AuthnContextDeclRef');
            $parent->appendChild($e);
        }

        $e->textContent = $this->declRef;

        return $e;
    }
}
