<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\XML\samlp;

use DOMElement;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Exception\InvalidArgumentException;
use SimpleSAML\SAML2\Exception\ProtocolViolationException;
use SimpleSAML\SAML2\XML\saml\Conditions;
use SimpleSAML\SAML2\XML\saml\Issuer;
use SimpleSAML\SAML2\XML\saml\Subject;
use SimpleSAML\SAML2\XML\saml\SubjectConfirmation;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XML\Utils as XMLUtils;
use SimpleSAML\XMLSecurity\XML\ds\Signature;

/**
 * Class for SAML 2 authentication request messages.
 *
 * @package simplesamlphp/saml2
 */
class AuthnRequest extends AbstractRequest
{
    /**
     * @var \SimpleSAML\SAML2\XML\saml\Subject|null
     */
    protected ?Subject $subject = null;

    /**
     * @var \SimpleSAML\SAML2\XML\samlp\Scoping|null
     */
    protected ?Scoping $scoping = null;

    /**
     * The options for what type of name identifier should be returned.
     *
     * @var \SimpleSAML\SAML2\XML\samlp\NameIDPolicy|null
     */
    protected ?NameIDPolicy $nameIdPolicy = null;

    /**
     * Whether the Identity Provider must authenticate the user again.
     *
     * @var bool|null
     */
    protected ?bool $forceAuthn = false;

    /**
     * Optional ProviderID attribute
     *
     * @var string|null
     */
    protected ?string $ProviderName = null;

    /**
     * Set to true if this request is passive.
     *
     * @var bool|null
     */
    protected ?bool $isPassive = false;

    /**
     * The URL of the assertion consumer service where the response should be delivered.
     *
     * @var string|null
     */
    protected ?string $assertionConsumerServiceURL;

    /**
     * What binding should be used when sending the response.
     *
     * @var string|null
     */
    protected ?string $protocolBinding;

    /**
     * The index of the AttributeConsumingService.
     *
     * @var int|null
     */
    protected ?int $attributeConsumingServiceIndex;

    /**
     * The index of the AssertionConsumerService.
     *
     * @var int|null
     */
    protected ?int $assertionConsumerServiceIndex = null;

    /**
     * What authentication context was requested.
     *
     * @var \SimpleSAML\SAML2\XML\samlp\RequestedAuthnContext|null
     */
    protected ?RequestedAuthnContext $requestedAuthnContext;

    /**
     * @var \SimpleSAML\SAML2\XML\saml\Conditions|null
     */
    protected ?Conditions $conditions = null;


    /**
     * Constructor for SAML 2 AuthnRequest
     *
     * @param \SimpleSAML\SAML2\XML\samlp\RequestedAuthnContext $requestedAuthnContext
     * @param \SimpleSAML\SAML2\XML\saml\Subject $subject
     * @param \SimpleSAML\SAML2\XML\samlp\NameIDPolicy $nameIdPolicy
     * @param \SimpleSAML\SAML2\XML\saml\Conditions $conditions
     * @param bool $forceAuthn
     * @param bool $isPassive
     * @param string|null $assertionConsumerServiceUrl
     * @param int|null $assertionConsumerServiceIndex
     * @param string|null $protocolBinding
     * @param int|null $attributeConsumingServiceIndex
     * @param string|null $providerName
     * @param \SimpleSAML\SAML2\XML\saml\Issuer|null $issuer
     * @param string|null $id
     * @param int|null $issueInstant
     * @param string|null $destination
     * @param string|null $consent
     * @param \SimpleSAML\SAML2\XML\samlp\Extensions|null $extensions
     * @param \SimpleSAML\SAML2\XML\samlp\Scoping|null $scoping
     * @throws \Exception
     */
    public function __construct(
        ?RequestedAuthnContext $requestedAuthnContext = null,
        ?Subject $subject = null,
        ?NameIDPolicy $nameIdPolicy = null,
        Conditions $conditions = null,
        ?bool $forceAuthn = null,
        ?bool $isPassive = null,
        ?string $assertionConsumerServiceUrl = null,
        ?int $assertionConsumerServiceIndex = null,
        ?string $protocolBinding = null,
        ?int $attributeConsumingServiceIndex = null,
        ?string $providerName = null,
        ?Issuer $issuer = null,
        ?string $id = null,
        ?int $issueInstant = null,
        ?string $destination = null,
        ?string $consent = null,
        ?Extensions $extensions = null,
        ?Scoping $scoping = null
    ) {
        parent::__construct($issuer, $id, $issueInstant, $destination, $consent, $extensions);

        $this->setRequestedAuthnContext($requestedAuthnContext);
        $this->setSubject($subject);
        $this->setNameIdPolicy($nameIdPolicy);
        $this->setConditions($conditions);

        $this->setForceAuthn($forceAuthn);
        $this->setIsPassive($isPassive);
        $this->setAssertionConsumerServiceUrl($assertionConsumerServiceUrl);
        $this->setAssertionConsumerServiceIndex($assertionConsumerServiceIndex);
        $this->setProtocolBinding($protocolBinding);
        $this->setAttributeConsumingServiceIndex($attributeConsumingServiceIndex);
        $this->setProviderName($providerName);
        $this->setScoping($scoping);
    }


    /**
     * @param \SimpleSAML\SAML2\XML\saml\Subject|null $subject
     */
    private function setSubject(?Subject $subject): void
    {
        $this->subject = $subject;
    }


    /**
     * @return \SimpleSAML\SAML2\XML\saml\Subject|null
     */
    public function getSubject(): ?Subject
    {
        return $this->subject;
    }


    /**
     * @param \SimpleSAML\SAML2\XML\samlp\Scoping|null $scoping
     */
    private function setScoping(?Scoping $scoping): void
    {
        $this->scoping = $scoping;
    }


    /**
     * @return \SimpleSAML\SAML2\XML\samlp\Scoping|null
     */
    public function getScoping(): ?Scoping
    {
        return $this->scoping;
    }


    /**
     * @param \SimpleSAML\SAML2\XML\saml\Conditions|null $conditions
     */
    private function setConditions(?Conditions $conditions): void
    {
        $this->conditions = $conditions;
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
     * Set the NameIDPolicy.
     *
     * @param \SimpleSAML\SAML2\XML\samlp\NameIDPolicy|null $nameIdPolicy The NameIDPolicy.
     */
    private function setNameIdPolicy(?NameIDPolicy $nameIdPolicy): void
    {
        $this->nameIdPolicy = $nameIdPolicy;
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
     * Set the value of the ForceAuthn attribute.
     *
     * @param bool $forceAuthn The ForceAuthn attribute.
     */
    private function setForceAuthn(?bool $forceAuthn): void
    {
        $this->forceAuthn = $forceAuthn;
    }


    /**
     * Retrieve the value of the ProviderName attribute.
     *
     * @return string|null The ProviderName attribute.
     */
    public function getProviderName(): ?string
    {
        return $this->ProviderName;
    }


    /**
     * Set the value of the ProviderName attribute.
     *
     * @param string|null $ProviderName The ProviderName attribute.
     */
    private function setProviderName(?string $ProviderName): void
    {
        Assert::nullOrNotWhitespaceOnly($ProviderName);
        $this->ProviderName = $ProviderName;
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
     * Set the value of the IsPassive attribute.
     *
     * @param bool|null $isPassive The IsPassive attribute.
     */
    private function setIsPassive(?bool $isPassive): void
    {
        $this->isPassive = $isPassive;
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
     * Set the value of the AssertionConsumerServiceURL attribute.
     *
     * @param string|null $assertionConsumerServiceURL The AssertionConsumerServiceURL attribute.
     */
    private function setAssertionConsumerServiceURL(string $assertionConsumerServiceURL = null): void
    {
        if (!is_null($assertionConsumerServiceURL) && !filter_var($assertionConsumerServiceURL, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('AuthnRequest AssertionConsumerServiceURL is not a valid URL.');
        }

        $this->assertionConsumerServiceURL = $assertionConsumerServiceURL;
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
     * Set the value of the ProtocolBinding attribute.
     *
     * @param string|null $protocolBinding The ProtocolBinding attribute.
     */
    private function setProtocolBinding(?string $protocolBinding): void
    {
        Assert::nullOrNotWhitespaceOnly($protocolBinding);

        $this->protocolBinding = $protocolBinding;
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
     * Set the value of the AttributeConsumingServiceIndex attribute.
     *
     * @param int|null $attributeConsumingServiceIndex The AttributeConsumingServiceIndex attribute.
     */
    private function setAttributeConsumingServiceIndex(?int $attributeConsumingServiceIndex): void
    {
        Assert::nullOrRange($attributeConsumingServiceIndex, 0, 65535);
        $this->attributeConsumingServiceIndex = $attributeConsumingServiceIndex;
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
     * Set the value of the AssertionConsumerServiceIndex attribute.
     *
     * @param int|null $assertionConsumerServiceIndex The AssertionConsumerServiceIndex attribute.
     */
    private function setAssertionConsumerServiceIndex(?int $assertionConsumerServiceIndex): void
    {
        Assert::nullOrRange($assertionConsumerServiceIndex, 0, 65535);
        $this->assertionConsumerServiceIndex = $assertionConsumerServiceIndex;
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
     * Set the RequestedAuthnContext.
     *
     * @param \SimpleSAML\SAML2\XML\samlp\RequestedAuthnContext|null $requestedAuthnContext The RequestedAuthnContext.
     */
    private function setRequestedAuthnContext(RequestedAuthnContext $requestedAuthnContext = null): void
    {
        $this->requestedAuthnContext = $requestedAuthnContext;
    }


    /**
     * Convert XML into an AuthnRequest
     *
     * @param \DOMElement $xml The XML element we should load
     * @return \SimpleSAML\SAML2\XML\samlp\AuthnRequest
     *
     * @throws \SimpleSAML\XML\Exception\InvalidDOMElementException if the qualified name of the supplied element is wrong
     * @throws \SimpleSAML\XML\Exception\MissingAttributeException if the supplied element is missing one of the mandatory attributes
     * @throws \SimpleSAML\XML\Exception\TooManyElementsException if too many child-elements of a type are specified
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'AuthnRequest', InvalidDOMElementException::class);
        Assert::same($xml->namespaceURI, AuthnRequest::NS, InvalidDOMElementException::class);
        Assert::same('2.0', self::getAttribute($xml, 'Version'));

        $issueInstant = self::getAttribute($xml, 'IssueInstant');
        Assert::validDateTimeZulu($issueInstant, ProtocolViolationException::class);
        $issueInstant = XMLUtils::xsDateTimeToTimestamp($issueInstant);

        $attributeConsumingServiceIndex = self::getIntegerAttribute($xml, 'AttributeConsumingServiceIndex', null);
        $assertionConsumerServiceIndex = self::getIntegerAttribute($xml, 'AssertionConsumerServiceIndex', null);

        $conditions = Conditions::getChildrenOfClass($xml);
        Assert::maxCount($conditions, 1, 'Only one <saml:Conditions> element is allowed.', TooManyElementsException::class);

        $nameIdPolicy = NameIDPolicy::getChildrenOfClass($xml);
        Assert::maxCount($nameIdPolicy, 1, 'Only one <samlp:NameIDPolicy> element is allowed.', TooManyElementsException::class);

        $subject = Subject::getChildrenOfClass($xml);
        Assert::maxCount($subject, 1, 'Only one <saml:Subject> element is allowed.', TooManyElementsException::class);

        $issuer = Issuer::getChildrenOfClass($xml);
        Assert::maxCount($issuer, 1, 'Only one <saml:Issuer> element is allowed.', TooManyElementsException::class);

        $requestedAuthnContext = RequestedAuthnContext::getChildrenOfClass($xml);
        Assert::maxCount(
            $requestedAuthnContext,
            1,
            'Only one <samlp:RequestedAuthnContext> element is allowed.',
            TooManyElementsException::class
        );

        $extensions = Extensions::getChildrenOfClass($xml);
        Assert::maxCount($extensions, 1, 'Only one <samlp:Extensions> element is allowed.', TooManyElementsException::class);

        $signature = Signature::getChildrenOfClass($xml);
        Assert::maxCount($signature, 1, 'Only one <ds:Signature> element is allowed.', TooManyElementsException::class);

        $scoping = Scoping::getChildrenOfClass($xml);
        Assert::maxCount($scoping, 1, 'Only one <samlp:Scoping> element is allowed.', TooManyElementsException::class);

        $request = new self(
            array_pop($requestedAuthnContext),
            array_pop($subject),
            array_pop($nameIdPolicy),
            array_pop($conditions),
            self::getBooleanAttribute($xml, 'ForceAuthn', null),
            self::getBooleanAttribute($xml, 'IsPassive', null),
            self::getAttribute($xml, 'AssertionConsumerServiceURL', null),
            $assertionConsumerServiceIndex,
            self::getAttribute($xml, 'ProtocolBinding', null),
            $attributeConsumingServiceIndex,
            self::getAttribute($xml, 'ProviderName', null),
            array_pop($issuer),
            self::getAttribute($xml, 'ID'),
            $issueInstant,
            self::getAttribute($xml, 'Destination', null),
            self::getAttribute($xml, 'Consent', null),
            array_pop($extensions),
            array_pop($scoping)
        );

        if (!empty($signature)) {
            $request->setSignature($signature[0]);
            $request->messageContainedSignatureUponConstruction = true;
        }

        return $request;
    }


    /**
     * Convert this authentication request to an XML element.
     *
     * @return \DOMElement This authentication request.
     * @throws \Exception
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        $parent = parent::toXML($parent);

        if ($this->forceAuthn == true) {
            $parent->setAttribute('ForceAuthn', 'true');
        }

        if (!empty($this->ProviderName)) {
            $parent->setAttribute('ProviderName', $this->ProviderName);
        }

        if ($this->isPassive === true) {
            $parent->setAttribute('IsPassive', 'true');
        }

        if ($this->assertionConsumerServiceIndex !== null) {
            $parent->setAttribute('AssertionConsumerServiceIndex', strval($this->assertionConsumerServiceIndex));
        } else {
            if ($this->assertionConsumerServiceURL !== null) {
                $parent->setAttribute('AssertionConsumerServiceURL', $this->assertionConsumerServiceURL);
            }
            if ($this->protocolBinding !== null) {
                $parent->setAttribute('ProtocolBinding', $this->protocolBinding);
            }
        }

        if ($this->attributeConsumingServiceIndex !== null) {
            $parent->setAttribute('AttributeConsumingServiceIndex', strval($this->attributeConsumingServiceIndex));
        }

        if ($this->subject !== null) {
            $this->subject->toXML($parent);
        }

        if ($this->nameIdPolicy !== null) {
            if (!$this->nameIdPolicy->isEmptyElement()) {
                $this->nameIdPolicy->toXML($parent);
            }
        }

        if ($this->conditions !== null) {
            if (!$this->conditions->isEmptyElement()) {
                $this->conditions->toXML($parent);
            }
        }

        if (!empty($this->requestedAuthnContext)) {
            $this->requestedAuthnContext->toXML($parent);
        }

        if ($this->scoping !== null) {
            if (!$this->scoping->isEmptyElement()) {
                $this->scoping->toXML($parent);
            }
        }

        return $this->signElement($parent);
    }
}
