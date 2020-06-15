<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use DOMElement;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\Exception\InvalidDOMElementException;
use SAML2\Exception\TooManyElementsException;
use SAML2\Utils;
use SAML2\XML\saml\AuthnContextClassRef;
use SAML2\XML\saml\AuthnContextDecl;
use SAML2\XML\saml\AuthnContextDeclRef;
use SimpleSAML\Assert\Assert;

/**
 * Class representing SAML2 AuthnContext
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package simplesamlphp
 */
final class AuthnContext extends AbstractSamlElement
{
    /** @var \SAML2\XML\saml\AuthnContextClassRef|null */
    protected $authnContextClassRef = null;

    /** @var \SAML2\XML\saml\AuthnContextDeclRef|null */
    protected $authnContextDeclRef = null;

    /** @var \SAML2\XML\saml\AuthnContextDecl|null */
    protected $authnContextDecl = null;

    /** @var string[] */
    protected $authenticatingAuthorities = [];


    /**
     * Initialize an AuthnContext.
     *
     * @param \SAML2\XML\saml\AuthnContextClassRef|null $authnContextClassRef
     * @param \SAML2\XML\saml\AuthnContextDecl|null $authnContextDecl
     * @param \SAML2\XML\saml\AuthnContextDeclRef|null $authnContextDeclRef
     * @param string[] $authenticatingAuthorities
     * @throws \SimpleSAML\Assert\AssertionFailedException
     */
    public function __construct(
        ?AuthnContextClassRef $authnContextClassRef,
        ?AuthnContextDecl $authnContextDecl,
        ?AuthnContextDeclRef $authnContextDeclRef,
        array $authenticatingAuthorities = []
    ) {
        if (!is_null($authnContextClassRef)) {
            Assert::oneOf(
                null,
                [$authnContextDecl, $authnContextDeclRef],
                'Can only have one of AuthnContextDecl/AuthnContextDeclRef'
            );
        } else {
            Assert::false(
                is_null($authnContextDecl) && is_null($authnContextDeclRef),
                'You need either an AuthnContextDecl or an AuthnContextDeclRef'
            );
        }

        $this->setAuthnContextClassRef($authnContextClassRef);
        $this->setAuthnContextDecl($authnContextDecl);
        $this->setAuthnContextDeclRef($authnContextDeclRef);
        $this->setAuthenticatingAuthorities($authenticatingAuthorities);
    }


    /**
     * Collect the value of the authnContextClassRef-property
     *
     * @return \SAML2\XML\saml\AuthnContextClassRef|null
     */
    public function getAuthnContextClassRef(): ?AuthnContextClassRef
    {
        return $this->authnContextClassRef;
    }


    /**
     * Set the value of the authnContextClassRef-property
     *
     * @param \SAML2\XML\saml\AuthnContextClassRef|null $authnContextClassRef
     * @return void
     */
    private function setAuthnContextClassRef(?AuthnContextClassRef $authnContextClassRef): void
    {
        $this->authnContextClassRef = $authnContextClassRef;
    }


    /**
     * Collect the value of the authnContextDeclRef-property
     *
     * @return \SAML2\XML\saml\AuthnContextDeclRef|null
     */
    public function getAuthnContextDeclRef(): ?AuthnContextDeclRef
    {
        return $this->authnContextDeclRef;
    }


    /**
     * Set the value of the authnContextDeclRef-property
     *
     * @param \SAML2\XML\saml\AuthnContextDeclRef|null $authnContextDeclRef
     * @return void
     */
    private function setAuthnContextDeclRef(?AuthnContextDeclRef $authnContextDeclRef): void
    {
        $this->authnContextDeclRef = $authnContextDeclRef;
    }


    /**
     * Collect the value of the authnContextDecl-property
     *
     * @return \SAML2\XML\saml\AuthnContextDecl|null
     */
    public function getAuthnContextDecl(): ?AuthnContextDecl
    {
        return $this->authnContextDecl;
    }


    /**
     * Set the value of the authnContextDecl-property
     *
     * @param \SAML2\XML\saml\AuthnContextDecl|null $authnContextDecl
     * @return void
     */
    private function setAuthnContextDecl(?AuthnContextDecl $authnContextDecl): void
    {
        $this->authnContextDecl = $authnContextDecl;
    }


    /**
     * Collect the value of the authenticatingAuthorities-property
     *
     * @return string[]
     */
    public function getAuthenticatingAuthorities(): array
    {
        return $this->authenticatingAuthorities;
    }


    /**
     * Set the value of the authenticatingAuthorities-property
     *
     * @param string[] $authenticatingAuthorities
     * @return void
     * @throws \SimpleSAML\Assert\AssertionFailedException
     */
    private function setAuthenticatingAuthorities(array $authenticatingAuthorities): void
    {
        Assert::allStringNotEmpty($authenticatingAuthorities);

        $this->authenticatingAuthorities = $authenticatingAuthorities;
    }


    /**
     * Convert XML into a AuthnContext
     *
     * @param \DOMElement $xml The XML element we should load
     * @return \SAML2\XML\saml\AuthnContext
     *
     * @throws \SAML2\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'AuthnContext', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, AuthnContext::NS, InvalidDOMElementException::class);

        $authnContextClassRef = AuthnContextClassRef::getChildrenOfClass($xml);
        Assert::maxCount($authnContextClassRef, 1, "More than one <saml:AuthnContextClassRef> found", TooManyElementsException::class);

        $authnContextDeclRef = AuthnContextDeclRef::getChildrenOfClass($xml);
        Assert::maxCount($authnContextDeclRef, 1, "More than one <saml:AuthnContextDeclRef> found", TooManyElementsException::class);

        $authnContextDecl = AuthnContextDecl::getChildrenOfClass($xml);
        Assert::maxCount($authnContextDecl, 1, "More than one <saml:AuthnContextDecl> found", TooManyElementsException::class);

        $authorities = Utils::extractStrings($xml, AbstractSamlElement::NS, 'AuthenticatingAuthority');

        return new self(
            array_pop($authnContextClassRef),
            array_pop($authnContextDecl),
            array_pop($authnContextDeclRef),
            $authorities
        );
    }


    /**
     * Convert this AuthContextDeclRef to XML.
     *
     * @param \DOMElement|null $parent The element we should append this AuthnContextDeclRef to.
     * @return \DOMElement
     */
    public function toXML(DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        if (!empty($this->authnContextClassRef)) {
            $this->authnContextClassRef->toXML($e);
        }

        if (!empty($this->authnContextDecl)) {
            $this->authnContextDecl->toXML($e);
        }

        if (!empty($this->authnContextDeclRef)) {
            $this->authnContextDeclRef->toXML($e);
        }

        Utils::addStrings($e, AbstractSamlElement::NS, 'saml:AuthenticatingAuthority', false, $this->authenticatingAuthorities);

        return $e;
    }
}
