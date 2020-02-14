<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use DOMElement;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\Utils;
use Webmozart\Assert\Assert;

/**
 * Class representing SAML2 AuthenticatingAuthority
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp/saml2
 */
final class AuthenticatingAuthority extends AbstractSamlElement
{
    /** @var string */
    protected $authority;


    /**
     * Initialize an AuthicatingAuthority.
     *
     * @param string $authority
     */
    public function __construct(string $authority)
    {
        $this->setAuthority($authority);
    }


    /**
     * Collect the value of the authority-property
     *
     * @return string
     */
    public function getAuthority(): string
    {
        return $this->authority;
    }


    /**
     * Set the value of the authority-property
     *
     * @param string $name
     * @return void
     */
    private function setAuthority(string $authority): void
    {
        $this->authority = trim($authority);
    }


    /**
     * Convert XML into a AuthenticatingAuthority
     *
     * @param \DOMElement $xml The XML element we should load
     * @return \SAML2\XML\saml\AuthenticatingAuthority
     * @throws \InvalidArgumentException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'AuthenticatingAuthority');
        Assert::same($xml->namespaceURI, AuthenticatingAuthority::NS);

        return new self($xml->textContent);
    }


    /**
     * Convert this AuthenticatingAuthority to XML.
     *
     * @param \DOMElement|null $parent The element we should append this AuthnContextClassRef to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);
        $e->textContent = $this->authority;

        return $e;
    }
}
