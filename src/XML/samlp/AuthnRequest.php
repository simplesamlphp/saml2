<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DOMElement;
use SimpleSAML\SAML2\Assert\Assert;
use SimpleSAML\SAML2\Exception\Protocol\RequestVersionTooHighException;
use SimpleSAML\SAML2\Exception\Protocol\RequestVersionTooLowException;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\Type\SAMLAnyURIValue;
use SimpleSAML\SAML2\Type\SAMLDateTimeValue;
use SimpleSAML\SAML2\Type\SAMLStringValue;
use SimpleSAML\SAML2\XML\saml\Conditions;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\saml\Subject;
use SimpleSAML\XML\SchemaValidatableElementInterface;
use SimpleSAML\XML\SchemaValidatableElementTrait;
use SimpleSAML\XMLSchema\Exception\InvalidDOMElementException;
use SimpleSAML\XMLSchema\Exception\TooManyElementsException;
use SimpleSAML\XMLSchema\Type\BooleanValue;
use SimpleSAML\XMLSchema\Type\IDValue;
use SimpleSAML\XMLSchema\Type\UnsignedShortValue;
use SimpleSAML\XMLSecurity\XML\ds\Signature;

use function array_pop;
use function strval;

/**
 * Class for SAML 2 authentication request messages.
 *
 * @package simplesamlphp/saml2
 */
class AuthnRequest extends AbstractRequest implements SchemaValidatableElementInterface
{
    use SchemaValidatableElementTrait;


    /**
     * Constructor for SAML 2 AuthnRequest
     *
     * @param \SimpleSAML\XMLSchema\Type\IDValue $id
     * @param \SimpleSAML\SAML2\Type\SAMLDateTimeValue $issueInstant
     * @param \SimpleSAML\SAML2\XML\samlp\RequestedAuthnContext|null $requestedAuthnContext
     * @param \SimpleSAML\SAML2\XML\saml\Subject|null $subject
     * @param \SimpleSAML\SAML2\XML\samlp\NameIDPolicy|null $nameIdPolicy
     * @param \SimpleSAML\SAML2\XML\saml\Conditions|null $conditions
     * @param \SimpleSAML\XMLSchema\Type\BooleanValue|null $forceAuthn
     * @param \SimpleSAML\XMLSchema\Type\BooleanValue|null $isPassive
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue|null $assertionConsumerServiceURL
     * @param \SimpleSAML\XMLSchema\Type\UnsignedShortValue|null $assertionConsumerServiceIndex
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue|null $protocolBinding
     * @param \SimpleSAML\XMLSchema\Type\UnsignedShortValue|null $attributeConsumingServiceIndex
     * @param \SimpleSAML\SAML2\Type\SAMLStringValue|null $providerName
     * @param \SimpleSAML\SAML2\XML\saml\Issuer|null $issuer
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue|null $destination
     * @param \SimpleSAML\SAML2\Type\SAMLAnyURIValue|null $consent
     * @param \SimpleSAML\SAML2\XML\samlp\Extensions|null $extensions
     * @param \SimpleSAML\SAML2\XML\samlp\Scoping|null $scoping
     * @throws \Exception
     */
    final public function __construct(
        IDValue $id,
        SAMLDateTimeValue $issueInstant,
        protected ?RequestedAuthnContext $requestedAuthnContext = null,
        protected ?Subject $subject = null,
        protected ?NameIDPolicy $nameIdPolicy = null,
        protected ?Conditions $conditions = null,
        protected ?BooleanValue $forceAuthn = null,
        protected ?BooleanValue $isPassive = null,
        protected ?SAMLAnyURIValue $assertionConsumerServiceURL = null,
        protected ?UnsignedShortValue $assertionConsumerServiceIndex = null,
        protected ?SAMLAnyURIValue $protocolBinding = null,
        protected ?UnsignedShortValue $attributeConsumingServiceIndex = null,
        protected ?SAMLStringValue $providerName = null,
        ?Issuer $issuer = null,
        ?SAMLAnyURIValue $destination = null,
        ?SAMLAnyURIValue $consent = null,
        ?Extensions $extensions = null,
        protected ?Scoping $scoping = null,
    ) {
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

        parent::__construct($id, $issuer, $issueInstant, $destination, $consent, $extensions);
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
     * @return \SimpleSAML\SAML2\XML\samlp\NameIDPolicy|null The NameIdPolicy.
     */
    public function getNameIdPolicy(): ?NameIDPolicy
    {
        return $this->nameIdPolicy;
    }


    /**
     * Retrieve the value of the ForceAuthn attribute.
     *
     * @return \SimpleSAML\XMLSchema\Type\BooleanValue|null The ForceAuthn attribute.
     */
    public function getForceAuthn(): ?BooleanValue
    {
        return $this->forceAuthn;
    }


    /**
     * Retrieve the value of the ProviderName attribute.
     *
     * @return \SimpleSAML\SAML2\Type\SAMLStringValue|null The ProviderName attribute.
     */
    public function getProviderName(): ?SAMLStringValue
    {
        return $this->providerName;
    }


    /**
     * Retrieve the value of the IsPassive attribute.
     *
     * @return \SimpleSAML\XMLSchema\Type\BooleanValue|null The IsPassive attribute.
     */
    public function getIsPassive(): ?BooleanValue
    {
        return $this->isPassive;
    }


    /**
     * Retrieve the value of the AssertionConsumerServiceURL attribute.
     *
     * @return \SimpleSAML\SAML2\Type\SAMLAnyURIValue|null The AssertionConsumerServiceURL attribute.
     */
    public function getAssertionConsumerServiceURL(): ?SAMLAnyURIValue
    {
        return $this->assertionConsumerServiceURL;
    }


    /**
     * Retrieve the value of the ProtocolBinding attribute.
     *
     * @return \SimpleSAML\SAML2\Type\SAMLAnyURIValue|null The ProtocolBinding attribute.
     */
    public function getProtocolBinding(): ?SAMLAnyURIValue
    {
        return $this->protocolBinding;
    }


    /**
     * Retrieve the value of the AttributeConsumingServiceIndex attribute.
     *
     * @return \SimpleSAML\XMLSchema\Type\UnsignedShortValue|null The AttributeConsumingServiceIndex attribute.
     */
    public function getAttributeConsumingServiceIndex(): ?UnsignedShortValue
    {
        return $this->attributeConsumingServiceIndex;
    }


    /**
     * Retrieve the value of the AssertionConsumerServiceIndex attribute.
     *
     * @return \SimpleSAML\XMLSchema\Type\UnsignedShortValue|null The AssertionConsumerServiceIndex attribute.
     */
    public function getAssertionConsumerServiceIndex(): ?UnsignedShortValue
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
     * @throws \SimpleSAML\XMLSchema\Exception\InvalidDOMElementException
     *   if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XMLSchema\Exception\MissingAttributeException
     *   if the supplied element is missing one of the mandatory attributes
     * @throws \SimpleSAML\XMLSchema\Exception\TooManyElementsException
     *   if too many child-elements of a type are specified
     */
    public static function fromXML(DOMElement $xml): static
    {
        Assert::same($xml->localName, 'AuthnRequest', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, AuthnRequest::NS, InvalidDOMElementException::class);

        $version = self::getAttribute($xml, 'Version', SAMLStringValue::class);
        Assert::true(version_compare('2.0', strval($version), '<='), RequestVersionTooLowException::class);
        Assert::true(version_compare('2.0', strval($version), '>='), RequestVersionTooHighException::class);

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
            self::getAttribute($xml, 'ID', IDValue::class),
            self::getAttribute($xml, 'IssueInstant', SAMLDateTimeValue::class),
            array_pop($requestedAuthnContext),
            array_pop($subject),
            array_pop($nameIdPolicy),
            array_pop($conditions),
            self::getOptionalAttribute($xml, 'ForceAuthn', BooleanValue::class, null),
            self::getOptionalAttribute($xml, 'IsPassive', BooleanValue::class, null),
            self::getOptionalAttribute($xml, 'AssertionConsumerServiceURL', SAMLAnyURIValue::class, null),
            self::getOptionalAttribute($xml, 'AssertionConsumerServiceIndex', UnsignedShortValue::class, null),
            self::getOptionalAttribute($xml, 'ProtocolBinding', SAMLAnyURIValue::class, null),
            self::getOptionalAttribute($xml, 'AttributeConsumingServiceIndex', UnsignedShortValue::class, null),
            self::getOptionalAttribute($xml, 'ProviderName', SAMLStringValue::class, null),
            array_pop($issuer),
            self::getOptionalAttribute($xml, 'Destination', SAMLAnyURIValue::class, null),
            self::getOptionalAttribute($xml, 'Consent', SAMLAnyURIValue::class, null),
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
            $e->setAttribute('ForceAuthn', strval($this->getForceAuthn()));
        }

        if ($this->getProviderName() !== null) {
            $e->setAttribute('ProviderName', strval($this->getProviderName()));
        }

        if ($this->getIsPassive() === true) {
            $e->setAttribute('IsPassive', strval($this->getIsPassive()));
        }

        if ($this->getAssertionConsumerServiceIndex() !== null) {
            $e->setAttribute('AssertionConsumerServiceIndex', strval($this->getAssertionConsumerServiceIndex()));
        } else {
            if ($this->getAssertionConsumerServiceURL() !== null) {
                $e->setAttribute('AssertionConsumerServiceURL', strval($this->getAssertionConsumerServiceURL()));
            }
            if ($this->getProtocolBinding() !== null) {
                $e->setAttribute('ProtocolBinding', strval($this->getProtocolBinding()));
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
