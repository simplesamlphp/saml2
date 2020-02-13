<?php

declare(strict_types=1);

namespace SAML2\XML\saml;

use DOMElement;
use SAML2\Constants;
use SAML2\DOMDocumentFactory;
use SAML2\Utils;
use SAML2\XML\saml\AuthenticatingAuthority;
use SAML2\XML\saml\AuthnContextClassRef;
use SAML2\XML\saml\AuthnContextDecl;
use SAML2\XML\saml\AuthnContextDeclRef;
use Webmozart\Assert\Assert;

/**
 * Class representing SAML2 AuthnContext
 *
 * @author Tim van Dijen, <tvdijen@gmail.com>
 * @package SimpleSAMLphp
 */
final class AuthnContext extends AbstractSamlElement
{
    /** @var \SAML2\XML\saml\AuthnContextClassRef|null */
    protected $authnContextClassRef = null;

    /** @var \SAML2\XML\saml\AuthnContextDeclRef|null */
    protected $authnContextDeclRef = null;

    /** @var \SAML2\XML\saml\AuthnContextDecl|null */
    protected $authnContextDecl = null;

    /** @var \SAML2\XML\saml\AuthenticatingAuthority[]|null */
    protected $authenticatingAuthorities = null;


    /**
     * Initialize an AuthnContext.
     *
     * @param \SAML2\XML\saml\AuthnContextClassRef|null $authnContextClassRef
     * @param \SAML2\XML\saml\AuthnContextDecl|null $authnContextDecl
     * @param \SAML2\XML\saml\AuthnContextDeclRef|null $authnContextDeclRef
     * @param \SAML2\XML\saml\AuthenticatingAuthority[]|null $authenticatingAuthorities
     */
    public function __construct(
        ?AuthnContextClassRef $authnContextClassRef,
        ?AuthnContextDecl $authnContextDecl,
        ?AuthnContextDeclRef $authnContextDeclRef,
        ?array $authenticatingAuthorities
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
     * @return \SAML2\XML\saml\AuthenticatingAuthority[]|null
     */
    public function getAuthenticatingAuthorities(): ?array
    {
        return $this->authenticatingAuthorities;
    }


    /**
     * Set the value of the authenticatingAuthorities-property
     *
     * @param \SAML2\XML\saml\AuthenticatingAuthority[]|null $authenticatingAuthorities
     * @return void
     */
    private function setAuthenticatingAuthorities(?array $authenticatingAuthorities): void
    {
        if (!is_null($authenticatingAuthorities)) {
            Assert::allIsInstanceof($authenticatingAuthorities, AuthenticatingAuthority::class);
        }

        $this->authenticatingAuthorities = $authenticatingAuthorities;
    }


    /**
     * Convert XML into a AuthnContext
     *
     * @param \DOMElement $xml The XML element we should load
     * @return \SAML2\XML\saml\AuthnContext
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'AuthnContext');
        Assert::same($xml->namespaceURI, AuthnContext::NS);

        /** @var \DOMElement[] $authnContextClassRef */
        $authnContextClassRef = Utils::xpQuery($xml, './saml_assertion:AuthnContextClassRef');
        Assert::maxCount($authnContextClassRef, 1);

        /** @var \DOMElement[] $authnContextDeclRef */
        $authnContextDeclRef = Utils::xpQuery($xml, './saml_assertion:AuthnContextDeclRef');
        Assert::maxCount($authnContextDeclRef, 1);

        /** @var \DOMElement[] $authnContextDecl */
        $authnContextDecl = Utils::xpQuery($xml, './saml_assertion:AuthnContextDecl');
        Assert::maxCount($authnContextDecl, 1);

        /** @var \DOMElement[] $authenticatingAuthorities */
        $authenticatingAuthorities = Utils::xpQuery($xml, './saml_assertion:AuthenticatingAuthority');

        return new self(
            empty($authnContextClassRef) ? null : AuthnContextClassRef::fromXML($authnContextClassRef[0]),
            empty($authnContextDecl) ? null : AuthnContextDecl::fromXML($authnContextDecl[0]),
            empty($authnContextDeclRef) ? null : AuthnContextDeclRef::fromXML($authnContextDeclRef[0]),
            array_map([AuthenticatingAuthority::class, 'fromXML'], $authenticatingAuthorities) ?: null
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

        if (!empty($this->authenticatingAuthorities)) {
            foreach ($this->authenticatingAuthorities as $authority) {
                $authority->toXML($e);
            }
        }

        return $e;
    }
}
