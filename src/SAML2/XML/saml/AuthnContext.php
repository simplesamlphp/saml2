<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Constants;
use SimpleSAML\SAML2\XML\saml\AuthnContextClassRef;
use SimpleSAML\SAML2\XML\saml\AuthnContextDecl;
use SimpleSAML\SAML2\XML\saml\AuthnContextDeclRef;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XML\Utils as XMLUtils;

/**
 * Class representing SAML2 AuthnContext
 *
 * @package simplesamlphp/saml2
 */
final class AuthnContext extends AbstractSamlElement
{
    /** @var \SimpleSAML\SAML2\XML\saml\AuthnContextClassRef|null */
    protected ?AuthnContextClassRef $authnContextClassRef = null;

    /** @var \SimpleSAML\SAML2\XML\saml\AuthnContextDeclRef|null */
    protected ?AuthnContextDeclRef $authnContextDeclRef = null;

    /** @var \SimpleSAML\SAML2\XML\saml\AuthnContextDecl|null */
    protected ?AuthnContextDecl $authnContextDecl = null;

    /** @var string[] */
    protected array $authenticatingAuthorities = [];


    /**
     * Initialize an AuthnContext.
     *
     * @param \SimpleSAML\SAML2\XML\saml\AuthnContextClassRef|null $authnContextClassRef
     * @param \SimpleSAML\SAML2\XML\saml\AuthnContextDecl|null $authnContextDecl
     * @param \SimpleSAML\SAML2\XML\saml\AuthnContextDeclRef|null $authnContextDeclRef
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
     * @return \SimpleSAML\SAML2\XML\saml\AuthnContextClassRef|null
     */
    public function getAuthnContextClassRef(): ?AuthnContextClassRef
    {
        return $this->authnContextClassRef;
    }


    /**
     * Set the value of the authnContextClassRef-property
     *
     * @param \SimpleSAML\SAML2\XML\saml\AuthnContextClassRef|null $authnContextClassRef
     */
    private function setAuthnContextClassRef(?AuthnContextClassRef $authnContextClassRef): void
    {
        $this->authnContextClassRef = $authnContextClassRef;
    }


    /**
     * Collect the value of the authnContextDeclRef-property
     *
     * @return \SimpleSAML\SAML2\XML\saml\AuthnContextDeclRef|null
     */
    public function getAuthnContextDeclRef(): ?AuthnContextDeclRef
    {
        return $this->authnContextDeclRef;
    }


    /**
     * Set the value of the authnContextDeclRef-property
     *
     * @param \SimpleSAML\SAML2\XML\saml\AuthnContextDeclRef|null $authnContextDeclRef
     */
    private function setAuthnContextDeclRef(?AuthnContextDeclRef $authnContextDeclRef): void
    {
        $this->authnContextDeclRef = $authnContextDeclRef;
    }


    /**
     * Collect the value of the authnContextDecl-property
     *
     * @return \SimpleSAML\SAML2\XML\saml\AuthnContextDecl|null
     */
    public function getAuthnContextDecl(): ?AuthnContextDecl
    {
        return $this->authnContextDecl;
    }


    /**
     * Set the value of the authnContextDecl-property
     *
     * @param \SimpleSAML\SAML2\XML\saml\AuthnContextDecl|null $authnContextDecl
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
     * @return \SimpleSAML\SAML2\XML\saml\AuthnContext
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'AuthnContext', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, AuthnContext::NS, InvalidDOMElementException::class);

        $authnContextClassRef = AuthnContextClassRef::getChildrenOfClass($xml);
        Assert::maxCount(
            $authnContextClassRef,
            1,
            "More than one <saml:AuthnContextClassRef> found",
            TooManyElementsException::class
        );

        $authnContextDeclRef = AuthnContextDeclRef::getChildrenOfClass($xml);
        Assert::maxCount(
            $authnContextDeclRef,
            1,
            "More than one <saml:AuthnContextDeclRef> found",
            TooManyElementsException::class
        );

        $authnContextDecl = AuthnContextDecl::getChildrenOfClass($xml);
        Assert::maxCount(
            $authnContextDecl,
            1,
            "More than one <saml:AuthnContextDecl> found",
            TooManyElementsException::class
        );

        $authorities = XMLUtils::extractStrings($xml, AbstractSamlElement::NS, 'AuthenticatingAuthority');

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

        XMLUtils::addStrings($e, AbstractSamlElement::NS, 'saml:AuthenticatingAuthority', false, $this->authenticatingAuthorities);

        return $e;
    }
}
