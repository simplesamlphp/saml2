<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DateTimeImmutable;
use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Exception\Protocol\RequestVersionTooHighException;
use SimpleSAML\SAML2\Exception\Protocol\RequestVersionTooLowException;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\XML\saml\Conditions;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\saml\Subject;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XMLSecurity\XML\ds\Signature;

use function array_pop;
use function strval;

/**
 * Class for SAML 2 authentication request messages.
 *
 * @package simplesamlphp/saml2
 */
class AuthnRequest extends AbstractRequest
{
    /**
     * Constructor for SAML 2 AuthnRequest
     *
     * @param \DateTimeImmutable $issueInstant
     * @param \SimpleSAML\SAML2\XML\samlp\RequestedAuthnContext|null $requestedAuthnContext
     * @param \SimpleSAML\SAML2\XML\saml\Subject|null $subject
     * @param \SimpleSAML\SAML2\XML\samlp\NameIDPolicy|null $nameIdPolicy
     * @param \SimpleSAML\SAML2\XML\saml\Conditions|null $conditions
     * @param bool|null $forceAuthn
     * @param bool|null $isPassive
     * @param string|null $assertionConsumerServiceURL
     * @param int|null $assertionConsumerServiceIndex
     * @param string|null $protocolBinding
     * @param int|null $attributeConsumingServiceIndex
     * @param string|null $providerName
     * @param \SimpleSAML\SAML2\XML\saml\Issuer|null $issuer
     * @param string|null $id
     * @param string $version
     * @param string|null $destination
     * @param string|null $consent
     * @param \SimpleSAML\SAML2\XML\samlp\Extensions|null $extensions
     * @param \SimpleSAML\SAML2\XML\samlp\Scoping|null $scoping
     * @throws \Exception
     */
    final public function __construct(
        DateTimeImmutable $issueInstant,
        protected ?RequestedAuthnContext $requestedAuthnContext = null,
        protected ?Subject $subject = null,
        protected ?NameIDPolicy $nameIdPolicy = null,
        protected ?Conditions $conditions = null,
        protected ?bool $forceAuthn = null,
        protected ?bool $isPassive = null,
        protected ?string $assertionConsumerServiceURL = null,
        protected ?int $assertionConsumerServiceIndex = null,
        protected ?string $protocolBinding = null,
        protected ?int $attributeConsumingServiceIndex = null,
        protected ?string $providerName = null,
        ?Issuer $issuer = null,
        ?string $id = null,
        string $version = '2.0',
        ?string $destination = null,
        ?string $consent = null,
        ?Extensions $extensions = null,
        protected ?Scoping $scoping = null,
    ) {
        Assert::nullOrNotWhitespaceOnly($providerName);
        Assert::oneOf(
            null,
            [$assertionConsumerServiceURL, $assertionConsumerServiceIndex],
            'The AssertionConsumerServiceURL and AssertionConsumerServiceIndex are mutually exclusive;'
            . ' please specify one or the other.',
            ProtocolViolationException::class,
        );
        Assert::oneOf(
            null,
            [$protocolBinding, $assertionConsumerServiceIndex],
            'The ProtocolBinding and AssertionConsumerServiceIndex are mutually exclusive;'
            . ' please specify one or the other.',
            ProtocolViolationException::class,
        );
        Assert::nullOrValidURL($assertionConsumerServiceURL);
        Assert::nullOrValidURI($protocolBinding); // Covers the empty string
        Assert::nullOrRange($attributeConsumingServiceIndex, 0, 65535);
        Assert::nullOrRange($assertionConsumerServiceIndex, 0, 65535);

        parent::__construct($issuer, $id, $version, $issueInstant, $destination, $consent, $extensions);
    }


    /**
     * @return \SimpleSAML\SAML2\XML\saml\Subject|null
     */
    public function getSubject(): ?Subject
    {
        return $this->subject;
    }


    /**
     * @return \SimpleSAML\SAML2\XML\samlp\Scoping|null
     */
    public function getScoping(): ?Scoping
    {
        return $this->scoping;
    }


    /**
     * @return \SimpleSAML\SAML2\XML\saml\Conditions|null
     */
    public function getConditions(): ?Conditions
    {
        return $this->conditions;
    }


    /**
     * Retrieve the NameIdPolicy.
     *
     * @see \SimpleSAML\SAML2\AuthnRequest::setNameIdPolicy()
     * @return \SimpleSAML\SAML2\XML\samlp\NameIDPolicy|null The NameIdPolicy.
     */
    public function getNameIdPolicy(): ?NameIDPolicy
    {
        return $this->nameIdPolicy;
    }


    /**
     * Retrieve the value of the ForceAuthn attribute.
     *
     * @return bool|null The ForceAuthn attribute.
     */
    public function getForceAuthn(): ?bool
    {
        return $this->forceAuthn;
    }


    /**
     * Retrieve the value of the ProviderName attribute.
     *
     * @return string|null The ProviderName attribute.
     */
    public function getProviderName(): ?string
    {
        return $this->providerName;
    }


    /**
     * Retrieve the value of the IsPassive attribute.
     *
     * @return bool|null The IsPassive attribute.
     */
    public function getIsPassive(): ?bool
    {
        return $this->isPassive;
    }


    /**
     * Retrieve the value of the AssertionConsumerServiceURL attribute.
     *
     * @return string|null The AssertionConsumerServiceURL attribute.
     */
    public function getAssertionConsumerServiceURL(): ?string
    {
        return $this->assertionConsumerServiceURL;
    }


    /**
     * Retrieve the value of the ProtocolBinding attribute.
     *
     * @return string|null The ProtocolBinding attribute.
     */
    public function getProtocolBinding(): ?string
    {
        return $this->protocolBinding;
    }


    /**
     * Retrieve the value of the AttributeConsumingServiceIndex attribute.
     *
     * @return int|null The AttributeConsumingServiceIndex attribute.
     */
    public function getAttributeConsumingServiceIndex(): ?int
    {
        return $this->attributeConsumingServiceIndex;
    }


    /**
     * Retrieve the value of the AssertionConsumerServiceIndex attribute.
     *
     * @return int|null The AssertionConsumerServiceIndex attribute.
     */
    public function getAssertionConsumerServiceIndex(): ?int
    {
        return $this->assertionConsumerServiceIndex;
    }


    /**
     * Retrieve the RequestedAuthnContext.
     *
     * @return \SimpleSAML\SAML2\XML\samlp\RequestedAuthnContext|null The RequestedAuthnContext.
     */
    public function getRequestedAuthnContext(): ?RequestedAuthnContext
    {
        return $this->requestedAuthnContext;
    }


    /**
     * Convert XML into an AuthnRequest
     *
     * @param \DOMElement $xml The XML element we should load
     * @return static
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingAttributeException
     *   if the supplied element is missing one of the mandatory attributes
     * @throws \SimpleSAML\XML\Exception\TooManyElementsException
     *   if too many child-elements of a type are specified
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'AuthnRequest', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, AuthnRequest::NS, InvalidDOMElementException::class);

        $version = self::getAttribute($xml, 'Version');
        Assert::true(version_compare('2.0', $version, '<='), RequestVersionTooLowException::class);
        Assert::true(version_compare('2.0', $version, '>='), RequestVersionTooHighException::class);

        $id = self::getAttribute($xml, 'ID');
        Assert::validNCName($id); // Covers the empty string

        $issueInstant = self::getAttribute($xml, 'IssueInstant');
        // Strip sub-seconds - See paragraph 1.3.3 of SAML core specifications
        $issueInstant = preg_replace('/([.][0-9]+Z)$/', 'Z', $issueInstant, 1);

        Assert::validDateTimeZulu($issueInstant, ProtocolViolationException::class);
        $issueInstant = new DateTimeImmutable($issueInstant);

        $attributeConsumingServiceIndex = self::getOptionalIntegerAttribute(
            $xml,
            'AttributeConsumingServiceIndex',
            null,
        );
        $assertionConsumerServiceIndex = self::getOptionalIntegerAttribute(
            $xml,
            'AssertionConsumerServiceIndex',
            null,
        );

        $conditions = Conditions::getChildrenOfClass($xml);
        Assert::maxCount(
            $conditions,
            1,
            'Only one <saml:Conditions> element is allowed.',
            TooManyElementsException::class,
        );

        $nameIdPolicy = NameIDPolicy::getChildrenOfClass($xml);
        Assert::maxCount(
            $nameIdPolicy,
            1,
            'Only one <samlp:NameIDPolicy> element is allowed.',
            TooManyElementsException::class,
        );

        $subject = Subject::getChildrenOfClass($xml);
        Assert::maxCount($subject, 1, 'Only one <saml:Subject> element is allowed.', TooManyElementsException::class);

        $issuer = Issuer::getChildrenOfClass($xml);
        Assert::maxCount($issuer, 1, 'Only one <saml:Issuer> element is allowed.', TooManyElementsException::class);

        $requestedAuthnContext = RequestedAuthnContext::getChildrenOfClass($xml);
        Assert::maxCount(
            $requestedAuthnContext,
            1,
            'Only one <samlp:RequestedAuthnContext> element is allowed.',
            TooManyElementsException::class,
        );

        $extensions = Extensions::getChildrenOfClass($xml);
        Assert::maxCount(
            $extensions,
            1,
            'Only one <samlp:Extensions> element is allowed.',
            TooManyElementsException::class,
        );

        $signature = Signature::getChildrenOfClass($xml);
        Assert::maxCount(
            $signature,
            1,
            'Only one <ds:Signature> element is allowed.',
            TooManyElementsException::class,
        );

        $scoping = Scoping::getChildrenOfClass($xml);
        Assert::maxCount($scoping, 1, 'Only one <samlp:Scoping> element is allowed.', TooManyElementsException::class);

        $request = new static(
            $issueInstant,
            array_pop($requestedAuthnContext),
            array_pop($subject),
            array_pop($nameIdPolicy),
            array_pop($conditions),
            self::getOptionalBooleanAttribute($xml, 'ForceAuthn', null),
            self::getOptionalBooleanAttribute($xml, 'IsPassive', null),
            self::getOptionalAttribute($xml, 'AssertionConsumerServiceURL', null),
            $assertionConsumerServiceIndex,
            self::getOptionalAttribute($xml, 'ProtocolBinding', null),
            $attributeConsumingServiceIndex,
            self::getOptionalAttribute($xml, 'ProviderName', null),
            array_pop($issuer),
            $id,
            $version,
            self::getOptionalAttribute($xml, 'Destination', null),
            self::getOptionalAttribute($xml, 'Consent', null),
            array_pop($extensions),
            array_pop($scoping),
        );

        if (!empty($signature)) {
            $request->setSignature($signature[0]);
            $request->messageContainedSignatureUponConstruction = true;
            $request->setXML($xml);
        }

        return $request;
    }


    /**
     * Convert this message to an unsigned XML document.
     * This method does not sign the resulting XML document.
     *
     * @return \DOMElement The root element of the DOM tree
     */
    protected function toUnsignedXML(?DOMElement $parent = null): DOMElement
    {
        $e = parent::toUnsignedXML($parent);

        if ($this->getForceAuthn() === true) {
            $e->setAttribute('ForceAuthn', 'true');
        }

        if ($this->getProviderName() !== null) {
            $e->setAttribute('ProviderName', $this->getProviderName());
        }

        if ($this->getIsPassive() === true) {
            $e->setAttribute('IsPassive', 'true');
        }

        if ($this->getAssertionConsumerServiceIndex() !== null) {
            $e->setAttribute('AssertionConsumerServiceIndex', strval($this->getAssertionConsumerServiceIndex()));
        } else {
            if ($this->getAssertionConsumerServiceURL() !== null) {
                $e->setAttribute('AssertionConsumerServiceURL', $this->getAssertionConsumerServiceURL());
            }
            if ($this->getProtocolBinding() !== null) {
                $e->setAttribute('ProtocolBinding', $this->getProtocolBinding());
            }
        }

        if ($this->getAttributeConsumingServiceIndex() !== null) {
            $e->setAttribute('AttributeConsumingServiceIndex', strval($this->getAttributeConsumingServiceIndex()));
        }

        $this->getSubject()?->toXML($e);

        $nameIdPolicy = $this->getNameIdPolicy();
        if ($nameIdPolicy !== null && !$nameIdPolicy->isEmptyElement()) {
            $nameIdPolicy->toXML($e);
        }

        $conditions = $this->getConditions();
        if ($conditions !== null && !$conditions->isEmptyElement()) {
            $conditions->toXML($e);
        }

        $this->getRequestedAuthnContext()?->toXML($e);

        $scoping = $this->getScoping();
        if ($scoping !== null && !$scoping->isEmptyElement()) {
            $scoping->toXML($e);
        }

        return $e;
    }
}
