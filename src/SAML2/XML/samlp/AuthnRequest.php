<?php

declare(strict_types=1);

namespace SAML2\XML\samlp;

use DOMElement;
use SAML2\XML\ds\Signature;
use SAML2\XML\saml\Conditions;
use SAML2\XML\saml\Issuer;
use SAML2\XML\saml\Subject;
use SAML2\Utils;
use Webmozart\Assert\Assert;

/**
 * Class for SAML 2 authentication request messages.
 *
 * @package SimpleSAMLphp
 */
class AuthnRequest extends AbstractRequest
{
    /**
     * @var \SAML2\XML\saml\Subject|null
     */
    protected $subject = null;

    /**
     * @var \SAML2\XML\samlp\Scoping|null
     */
    protected $scoping = null;

    /**
     * The options for what type of name identifier should be returned.
     *
     * @var \SAML2\XML\samlp\NameIDPolicy|null
     */
    protected $nameIdPolicy = null;

    /**
     * Whether the Identity Provider must authenticate the user again.
     *
     * @var bool|null
     */
    protected $forceAuthn = false;

    /**
     * Optional ProviderID attribute
     *
     * @var string|null
     */
    protected $ProviderName = null;

    /**
     * Set to true if this request is passive.
     *
     * @var bool|null
     */
    protected $isPassive = false;

    /**
     * The URL of the assertion consumer service where the response should be delivered.
     *
     * @var string|null
     */
    protected $assertionConsumerServiceURL;

    /**
     * What binding should be used when sending the response.
     *
     * @var string|null
     */
    protected $protocolBinding;

    /**
     * The index of the AttributeConsumingService.
     *
     * @var int|null
     */
    protected $attributeConsumingServiceIndex;

    /**
     * The index of the AssertionConsumerService.
     *
     * @var int|null
     */
    protected $assertionConsumerServiceIndex;

    /**
     * What authentication context was requested.
     *
     * @var \SAML2\XML\samlp\RequestedAuthnContext|null
     */
    protected $requestedAuthnContext;

    /**
     * @var \SAML2\XML\saml\Conditions|null
     */
    protected $conditions = null;

    /**
     * @var \SAML2\XML\saml\SubjectConfirmation[]
     */
    protected $subjectConfirmation = [];


    /**
     * Constructor for SAML 2 AuthnRequest
     *
     * @param \SAML2\XML\samlp\RequestedAuthnContext $requestedAuthnContext
     * @param \SAML2\XML\saml\Subject $subject
     * @param \SAML2\XML\samlp\NameIDPolicy $nameIdPolicy
     * @param \SAML2\XML\saml\Conditions $conditions
     * @param bool $forceAuthn
     * @param bool $isPassive
     * @param string $assertionConsumerServiceUrl
     * @param string $protocolBinding
     * @param int $attributeConsumingServiceIndex
     * @param string $providerName
     * @param \SAML2\XML\saml\Issuer|null $issuer
     * @param string|null $id
     * @param string|null $version
     * @param int|null $issueInstant
     * @param string|null $destination
     * @param string|null $consent
     * @param \SAML2\XML\samlp\Extensions|null $extensions
     * @param \SAML2\XML\samlp\Scoping|null $scoping
     */
    public function __construct(
        ?RequestedAuthnContext $requestedAuthnContext = null,
        ?Subject $subject = null,
        ?NameIDPolicy $nameIdPolicy = null,
        Conditions $conditions = null,

        ?bool $forceAuthn = null,
        ?bool $isPassive = null,
        ?string $assertionConsumerServiceUrl = null,
        ?string $protocolBinding = null,
        ?int $attributeConsumingServiceIndex = null,
        ?string $providerName = null,

        ?Issuer $issuer = null,
        ?string $id = null,
        ?string $version = null,
        ?int $issueInstant = null,
        ?string $destination = null,
        ?string $consent = null,
        ?Extensions $extensions = null,
        ?Scoping $scoping = null
    ) {
        parent::__construct($issuer, $id, $version, $issueInstant, $destination, $consent, $extensions);

        $this->setRequestedAuthnContext($requestedAuthnContext);
        $this->setSubject($subject);
        $this->setNameIdPolicy($nameIdPolicy);
        $this->setConditions($conditions);

        $this->setForceAuthn($forceAuthn);
        $this->setIsPassive($isPassive);
        $this->setAssertionConsumerServiceUrl($assertionConsumerServiceUrl);
        $this->setProtocolBinding($protocolBinding);
        $this->setAttributeConsumingServiceIndex($attributeConsumingServiceIndex);
        $this->setProviderName($providerName);
        $this->setScoping($scoping);
    }


    /**
     * @param \SAML2\XML\saml\Subject|null $subject
     * @return void
     */
    private function setSubject(?Subject $subject): void
    {
        $this->subject = $subject;
    }


    /**
     * @return \SAML2\XML\saml\Subject|null
     */
    public function getSubject(): ?Subject
    {
        return $this->subject;
    }


    /**
     * @param \SAML2\XML\saml\Scoping|null $scoping
     * @return void
     */
    private function setScoping(?Scoping $scoping): void
    {
        $this->scoping = $scoping;
    }


    /**
     * @return \SAML2\XML\saml\Scoping|null
     */
    public function getScoping(): ?Scoping
    {
        return $this->scoping;
    }


    /**
     * @param \SAML2\XML\saml\Conditions|null $conditions
     * @return void
     */
    private function setConditions(?Conditions $conditions): void
    {
        $this->conditions = $conditions;
    }


    /**
     * @return \SAML2\XML\saml\Conditions|null
     */
    public function getConditions(): ?Conditions
    {
        return $this->conditions;
    }


    /**
     * Retrieve the NameIdPolicy.
     *
     * @see \SAML2\AuthnRequest::setNameIdPolicy()
     * @return \SAML2\XML\samlp\NameIDPolicy|null The NameIdPolicy.
     */
    public function getNameIdPolicy(): ?NameIDPolicy
    {
        return $this->nameIdPolicy;
    }


    /**
     * Set the NameIDPolicy.
     *
     * @param \SAML2\XML\samlp\NameIDPolicy|null $nameIdPolicy The NameIDPolicy.
     * @return void
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
     * @return void
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
     * @return void
     */
    private function setProviderName(?string $ProviderName): void
    {
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
     * @return void
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
     * @return void
     */
    private function setAssertionConsumerServiceURL(string $assertionConsumerServiceURL = null): void
    {
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
     * @param string $protocolBinding The ProtocolBinding attribute.
     * @return void
     */
    private function setProtocolBinding(string $protocolBinding = null): void
    {
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
     * @return void
     */
    private function setAttributeConsumingServiceIndex(int $attributeConsumingServiceIndex = null): void
    {
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
     * @return void
     */
    private function setAssertionConsumerServiceIndex(int $assertionConsumerServiceIndex = null): void
    {
        $this->assertionConsumerServiceIndex = $assertionConsumerServiceIndex;
    }


    /**
     * Retrieve the RequestedAuthnContext.
     *
     * @return \SAML2\XML\samlp\RequestedAuthnContext|null The RequestedAuthnContext.
     */
    public function getRequestedAuthnContext(): ?RequestedAuthnContext
    {
        return $this->requestedAuthnContext;
    }


    /**
     * Set the RequestedAuthnContext.
     *
     * @param \SAML2\XML\samlp\RequestedAuthnContext|null $requestedAuthnContext The RequestedAuthnContext.
     * @return void
     */
    private function setRequestedAuthnContext(RequestedAuthnContext $requestedAuthnContext = null): void
    {
        $this->requestedAuthnContext = $requestedAuthnContext;
    }


    /**
     * Retrieve the SubjectConfirmation elements we have in our Subject element.
     *
     * @return \SAML2\XML\saml\SubjectConfirmation[]
     */
    public function getSubjectConfirmation(): array
    {
        return $this->subjectConfirmation;
    }


    /**
     * Set the SubjectConfirmation elements that should be included in the assertion.
     *
     * @param array \SAML2\XML\saml\SubjectConfirmation[]
     * @return void
     */
    private function setSubjectConfirmation(array $subjectConfirmation): void
    {
        $this->subjectConfirmation = $subjectConfirmation;
    }


    /**
     * Convert XML into an AuthnRequest
     *
     * @param \DOMElement $xml The XML element we should load
     * @return \SAML2\XML\samlp\AuthnRequest
     * @throws \InvalidArgumentException if the qualified name of the supplied element is wrong
     */
    public static function fromXML(DOMElement $xml): object
    {
        Assert::same($xml->localName, 'AuthnRequest');
        Assert::same($xml->namespaceURI, AuthnRequest::NS);

        $id = self::getAttribute($xml, 'ID');
        $version = self::getAttribute($xml, 'Version');
        $issueInstant = Utils::xsDateTimeToTimestamp(self::getAttribute($xml, 'IssueInstant'));
        $inResponseTo = self::getAttribute($xml, 'InResponseTo', null);
        $destination = self::getAttribute($xml, 'Destination', null);
        $consent = self::getAttribute($xml, 'Consent', null);

        $forceAuthn = self::getBooleanAttribute($xml, 'ForceAuthn', 'false');
        $isPassive = self::getBooleanAttribute($xml, 'IsPassive', 'false');

        $assertionConsumerServiceUrl = self::getAttribute($xml, 'AssertionConsumerServiceURL', null);
        $protocolBinding = self::getAttribute($xml, 'ProtocolBinding', null);

        $attributeConsumingServiceIndex = self::getAttribute($xml, 'AttributeConsumingServiceIndex', null);
        if ($attributeConsumingServiceIndex !== null) {
            $attributeConsumingServiceIndex = intval($attributeConsumingServiceIndex);
        }
        $providerName = self::getAttribute($xml, 'ProviderName', null);

        $conditions = Conditions::getChildrenOfClass($xml);
        Assert::maxCount($conditions, 1, 'Only one <saml:Conditions> element is allowed.');

        $nameIdPolicy = NameIDPolicy::getChildrenOfClass($xml);
        Assert::maxCount($nameIdPolicy, 1, 'Only one <samlp:NameIDPolicy> element is allowed.');

        $subject = Subject::getChildrenOfClass($xml);
        Assert::maxCount($subject, 1, 'Only one <saml:Subject> element is allowed.');

        $issuer = Issuer::getChildrenOfClass($xml);
        Assert::maxCount($issuer, 1, 'Only one <saml:Issuer> element is allowed.');

        $requestedAuthnContext = RequestedAuthnContext::getChildrenOfClass($xml);
        Assert::maxCount($requestedAuthnContext, 1, 'Only one <samlp:RequestedAuthnContext> element is allowed.');

        $extensions = Extensions::getChildrenOfClass($xml);
        Assert::maxCount($extensions, 1, 'Only one <samlp:Extensions> element is allowed.');

        $signature = Signature::getChildrenOfClass($xml);
        Assert::maxCount($signature, 1, 'Only one <ds:Signature> element is allowed.');

        $scoping = Scoping::getChildrenOfClass($xml);
        Assert::maxCount($scoping, 1, 'Only one <samlp:Scoping> element is allowed.');


        $request = new self(
            array_pop($requestedAuthnContext),
            array_pop($subject),
            array_pop($nameIdPolicy),
            array_pop($conditions),

            $forceAuthn,
            $isPassive,
            $assertionConsumerServiceUrl,
            $protocolBinding,
            $attributeConsumingServiceIndex,
            $providerName,

            array_pop($issuer),
            $id,
            $version,
            $issueInstant,
            $destination,
            $consent,
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
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        Assert::null($parent);

        $parent = parent::toXML($parent);

        if ($this->forceAuthn) {
            $parent->setAttribute('ForceAuthn', 'true');
        }

        if (!empty($this->ProviderName)) {
            $parent->setAttribute('ProviderName', $this->ProviderName);
        }

        if ($this->isPassive) {
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
