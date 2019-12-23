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
class AuthnContextDeclRef extends \SAML2\XML\AbstractConvertable
{
    /** @var string */
    private $declRef;


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
    public function setDeclRef(string $declRef): void
    {
        $declRef = trim($declRef);
        Assert::stringNotEmpty($declRef);
        $this->declRef = $declRef;
    }


    /**
     * Convert XML into a AuthnContextDeclRef
     *
     * @param \DOMElement $xml The XML element we should load
     * @return self
     */
    public static function fromXML(DOMElement $xml): object
    {
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
        Assert::stringNotEmpty($this->declRef, 'Cannot convert AuthnContextDeclRef to XML without a DeclRef set');

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
