<?php

declare(strict_types=1);

namespace SAML2\XML\samlp;

use DOMDocument;
use DOMElement;
use Exception;
use RobRichards\XMLSecLibs\XMLSecEnc;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SAML2\Constants;
use SAML2\Exception\InvalidArgumentException;
use SAML2\XML\saml\NameID;
use SAML2\XML\saml\SubjectConfirmation;
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
     * The options for what type of name identifier should be returned.
     *
     * @var \SAML2\XML\samlp\NameIDPolicy|null
     */
    private $nameIdPolicy = null;

    /**
     * Whether the Identity Provider must authenticate the user again.
     *
     * @var bool
     */
    private $forceAuthn = false;

    /**
     * Optional ProviderID attribute
     *
     * @var string|null
     */
    private $ProviderName = null;

    /**
     * Set to true if this request is passive.
     *
     * @var bool
     */
    private $isPassive = false;

    /**
     * The list of providerIDs in this request's scoping element
     *
     * @var array
     */
    private $IDPList = [];

    /**
     * The ProxyCount in this request's scoping element
     *
     * @var int|null
     */
    private $ProxyCount = null;

    /**
     * The RequesterID list in this request's scoping element
     *
     * @var array
     */
    private $RequesterID = [];

    /**
     * The URL of the assertion consumer service where the response should be delivered.
     *
     * @var string|null
     */
    private $assertionConsumerServiceURL;

    /**
     * What binding should be used when sending the response.
     *
     * @var string|null
     */
    private $protocolBinding;

    /**
     * The index of the AttributeConsumingService.
     *
     * @var int|null
     */
    private $attributeConsumingServiceIndex;

    /**
     * The index of the AssertionConsumerService.
     *
     * @var int|null
     */
    private $assertionConsumerServiceIndex;

    /**
     * What authentication context was requested.
     *
     * @var \SAML2\XML\samlp\RequestedAuthnContext|null
     */
    private $requestedAuthnContext;

    /**
     * Audiences to send in the request.
     *
     * @var array
     */
    private $audiences = [];

    /**
     * @var \SAML2\XML\saml\SubjectConfirmation[]
     */
    private $subjectConfirmation = [];

    /**
     * @var \DOMElement|null
     */
    private $encryptedNameId = null;

    /**
     * @var \SAML2\XML\saml\NameID|null
     */
    private $nameId = null;


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
        $this->parseNameIdPolicy($xml);
        $this->parseRequestedAuthnContext($xml);
        $this->parseScoping($xml);
        $this->parseConditions($xml);
    }


    /**
     * @param $xml
     * @throws \Exception
     * @return void
     */
    private function parseSubject(DOMElement $xml): void
    {
        /** @var \DOMElement[] $subject */
        $subject = Utils::xpQuery($xml, './saml_assertion:Subject');
        if (empty($subject)) {
            return;
        }

        if (count($subject) > 1) {
            throw new Exception('More than one <saml:Subject> in <saml:AuthnRequest>.');
        }
        $subject = $subject[0];

        /** @var \DOMElement[] $nameId */
        $nameId = Utils::xpQuery(
            $subject,
            './saml_assertion:NameID | ./saml_assertion:EncryptedID/xenc:EncryptedData'
        );
        if (empty($nameId)) {
            throw new Exception('Missing <saml:NameID> or <saml:EncryptedID> in <saml:Subject>.');
        } elseif (count($nameId) > 1) {
            throw new Exception('More than one <saml:NameID> or <saml:EncryptedID> in <saml:Subject>.');
        }
        $nameId = $nameId[0];
        if ($nameId->localName === 'EncryptedData') { // the NameID element is encrypted
            $this->encryptedNameId = $nameId;
        } else {
            $this->nameId = NameID::fromXML($nameId);
        }

        /** @var \DOMElement[] $subjectConfirmation */
        $subjectConfirmation = Utils::xpQuery($subject, './saml_assertion:SubjectConfirmation');
        foreach ($subjectConfirmation as $sc) {
            $this->subjectConfirmation[] = SubjectConfirmation::fromXML($sc);
        }
    }


    /**
     * @param \DOMElement $xml
     * @throws \Exception
     * @return void
     */
    protected function parseNameIdPolicy(DOMElement $xml): void
    {
        /** @var \DOMElement[] $nameIdPolicy */
        $nameIdPolicy = Utils::xpQuery($xml, './saml_protocol:NameIDPolicy');
        if (empty($nameIdPolicy)) {
            return;
        }

        $this->nameIdPolicy = NameIDPolicy::fromXML($nameIdPolicy[0]);
    }


    /**
     * @param \DOMElement $xml
     * @return void
     */
    protected function parseRequestedAuthnContext(DOMElement $xml): void
    {
        /** @var \DOMElement[] $requestedAuthnContext */
        $requestedAuthnContext = Utils::xpQuery($xml, './saml_protocol:RequestedAuthnContext');
        if (empty($requestedAuthnContext)) {
            return;
        }

        $this->requestedAuthnContext = RequestedAuthnContext::fromXML($requestedAuthnContext[0]);
    }


    /**
     * @param \DOMElement $xml
     * @throws \Exception
     * @return void
     */
    protected function parseScoping(DOMElement $xml): void
    {
        /** @var \DOMElement[] $scoping */
        $scoping = Utils::xpQuery($xml, './saml_protocol:Scoping');
        if (empty($scoping)) {
            return;
        }

        $scoping = $scoping[0];

        if ($scoping->hasAttribute('ProxyCount')) {
            $this->ProxyCount = (int) $scoping->getAttribute('ProxyCount');
        }
        /** @var \DOMElement[] $idpEntries */
        $idpEntries = Utils::xpQuery($scoping, './saml_protocol:IDPList/saml_protocol:IDPEntry');

        foreach ($idpEntries as $idpEntry) {
            if (!$idpEntry->hasAttribute('ProviderID')) {
                throw new Exception("Could not get ProviderID from Scoping/IDPEntry element in AuthnRequest object");
            }
            $this->IDPList[] = $idpEntry->getAttribute('ProviderID');
        }

        /** @var \DOMElement[] $requesterIDs */
        $requesterIDs = Utils::xpQuery($scoping, './saml_protocol:RequesterID');
        foreach ($requesterIDs as $requesterID) {
            $this->RequesterID[] = trim($requesterID->textContent);
        }
    }


    /**
     * @param \DOMElement $xml
     * @return void
     */
    protected function parseConditions(DOMElement $xml): void
    {
        /** @var \DOMElement[] $conditions */
        $conditions = Utils::xpQuery($xml, './saml_assertion:Conditions');
        if (empty($conditions)) {
            return;
        }
        $conditions = $conditions[0];

        /** @var \DOMElement[] $ar */
        $ar = Utils::xpQuery($conditions, './saml_assertion:AudienceRestriction');
        if (empty($ar)) {
            return;
        }
        $ar = $ar[0];

        /** @var \DOMElement[] $audiences */
        $audiences = Utils::xpQuery($ar, './saml_assertion:Audience');
        $this->audiences = array();
        foreach ($audiences as $a) {
            $this->audiences[] = trim($a->textContent);
        }
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
     * @return array The audiences.
     */
    public function getAudiences(): array
    {
        return $this->audiences;
    }


    /**
     * Set the audiences to send in the request.
     * This may be an empty string, in which case no audience will be sent.
     *
     * @param array $audiences The audiences.
     * @return void
     */
    public function setAudiences(array $audiences): void
    {
        $this->audiences = $audiences;
    }


    /**
     * This function sets the scoping for the request.
     * See Core 3.4.1.2 for the definition of scoping.
     * Currently we support an IDPList of idpEntries.
     *
     * Each idpEntries consists of an array, containing
     * keys (mapped to attributes) and corresponding values.
     * Allowed attributes: Loc, Name, ProviderID.
     *
     * For backward compatibility, an idpEntries can also
     * be a string instead of an array, where each string
     * is mapped to the value of attribute ProviderID.
     *
     * @param array $IDPList List of idpEntries to scope the request to.
     * @return void
     */
    public function setIDPList(array $IDPList): void
    {
        $this->IDPList = $IDPList;
    }


    /**
     * This function retrieves the list of providerIDs from this authentication request.
     * Currently we only support a list of ipd ientity id's.
     *
     * @return array List of idp EntityIDs from the request
     */
    public function getIDPList(): array
    {
        return $this->IDPList;
    }


    /**
     * @param int $ProxyCount
     * @return void
     */
    public function setProxyCount(int $ProxyCount): void
    {
        $this->ProxyCount = $ProxyCount;
    }


    /**
     * @return int|null
     */
    public function getProxyCount(): ?int
    {
        return $this->ProxyCount;
    }


    /**
     * @param array $RequesterID
     * @return void
     */
    public function setRequesterID(array $RequesterID): void
    {
        $this->RequesterID = $RequesterID;
    }


    /**
     * @return array
     */
    public function getRequesterID(): array
    {
        return $this->RequesterID;
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
    public function setRequestedAuthnContext(RequestedAuthnContext $requestedAuthnContext = null): void
    {
        $this->requestedAuthnContext = $requestedAuthnContext;
    }


    /**
     * Retrieve the NameId of the subject in the assertion.
     *
     * @throws \Exception
     * @return \SAML2\XML\saml\NameID|null The name identifier of the assertion.
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
     * @param \SAML2\XML\saml\NameID|null $nameId The name identifier of the assertion.
     * @return void
     */
    public function setNameId(NameID $nameId = null): void
    {
        $this->nameId = $nameId;
    }


    /**
     * Encrypt the NameID in the AuthnRequest.
     *
     * @param XMLSecurityKey $key The encryption key.
     * @return void
     *
     * @throws \InvalidArgumentException if assertions are false
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
        $enc->type = XMLSecEnc::Element;

        $symmetricKey = new XMLSecurityKey(XMLSecurityKey::AES128_CBC);
        $symmetricKey->generateSessionKey();
        $enc->encryptKey($key, $symmetricKey);

        /**
         * @var \DOMElement encryptedNameId
         * @psalm-suppress UndefinedClass
         */
        $this->encryptedNameId = $enc->encryptNode($symmetricKey);
        $this->nameId = null;
    }


    /**
     * Decrypt the NameId of the subject in the assertion.
     *
     * @param XMLSecurityKey $key       The decryption key.
     * @param array          $blacklist Blacklisted decryption algorithms.
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
        $this->nameId = NameID::fromXML($nameId);

        $this->encryptedNameId = null;
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
    public function setSubjectConfirmation(array $subjectConfirmation): void
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
    }


    /**
     * Convert this authentication request to an XML element.
     *
     * @return \DOMElement This authentication request.
     */
    public function toXML(?DOMElement $parent = null): DOMElement
    {
        Assert::null($parent);

        $parent = parent::toXML();

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

        $this->addSubject($parent);

        if ($this->nameIdPolicy !== null) {
            if (!$this->nameIdPolicy->isEmptyElement()) {
                $this->nameIdPolicy->toXML($parent);
            }
        }

        $this->addConditions($parent);

        if (!empty($this->requestedAuthnContext)) {
            $this->requestedAuthnContext->toXML($parent);
        }

        if ($this->ProxyCount !== null || count($this->IDPList) > 0 || count($this->RequesterID) > 0) {
            $scoping = $parent->ownerDocument->createElementNS(Constants::NS_SAMLP, 'Scoping');
            $parent->appendChild($scoping);
            if ($this->ProxyCount !== null) {
                $scoping->setAttribute('ProxyCount', strval($this->ProxyCount));
            }
            if (count($this->IDPList) > 0) {
                $idplist = $parent->ownerDocument->createElementNS(Constants::NS_SAMLP, 'IDPList');
                foreach ($this->IDPList as $provider) {
                    $idpEntry = $parent->ownerDocument->createElementNS(Constants::NS_SAMLP, 'IDPEntry');
                    if (is_string($provider)) {
                        $idpEntry->setAttribute('ProviderID', $provider);
                    } elseif (is_array($provider)) {
                        foreach ($provider as $attribute => $value) {
                            if (
                                in_array($attribute, [
                                    'ProviderID',
                                    'Loc',
                                    'Name'
                                ], true)
                            ) {
                                $idpEntry->setAttribute($attribute, $value);
                            }
                        }
                    }
                    $idplist->appendChild($idpEntry);
                }
                $scoping->appendChild($idplist);
            }

            Utils::addStrings($scoping, Constants::NS_SAMLP, 'RequesterID', false, $this->RequesterID);
        }

        return $parent;
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
        if (!empty($this->audiences)) {
            $document = $root->ownerDocument;

            $conditions = $document->createElementNS(Constants::NS_SAML, 'saml:Conditions');
            $root->appendChild($conditions);

            $ar = $document->createElementNS(Constants::NS_SAML, 'saml:AudienceRestriction');
            $conditions->appendChild($ar);

            Utils::addStrings($ar, Constants::NS_SAML, 'saml:Audience', false, $this->getAudiences());
        }
    }
}
