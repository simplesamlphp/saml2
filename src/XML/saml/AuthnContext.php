<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\saml;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\XML\saml\AuthnContextClassRef;
use SimpleSAML\SAML2\XML\saml\AuthnContextDecl;
use SimpleSAML\SAML2\XML\saml\AuthnContextDeclRef;
use SimpleSAML\XML\Constants as C;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XMLSchema\Exception\InvalidDOMElementException;
use SimpleSAML\XMLSchema\Exception\TooManyElementsException;

use function array_pop;
use function is_null;

/**
 * Class representing SAML2 AuthnContext
 *
 * @package simplesamlphp/saml2
 */
final class AuthnContext extends AbstractSamlElement implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;


    /**
     * Initialize an AuthnContext.
     *
     * @param \SimpleSAML\SAML2\XML\saml\AuthnContextClassRef|null $authnContextClassRef
     * @param \SimpleSAML\SAML2\XML\saml\AuthnContextDecl|null $authnContextDecl
     * @param \SimpleSAML\SAML2\XML\saml\AuthnContextDeclRef|null $authnContextDeclRef
     * @param \SimpleSAML\SAML2\XML\saml\AuthenticatingAuthority[] $authenticatingAuthorities
     * @throws \SimpleSAML\Assert\AssertionFailedException
     */
    public function __construct(
        protected ?AuthnContextClassRef $authnContextClassRef,
        protected ?AuthnContextDecl $authnContextDecl,
        protected ?AuthnContextDeclRef $authnContextDeclRef,
        protected array $authenticatingAuthorities = [],
    ) {
        if (is_null($authnContextClassRef)) {
            Assert::false(
                is_null($authnContextDecl) && is_null($authnContextDeclRef),
                'You need either an AuthnContextDecl or an AuthnContextDeclRef',
            );
        }
        Assert::oneOf(
            null,
            [$authnContextDecl, $authnContextDeclRef],
            'Can only have one of AuthnContextDecl/AuthnContextDeclRef',
        );

        Assert::maxCount($authenticatingAuthorities, C::UNBOUNDED_LIMIT);
        Assert::allIsInstanceOf($authenticatingAuthorities, AuthenticatingAuthority::class);
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
     * Collect the value of the authnContextDeclRef-property
     *
     * @return \SimpleSAML\SAML2\XML\saml\AuthnContextDeclRef|null
     */
    public function getAuthnContextDeclRef(): ?AuthnContextDeclRef
    {
        return $this->authnContextDeclRef;
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
     * Collect the value of the authenticatingAuthorities-property
     *
     * @return \SimpleSAML\SAML2\XML\saml\AuthenticatingAuthority[]
     */
    public function getAuthenticatingAuthorities(): array
    {
        return $this->authenticatingAuthorities;
    }


    /**
     * Convert XML into a AuthnContext
     *
     * @param \DOMElement $xml The XML element we should load
     * @return static
     *
     * @throws \SimpleSAML\XMLSchema\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'AuthnContext', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, AuthnContext::NS, InvalidDOMElementException::class);

        $authnContextClassRef = AuthnContextClassRef::getChildrenOfClass($xml);
        Assert::maxCount(
            $authnContextClassRef,
            1,
            "More than one <saml:AuthnContextClassRef> found",
            TooManyElementsException::class,
        );

        $authnContextDeclRef = AuthnContextDeclRef::getChildrenOfClass($xml);
        Assert::maxCount(
            $authnContextDeclRef,
            1,
            "More than one <saml:AuthnContextDeclRef> found",
            TooManyElementsException::class,
        );

        $authnContextDecl = AuthnContextDecl::getChildrenOfClass($xml);
        Assert::maxCount(
            $authnContextDecl,
            1,
            "More than one <saml:AuthnContextDecl> found",
            TooManyElementsException::class,
        );

        $authorities = AuthenticatingAuthority::getChildrenOfClass($xml);

        return new static(
            array_pop($authnContextClassRef),
            array_pop($authnContextDecl),
            array_pop($authnContextDeclRef),
            $authorities,
        );
    }


    /**
     * Convert this AuthContextDeclRef to XML.
     *
     * @param \DOMElement|null $parent The element we should append this AuthnContextDeclRef to.
     * @return \DOMElement
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $e = $this->instantiateParentElement($parent);

        $this->getAuthnContextClassRef()?->toXML($e);
        $this->getAuthnContextDecl()?->toXML($e);
        $this->getAuthnContextDeclRef()?->toXML($e);

        foreach ($this->getAuthenticatingAuthorities() as $authority) {
            $authority->toXML($e);
        }

        return $e;
    }
}
