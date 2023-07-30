<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2;

use DOMDocument;
use DOMElement;
use Exception;
use RobRichards\XMLSecLibs\XMLSecEnc;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SimpleSAML\Assert\Assert;
use SimpleSAML\SAML2\Exception\InvalidArgumentException;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\SAML2\XML\saml\Audience;
use SimpleSAML\SAML2\XML\saml\NameID;
use SimpleSAML\SAML2\XML\saml\SubjectConfirmation;
use SimpleSAML\SAML2\XML\samlp\IDPEntry;
use SimpleSAML\SAML2\XML\samlp\IDPList;
use SimpleSAML\SAML2\XML\samlp\NameIDPolicy;
use SimpleSAML\SAML2\XML\samlp\RequestedAuthnContext;
use SimpleSAML\SAML2\XML\samlp\RequesterID;
use SimpleSAML\SAML2\XML\samlp\Scoping;
use SimpleSAML\XML\Exception\MissingElementException;
use SimpleSAML\XML\Exception\TooManyElementsException;
use SimpleSAML\XML\Utils as XMLUtils;

use function array_pop;
use function count;
use function intval;
use function in_array;
use function is_array;
use function is_string;
use function trim;

/**
 * Class for SAML 2 authentication request messages.
 *
 * @package SimpleSAMLphp
 */
class AuthnRequest extends Request
{
    /**
     * The options for what type of name identifier should be returned.
     *
     * @var \SimpleSAML\SAML2\XML\samlp\NameIDPolicy|null
     */
    private ?NameIDPolicy $nameIdPolicy = null;

    /**
     * Whether the Identity Provider must authenticate the user again.
     *
     * @var bool
     */
    private bool $forceAuthn = false;

    /**
     * Optional ProviderID attribute
     *
     * @var string|null
     */
    private ?string $ProviderName = null;

    /**
     * Set to true if this request is passive.
     *
     * @var bool
     */
    private bool $isPassive = false;

    /**
     * The list of providerIDs in this request's scoping element
     *
     * @var \SimpleSAML\SAML2\XML\samlp\Scoping|null
     */
    private ?Scoping $scoping = null;

    /**
     * The URL of the asertion consumer service where the response should be delivered.
     *
     * @var string|null
     */
    private ?string $assertionConsumerServiceURL = null;

    /**
     * What binding should be used when sending the response.
     *
     * @var string|null
     */
    private ?string $protocolBinding = null;

    /**
     * The index of the AttributeConsumingService.
     *
     * @var int|null
     */
    private ?int $attributeConsumingServiceIndex = null;

    /**
     * The index of the AssertionConsumerService.
     *
     * @var int|null
     */
    private ?int $assertionConsumerServiceIndex = null;

    /**
     * What authentication context was requested.
     *
     * @var \SimpleSAML\SAML2\XML\samlp\RequestedAuthnContext|null
     */
    private ?RequestedAuthnContext $requestedAuthnContext = null;

    /**
     * Audiences to send in the request.
     *
     * @var \SimpleSAML\SAML2\XML\saml\Audience[]
     */
    private array $audiences = [];

    /**
     * @var \SimpleSAML\SAML2\XML\saml\SubjectConfirmation[]
     */
    private array $subjectConfirmation = [];

    /**
     * @var \DOMElement|null
     */
    private ?DOMElement $encryptedNameId = null;

    /**
     * @var \SimpleSAML\SAML2\XML\saml\NameID|null
     */
    private ?NameID $nameId = null;


    /**
     * Constructor for SAML 2 authentication request messages.
     *
     * @param \DOMElement|null $xml The input message.
     * @throws \Exception
     */
    public function __construct(DOMElement $xml = null)
    {
        parent::__construct('AuthnRequest', $xml);

        if ($xml === null) {
            return;
        }

        $this->forceAuthn = Utils::parseBoolean($xml, 'ForceAuthn', false);
        $this->isPassive = Utils::parseBoolean($xml, 'IsPassive', false);

        if ($xml->hasAttribute('AssertionConsumerServiceURL')) {
            $this->assertionConsumerServiceURL = $xml->getAttribute('AssertionConsumerServiceURL');
        }

        if ($xml->hasAttribute('ProtocolBinding')) {
            $this->protocolBinding = $xml->getAttribute('ProtocolBinding');
        }

        if ($xml->hasAttribute('AttributeConsumingServiceIndex')) {
            $this->attributeConsumingServiceIndex = intval($xml->getAttribute('AttributeConsumingServiceIndex'));
        }

        if ($xml->hasAttribute('AssertionConsumerServiceIndex')) {
            $this->assertionConsumerServiceIndex = intval($xml->getAttribute('AssertionConsumerServiceIndex'));
        }

        if ($xml->hasAttribute('ProviderName')) {
            $this->ProviderName = $xml->getAttribute('ProviderName');
        }

        $this->parseSubject($xml);

        $nameIdPolicy = NameIDPolicy::getChildrenOfClass($xml);
        $this->nameIdPolicy = array_pop($nameIdPolicy);

        $requestedAuthnContext = RequestedAuthnContext::getChildrenOfClass($xml);
        $this->requestedAuthnContext = array_pop($requestedAuthnContext);

        $this->parseConditions($xml);

        $scoping = Scoping::getChildrenOfClass($xml);
        $this->scoping = array_pop($scoping);
    }


    /**
     * @param $xml
     * @throws \Exception
     * @return void
     */
    private function parseSubject(DOMElement $xml): void
    {
        $xpCache = XPath::getXPath($xml);

        /** @var \DOMElement[] $subject */
        $subject = XPath::xpQuery($xml, './saml_assertion:Subject', $xpCache);
        if (empty($subject)) {
            return;
        }

        if (count($subject) > 1) {
            throw new TooManyElementsException('More than one <saml:Subject> in <saml:AuthnRequest>.');
        }
        $subject = $subject[0];

        $xpCache = XPath::getXPath($subject);
        /** @var \DOMElement[] $nameId */
        $nameId = XPath::xpQuery(
            $subject,
            './saml_assertion:NameID | ./saml_assertion:EncryptedID/xenc:EncryptedData',
            $xpCache,
        );
        if (empty($nameId)) {
            throw new MissingElementException('Missing <saml:NameID> or <saml:EncryptedID> in <saml:Subject>.');
        } elseif (count($nameId) > 1) {
            throw new TooManyElementsException('More than one <saml:NameID> or <saml:EncryptedID> in <saml:Subject>.');
        }
        $nameId = $nameId[0];
        if ($nameId->localName === 'EncryptedData') { // the NameID element is encrypted
            $this->encryptedNameId = $nameId;
        } else {
            $this->nameId = new NameID($nameId);
        }

        /** @var \DOMElement[] $subjectConfirmation */
        $subjectConfirmation = XPath::xpQuery($subject, './saml_assertion:SubjectConfirmation', $xpCache);
        foreach ($subjectConfirmation as $sc) {
            $this->subjectConfirmation[] = new SubjectConfirmation($sc);
        }
    }


    /**
     * @param \DOMElement $xml
     * @return void
     */
    protected function parseConditions(DOMElement $xml): void
    {
        $xpCache = XPath::getXPath($xml);

        /** @var \DOMElement[] $conditions */
        $conditions = XPath::xpQuery($xml, './saml_assertion:Conditions', $xpCache);
        if (empty($conditions)) {
            return;
        }
        $conditions = $conditions[0];

        $xpCache = XPath::getXPath($conditions);
        /** @var \DOMElement[] $ar */
        $ar = XPath::xpQuery($conditions, './saml_assertion:AudienceRestriction', $xpCache);
        if (empty($ar)) {
            return;
        }
        $ar = $ar[0];

        $xpCache = XPath::getXPath($ar);
        /** @var \DOMElement[] $audiences */
        $audiences = XPath::xpQuery($ar, './saml_assertion:Audience', $xpCache);
        foreach ($audiences as $a) {
            $this->audiences[] = Audience::fromXML($a);
        }
    }


    /**
     * Retrieve the NameIdPolicy.
     *
     * @return \SimpleSAML\SAML2\XML\samlp\NameIDPolicy|null array The NameIdPolicy.
     */
    public function getNameIdPolicy(): ?NameIDPolicy
    {
        return $this->nameIdPolicy;
    }


    /**
     * Set the NameIDPolicy.
     *
     * @param \SimpleSAML\SAML2\XML\samlp\NameIDPolicy|null $nameIdPolicy The NameIDPolicy.
     * @return void
     */
    public function setNameIdPolicy(?NameIDPolicy $nameIdPolicy): void
    {
        $this->nameIdPolicy = $nameIdPolicy;
    }


    /**
     * Retrieve the value of the ForceAuthn attribute.
     *
     * @return bool The ForceAuthn attribute.
     */
    public function getForceAuthn(): bool
    {
        return $this->forceAuthn;
    }


    /**
     * Set the value of the ForceAuthn attribute.
     *
     * @param bool $forceAuthn The ForceAuthn attribute.
     * @return void
     */
    public function setForceAuthn(bool $forceAuthn): void
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
     * @param string $ProviderName The ProviderName attribute.
     * @return void
     */
    public function setProviderName(string $ProviderName): void
    {
        $this->ProviderName = $ProviderName;
    }


    /**
     * Retrieve the value of the IsPassive attribute.
     *
     * @return bool The IsPassive attribute.
     */
    public function getIsPassive(): bool
    {
        return $this->isPassive;
    }


    /**
     * Set the value of the IsPassive attribute.
     *
     * @param bool $isPassive The IsPassive attribute.
     * @return void
     */
    public function setIsPassive(bool $isPassive): void
    {
        $this->isPassive = $isPassive;
    }


    /**
     * Retrieve the audiences from the request.
     * This may be an empty string, in which case no audience is included.
     *
     * @return \SimpleSAML\SAML2\XML\saml\Audience[] The audiences.
     */
    public function getAudiences(): array
    {
        return $this->audiences;
    }


    /**
     * Set the audiences to send in the request.
     * This may be an empty string, in which case no audience will be sent.
     *
     * @param \SimpleSAML\SAML2\XML\saml\Audience[] $audiences The audiences.
     * @return void
     */
    public function setAudiences(array $audiences): void
    {
        $this->audiences = $audiences;
    }


    /**
     * @param \SimpleSAML\SAML2\XML\samlp\Scoping|null $scoping The scope.
     * @return void
     */
    public function setScoping(?Scoping $scoping): void
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
    public function setAssertionConsumerServiceURL(string $assertionConsumerServiceURL = null): void
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
    public function setProtocolBinding(string $protocolBinding = null): void
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
    public function setAttributeConsumingServiceIndex(int $attributeConsumingServiceIndex = null): void
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
    public function setAssertionConsumerServiceIndex(int $assertionConsumerServiceIndex = null): void
    {
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
     * @return void
     */
    public function setRequestedAuthnContext(?RequestedAuthnContext $requestedAuthnContext = null): void
    {
        $this->requestedAuthnContext = $requestedAuthnContext;
    }


    /**
     * Retrieve the NameId of the subject in the assertion.
     *
     * @throws \Exception
     * @return \SimpleSAML\SAML2\XML\saml\NameID|null The name identifier of the assertion.
     */
    public function getNameId(): ?NameID
    {
        if ($this->encryptedNameId !== null) {
            throw new Exception('Attempted to retrieve encrypted NameID without decrypting it first.');
        }

        return $this->nameId;
    }


    /**
     * Set the NameId of the subject in the assertion.
     *
     * @param \SimpleSAML\SAML2\XML\saml\NameID|null $nameId The name identifier of the assertion.
     * @return void
     */
    public function setNameId(NameID $nameId = null): void
    {
        $this->nameId = $nameId;
    }


    /**
     * Encrypt the NameID in the AuthnRequest.
     *
     * @param \RobRichards\XMLSecLibs\XMLSecurityKey $key The encryption key.
     * @return void
     */
    public function encryptNameId(XMLSecurityKey $key): void
    {
        Assert::notNull($this->nameId, 'Cannot encrypt NameID if no NameID has been set.');

        /* First create a XML representation of the NameID. */
        $doc  = new DOMDocument();
        $root = $doc->createElement('root');
        $doc->appendChild($root);
        /** @psalm-suppress PossiblyNullReference */
        $this->nameId->toXML($root);
        /** @var \DOMElement $nameId */
        $nameId = $root->firstChild;

        Utils::getContainer()->debugMessage($nameId, 'encrypt');

        /* Encrypt the NameID. */
        $enc = new XMLSecEnc();
        $enc->setNode($nameId);
        // @codingStandardsIgnoreStart
        $enc->type = XMLSecEnc::Element;
        // @codingStandardsIgnoreEnd

        $symmetricKey = new XMLSecurityKey(XMLSecurityKey::AES128_CBC);
        $symmetricKey->generateSessionKey();
        $enc->encryptKey($key, $symmetricKey);

        /**
         * @psalm-suppress UndefinedClass
         */
        $this->encryptedNameId = $enc->encryptNode($symmetricKey);
        $this->nameId = null;
    }


    /**
     * Decrypt the NameId of the subject in the assertion.
     *
     * @param \RobRichards\XMLSecLibs\XMLSecurityKey $key The decryption key.
     * @param array $blacklist Blacklisted decryption algorithms.
     * @return void
     */
    public function decryptNameId(XMLSecurityKey $key, array $blacklist = []): void
    {
        if ($this->encryptedNameId === null) {
            /* No NameID to decrypt. */
            return;
        }

        $nameId = Utils::decryptElement($this->encryptedNameId, $key, $blacklist);
        Utils::getContainer()->debugMessage($nameId, 'decrypt');
        $this->nameId = new NameID($nameId);

        $this->encryptedNameId = null;
    }


    /**
     * Retrieve the SubjectConfirmation elements we have in our Subject element.
     *
     * @return \SimpleSAML\SAML2\XML\saml\SubjectConfirmation[]
     */
    public function getSubjectConfirmation(): array
    {
        return $this->subjectConfirmation;
    }


    /**
     * Set the SubjectConfirmation elements that should be included in the assertion.
     *
     * @param array \SimpleSAML\SAML2\XML\saml\SubjectConfirmation[]
     * @return void
     */
    public function setSubjectConfirmation(array $subjectConfirmation): void
    {
        $this->subjectConfirmation = $subjectConfirmation;
    }


    /**
     * Convert this authentication request to an XML element.
     *
     * @return \DOMElement This authentication request.
     */
    public function toUnsignedXML(): DOMElement
    {
        $root = parent::toUnsignedXML();

        if ($this->forceAuthn) {
            $root->setAttribute('ForceAuthn', 'true');
        }

        if (!empty($this->ProviderName)) {
            $root->setAttribute('ProviderName', $this->ProviderName);
        }

        if ($this->isPassive) {
            $root->setAttribute('IsPassive', 'true');
        }

        if ($this->assertionConsumerServiceIndex !== null) {
            $root->setAttribute('AssertionConsumerServiceIndex', strval($this->assertionConsumerServiceIndex));
        } else {
            if ($this->assertionConsumerServiceURL !== null) {
                $root->setAttribute('AssertionConsumerServiceURL', $this->assertionConsumerServiceURL);
            }
            if ($this->protocolBinding !== null) {
                $root->setAttribute('ProtocolBinding', $this->protocolBinding);
            }
        }

        if ($this->attributeConsumingServiceIndex !== null) {
            $root->setAttribute('AttributeConsumingServiceIndex', strval($this->attributeConsumingServiceIndex));
        }

        $this->addSubject($root);

        if ($this->nameIdPolicy !== null) {
            $this->nameIdPolicy->toXML($root);
        }

        $this->addConditions($root);

        $this->requestedAuthnContext?->toXML($root);

        if ($this->scoping !== null) {
            $this->scoping->toXML($root);
        }

        return $root;
    }


    /**
     * Add a Subject-node to the assertion.
     *
     * @param \DOMElement $root The assertion element we should add the subject to.
     * @return void
     */
    private function addSubject(DOMElement $root): void
    {
        // If there is no nameId (encrypted or not) there is nothing to create a subject for
        if ($this->nameId === null && $this->encryptedNameId === null) {
            return;
        }

        $subject = $root->ownerDocument->createElementNS(Constants::NS_SAML, 'saml:Subject');
        $root->appendChild($subject);

        if ($this->encryptedNameId === null) {
            $this->nameId->toXML($subject);
        } else {
            $eid = $subject->ownerDocument->createElementNS(Constants::NS_SAML, 'saml:EncryptedID');
            $eid->appendChild($subject->ownerDocument->importNode($this->encryptedNameId, true));
            $subject->appendChild($eid);
        }

        foreach ($this->subjectConfirmation as $sc) {
            $sc->toXML($subject);
        }
    }


    /**
     * Add a Conditions-node to the request.
     *
     * @param \DOMElement $root The request element we should add the conditions to.
     * @return void
     */
    private function addConditions(DOMElement $root): void
    {
        if ($this->audiences !== []) {
            $document = $root->ownerDocument;

            $conditions = $document->createElementNS(Constants::NS_SAML, 'saml:Conditions');
            $root->appendChild($conditions);

            $ar = $document->createElementNS(Constants::NS_SAML, 'saml:AudienceRestriction');
            $conditions->appendChild($ar);

            foreach ($this->audiences as $audience) {
                $audience->toXML($ar);
            }
        }
    }
}
